<?php

namespace Tests\Unit\Auth;

use PHPUnit\Framework\TestCase;
use NovaFlow\Core\Controller;

class TestRBACController extends Controller
{
    public function publicGetUserId(): int
    {
        return $this->getUserId();
    }

    public function publicGetUserRole(): string
    {
        return $this->getUserRole();
    }

    public function publicIsAdmin(): bool
    {
        return $this->isAdmin();
    }

    public function publicIsManager(): bool
    {
        return $this->isManager();
    }

    public function publicGetAssignedShopIds(): array
    {
        return $this->getAssignedShopIds();
    }
}

class RBACTest extends TestCase
{
    private TestRBACController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        $this->controller = new TestRBACController();
    }

    public function testDefaultGuestValuesWhenNoSession(): void
    {
        $this->assertEquals(0, $this->controller->publicGetUserId());
        $this->assertEquals('viewer', $this->controller->publicGetUserRole());
        $this->assertFalse($this->controller->publicIsAdmin());
        $this->assertFalse($this->controller->publicIsManager());
        $this->assertEmpty($this->controller->publicGetAssignedShopIds());
        $this->assertFalse($this->controller->hasShopAccess(1));
        $this->assertFalse($this->controller->hasPermission('dashboard'));
    }

    public function testAdminRoleHasOmnipotentAccess(): void
    {
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'admin';

        $this->assertTrue($this->controller->publicIsAdmin());
        $this->assertFalse($this->controller->publicIsManager());

        // Admin has access to all shops regardless of assignment
        $this->assertTrue($this->controller->hasShopAccess(1));
        $this->assertTrue($this->controller->hasShopAccess(9999));

        // Admin has access to any module
        $this->assertTrue($this->controller->hasPermission('sales'));
        $this->assertTrue($this->controller->hasPermission('super_secret_module'));
    }

    public function testManagerShopMultiTenantAccess(): void
    {
        $_SESSION['user_id'] = 5;
        $_SESSION['role'] = 'manager';
        $_SESSION['assigned_shop_ids'] = [10, 20];

        $this->assertFalse($this->controller->publicIsAdmin());
        $this->assertTrue($this->controller->publicIsManager());

        // Has access to assigned shops
        $this->assertTrue($this->controller->hasShopAccess(10));
        $this->assertTrue($this->controller->hasShopAccess(20));

        // Unauthorized shop access blocked
        $this->assertFalse($this->controller->hasShopAccess(30), "Manager must not have access to unassigned shop 30");
        $this->assertFalse($this->controller->hasShopAccess(1), "Manager must not have access to unassigned shop 1");
    }

    public function testGranularModulePermissionsWithArray(): void
    {
        $_SESSION['user_id'] = 12;
        $_SESSION['role'] = 'staff';
        $_SESSION['permissions'] = [
            'invoicing' => true,
            'inventory' => true,
            'settings' => false
        ];

        $this->assertTrue($this->controller->hasPermission('invoicing'));
        $this->assertTrue($this->controller->hasPermission('inventory'));
        $this->assertFalse($this->controller->hasPermission('settings'));
        $this->assertFalse($this->controller->hasPermission('payroll')); // Unset permission
    }

    public function testGranularModulePermissionsWithJsonEncodedString(): void
    {
        $_SESSION['user_id'] = 15;
        $_SESSION['role'] = 'auditor';
        $_SESSION['permissions'] = json_encode([
            'reports' => true,
            'audit_log' => true,
            'delete_records' => false
        ]);

        $this->assertTrue($this->controller->hasPermission('reports'));
        $this->assertTrue($this->controller->hasPermission('audit_log'));
        $this->assertFalse($this->controller->hasPermission('delete_records'));
        $this->assertFalse($this->controller->hasPermission('edit_users'));
    }
}
