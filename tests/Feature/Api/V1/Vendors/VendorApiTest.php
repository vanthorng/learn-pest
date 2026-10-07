<?php

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use App\Models\Vendor;
use Laravel\Sanctum\Sanctum;

test('a Sanctum-authenticated team member can access their team vendors', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);
    Vendor::factory()->for($team)->create(['name' => 'Acme Supplies']);

    Sanctum::actingAs($user);

    $this->getJson(route('api.v1.vendors.index', $team))
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Acme Supplies');
});

test('a Sanctum-authenticated team member can manage vendors through the API', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);

    Sanctum::actingAs($user);

    $vendor = $this->postJson(route('api.v1.vendors.store', $team), [
        'name' => 'Acme Supplies',
    ])->assertCreated()->json('data');

    $this->patchJson(route('api.v1.vendors.update', [$team, $vendor['id']]), [
        'name' => 'Acme Wholesale',
    ])->assertOk()->assertJsonPath('data.name', 'Acme Wholesale');

    $this->deleteJson(route('api.v1.vendors.destroy', [$team, $vendor['id']]))
        ->assertNoContent();
});
