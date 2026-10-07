<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChartOfAccount\StoreChartOfAccountRequest;
use App\Http\Requests\ChartOfAccount\UpdateChartOfAccountRequest;
use App\Models\ChartOfAccount;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ChartOfAccountController extends Controller
{
    /**
     * Display the team's chart of accounts.
     */
    public function index(Team $currentTeam): Response
    {
        return Inertia::render('chart-of-accounts/index', [
            'team' => [
                'name' => $currentTeam->name,
                'slug' => $currentTeam->slug,
            ],
            'accounts' => $currentTeam->chartOfAccounts()
                ->orderBy('code')
                ->get(['id', 'code', 'name', 'type']),
        ]);
    }

    /**
     * Store a newly created chart of account.
     */
    public function store(StoreChartOfAccountRequest $request, Team $currentTeam): RedirectResponse
    {
        $currentTeam->chartOfAccounts()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Account created.')]);

        return to_route('chart-of-accounts.index', $currentTeam);
    }

    /**
     * Update the specified chart of account.
     */
    public function update(UpdateChartOfAccountRequest $request, Team $currentTeam, ChartOfAccount $chartOfAccount): RedirectResponse
    {
        $chartOfAccount->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Account updated.')]);

        return to_route('chart-of-accounts.index', $currentTeam);
    }

    /**
     * Remove the specified chart of account.
     */
    public function destroy(Team $currentTeam, ChartOfAccount $chartOfAccount): RedirectResponse
    {
        $chartOfAccount->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Account deleted.')]);

        return to_route('chart-of-accounts.index', $currentTeam);
    }
}
