<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;       // <--- Add this
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    // Reset cached roles and permissions
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    // 1. CREATE PERMISSIONS
    // Cashbox Permissions
    Permission::create(['name' => 'view cashboxes']);
    Permission::create(['name' => 'create cashboxes']);
    Permission::create(['name' => 'edit cashboxes']);
    Permission::create(['name' => 'delete cashboxes']);

    // Log Permissions
    Permission::create(['name' => 'view logs']);

    // 2. CREATE ROLES & ASSIGN PERMISSIONS
    
    // Accountant: Can only view and create
    $accountant = Role::create(['name' => 'accountant']);
    $accountant->givePermissionTo(['view cashboxes', 'create cashboxes']);

    // Manager: Can do everything with cashboxes
    $manager = Role::create(['name' => 'manager']);
    $manager->givePermissionTo(Permission::all());

    // Super Admin: Has all permissions (including logs)
    $superAdmin = Role::create(['name' => 'super-admin']);
    // We don't need to give specific permissions to Super Admin 
    // because we will use a "Gate bypass" next.
}
}
