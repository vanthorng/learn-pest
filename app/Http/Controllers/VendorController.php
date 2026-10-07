<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vendor\StoreVendorRequest;
use App\Http\Requests\Vendor\UpdateVendorRequest;
use App\Models\Team;
use App\Models\Vendor;
use App\Services\Vendor\VendorService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class VendorController extends Controller
{
    public function index(Team $currentTeam): Response
    {
        return Inertia::render('vendors/index', [
            'vendors' => $currentTeam->vendors()->orderBy('name')->get(['id', 'name', 'email', 'phone']),
        ]);
    }

    public function store(StoreVendorRequest $request, Team $currentTeam, VendorService $vendorService): RedirectResponse
    {
        $vendorService->create($currentTeam, $request->validated());

        return to_route('vendors.index', $currentTeam);
    }

    public function update(UpdateVendorRequest $request, Team $currentTeam, Vendor $vendor, VendorService $vendorService): RedirectResponse
    {
        $vendorService->update($vendor, $request->validated());

        return to_route('vendors.index', $currentTeam);
    }

    public function destroy(Team $currentTeam, Vendor $vendor, VendorService $vendorService): RedirectResponse
    {
        $vendorService->delete($vendor);

        return to_route('vendors.index', $currentTeam);
    }
}
