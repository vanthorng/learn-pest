<?php

use App\Enums\TeamRole;
use App\Models\Customer;
use App\Models\Estimate;
use App\Models\EstimateLine;
use App\Models\Item;
use App\Models\Team;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('team members can view estimates with their customers and lines', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $customer = Customer::factory()->for($team)->create(['name' => 'Acme Ltd']);
    $estimate = Estimate::factory()->for($team)->for($customer)->create([
        'number' => 'EST-00001',
        'total' => 125,
    ]);
    EstimateLine::factory()->for($estimate)->create([
        'name' => 'Website design',
        'quantity' => 1,
        'unit_price' => 125,
        'line_total' => 125,
    ]);
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);

    $this
        ->actingAs($user)
        ->get(route('estimates.index', $team))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('estimates/index')
            ->where('estimates.0.number', 'EST-00001')
            ->where('estimates.0.customer.name', 'Acme Ltd')
            ->where('estimates.0.lines.0.name', 'Website design')
            ->where('estimates.0.total', '125.00'),
        );
});

test('team members can create an estimate with calculated totals and item snapshots', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $customer = Customer::factory()->for($team)->create();
    $design = Item::factory()->for($team)->create([
        'name' => 'Website design',
        'unit_price' => 100,
        'description' => 'A custom landing page.',
    ]);
    $hosting = Item::factory()->for($team)->create([
        'name' => 'Managed hosting',
        'unit_price' => 50,
    ]);
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);

    $this
        ->actingAs($user)
        ->post(route('estimates.store', $team), estimatePayload($customer, [
            ['item_id' => $design->id, 'quantity' => 2],
            ['item_id' => $hosting->id, 'quantity' => 1],
        ], discount: 20, taxRate: 10))
        ->assertRedirect(route('estimates.index', $team));

    $estimate = Estimate::firstOrFail();

    $this->assertDatabaseHas('estimates', [
        'id' => $estimate->id,
        'team_id' => $team->id,
        'customer_id' => $customer->id,
        'status' => 'draft',
        'subtotal' => '250.00',
        'tax_amount' => '23.00',
        'total' => '253.00',
    ]);
    $this->assertDatabaseHas('estimate_lines', [
        'estimate_id' => $estimate->id,
        'item_id' => $design->id,
        'name' => 'Website design',
        'description' => 'A custom landing page.',
        'quantity' => '2.00',
        'unit_price' => '100.00',
        'line_total' => '200.00',
    ]);
});

test('estimates cannot use another teams customer or item', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $customer = Customer::factory()->for($team)->create();
    $otherCustomer = Customer::factory()->create();
    $item = Item::factory()->for($team)->create();
    $otherItem = Item::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);

    $this
        ->actingAs($user)
        ->from(route('estimates.index', $team))
        ->post(route('estimates.store', $team), estimatePayload($otherCustomer, [
            ['item_id' => $otherItem->id, 'quantity' => 1],
        ]))
        ->assertRedirect(route('estimates.index', $team))
        ->assertSessionHasErrors(['customer_id', 'items.0.item_id']);

    $this->assertDatabaseCount('estimates', 0);
    expect($customer->exists)->toBeTrue();
    expect($item->exists)->toBeTrue();
});

test('an estimate requires a customer and at least one item', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);

    $this
        ->actingAs($user)
        ->from(route('estimates.index', $team))
        ->post(route('estimates.store', $team), [])
        ->assertRedirect(route('estimates.index', $team))
        ->assertSessionHasErrors(['customer_id', 'issue_date', 'items']);
});

test('draft estimates can be updated and their totals are recalculated', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $customer = Customer::factory()->for($team)->create();
    $item = Item::factory()->for($team)->create(['unit_price' => 80]);
    $estimate = Estimate::factory()->for($team)->for($customer)->create();
    EstimateLine::factory()->for($estimate)->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);

    $this
        ->actingAs($user)
        ->patch(route('estimates.update', [$team, $estimate]), estimatePayload($customer, [
            ['item_id' => $item->id, 'quantity' => 3],
        ], discount: 10, taxRate: 5))
        ->assertRedirect(route('estimates.index', $team));

    $this->assertDatabaseHas('estimates', [
        'id' => $estimate->id,
        'subtotal' => '240.00',
        'tax_amount' => '11.50',
        'total' => '241.50',
    ]);
    $this->assertDatabaseCount('estimate_lines', 1);
});

test('estimates follow the draft sent accepted lifecycle', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $customer = Customer::factory()->for($team)->create();
    $estimate = Estimate::factory()->for($team)->for($customer)->create(['status' => 'draft']);
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);

    $this
        ->actingAs($user)
        ->patch(route('estimates.status.update', [$team, $estimate]), ['status' => 'sent'])
        ->assertRedirect(route('estimates.index', $team));
    $this->assertDatabaseHas('estimates', ['id' => $estimate->id, 'status' => 'sent']);

    $this
        ->actingAs($user)
        ->patch(route('estimates.status.update', [$team, $estimate]), ['status' => 'accepted'])
        ->assertRedirect(route('estimates.index', $team));
    $this->assertDatabaseHas('estimates', ['id' => $estimate->id, 'status' => 'accepted']);
});

test('an estimate cannot skip lifecycle states or be edited after it is sent', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $customer = Customer::factory()->for($team)->create();
    $item = Item::factory()->for($team)->create();
    $estimate = Estimate::factory()->for($team)->for($customer)->create(['status' => 'draft']);
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);

    $this
        ->actingAs($user)
        ->from(route('estimates.index', $team))
        ->patch(route('estimates.status.update', [$team, $estimate]), ['status' => 'accepted'])
        ->assertRedirect(route('estimates.index', $team))
        ->assertSessionHasErrors('status');

    $estimate->update(['status' => 'sent']);

    $this
        ->actingAs($user)
        ->from(route('estimates.index', $team))
        ->patch(route('estimates.update', [$team, $estimate]), estimatePayload($customer, [
            ['item_id' => $item->id, 'quantity' => 1],
        ]))
        ->assertRedirect(route('estimates.index', $team))
        ->assertSessionHasErrors('status');
});

test('team members cannot access an estimate from another team', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $customer = Customer::factory()->for($team)->create();
    $estimate = Estimate::factory()->for($customer)->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);

    $this
        ->actingAs($user)
        ->patch(route('estimates.status.update', [$team, $estimate]), ['status' => 'sent'])
        ->assertNotFound();
});

test('draft estimates can be deleted', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $customer = Customer::factory()->for($team)->create();
    $estimate = Estimate::factory()->for($team)->for($customer)->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);

    $this
        ->actingAs($user)
        ->delete(route('estimates.destroy', [$team, $estimate]))
        ->assertRedirect(route('estimates.index', $team));

    $this->assertModelMissing($estimate);
});

function estimatePayload(Customer $customer, array $items, float $discount = 0, float $taxRate = 0): array
{
    return [
        'customer_id' => $customer->id,
        'issue_date' => '2026-10-07',
        'valid_until' => '2026-11-06',
        'discount_amount' => $discount,
        'tax_rate' => $taxRate,
        'notes' => 'Thank you for your business.',
        'items' => $items,
    ];
}
