<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\Team;
use App\Services\Customer\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class CustomerController extends Controller
{
    public function index(Team $currentTeam): AnonymousResourceCollection
    {
        return CustomerResource::collection(
            $currentTeam->customers()->orderBy('name')->get(),
        );
    }

    public function store(StoreCustomerRequest $request, Team $currentTeam, CustomerService $customerService): JsonResponse
    {
        $customer = $customerService->create($currentTeam, $request->validated());

        return (new CustomerResource($customer))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Team $currentTeam, Customer $customer): CustomerResource
    {
        return new CustomerResource($customer);
    }

    public function update(UpdateCustomerRequest $request, Team $currentTeam, Customer $customer, CustomerService $customerService): CustomerResource
    {
        $customerService->update($customer, $request->validated());

        return new CustomerResource($customer->fresh());
    }

    public function destroy(Team $currentTeam, Customer $customer, CustomerService $customerService): Response
    {
        $customerService->delete($customer);

        return response()->noContent();
    }
}
