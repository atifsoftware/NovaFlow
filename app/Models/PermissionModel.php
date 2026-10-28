<?php

namespace App\Models;

use NovaFlow\Core\Model;

class PermissionModel extends Model
{
    protected string $table = 'permissions';
    protected array $allowedFields = ['name', 'slug', 'module'];
}
