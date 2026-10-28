<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionEnforcementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->withoutVite();
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_removing_a_permission_from_a_role_removes_the_access(): void
    {
        $admin    = $this->userWithRole('super_admin');
        $empleado = $this->userWithRole('empleado');

        $this->actingAs($empleado)->get(route('payments.index'))->assertOk();

        // El administrador le quita "Ver reportes" desde Roles y Permisos
        $permisos = Role::findByName('empleado')->permissions->pluck('name')
            ->reject(fn ($n) => $n === 'Ver pagos')->values()->all();

        $this->actingAs($admin)
            ->patch(route('admin.roles.update', Role::findByName('empleado')), ['permissions' => $permisos])
            ->assertSessionHasNoErrors();

        $this->actingAs($empleado->fresh())->get(route('payments.index'))->assertForbidden();
        // Lo demás sigue funcionando
        $this->actingAs($empleado->fresh())->get(route('affiliates.index'))->assertOk();
    }

    public function test_each_action_needs_its_own_permission(): void
    {
        $empleado = $this->userWithRole('empleado');
        Role::findByName('empleado')->revokePermissionTo('Crear afiliados');

        $this->actingAs($empleado->fresh())->get(route('affiliates.create'))->assertForbidden();
        $this->actingAs($empleado->fresh())->post(route('affiliates.store'), [])->assertForbidden();
        $this->actingAs($empleado->fresh())->get(route('affiliates.index'))->assertOk();
    }

    public function test_default_roles_keep_the_access_they_had(): void
    {
        $empleado = $this->userWithRole('empleado');
        $this->actingAs($empleado)->get(route('dashboard'))->assertOk();
        $this->actingAs($empleado)->get(route('payments.portfolio'))->assertOk();
        $this->actingAs($empleado)->get(route('plans.index'))->assertForbidden();
        $this->actingAs($empleado)->get(route('branches.index'))->assertForbidden();
        $this->actingAs($empleado)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($empleado)->get(route('activity.index'))->assertForbidden();

        $cliente = $this->userWithRole('cliente');
        $this->actingAs($cliente)->get(route('portal.index'))->assertOk();
        $this->actingAs($cliente)->get(route('payments.mine'))->assertOk();
        $this->actingAs($cliente)->get(route('affiliates.index'))->assertForbidden();
        $this->actingAs($cliente)->get(route('dashboard'))->assertForbidden();

        $admin = $this->userWithRole('super_admin');
        foreach (['dashboard', 'affiliates.index', 'plans.index', 'branches.index', 'admin.users.index',
                  'admin.roles.index', 'activity.index', 'payments.index'] as $ruta) {
            $this->actingAs($admin)->get(route($ruta))->assertOk();
        }
    }

    public function test_custom_role_works_with_only_the_permissions_given(): void
    {
        $rol = Role::create(['name' => 'contador', 'guard_name' => 'web']);
        $rol->givePermissionTo(['Ver pagos', 'Ver sedes']);
        $contador = $this->userWithRole('contador');

        $this->actingAs($contador)->get(route('payments.index'))->assertOk();
        $this->actingAs($contador)->get(route('branches.index'))->assertOk();
        $this->actingAs($contador)->get(route('affiliates.index'))->assertForbidden();

        // Sin panel ni portal: tras iniciar sesión cae en su perfil, no en un 403
        $this->assertSame('profile.edit', $contador->homeRouteName());
    }

    public function test_super_admin_role_cannot_be_edited_and_always_has_access(): void
    {
        $admin = $this->userWithRole('super_admin');

        $this->actingAs($admin)
            ->patch(route('admin.roles.update', Role::findByName('super_admin')), ['permissions' => []])
            ->assertSessionHas('error');

        $this->assertGreaterThan(0, Role::findByName('super_admin')->permissions()->count());

        // Aunque a su rol le falte un permiso, el super_admin pasa igual
        Role::findByName('super_admin')->revokePermissionTo('Ver planes');
        $this->actingAs($admin->fresh())->get(route('plans.index'))->assertOk();
    }

    public function test_only_super_admin_can_modify_roles_and_nobody_can_escalate(): void
    {
        $rol = Role::create(['name' => 'rrhh', 'guard_name' => 'web']);
        $rol->givePermissionTo(['Ver roles y permisos', 'Ver usuarios', 'Crear usuarios', 'Editar usuarios']);
        $rrhh = $this->userWithRole('rrhh');

        // Puede ver, pero no modificar roles ni permisos
        $this->actingAs($rrhh)->get(route('admin.roles.index'))->assertOk();
        $this->actingAs($rrhh)->patch(route('admin.roles.update', $rol), ['permissions' => ['Ver afiliados']])->assertForbidden();
        $this->actingAs($rrhh)->post(route('admin.permissions.store'), ['name' => 'Algo'])->assertForbidden();

        // No puede crear un super_admin ni asignar roles sin el permiso "Asignar roles"
        $this->actingAs($rrhh)->post(route('admin.users.store'), [
            'name' => 'X', 'email' => 'x@x.com', 'password' => 'Clave1234', 'role' => 'super_admin',
        ])->assertForbidden();
        $this->actingAs($rrhh)->post(route('admin.users.store'), [
            'name' => 'Y', 'email' => 'y@x.com', 'password' => 'Clave1234', 'role' => 'empleado',
        ])->assertForbidden();

        $rol->givePermissionTo('Asignar roles');
        $this->actingAs($rrhh->fresh())->post(route('admin.users.store'), [
            'name' => 'Y', 'email' => 'y@x.com', 'password' => 'Clave1234', 'role' => 'empleado',
        ])->assertSessionHasNoErrors();
        $this->assertTrue(User::where('email', 'y@x.com')->firstOrFail()->hasRole('empleado'));

        // Ni siquiera con "Asignar roles" puede tocar una cuenta super_admin
        $admin = $this->userWithRole('super_admin');
        $this->actingAs($rrhh->fresh())->patch(route('admin.users.update', $admin), [
            'name' => 'Hackeado', 'email' => $admin->email, 'role' => 'empleado',
        ])->assertNotFound();
    }

    public function test_super_admin_accounts_and_role_are_hidden_from_other_users(): void
    {
        $rol = Role::create(['name' => 'rrhh', 'guard_name' => 'web']);
        $rol->givePermissionTo(['Ver roles y permisos', 'Ver usuarios', 'Crear usuarios', 'Editar usuarios',
                                'Activar o inactivar usuarios', 'Asignar roles']);
        $rrhh  = $this->userWithRole('rrhh');
        $admin = $this->userWithRole('super_admin');
        $admin->update(['name' => 'Jefa Suprema']);
        $this->userWithRole('empleado')->update(['name' => 'Pedro Empleado']);

        // Lista: no aparece el super_admin, ni siquiera buscándolo por nombre
        $this->actingAs($rrhh)->get(route('admin.users.index'))
            ->assertInertia(fn ($p) => $p->component('Admin/Users/Index')
                ->where('users.data', fn ($u) => collect($u)->doesntContain('name', 'Jefa Suprema')
                    && collect($u)->contains('name', 'Pedro Empleado'))
                ->where('roles', fn ($r) => collect($r)->doesntContain('name', 'super_admin')));

        $this->actingAs($rrhh)->get(route('admin.users.index', ['search' => 'Jefa']))
            ->assertInertia(fn ($p) => $p->where('users.total', 0));

        // El filtro por rol tampoco lo deja pasar
        $this->actingAs($rrhh)->get(route('admin.users.index', ['role' => 'super_admin']))
            ->assertInertia(fn ($p) => $p->where('users.total', 0));

        // Crear: no se ofrece el rol super_admin
        $this->actingAs($rrhh)->get(route('admin.users.create'))
            ->assertInertia(fn ($p) => $p->where('roles', fn ($r) => collect($r)->doesntContain('name', 'super_admin')));

        // Editar o inactivar una cuenta super_admin "no existe"
        $this->actingAs($rrhh)->get(route('admin.users.edit', $admin))->assertNotFound();
        $this->actingAs($rrhh)->patch(route('admin.users.toggle', $admin))->assertNotFound();
        $this->assertTrue($admin->fresh()->is_active);

        // Roles y permisos: no se lista el rol super_admin
        $this->actingAs($rrhh)->get(route('admin.roles.index'))
            ->assertInertia(fn ($p) => $p->where('roles', fn ($r) => collect($r)->doesntContain('name', 'super_admin')));

        // El super_admin sí ve todo
        $this->actingAs($admin)->get(route('admin.users.index'))
            ->assertInertia(fn ($p) => $p->where('users.data', fn ($u) => collect($u)->contains('name', 'Jefa Suprema'))
                ->where('roles', fn ($r) => collect($r)->contains('name', 'super_admin')));
    }

    public function test_user_search_keeps_the_other_filters(): void
    {
        $admin = $this->userWithRole('super_admin');
        $a = $this->userWithRole('empleado');
        $b = $this->userWithRole('cliente');
        $a->update(['name' => 'Juan Perez']);
        $b->update(['name' => 'Juan Gomez']);

        // Buscar "Juan" filtrando por rol cliente solo debe dar el cliente
        $this->actingAs($admin)->get(route('admin.users.index', ['search' => 'Juan', 'role' => 'cliente']))
            ->assertInertia(fn ($p) => $p->where('users.total', 1)->where('users.data.0.name', 'Juan Gomez'));
    }

    public function test_seeder_does_not_overwrite_permissions_edited_on_existing_roles(): void
    {
        Role::findByName('empleado')->revokePermissionTo('Ver pagos');
        Role::findByName('empleado')->givePermissionTo(Permission::findByName('Ver sedes'));

        // El seeder corre en cada despliegue
        $this->seed(RolesAndPermissionsSeeder::class);

        $empleado = Role::findByName('empleado');
        $this->assertFalse($empleado->hasPermissionTo('Ver pagos'));
        $this->assertTrue($empleado->hasPermissionTo('Ver sedes'));
        // super_admin sí se mantiene con todos
        $this->assertSame(Permission::count(), Role::findByName('super_admin')->permissions()->count());
    }
}
