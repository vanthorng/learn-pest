<?php

namespace App\Services\Item;

use App\Models\Item;
use App\Models\Team;

class ItemService
{
    /**
     * Create an item for a team.
     *
     * @param  array{name: string, type: 'product'|'service', sku?: ?string, unit_price: string, description?: ?string}  $attributes
     */
    public function create(Team $team, array $attributes): Item
    {
        return $team->items()->create($attributes);
    }

    /**
     * Update an item.
     *
     * @param  array{name: string, type: 'product'|'service', sku?: ?string, unit_price: string, description?: ?string}  $attributes
     */
    public function update(Item $item, array $attributes): void
    {
        $item->update($attributes);
    }

    /**
     * Delete an item.
     */
    public function delete(Item $item): void
    {
        $item->delete();
    }
}
