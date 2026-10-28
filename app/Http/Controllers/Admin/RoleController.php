<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    // Roles propios del sistema: no se pueden eliminar ni renombrar.
    private const ROLES_PROTEGIDOS = ['super_admin', 'empleado', 'cliente'];

    public function index()
    {
        return Inertia::render('Admin/Roles/Index', [
            'roles' => Role::with('permissions')->withCount('users')->orderBy('name')->get(),
            'permissions' => Permission::orderBy('name')->get(['id', 'name']),
            'protectedRoles' => self::ROLES_PROTEGIDOS,
        ]);
    }

    // ── ROLES ─────────────────────────────────────────────────
    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:60|unique:roles,name',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);
        $role->syncPermissions($validated['permissions'] ?? []);

        return back()->with('message', "Rol \"{$role->name}\" creado correctamente.");
    }

    public function updateRole(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        // El super_admin siempre tiene todos los permisos: editarlo solo serviría para dejar a alguien fuera.
        if ($role->name === 'super_admin') {
            return back()->with('error', 'El rol Super Admin siempre tiene todos los permisos y no se puede modificar.');
        }

        $role->syncPermissions($validated['permissions'] ?? []);

        return back()->with('message', "Permisos de \"{$role->name}\" actualizados.");
    }

    public function destroyRole(Role $role)
    {
        if (in_array($role->name, self::ROLES_PROTEGIDOS, true)) {
            return back()->with('error', 'Ese rol es propio del sistema y no se puede eliminar.');
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', 'No se puede eliminar: hay usuarios con este rol asignado.');
        }

        $role->delete();

        return back()->with('message', "Rol \"{$role->name}\" eliminado.");
    }

    // ── PERMISOS ──────────────────────────────────────────────
    public function storePermission(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('permissions', 'name')],
        ]);

        Permission::create(['name' => $validated['name'], 'guard_name' => 'web']);

        return back()->with('message', "Permiso \"{$validated['name']}\" creado correctamente.");
    }
}
