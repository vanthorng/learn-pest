<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\Team;
use App\Services\Customer\CustomerService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    /**
     * Display the customers belonging to the current team.
     */
    public function index(Team $currentTeam): Response
    {
        return Inertia::render('customers/index', [
            'team' => [
                'name' => $currentTeam->name,
                'slug' => $currentTeam->slug,
            ],
            'customers' => $currentTeam->customers()
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'phone', 'address']),
        ]);
    }

    /**
     * Store a new customer for the current team.
     */
    public function store(StoreCustomerRequest $request, Team $currentTeam, CustomerService $customerService): RedirectResponse
    {
        $customerService->create($currentTeam, $request->validated());

        return to_route('customers.index', $currentTeam);
    }

    /**
     * Update the specified customer.
     */
    public function update(UpdateCustomerRequest $request, Team $currentTeam, Customer $customer, CustomerService $customerService): RedirectResponse
    {
        $customerService->update($customer, $request->validated());

        return to_route('customers.index', $currentTeam);
    }

    /**
     * Delete the specified customer.
     */
    public function destroy(Team $currentTeam, Customer $customer, CustomerService $customerService): RedirectResponse
    {
        $customerService->delete($customer);

        return to_route('customers.index', $currentTeam);
    }
}
