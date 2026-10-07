<?php

use App\Models\Team;

test('resource APIs require a Sanctum access token', function () {
    $team = Team::factory()->create();

    $this->getJson(route('api.v1.customers.index', $team))
        ->assertUnauthorized();
});
