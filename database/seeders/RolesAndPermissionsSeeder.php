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
        //  Nombres en español legible (se muestran tal cual en
        //  Roles y Permisos). El mapeo "antiguo → nuevo" permite
        //  renombrar los permisos ya sembrados con la notación
        //  anterior (affiliates.view, etc.) sin duplicarlos.
        // ══════════════════════════════════════════════════════

        $permissions = [
            // Afiliados
            'affiliates.view'          => 'Ver afiliados',
            'affiliates.create'        => 'Crear afiliados',
            'affiliates.edit'          => 'Editar afiliados',
            'affiliates.toggle_status' => 'Activar o inactivar afiliados',
            'affiliates.import'        => 'Importar afiliados',

            // Beneficiarios
            'beneficiaries.view'   => 'Ver beneficiarios',
            'beneficiaries.create' => 'Crear beneficiarios',
            'beneficiaries.edit'   => 'Editar beneficiarios',

            // Usuarios (admin)
            'users.view'          => 'Ver usuarios',
            'users.create'        => 'Crear usuarios',
            'users.edit'          => 'Editar usuarios',
            'users.toggle_status' => 'Activar o inactivar usuarios',
            'users.assign_role'   => 'Asignar roles',

            // Roles
            'roles.view' => 'Ver roles y permisos',

            // Portal cliente
            'portal.view' => 'Ver portal del cliente',

            // Sedes
            'branches.view'   => 'Ver sedes',
            'branches.create' => 'Crear sedes',
            'branches.edit'   => 'Editar sedes',
            'branches.delete' => 'Eliminar sedes',

            // Pagos
            'payments.view'     => 'Ver pagos',
            'payments.register' => 'Registrar pagos',
            'payments.void'     => 'Anular pagos',
            'payments.view_own' => 'Ver mis pagos',

            // Historial
            'activity.view' => 'Ver historial',

            // Reportes
            'reports.view' => 'Ver reportes',
        ];

        foreach ($permissions as $anterior => $nuevo) {
            $existente = Permission::where('name', $anterior)->where('guard_name', 'web')->first();

            if ($existente) {
                $existente->update(['name' => $nuevo]);
            } else {
                Permission::firstOrCreate(['name' => $nuevo, 'guard_name' => 'web']);
            }
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
            'Ver afiliados',
            'Crear afiliados',
            'Editar afiliados',
            'Importar afiliados',
            'Ver beneficiarios',
            'Crear beneficiarios',
            'Editar beneficiarios',
            'Ver pagos',
            'Registrar pagos',
            'Anular pagos',
            'Ver reportes',
        ]);

        // CLIENTE — solo ve su portal personal
        $cliente = Role::firstOrCreate(['name' => 'cliente', 'guard_name' => 'web']);
        $cliente->syncPermissions([
            'Ver portal del cliente',
            'Ver mis pagos',
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