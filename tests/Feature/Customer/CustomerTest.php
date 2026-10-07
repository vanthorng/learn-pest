<?php

use App\Enums\TeamRole;
use App\Models\Customer;
use App\Models\Team;
use App\Models\User;

test('team members can create, update, and delete customers', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);

    $this->actingAs($user)
        ->post(route('customers.store', $team), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.test',
            'phone' => '+855 12 345 678',
            'address' => 'Phnom Penh',
        ])
        ->assertRedirect(route('customers.index', $team));

    $customer = Customer::where('name', 'Ada Lovelace')->firstOrFail();

    $this->actingAs($user)
        ->patch(route('customers.update', [$team, $customer]), [
            'name' => 'Ada Byron',
            'email' => 'ada@example.test',
            'phone' => '+855 12 345 678',
            'address' => 'Siem Reap',
        ])
        ->assertRedirect(route('customers.index', $team));

    $this->assertDatabaseHas('customers', [
        'id' => $customer->id,
        'name' => 'Ada Byron',
        'address' => 'Siem Reap',
    ]);

    $this->actingAs($user)
        ->delete(route('customers.destroy', [$team, $customer]))
        ->assertRedirect(route('customers.index', $team));

    $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
});

test('customer names must be unique within a team', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);
    Customer::factory()->for($team)->create(['name' => 'Acme Ltd']);

    $this->actingAs($user)
        ->from(route('dashboard', $team))
        ->post(route('customers.store', $team), ['name' => 'Acme Ltd'])
        ->assertRedirect(route('dashboard', $team))
        ->assertSessionHasErrors('name');
});

test('a team only sees its own customers', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);
    $visibleCustomer = Customer::factory()->for($team)->create(['name' => 'Visible Customer']);
    Customer::factory()->create(['name' => 'Other Team Customer']);

    $this->actingAs($user)
        ->get(route('customers.index', $team))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('customers/index')
            ->has('customers', 1)
            ->where('customers.0.id', $visibleCustomer->id));
});

test('customers are scoped to their team', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);
    $customer = Customer::factory()->create();

    $this->actingAs($user)
        ->patch(route('customers.update', [$team, $customer]), ['name' => 'Changed'])
        ->assertNotFound();
});
