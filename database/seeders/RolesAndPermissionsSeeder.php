<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Ejecutar con: php artisan db:seed --class=RolesAndPermissionsSeeder
 *
 * O agregar al DatabaseSeeder:
 *   $this->call(RolesAndPermissionsSeeder::class);
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar caché de permisos de Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ══════════════════════════════════════════════════════
        //  PERMISOS
        // ══════════════════════════════════════════════════════

        $permissions = [
            // Afiliados
            'affiliates.view',
            'affiliates.create',
            'affiliates.edit',
            'affiliates.toggle_status',
            'affiliates.import',

            // Beneficiarios
            'beneficiaries.view',
            'beneficiaries.create',
            'beneficiaries.edit',

            // Usuarios (admin)
            'users.view',
            'users.create',
            'users.edit',
            'users.toggle_status',
            'users.assign_role',

            // Roles
            'roles.view',

            // Portal cliente
            'portal.view',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // ══════════════════════════════════════════════════════
        //  ROLES Y ASIGNACIÓN DE PERMISOS
        // ══════════════════════════════════════════════════════

        // SUPER ADMIN — todo el sistema
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // EMPLEADO — gestión de afiliados, sin admin de usuarios/roles
        $empleado = Role::firstOrCreate(['name' => 'empleado', 'guard_name' => 'web']);
        $empleado->syncPermissions([
            'affiliates.view',
            'affiliates.create',
            'affiliates.edit',
            'affiliates.import',
            'beneficiaries.view',
            'beneficiaries.create',
            'beneficiaries.edit',
        ]);

        // CLIENTE — solo ve su portal personal
        $cliente = Role::firstOrCreate(['name' => 'cliente', 'guard_name' => 'web']);
        $cliente->syncPermissions([
            'portal.view',
        ]);

        // ══════════════════════════════════════════════════════
        //  USUARIO SUPER ADMIN POR DEFECTO
        //  ⚠️  Cambia el email y contraseña antes de producción
        // ══════════════════════════════════════════════════════
        $admin = User::firstOrCreate(
            ['email' => 'admin@horizonte.com'],
            [
                'name'      => 'Administrador',
                'password'  => Hash::make('Horizonte2024!'),
                'is_active' => true,
            ]
        );

        $admin->assignRole('super_admin');

        $this->command->info('✅ Roles, permisos y usuario admin creados correctamente.');
        $this->command->warn('⚠️  Recuerda cambiar la contraseña del admin en producción.');
    }
}