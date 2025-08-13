<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $rolesWithPermissions = '[{"name":"panel_user","guard_name":"web","permissions":[]},{"name":"super_admin","guard_name":"web","permissions":["view_activity::log","view_any_activity::log","create_activity::log","update_activity::log","restore_activity::log","restore_any_activity::log","replicate_activity::log","reorder_activity::log","delete_activity::log","delete_any_activity::log","force_delete_activity::log","force_delete_any_activity::log","view_channel","view_any_channel","create_channel","update_channel","restore_channel","restore_any_channel","replicate_channel","reorder_channel","delete_channel","delete_any_channel","force_delete_channel","force_delete_any_channel","view_division","view_any_division","create_division","update_division","restore_division","restore_any_division","replicate_division","reorder_division","delete_division","delete_any_division","force_delete_division","force_delete_any_division","view_guide::template","view_any_guide::template","create_guide::template","update_guide::template","restore_guide::template","restore_any_guide::template","replicate_guide::template","reorder_guide::template","delete_guide::template","delete_any_guide::template","force_delete_guide::template","force_delete_any_guide::template","view_level","view_any_level","create_level","update_level","restore_level","restore_any_level","replicate_level","reorder_level","delete_level","delete_any_level","force_delete_level","force_delete_any_level","view_role","view_any_role","create_role","update_role","delete_role","delete_any_role","view_scale","view_any_scale","create_scale","update_scale","restore_scale","restore_any_scale","replicate_scale","reorder_scale","delete_scale","delete_any_scale","force_delete_scale","force_delete_any_scale","view_template::item","view_any_template::item","create_template::item","update_template::item","restore_template::item","restore_any_template::item","replicate_template::item","reorder_template::item","delete_template::item","delete_any_template::item","force_delete_template::item","force_delete_any_template::item","view_user","view_any_user","create_user","update_user","restore_user","restore_any_user","replicate_user","reorder_user","delete_user","delete_any_user","force_delete_user","force_delete_any_user","page_ReporteGuiasRespondidas","view_evaluation::session","view_any_evaluation::session","create_evaluation::session","update_evaluation::session","restore_evaluation::session","restore_any_evaluation::session","replicate_evaluation::session","reorder_evaluation::session","delete_evaluation::session","delete_any_evaluation::session","force_delete_evaluation::session","force_delete_any_evaluation::session","view_cycle","view_any_cycle","create_cycle","update_cycle","restore_cycle","restore_any_cycle","replicate_cycle","reorder_cycle","delete_cycle","delete_any_cycle","force_delete_cycle","force_delete_any_cycle","view_evaluation::session::signature","view_any_evaluation::session::signature","create_evaluation::session::signature","update_evaluation::session::signature","restore_evaluation::session::signature","restore_any_evaluation::session::signature","replicate_evaluation::session::signature","reorder_evaluation::session::signature","delete_evaluation::session::signature","delete_any_evaluation::session::signature","force_delete_evaluation::session::signature","force_delete_any_evaluation::session::signature","view_guide::group","view_any_guide::group","create_guide::group","update_guide::group","restore_guide::group","restore_any_guide::group","replicate_guide::group","reorder_guide::group","delete_guide::group","delete_any_guide::group","force_delete_guide::group","force_delete_any_guide::group","view_guide::item::response","view_any_guide::item::response","create_guide::item::response","update_guide::item::response","restore_guide::item::response","restore_any_guide::item::response","replicate_guide::item::response","reorder_guide::item::response","delete_guide::item::response","delete_any_guide::item::response","force_delete_guide::item::response","force_delete_any_guide::item::response","view_guide::response","view_any_guide::response","create_guide::response","update_guide::response","restore_guide::response","restore_any_guide::response","replicate_guide::response","reorder_guide::response","delete_guide::response","delete_any_guide::response","force_delete_guide::response","force_delete_any_guide::response"]},{"name":"administrador","guard_name":"web","permissions":[]},{"name":"auditor","guard_name":"web","permissions":[]},{"name":"coach","guard_name":"web","permissions":["view_guide::template","view_any_guide::template","view_template::item","view_any_template::item","view_user","view_any_user","page_ReporteGuiasRespondidas","view_evaluation::session","view_any_evaluation::session","create_evaluation::session","update_evaluation::session","delete_evaluation::session","evaluate_participants","view_own_evaluations","sign_evaluations","export_evaluation_results"]},{"name":"coachee","guard_name":"web","permissions":["view_evaluation::session","view_any_evaluation::session","reorder_evaluation::session","view_own_evaluations","sign_evaluations","export_evaluation_results"]}]';
        $directPermissions = '[]';

        static::makeRolesWithPermissions($rolesWithPermissions);
        static::makeDirectPermissions($directPermissions);

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (! blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            /** @var Model $roleModel */
            $roleModel = Utils::getRoleModel();
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($rolePlusPermissions as $rolePlusPermission) {
                $role = $roleModel::firstOrCreate([
                    'name' => $rolePlusPermission['name'],
                    'guard_name' => $rolePlusPermission['guard_name'],
                ]);

                if (! blank($rolePlusPermission['permissions'])) {
                    $permissionModels = collect($rolePlusPermission['permissions'])
                        ->map(fn ($permission) => $permissionModel::firstOrCreate([
                            'name' => $permission,
                            'guard_name' => $rolePlusPermission['guard_name'],
                        ]))
                        ->all();

                    $role->syncPermissions($permissionModels);
                }
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (! blank($permissions = json_decode($directPermissions, true))) {
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($permissions as $permission) {
                if ($permissionModel::whereName($permission)->doesntExist()) {
                    $permissionModel::create([
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'],
                    ]);
                }
            }
        }
    }
}
