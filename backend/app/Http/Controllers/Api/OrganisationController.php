<?php

namespace App\Http\Controllers\Api;

use App\Domain\Organisation\Models\Organisation;
use App\Domain\Organisation\Services\OrganisationService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Organisation\StoreOrganisationRequest;
use App\Http\Requests\Organisation\UpdateOrganisationRequest;
use App\Http\Resources\OrganisationListResource;
use App\Http\Resources\OrganisationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrganisationController extends Controller
{
    public function __construct(private readonly OrganisationService $service) {}

    public function index(): AnonymousResourceCollection
    {
        return OrganisationListResource::collection($this->service->list());
    }

    public function store(StoreOrganisationRequest $request): JsonResponse
    {
        $organisation = $this->service->create($request->validated(), $request->user());

        return response()->json([
            'data'    => new OrganisationResource($organisation),
            'message' => 'Organisation created',
        ], 201);
    }

    public function show(Organisation $organisation): JsonResponse
    {
        return response()->json([
            'data' => new OrganisationResource($organisation),
        ]);
    }

    public function update(UpdateOrganisationRequest $request, Organisation $organisation): JsonResponse
    {
        $organisation = $this->service->update($organisation, $request->validated());

        return response()->json([
            'data'    => new OrganisationResource($organisation),
            'message' => 'Organisation updated',
        ]);
    }

    public function destroy(Organisation $organisation): JsonResponse
    {
        $this->service->delete($organisation);

        return response()->json(['message' => 'Organisation deleted']);
    }
}
