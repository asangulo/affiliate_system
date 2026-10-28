<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Las rutas pasan de decidir por nombre de rol a exigir permisos. Para que nadie pierda
 * el acceso que ya tenía, se crean los permisos nuevos y se asignan a los roles existentes
 * tal como estaban funcionando antes:
 *  - super_admin: todos.
 *  - empleado: panel principal y activar/inactivar afiliados (antes entraba a ambos por su rol).
 * En una instalación nueva los roles todavía no existen: los crea el seeder con sus permisos.
 */
return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $nuevos = [
            'Ver panel principal',
            'Ver planes', 'Crear planes', 'Editar planes', 'Eliminar planes',
        ];

        foreach ($nuevos as $nombre) {
            Permission::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);
        }

        if ($superAdmin = Role::where('name', 'super_admin')->where('guard_name', 'web')->first()) {
            $superAdmin->syncPermissions(Permission::all());
        }

        if ($empleado = Role::where('name', 'empleado')->where('guard_name', 'web')->first()) {
            $empleado->givePermissionTo(
                Permission::whereIn('name', ['Ver panel principal', 'Activar o inactivar afiliados'])->get()
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Datos de acceso: no se revierten.
    }
};
