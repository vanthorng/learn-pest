<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Models\Item;
use App\Models\Team;
use App\Services\Item\ItemService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ItemController extends Controller
{
    /**
     * Display the team's products and services.
     */
    public function index(Team $currentTeam): Response
    {
        return Inertia::render('items/index', [
            'team' => [
                'name' => $currentTeam->name,
                'slug' => $currentTeam->slug,
            ],
            'items' => $currentTeam->items()
                ->orderBy('name')
                ->get(['id', 'name', 'type', 'sku', 'unit_price', 'description']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreItemRequest $request, Team $currentTeam, ItemService $itemService): RedirectResponse
    {
        $itemService->create($currentTeam, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Item created.')]);

        return to_route('items.index', $currentTeam);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateItemRequest $request, Team $currentTeam, Item $item, ItemService $itemService): RedirectResponse
    {
        $itemService->update($item, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Item updated.')]);

        return to_route('items.index', $currentTeam);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Team $currentTeam, Item $item, ItemService $itemService): RedirectResponse
    {
        $itemService->delete($item);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Item deleted.')]);

        return to_route('items.index', $currentTeam);
    }
}
