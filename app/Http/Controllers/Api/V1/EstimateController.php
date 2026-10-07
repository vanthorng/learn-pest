<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEstimateRequest;
use App\Http\Requests\UpdateEstimateRequest;
use App\Http\Requests\UpdateEstimateStatusRequest;
use App\Http\Resources\EstimateResource;
use App\Models\Estimate;
use App\Models\Team;
use App\Services\Estimate\EstimateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class EstimateController extends Controller
{
    public function index(Team $currentTeam): AnonymousResourceCollection
    {
        return EstimateResource::collection(
            $currentTeam->estimates()
                ->with(['customer:id,name', 'lines:id,estimate_id,item_id,name,quantity,unit_price,line_total'])
                ->latest('id')
                ->get(),
        );
    }

    public function store(StoreEstimateRequest $request, Team $currentTeam, EstimateService $estimateService): JsonResponse
    {
        $estimate = $estimateService->create($currentTeam, $request->validated());

        return (new EstimateResource($this->loadEstimate($estimate)))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Team $currentTeam, Estimate $estimate): EstimateResource
    {
        return new EstimateResource($this->loadEstimate($estimate));
    }

    public function update(UpdateEstimateRequest $request, Team $currentTeam, Estimate $estimate, EstimateService $estimateService): EstimateResource
    {
        $estimateService->update($estimate, $currentTeam, $request->validated());

        return new EstimateResource($this->loadEstimate($estimate));
    }

    public function updateStatus(UpdateEstimateStatusRequest $request, Team $currentTeam, Estimate $estimate, EstimateService $estimateService): EstimateResource
    {
        $estimateService->transitionTo($estimate, $request->string('status')->toString());

        return new EstimateResource($this->loadEstimate($estimate));
    }

    public function destroy(Team $currentTeam, Estimate $estimate, EstimateService $estimateService): Response
    {
        $estimateService->delete($estimate);

        return response()->noContent();
    }

    private function loadEstimate(Estimate $estimate): Estimate
    {
        $estimate->load([
            'customer:id,name',
            'lines:id,estimate_id,item_id,name,quantity,unit_price,line_total',
        ]);

        return $estimate;
    }
}
