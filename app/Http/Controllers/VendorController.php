<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vendor\StoreVendorRequest;
use App\Http\Requests\Vendor\UpdateVendorRequest;
use App\Models\Team;
use App\Models\Vendor;
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

    public function store(StoreVendorRequest $request, Team $currentTeam): RedirectResponse
    {
        $currentTeam->vendors()->create($request->validated());

        return to_route('vendors.index', $currentTeam);
    }

    public function update(UpdateVendorRequest $request, Team $currentTeam, Vendor $vendor): RedirectResponse
    {
        $vendor->update($request->validated());

        return to_route('vendors.index', $currentTeam);
    }

    public function destroy(Team $currentTeam, Vendor $vendor): RedirectResponse
    {
        $vendor->delete();

        return to_route('vendors.index', $currentTeam);
    }
}
