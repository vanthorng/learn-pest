<?php

namespace App\Services\Vendor;

use App\Models\Team;
use App\Models\Vendor;

class VendorService
{
    /**
     * Create a vendor for a team.
     *
     * @param  array{name: string, email?: ?string, phone?: ?string}  $attributes
     */
    public function create(Team $team, array $attributes): Vendor
    {
        return $team->vendors()->create($attributes);
    }

    /**
     * Update a vendor.
     *
     * @param  array{name: string, email?: ?string, phone?: ?string}  $attributes
     */
    public function update(Vendor $vendor, array $attributes): void
    {
        $vendor->update($attributes);
    }

    /**
     * Delete a vendor.
     */
    public function delete(Vendor $vendor): void
    {
        $vendor->delete();
    }
}
