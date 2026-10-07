<?php

use App\Enums\TeamRole;
use App\Models\Customer;
use App\Models\Team;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('a Sanctum-authenticated team member can access only their team customers', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $otherTeam = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);

    Customer::factory()->for($team)->create(['name' => 'Acme Ltd']);
    Customer::factory()->for($otherTeam)->create(['name' => 'Other customer']);

    Sanctum::actingAs($user);

    $this->getJson(route('api.v1.customers.index', $team))
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Acme Ltd')
        ->assertJsonCount(1, 'data');

    $this->getJson(route('api.v1.customers.index', $otherTeam))
        ->assertForbidden();
});

test('a Sanctum-authenticated team member can manage customers through the API', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);

    Sanctum::actingAs($user);

    $customer = $this->postJson(route('api.v1.customers.store', $team), [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.test',
    ])->assertCreated()->json('data');

    $this->patchJson(route('api.v1.customers.update', [$team, $customer['id']]), [
        'name' => 'Ada Byron',
        'email' => 'ada@example.test',
    ])->assertOk()->assertJsonPath('data.name', 'Ada Byron');

    $this->deleteJson(route('api.v1.customers.destroy', [$team, $customer['id']]))
        ->assertNoContent();
});
