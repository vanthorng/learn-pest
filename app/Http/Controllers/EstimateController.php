<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEstimateRequest;
use App\Http\Requests\UpdateEstimateRequest;
use App\Http\Requests\UpdateEstimateStatusRequest;
use App\Models\Estimate;
use App\Models\Team;
use App\Services\Estimate\EstimateService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EstimateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Team $currentTeam): Response
    {
        return Inertia::render('estimates/index', [
            'team' => [
                'name' => $currentTeam->name,
                'slug' => $currentTeam->slug,
            ],
            'customers' => $currentTeam->customers()
                ->orderBy('name')
                ->get(['id', 'name']),
            'items' => $currentTeam->items()
                ->orderBy('name')
                ->get(['id', 'name', 'type', 'sku', 'unit_price', 'description']),
            'estimates' => $currentTeam->estimates()
                ->with([
                    'customer:id,name',
                    'lines:id,estimate_id,name,quantity,unit_price,line_total',
                ])
                ->latest('id')
                ->get()
                ->map(fn (Estimate $estimate): array => [
                    'id' => $estimate->id,
                    'number' => $estimate->number,
                    'status' => $estimate->status,
                    'issue_date' => $estimate->issue_date?->toDateString(),
                    'valid_until' => $estimate->valid_until?->toDateString(),
                    'discount_amount' => $estimate->discount_amount,
                    'tax_rate' => $estimate->tax_rate,
                    'subtotal' => $estimate->subtotal,
                    'tax_amount' => $estimate->tax_amount,
                    'total' => $estimate->total,
                    'notes' => $estimate->notes,
                    'customer' => [
                        'id' => $estimate->customer->id,
                        'name' => $estimate->customer->name,
                    ],
                    'lines' => $estimate->lines->map(fn ($line): array => [
                        'id' => $line->id,
                        'item_id' => $line->item_id,
                        'name' => $line->name,
                        'quantity' => $line->quantity,
                        'unit_price' => $line->unit_price,
                        'line_total' => $line->line_total,
                    ]),
                ]),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEstimateRequest $request, Team $currentTeam, EstimateService $estimateService): RedirectResponse
    {
        $estimateService->create($currentTeam, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Estimate created.')]);

        return to_route('estimates.index', $currentTeam);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEstimateRequest $request, Team $currentTeam, Estimate $estimate, EstimateService $estimateService): RedirectResponse
    {
        $estimateService->update($estimate, $currentTeam, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Estimate updated.')]);

        return to_route('estimates.index', $currentTeam);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function updateStatus(UpdateEstimateStatusRequest $request, Team $currentTeam, Estimate $estimate, EstimateService $estimateService): RedirectResponse
    {
        $estimateService->transitionTo($estimate, $request->string('status')->toString());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Estimate status updated.')]);

        return to_route('estimates.index', $currentTeam);
    }

    /**
     * Remove a draft estimate.
     */
    public function destroy(Team $currentTeam, Estimate $estimate, EstimateService $estimateService): RedirectResponse
    {
        $estimateService->delete($estimate);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Estimate deleted.')]);

        return to_route('estimates.index', $currentTeam);
    }
}
