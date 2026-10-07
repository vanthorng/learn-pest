<?php

use App\Enums\TeamRole;
use App\Models\Item;
use App\Models\Team;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('a Sanctum-authenticated team member can access their team items', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);
    Item::factory()->for($team)->create(['name' => 'Website design']);

    Sanctum::actingAs($user);

    $this->getJson(route('api.v1.items.index', $team))
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Website design');
});

test('a Sanctum-authenticated team member can manage items through the API', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);

    Sanctum::actingAs($user);

    $item = $this->postJson(route('api.v1.items.store', $team), [
        'name' => 'Website design',
        'type' => 'service',
        'unit_price' => 500,
    ])->assertCreated()->json('data');

    $this->patchJson(route('api.v1.items.update', [$team, $item['id']]), [
        'name' => 'Website redesign',
        'type' => 'service',
        'unit_price' => 750,
    ])->assertOk()->assertJsonPath('data.name', 'Website redesign');

    $this->deleteJson(route('api.v1.items.destroy', [$team, $item['id']]))
        ->assertNoContent();
});
