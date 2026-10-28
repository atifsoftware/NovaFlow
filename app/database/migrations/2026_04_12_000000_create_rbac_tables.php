<?php

use NovaFlow\Core\QueryBuilder\Schema;
use NovaFlow\Core\QueryBuilder\Blueprint;

/**
 * Migration for Dynamic RBAC Tables
 */
class CreateRbacTables
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // 1. Roles table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });

        // 2. Permissions table
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('module', 50)->default('general');
            $table->timestamps();
        });

        // 3. Role Permissions pivot
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->integer('role_id')->index();
            $table->integer('permission_id')->index();
        });

        // 4. User Roles pivot
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->index();
            $table->integer('role_id')->index();
        });

        // 5. User direct permissions override pivot
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->index();
            $table->integer('permission_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('user_permissions');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
}
