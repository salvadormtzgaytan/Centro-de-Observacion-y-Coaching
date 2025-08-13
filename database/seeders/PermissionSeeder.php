<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        $customPermissions = [
            'evaluate_participants',
            'view_own_evaluations',
            'sign_evaluations',
            'export_evaluation_results'
        ];

        foreach ($customPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Asignar a roles
        Role::firstWhere('name', 'coach')->givePermissionTo(['evaluate_participants']);
        Role::firstWhere('name', 'coachee')->givePermissionTo(['view_own_evaluations']);
    }
}