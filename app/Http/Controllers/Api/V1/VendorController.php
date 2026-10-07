<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\StoreVendorRequest;
use App\Http\Requests\Vendor\UpdateVendorRequest;
use App\Http\Resources\VendorResource;
use App\Models\Team;
use App\Models\Vendor;
use App\Services\Vendor\VendorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class VendorController extends Controller
{
    public function index(Team $currentTeam): AnonymousResourceCollection
    {
        return VendorResource::collection(
            $currentTeam->vendors()->orderBy('name')->get(),
        );
    }

    public function store(StoreVendorRequest $request, Team $currentTeam, VendorService $vendorService): JsonResponse
    {
        $vendor = $vendorService->create($currentTeam, $request->validated());

        return (new VendorResource($vendor))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Team $currentTeam, Vendor $vendor): VendorResource
    {
        return new VendorResource($vendor);
    }

    public function update(UpdateVendorRequest $request, Team $currentTeam, Vendor $vendor, VendorService $vendorService): VendorResource
    {
        $vendorService->update($vendor, $request->validated());

        return new VendorResource($vendor->fresh());
    }

    public function destroy(Team $currentTeam, Vendor $vendor, VendorService $vendorService): Response
    {
        $vendorService->delete($vendor);

        return response()->noContent();
    }
}
