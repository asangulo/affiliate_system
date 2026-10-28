<?php

namespace Tests\Feature;

use App\Models\Affiliate;
use App\Models\Branch;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PlanTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate($role, 'web'));

        return $user;
    }

    public function test_super_admin_can_create_and_edit_plans(): void
    {
        $admin = $this->userWithRole('super_admin');

        $this->actingAs($admin)->post(route('plans.store'), [
            'name' => 'Premium', 'base_price' => 80000, 'description' => 'Cobertura completa',
        ])->assertSessionHasNoErrors();

        $plan = Plan::where('name', 'Premium')->firstOrFail();
        $this->assertTrue($plan->is_active);
        $this->assertEquals(80000, $plan->base_price);

        $this->actingAs($admin)->put(route('plans.update', $plan), [
            'name' => 'Premium Plus', 'base_price' => 90000, 'is_active' => true,
        ])->assertSessionHasNoErrors();

        $this->assertSame('Premium Plus', $plan->fresh()->name);
        $this->assertEquals(90000, $plan->fresh()->base_price);
    }

    public function test_name_must_be_unique_and_price_required(): void
    {
        $admin = $this->userWithRole('super_admin');
        Plan::create(['name' => 'Basico', 'base_price' => 1000]);

        $this->actingAs($admin)->post(route('plans.store'), ['name' => 'Basico', 'base_price' => 5])
            ->assertSessionHasErrors('name');
        $this->actingAs($admin)->post(route('plans.store'), ['name' => 'Otro'])
            ->assertSessionHasErrors('base_price');
    }

    public function test_updating_a_plan_keeps_its_own_name_valid_and_does_not_change_affiliate_fees(): void
    {
        $admin = $this->userWithRole('super_admin');
        $plan  = Plan::create(['name' => 'Basico', 'base_price' => 1000]);
        $aff   = Affiliate::create([
            'full_name' => 'Ana Ruiz', 'id_number' => '1', 'fee_amount' => 1000, 'is_active' => true,
            'branch_id' => Branch::create(['name' => 'S'])->id, 'plan_id' => $plan->id,
        ]);

        $this->actingAs($admin)->put(route('plans.update', $plan), ['name' => 'Basico', 'base_price' => 2000])
            ->assertSessionHasNoErrors();

        $this->assertEquals(2000, $plan->fresh()->base_price);
        $this->assertEquals(1000, $aff->fresh()->fee_amount);
    }

    public function test_plan_with_affiliates_cannot_be_deleted_but_can_be_deactivated(): void
    {
        $admin = $this->userWithRole('super_admin');
        $plan  = Plan::create(['name' => 'Basico', 'base_price' => 1000]);
        Affiliate::create([
            'full_name' => 'Ana Ruiz', 'id_number' => '1', 'fee_amount' => 1000, 'is_active' => true,
            'branch_id' => Branch::create(['name' => 'S'])->id, 'plan_id' => $plan->id,
        ]);

        $this->actingAs($admin)->delete(route('plans.destroy', $plan))->assertSessionHas('error');
        $this->assertDatabaseHas('plans', ['id' => $plan->id]);

        $this->actingAs($admin)->patch(route('plans.toggle', $plan));
        $this->assertFalse($plan->fresh()->is_active);

        $vacio = Plan::create(['name' => 'Sin uso', 'base_price' => 0]);
        $this->actingAs($admin)->delete(route('plans.destroy', $vacio))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('plans', ['id' => $vacio->id]);
    }

    public function test_only_super_admin_can_manage_plans(): void
    {
        $empleado = $this->userWithRole('empleado');

        $this->actingAs($empleado)->get(route('plans.index'))->assertForbidden();
        $this->actingAs($empleado)->post(route('plans.store'), ['name' => 'X', 'base_price' => 1])->assertForbidden();
        $this->assertDatabaseCount('plans', 0);
    }

    public function test_new_affiliate_form_only_offers_active_plans(): void
    {
        $empleado = $this->userWithRole('empleado');
        Plan::create(['name' => 'Activo', 'base_price' => 1000]);
        Plan::create(['name' => 'Viejo', 'base_price' => 500, 'is_active' => false]);

        $this->withoutVite()->actingAs($empleado)->get(route('affiliates.create'))
            ->assertInertia(fn ($page) => $page
                ->component('Affiliates/Create')
                ->has('plans', 1)
                ->where('plans.0.name', 'Activo'));
    }
}
