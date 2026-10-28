<?php

namespace NovaFlow\Core;

/**
 * RBAC - Dynamic Role-Based Access Control
 * Enterprise permission engine with role hierarchies, wildcard matching, and database backing.
 */
class RBAC
{
    private static array $permissionCache = [];
    private static array $roleCache = [];

    /**
     * Check if a user has a specific permission
     * Supports exact matches, wildcards (e.g. 'sales.*'), and super-admin '*'
     */
    public static function hasPermission(mixed $user, string $permission): bool
    {
        $userId = self::resolveUserId($user);
        if ($userId <= 0) {
            return false;
        }

        // Super-admin role bypass
        $roles = self::getUserRoles($userId);
        if (in_array('admin', $roles, true) || in_array('super_admin', $roles, true)) {
            return true;
        }

        $permissions = self::getUserPermissions($userId);

        if (in_array('*', $permissions, true)) {
            return true;
        }

        if (in_array($permission, $permissions, true)) {
            return true;
        }

        // Wildcard check: 'sales.*' matches 'sales.invoices.view' or 'sales.create'
        foreach ($permissions as $p) {
            if (str_ends_with($p, '.*')) {
                $prefix = substr($p, 0, -2);
                if (str_starts_with($permission, $prefix . '.') || $permission === $prefix) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if current authenticated user has permission
     */
    public static function can(string $permission, mixed $user = null): bool
    {
        return self::hasPermission($user, $permission);
    }

    /**
     * Check if a user has one or any of the given roles
     */
    public static function hasRole(mixed $user, string|array $roles): bool
    {
        $userId = self::resolveUserId($user);
        if ($userId <= 0) {
            return false;
        }

        $userRoles = self::getUserRoles($userId);
        $rolesToCheck = is_array($roles) ? $roles : explode(',', $roles);
        $rolesToCheck = array_map('trim', $rolesToCheck);

        foreach ($rolesToCheck as $role) {
            if (in_array($role, $userRoles, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all roles for a user
     */
    public static function getUserRoles(mixed $user): array
    {
        $userId = self::resolveUserId($user);
        if ($userId <= 0) {
            return [];
        }

        if (isset(self::$roleCache[$userId])) {
            return self::$roleCache[$userId];
        }

        $roles = [];

        // Check fallback session role if user matches current session
        if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $userId && !empty($_SESSION['role'])) {
            $roles[] = $_SESSION['role'];
        }

        // Fetch from user_roles and roles table
        try {
            $dbRoles = DB::table('user_roles')
                ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                ->where('user_roles.user_id', $userId)
                ->select('roles.slug')
                ->get();

            foreach ($dbRoles as $r) {
                $slug = is_object($r) ? $r->slug : ($r['slug'] ?? null);
                if ($slug && !in_array($slug, $roles, true)) {
                    $roles[] = $slug;
                }
            }
        } catch (\Throwable $e) {
            // Table might not exist or running in mock environment
        }

        self::$roleCache[$userId] = $roles;
        return $roles;
    }

    /**
     * Get all permission slugs for a user
     */
    public static function getUserPermissions(mixed $user): array
    {
        $userId = self::resolveUserId($user);
        if ($userId <= 0) {
            return [];
        }

        if (isset(self::$permissionCache[$userId])) {
            return self::$permissionCache[$userId];
        }

        $permissions = [];

        // 1. Check permissions from session if available
        if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $userId && !empty($_SESSION['permissions'])) {
            $sessPerms = $_SESSION['permissions'];
            if (is_string($sessPerms)) {
                $sessPerms = json_decode($sessPerms, true) ?: [];
            }
            if (is_array($sessPerms)) {
                foreach ($sessPerms as $k => $v) {
                    if ($v === true || $v === 1 || $v === '1') {
                        $permissions[] = is_numeric($k) ? $v : $k;
                    }
                }
            }
        }

        // 2. Fetch role permissions from DB
        try {
            $rolePerms = DB::table('user_roles')
                ->join('role_permissions', 'user_roles.role_id', '=', 'role_permissions.role_id')
                ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
                ->where('user_roles.user_id', $userId)
                ->select('permissions.slug')
                ->get();

            foreach ($rolePerms as $p) {
                $slug = is_object($p) ? $p->slug : ($p['slug'] ?? null);
                if ($slug && !in_array($slug, $permissions, true)) {
                    $permissions[] = $slug;
                }
            }

            // 3. Direct user permission overrides
            $directPerms = DB::table('user_permissions')
                ->join('permissions', 'user_permissions.permission_id', '=', 'permissions.id')
                ->where('user_permissions.user_id', $userId)
                ->select('permissions.slug')
                ->get();

            foreach ($directPerms as $p) {
                $slug = is_object($p) ? $p->slug : ($p['slug'] ?? null);
                if ($slug && !in_array($slug, $permissions, true)) {
                    $permissions[] = $slug;
                }
            }
        } catch (\Throwable $e) {
            // DB tables might not exist
        }

        self::$permissionCache[$userId] = $permissions;
        return $permissions;
    }

    /**
     * Assign a role to a user
     */
    public static function assignRole(mixed $user, string|int $role): bool
    {
        $userId = self::resolveUserId($user);
        $roleId = self::resolveRoleId($role);

        if (!$userId || !$roleId) {
            return false;
        }

        $exists = DB::table('user_roles')
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->exists();

        if (!$exists) {
            DB::table('user_roles')->insert([
                'user_id' => $userId,
                'role_id' => $roleId
            ]);
        }

        self::clearCache($userId);
        return true;
    }

    /**
     * Remove a role from a user
     */
    public static function removeRole(mixed $user, string|int $role): bool
    {
        $userId = self::resolveUserId($user);
        $roleId = self::resolveRoleId($role);

        if (!$userId || !$roleId) {
            return false;
        }

        DB::table('user_roles')
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->delete();

        self::clearCache($userId);
        return true;
    }

    /**
     * Give a permission to a role
     */
    public static function givePermissionToRole(string|int $role, string|int|array $permissions): bool
    {
        $roleId = self::resolveRoleId($role);
        if (!$roleId) {
            return false;
        }

        $perms = is_array($permissions) ? $permissions : [$permissions];

        foreach ($perms as $perm) {
            $permId = self::resolvePermissionId($perm);
            if ($permId) {
                $exists = DB::table('role_permissions')
                    ->where('role_id', $roleId)
                    ->where('permission_id', $permId)
                    ->exists();

                if (!$exists) {
                    DB::table('role_permissions')->insert([
                        'role_id' => $roleId,
                        'permission_id' => $permId
                    ]);
                }
            }
        }

        self::clearCache();
        return true;
    }

    /**
     * Create a role dynamically
     */
    public static function createRole(string $name, string $slug, string $description = ''): ?int
    {
        $existing = DB::table('roles')->where('slug', $slug)->first();
        if ($existing) {
            return is_object($existing) ? $existing->id : $existing['id'];
        }

        return DB::table('roles')->insertGetId([
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Create a permission dynamically
     */
    public static function createPermission(string $name, string $slug, string $module = 'general'): ?int
    {
        $existing = DB::table('permissions')->where('slug', $slug)->first();
        if ($existing) {
            return is_object($existing) ? $existing->id : $existing['id'];
        }

        return DB::table('permissions')->insertGetId([
            'name' => $name,
            'slug' => $slug,
            'module' => $module,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Clear cached permissions and roles
     */
    public static function clearCache(?int $userId = null): void
    {
        if ($userId !== null) {
            unset(self::$permissionCache[$userId], self::$roleCache[$userId]);
        } else {
            self::$permissionCache = [];
            self::$roleCache = [];
        }
    }

    protected static function resolveUserId(mixed $user): int
    {
        if (is_numeric($user)) {
            return (int) $user;
        }
        if (is_object($user) && isset($user->id)) {
            return (int) $user->id;
        }
        if ($user === null && isset($_SESSION['user_id'])) {
            return (int) $_SESSION['user_id'];
        }
        return 0;
    }

    protected static function resolveRoleId(string|int $role): ?int
    {
        if (is_numeric($role)) {
            return (int) $role;
        }
        $r = DB::table('roles')->where('slug', $role)->first();
        if (!$r) {
            return self::createRole(ucfirst($role), $role);
        }
        return (int)(is_object($r) ? $r->id : $r['id']);
    }

    protected static function resolvePermissionId(string|int $permission): ?int
    {
        if (is_numeric($permission)) {
            return (int) $permission;
        }
        $p = DB::table('permissions')->where('slug', $permission)->first();
        if (!$p) {
            return self::createPermission(ucwords(str_replace(['.', '_', '-'], ' ', $permission)), $permission);
        }
        return (int)(is_object($p) ? $p->id : $p['id']);
    }
}
