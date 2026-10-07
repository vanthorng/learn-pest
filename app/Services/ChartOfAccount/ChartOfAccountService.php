<?php

namespace App\Services\ChartOfAccount;

use App\Models\ChartOfAccount;
use App\Models\Team;

class ChartOfAccountService
{
    /**
     * Create an account for a team.
     *
     * @param  array{code: string, name: string, type: string}  $attributes
     */
    public function create(Team $team, array $attributes): ChartOfAccount
    {
        return $team->chartOfAccounts()->create($attributes);
    }

    /**
     * Update an account.
     *
     * @param  array{code: string, name: string, type: string}  $attributes
     */
    public function update(ChartOfAccount $chartOfAccount, array $attributes): void
    {
        $chartOfAccount->update($attributes);
    }

    /**
     * Delete an account.
     */
    public function delete(ChartOfAccount $chartOfAccount): void
    {
        $chartOfAccount->delete();
    }
}
