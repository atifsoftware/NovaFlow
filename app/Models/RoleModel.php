<?php

namespace App\Models;

use NovaFlow\Core\Model;

class RoleModel extends Model
{
    protected string $table = 'roles';
    protected array $allowedFields = ['name', 'slug', 'description'];

    /**
     * Users assigned to this role
     */
    public function users()
    {
        return $this->hasMany(UserModel::class, 'role_id');
    }
}
