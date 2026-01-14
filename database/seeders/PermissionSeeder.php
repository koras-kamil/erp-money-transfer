<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
 public function run(): void
{
    $permissions = [
        'user-view', 'user-create', 'user-edit', 'user-delete',
        'currency-manage', 'report-view', 'branch-manage'
    ];

    foreach ($permissions as $permission) {
        \Spatie\Permission\Models\Permission::create(['name' => $permission]);
    }
}
}
