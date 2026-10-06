<?php

use App\Enums\TeamRole;
use App\Models\ChartOfAccount;
use App\Models\Team;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('team members can view their chart of accounts', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);
    $account = ChartOfAccount::factory()->for($team)->create([
        'code' => '1000',
        'name' => 'Cash',
        'type' => 'asset',
    ]);

    $this
        ->actingAs($user)
        ->get(route('chart-of-accounts.index', $team))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('chart-of-accounts/index')
            ->where('accounts.0.id', $account->id)
            ->where('accounts.0.code', '1000')
            ->where('accounts.0.name', 'Cash')
            ->where('accounts.0.type', 'asset'),
        );
});

test('team members can create an account', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);

    $this
        ->actingAs($user)
        ->post(route('chart-of-accounts.store', $team), [
            'code' => '4000',
            'name' => 'Sales Revenue',
            'type' => 'revenue',
        ])
        ->assertRedirect(route('chart-of-accounts.index', $team));

    $this->assertDatabaseHas('chart_of_accounts', [
        'team_id' => $team->id,
        'code' => '4000',
        'name' => 'Sales Revenue',
        'type' => 'revenue',
    ]);
});

test('account codes must be unique within a team', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);
    ChartOfAccount::factory()->for($team)->create(['code' => '1000']);

    $this
        ->actingAs($user)
        ->from(route('chart-of-accounts.index', $team))
        ->post(route('chart-of-accounts.store', $team), [
            'code' => '1000',
            'name' => 'Petty Cash',
            'type' => 'asset',
        ])
        ->assertRedirect(route('chart-of-accounts.index', $team))
        ->assertSessionHasErrors(['code' => 'The code has already been taken.']);
});

test('users cannot access another teams chart of accounts', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $this
        ->actingAs($user)
        ->get(route('chart-of-accounts.index', $team))
        ->assertForbidden();
});

test('team members can update an account', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);
    $account = ChartOfAccount::factory()->for($team)->create([
        'code' => '1000',
        'name' => 'Cash',
        'type' => 'asset',
    ]);

    $this
        ->actingAs($user)
        ->patch(route('chart-of-accounts.update', [$team, $account]), [
            'code' => '1010',
            'name' => 'Petty Cash',
            'type' => 'asset',
        ])
        ->assertRedirect(route('chart-of-accounts.index', $team));

    $this->assertDatabaseHas('chart_of_accounts', [
        'id' => $account->id,
        'code' => '1010',
        'name' => 'Petty Cash',
    ]);
});

test('team members can delete an account', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);
    $account = ChartOfAccount::factory()->for($team)->create();

    $this
        ->actingAs($user)
        ->delete(route('chart-of-accounts.destroy', [$team, $account]))
        ->assertRedirect(route('chart-of-accounts.index', $team));

    $this->assertDatabaseMissing('chart_of_accounts', ['id' => $account->id]);
});

test('team members cannot update an account from another team', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);
    $anotherTeamAccount = ChartOfAccount::factory()->create();

    $this
        ->actingAs($user)
        ->patch(route('chart-of-accounts.update', [$team, $anotherTeamAccount]), [
            'code' => '2000',
            'name' => 'Accounts Payable',
            'type' => 'liability',
        ])
        ->assertNotFound();
});
