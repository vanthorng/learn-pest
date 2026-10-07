<?php

use App\Enums\TeamRole;
use App\Models\Item;
use App\Models\Team;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('team members can view their items', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);
    $item = Item::factory()->for($team)->create([
        'name' => 'Website design',
        'type' => 'service',
        'sku' => 'SVC-001',
        'unit_price' => 750,
    ]);

    $this
        ->actingAs($user)
        ->get(route('items.index', $team))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('items/index')
            ->where('items.0.id', $item->id)
            ->where('items.0.name', 'Website design')
            ->where('items.0.type', 'service')
            ->where('items.0.sku', 'SVC-001')
            ->where('items.0.unit_price', '750.00'),
        );
});

test('users who do not belong to a team cannot view its items', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $this
        ->actingAs($user)
        ->get(route('items.index', $team))
        ->assertForbidden();
});

test('guests are redirected to login when viewing items', function () {
    $team = Team::factory()->create();

    $this
        ->get(route('items.index', $team))
        ->assertRedirect(route('login'));
});

test('team members can create products and services', function (array $attributes) {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);

    $this
        ->actingAs($user)
        ->post(route('items.store', $team), $attributes)
        ->assertRedirect(route('items.index', $team));

    $this->assertDatabaseHas('items', [
        'team_id' => $team->id,
        'name' => $attributes['name'],
        'type' => $attributes['type'],
        'sku' => $attributes['sku'],
        'unit_price' => number_format($attributes['unit_price'], 2, '.', ''),
    ]);
})->with([
    'product' => [[
        'name' => 'USB-C cable',
        'type' => 'product',
        'sku' => 'CAB-001',
        'unit_price' => 12.5,
        'description' => 'A braided one-metre cable.',
    ]],
    'service' => [[
        'name' => 'Installation',
        'type' => 'service',
        'sku' => 'SVC-001',
        'unit_price' => 85,
        'description' => 'On-site installation service.',
    ]],
]);

test('creating an item requires a name, type, and unit price', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);

    $this
        ->actingAs($user)
        ->from(route('items.index', $team))
        ->post(route('items.store', $team), [])
        ->assertRedirect(route('items.index', $team))
        ->assertSessionHasErrors([
            'name' => 'The name field is required.',
            'type' => 'The type field is required.',
            'unit_price' => 'The unit price field is required.',
        ]);
});

test('an item must have a valid type and a non-negative unit price', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);

    $this
        ->actingAs($user)
        ->from(route('items.index', $team))
        ->post(route('items.store', $team), [
            'name' => 'Invalid item',
            'type' => 'subscription',
            'unit_price' => -1,
        ])
        ->assertRedirect(route('items.index', $team))
        ->assertSessionHasErrors([
            'type' => 'The selected type is invalid.',
            'unit_price' => 'The unit price field must be at least 0.',
        ]);
});

test('an item SKU must be unique within a team', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);
    Item::factory()->for($team)->create(['sku' => 'PRO-001']);

    $this
        ->actingAs($user)
        ->from(route('items.index', $team))
        ->post(route('items.store', $team), [
            'name' => 'Duplicate SKU',
            'type' => 'product',
            'sku' => 'PRO-001',
            'unit_price' => 20,
        ])
        ->assertRedirect(route('items.index', $team))
        ->assertSessionHasErrors(['sku' => 'The sku has already been taken.']);
});

test('the same SKU can be used by another team', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $anotherTeam = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);
    Item::factory()->for($anotherTeam)->create(['sku' => 'PRO-001']);

    $this
        ->actingAs($user)
        ->post(route('items.store', $team), [
            'name' => 'Team item',
            'type' => 'product',
            'sku' => 'PRO-001',
            'unit_price' => 20,
        ])
        ->assertRedirect(route('items.index', $team));

    $this->assertDatabaseHas('items', [
        'team_id' => $team->id,
        'sku' => 'PRO-001',
    ]);
});

test('team members cannot update an item from another team', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);
    $anotherTeamItem = Item::factory()->create();

    $this
        ->actingAs($user)
        ->patch(route('items.update', [$team, $anotherTeamItem]), [
            'name' => 'Changed',
            'type' => 'product',
            'unit_price' => 20,
        ])
        ->assertNotFound();
});

test('team members can update an item', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);
    $item = Item::factory()->for($team)->create([
        'name' => 'USB-C cable',
        'type' => 'product',
        'sku' => 'CAB-001',
        'unit_price' => 12.5,
    ]);

    $this
        ->actingAs($user)
        ->patch(route('items.update', [$team, $item]), [
            'name' => 'Premium USB-C cable',
            'type' => 'product',
            'sku' => 'CAB-001',
            'unit_price' => 18,
            'description' => 'A durable two-metre cable.',
        ])
        ->assertRedirect(route('items.index', $team));

    $this->assertDatabaseHas('items', [
        'id' => $item->id,
        'name' => 'Premium USB-C cable',
        'unit_price' => '18.00',
    ]);
});

test('team members can delete an item', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);
    $item = Item::factory()->for($team)->create();

    $this
        ->actingAs($user)
        ->delete(route('items.destroy', [$team, $item]))
        ->assertRedirect(route('items.index', $team));

    $this->assertModelMissing($item);
});
