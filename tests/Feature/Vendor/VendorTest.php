<?php

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use App\Models\Vendor;

test('team members can create, update, and delete vendors', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);

    $this->actingAs($user)->post(route('vendors.store', $team), ['name' => 'Acme Supplies', 'email' => 'sales@acme.test'])
        ->assertRedirect(route('vendors.index', $team));
    $vendor = Vendor::where('name', 'Acme Supplies')->firstOrFail();
    $this->actingAs($user)->patch(route('vendors.update', [$team, $vendor]), ['name' => 'Acme Wholesale', 'email' => 'sales@acme.test'])
        ->assertRedirect(route('vendors.index', $team));
    $this->actingAs($user)->delete(route('vendors.destroy', [$team, $vendor]))
        ->assertRedirect(route('vendors.index', $team));

    $this->assertDatabaseMissing('vendors', ['id' => $vendor->id]);
});

it('users cannot access vendors from another team', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);
    $vendor = Vendor::factory()->create();

    $this->actingAs($user)->patch(route('vendors.update', [$team, $vendor]), ['name' => 'Changed'])
        ->assertNotFound();
});
