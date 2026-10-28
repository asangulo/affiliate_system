<?php

namespace Tests\Feature;

use App\Models\Affiliate;
use App\Models\Beneficiary;
use App\Models\Branch;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AffiliateDeceasedTest extends TestCase
{
    use RefreshDatabase;

    private function actor(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::create(['name' => 'empleado']));

        return $user;
    }

    private function affiliate(): Affiliate
    {
        $branch = Branch::create(['name' => 'Sede 1']);
        $plan   = Plan::create(['name' => 'Plan 1']);

        return Affiliate::create([
            'full_name'  => 'Titular Uno',
            'id_number'  => '1000',
            'branch_id'  => $branch->id,
            'plan_id'    => $plan->id,
            'fee_amount' => 50000,
            'is_active'  => true,
        ]);
    }

    private function beneficiary(Affiliate $aff, string $name): Beneficiary
    {
        return $aff->beneficiaries()->create([
            'full_name' => $name, 'relationship' => 'HIJO/A', 'age' => '20', 'is_active' => true,
        ]);
    }

    public function test_beneficiary_becomes_holder_and_previous_holder_is_kept_in_history(): void
    {
        $aff  = $this->affiliate();
        $hijo = $this->beneficiary($aff, 'Hijo Dos');
        $otro = $this->beneficiary($aff, 'Hija Tres');

        $this->actingAs($this->actor())
            ->post(route('affiliates.deceased', $aff), [
                'deceased_at'   => now()->toDateString(),
                'notes'         => 'Certificado entregado',
                'successor_id'  => $hijo->id,
                'new_id_number' => '2000',
            ])
            ->assertSessionHasNoErrors();

        $aff->refresh();
        $this->assertSame('Hijo Dos', $aff->full_name);
        $this->assertSame('2000', $aff->id_number);
        $this->assertTrue($aff->is_active);
        $this->assertFalse($hijo->fresh()->is_active);
        $this->assertTrue($otro->fresh()->is_active);

        $this->assertDatabaseHas('holder_changes', [
            'affiliate_id'       => $aff->id,
            'previous_full_name' => 'Titular Uno',
            'previous_id_number' => '1000',
            'new_id_number'      => '2000',
            'beneficiary_id'     => $hijo->id,
        ]);
    }

    public function test_without_successor_the_affiliate_is_deactivated(): void
    {
        $aff = $this->affiliate();

        $this->actingAs($this->actor())
            ->post(route('affiliates.deceased', $aff), ['deceased_at' => now()->toDateString()])
            ->assertSessionHasNoErrors();

        $this->assertFalse($aff->fresh()->is_active);
        $this->assertSame('Titular Uno', $aff->fresh()->full_name);
        $this->assertDatabaseHas('holder_changes', ['affiliate_id' => $aff->id, 'new_id_number' => null]);
    }

    public function test_new_holder_id_number_is_required_and_must_be_unique(): void
    {
        $aff  = $this->affiliate();
        $hijo = $this->beneficiary($aff, 'Hijo Dos');
        $user = $this->actor();

        $this->actingAs($user)
            ->post(route('affiliates.deceased', $aff), [
                'deceased_at' => now()->toDateString(), 'successor_id' => $hijo->id,
            ])
            ->assertSessionHasErrors('new_id_number');

        $this->actingAs($user)
            ->post(route('affiliates.deceased', $aff), [
                'deceased_at' => now()->toDateString(), 'successor_id' => $hijo->id, 'new_id_number' => '1000',
            ])
            ->assertSessionHasErrors('new_id_number');

        $this->assertDatabaseCount('holder_changes', 0);
        $this->assertSame('Titular Uno', $aff->fresh()->full_name);
    }

    public function test_successor_must_be_an_active_beneficiary_of_the_same_affiliate(): void
    {
        $aff   = $this->affiliate();
        $ajeno = $this->beneficiary($this->affiliate2(), 'Ajeno');

        $this->actingAs($this->actor())
            ->post(route('affiliates.deceased', $aff), [
                'deceased_at' => now()->toDateString(), 'successor_id' => $ajeno->id, 'new_id_number' => '3000',
            ])
            ->assertSessionHasErrors('successor_id');
    }

    private function affiliate2(): Affiliate
    {
        return Affiliate::create([
            'full_name' => 'Otro Titular', 'id_number' => '9999',
            'branch_id' => Branch::first()->id, 'plan_id' => Plan::first()->id,
            'fee_amount' => 1, 'is_active' => true,
        ]);
    }
}
