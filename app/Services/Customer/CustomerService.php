<?php

namespace App\Services\Customer;

use App\Models\Customer;
use App\Models\Team;

class CustomerService
{
    /**
     * Create a customer for a team.
     *
     * @param  array{name: string, email?: ?string, phone?: ?string, address?: ?string}  $attributes
     */
    public function create(Team $team, array $attributes): Customer
    {
        return $team->customers()->create($attributes);
    }

    /**
     * Update a customer.
     *
     * @param  array{name: string, email?: ?string, phone?: ?string, address?: ?string}  $attributes
     */
    public function update(Customer $customer, array $attributes): void
    {
        $customer->update($attributes);
    }

    /**
     * Delete a customer.
     */
    public function delete(Customer $customer): void
    {
        $customer->delete();
    }
}
