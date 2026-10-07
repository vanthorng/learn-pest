<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use App\Models\Team;
use App\Services\Item\ItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ItemController extends Controller
{
    public function index(Team $currentTeam): AnonymousResourceCollection
    {
        return ItemResource::collection(
            $currentTeam->items()->orderBy('name')->get(),
        );
    }

    public function store(StoreItemRequest $request, Team $currentTeam, ItemService $itemService): JsonResponse
    {
        $item = $itemService->create($currentTeam, $request->validated());

        return (new ItemResource($item))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Team $currentTeam, Item $item): ItemResource
    {
        return new ItemResource($item);
    }

    public function update(UpdateItemRequest $request, Team $currentTeam, Item $item, ItemService $itemService): ItemResource
    {
        $itemService->update($item, $request->validated());

        return new ItemResource($item->fresh());
    }

    public function destroy(Team $currentTeam, Item $item, ItemService $itemService): Response
    {
        $itemService->delete($item);

        return response()->noContent();
    }
}
