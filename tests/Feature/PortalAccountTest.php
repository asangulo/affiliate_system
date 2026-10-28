<?php

namespace Tests\Feature;

use App\Models\Affiliate;
use App\Models\Branch;
use App\Models\Plan;
use App\Models\User;
use App\Services\PortalAccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PortalAccountTest extends TestCase
{
    use RefreshDatabase;

    private function affiliate(string $name, string $cedula, array $extra = []): Affiliate
    {
        return Affiliate::create($extra + [
            'full_name'  => $name,
            'id_number'  => $cedula,
            'branch_id'  => Branch::firstOrCreate(['name' => 'Sede 1'])->id,
            'plan_id'    => Plan::firstOrCreate(['name' => 'Plan 1'])->id,
            'fee_amount' => 50000,
            'is_active'  => true,
        ]);
    }

    public function test_username_rules(): void
    {
        $this->assertSame('alex.angulo', PortalAccountService::usernameBase('ALEX SANTIAGO ANGULO GOMEZ'));
        $this->assertSame('maria.gomez', PortalAccountService::usernameBase('Maria Lopez Gomez'));
        $this->assertSame('juan.perez', PortalAccountService::usernameBase('Juan Pérez'));
        $this->assertSame('jose.nunez', PortalAccountService::usernameBase('  José   Núñez '));
        $this->assertSame('pedro', PortalAccountService::usernameBase('Pedro'));
        $this->assertNull(PortalAccountService::usernameBase('   '));
    }

    public function test_creates_client_account_with_default_password(): void
    {
        $aff  = $this->affiliate('Alex Santiago Angulo Gomez', '1');
        $user = app(PortalAccountService::class)->ensureFor($aff);

        $this->assertSame('alex.angulo', $user->username);
        $this->assertTrue($user->hasRole('cliente'));
        $this->assertTrue($user->must_change_password);
        $this->assertTrue(Hash::check('Horizonte2026!', $user->password));
        $this->assertSame($aff->id, $user->affiliate_id);
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame('alex.angulo@clientes.horizonte.local', $user->email);
    }

    public function test_repeated_names_get_a_number_and_it_is_idempotent(): void
    {
        $service = app(PortalAccountService::class);
        $a = $this->affiliate('Alex Angulo', '1');
        $b = $this->affiliate('Alex Angulo', '2');

        $this->assertSame('alex.angulo', $service->ensureFor($a)->username);
        $this->assertSame('alex.angulo2', $service->ensureFor($b)->username);
        $this->assertNull($service->ensureFor($a->fresh()), 'Ya tiene cuenta: no se duplica');
        $this->assertSame(2, User::count());
    }

    public function test_inactive_affiliates_get_no_account_and_own_email_is_reused(): void
    {
        $service = app(PortalAccountService::class);

        $this->assertNull($service->ensureFor($this->affiliate('Ana Ruiz', '1', ['is_active' => false])));

        $user = $service->ensureFor($this->affiliate('Luis Mora', '2', ['email' => 'Luis@Correo.com']));
        $this->assertSame('luis@correo.com', $user->email);
    }

    public function test_command_creates_missing_accounts_and_dry_run_saves_nothing(): void
    {
        $this->affiliate('Ana Ruiz', '1');
        $this->affiliate('Luis Mora', '2');

        $this->artisan('portal:create-users', ['--dry-run' => true])->assertSuccessful();
        $this->assertSame(0, User::count());

        $this->artisan('portal:create-users')->assertSuccessful();
        $this->assertSame(2, User::count());

        $this->artisan('portal:create-users')->assertSuccessful();
        $this->assertSame(2, User::count());
    }

    public function test_client_can_log_in_with_username_and_is_sent_to_change_password(): void
    {
        $user = app(PortalAccountService::class)->ensureFor($this->affiliate('Alex Angulo', '1'));

        $this->post('/login', ['email' => 'Alex.Angulo', 'password' => 'Horizonte2026!'])
            ->assertRedirect(route('portal.index', absolute: false));
        $this->assertAuthenticatedAs($user);

        $this->get('/portal')->assertRedirect(route('profile.edit'));
        $this->get('/mis-pagos')->assertRedirect(route('profile.edit'));
    }

    public function test_wrong_password_is_rejected(): void
    {
        app(PortalAccountService::class)->ensureFor($this->affiliate('Alex Angulo', '1'));

        $this->post('/login', ['email' => 'alex.angulo', 'password' => 'otra'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_changing_the_password_clears_the_flag_and_cannot_reuse_the_old_one(): void
    {
        $user = app(PortalAccountService::class)->ensureFor($this->affiliate('Alex Angulo', '1'));

        $this->actingAs($user)->put('/password', [
            'current_password'      => 'Horizonte2026!',
            'password'              => 'Horizonte2026!',
            'password_confirmation' => 'Horizonte2026!',
        ])->assertSessionHasErrors('password');

        $this->actingAs($user)->put('/password', [
            'current_password'      => 'Horizonte2026!',
            'password'              => 'ClaveNueva2027*',
            'password_confirmation' => 'ClaveNueva2027*',
        ])->assertSessionHasNoErrors();

        $this->assertFalse($user->fresh()->must_change_password);
        $this->assertTrue(Hash::check('ClaveNueva2027*', $user->fresh()->password));
    }

    public function test_client_cannot_read_activity_logs_of_other_records(): void
    {
        $user = app(PortalAccountService::class)->ensureFor($this->affiliate('Alex Angulo', '1'));
        $user->update(['must_change_password' => false]);

        $this->actingAs($user)->getJson('/historial/affiliate/1')->assertForbidden();
    }

    public function test_deactivated_user_is_logged_out(): void
    {
        $user = app(PortalAccountService::class)->ensureFor($this->affiliate('Alex Angulo', '1'));
        $user->update(['must_change_password' => false, 'is_active' => false]);

        $this->actingAs($user)->get('/portal')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_new_holder_after_death_gets_own_account_and_old_one_is_disabled(): void
    {
        $empleado = User::factory()->create();
        $empleado->assignRole(Role::create(['name' => 'empleado']));

        $aff  = $this->affiliate('Carlos Perez Gil', '1');
        $old  = app(PortalAccountService::class)->ensureFor($aff);
        $hijo = $aff->beneficiaries()->create(['full_name' => 'Luis Perez Mora', 'age' => '20', 'is_active' => true]);

        $this->actingAs($empleado)->post(route('affiliates.deceased', $aff), [
            'deceased_at' => now()->toDateString(), 'successor_id' => $hijo->id, 'new_id_number' => '2',
        ])->assertSessionHasNoErrors();

        $this->assertFalse($old->fresh()->is_active);
        $this->assertNull($old->fresh()->affiliate_id);

        $nuevo = $aff->fresh()->user;
        $this->assertSame('luis.mora', $nuevo->username);
        $this->assertTrue($nuevo->hasRole('cliente'));
    }
}
