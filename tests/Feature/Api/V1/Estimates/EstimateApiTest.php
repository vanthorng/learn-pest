<?php

use App\Enums\TeamRole;
use App\Models\Customer;
use App\Models\Estimate;
use App\Models\EstimateLine;
use App\Models\Item;
use App\Models\Team;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('a Sanctum-authenticated team member can access their team estimates', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);
    $customer = Customer::factory()->for($team)->create(['name' => 'Acme Ltd']);
    $estimate = Estimate::factory()->for($team)->for($customer)->create(['number' => 'EST-00001']);
    EstimateLine::factory()->for($estimate)->create(['name' => 'Website design']);

    Sanctum::actingAs($user);

    $this->getJson(route('api.v1.estimates.index', $team))
        ->assertOk()
        ->assertJsonPath('data.0.number', 'EST-00001')
        ->assertJsonPath('data.0.customer.name', 'Acme Ltd')
        ->assertJsonPath('data.0.lines.0.name', 'Website design');
});

test('a Sanctum-authenticated team member can manage estimates through the API', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $customer = Customer::factory()->for($team)->create();
    $item = Item::factory()->for($team)->create(['unit_price' => 100]);
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);

    Sanctum::actingAs($user);

    $estimate = $this->postJson(route('api.v1.estimates.store', $team), apiEstimatePayload($customer, $item))
        ->assertCreated()
        ->assertJsonPath('data.subtotal', '200.00')
        ->assertJsonPath('data.total', '198.00')
        ->json('data');

    $this->patchJson(route('api.v1.estimates.status.update', [$team, $estimate['id']]), ['status' => 'sent'])
        ->assertOk()
        ->assertJsonPath('data.status', 'sent');
});

function apiEstimatePayload(Customer $customer, Item $item): array
{
    return [
        'customer_id' => $customer->id,
        'issue_date' => '2026-10-07',
        'valid_until' => '2026-11-06',
        'discount_amount' => 20,
        'tax_rate' => 10,
        'items' => [
            ['item_id' => $item->id, 'quantity' => 2],
        ],
    ];
}
