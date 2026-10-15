<?php

namespace App\Modules\HRM\Models;

use NovaFlow\Core\Model;

class HRM extends Model
{
    protected static string $table = 'hrms';
    protected static array $fillable = ['name', 'status', 'description'];
}
