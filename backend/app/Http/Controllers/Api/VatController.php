<?php

namespace App\Http\Controllers\Api;

use App\Domain\Organisation\Models\Organisation;
use App\Domain\Organisation\Models\Vat;
use App\Http\Controllers\Controller;
use App\Http\Resources\VatResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VatController extends Controller
{
    public function index(Organisation $organisation): AnonymousResourceCollection
    {
        abort_unless(request()->member->can('organisation.config.update'), 403);

        $config = $organisation->configFinancial;
        abort_if(! $config, 404);

        return VatResource::collection($config->vats);
    }

    public function store(Request $request, Organisation $organisation): JsonResponse
    {
        abort_unless($request->member->can('organisation.config.update'), 403);

        $config = $organisation->configFinancial;
        abort_if(! $config, 422, 'Financial config not set up for this organisation.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $vat = $config->vats()->create($validated);

        return response()->json(['data' => new VatResource($vat), 'message' => 'VAT rate created.'], 201);
    }

    public function show(Organisation $organisation, Vat $vat): JsonResponse
    {
        abort_unless(request()->member->can('organisation.config.update'), 403);
        abort_unless($organisation->configFinancial?->id === $vat->organisation_config_financial_id, 404);

        return response()->json(['data' => new VatResource($vat)]);
    }

    public function update(Request $request, Organisation $organisation, Vat $vat): JsonResponse
    {
        abort_unless($request->member->can('organisation.config.update'), 403);
        abort_unless($organisation->configFinancial?->id === $vat->organisation_config_financial_id, 404);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'rate' => ['sometimes', 'numeric', 'min:0', 'max:100'],
        ]);

        $vat->update($validated);

        return response()->json(['data' => new VatResource($vat->fresh()), 'message' => 'VAT rate updated.']);
    }

    public function destroy(Organisation $organisation, Vat $vat): JsonResponse
    {
        abort_unless(request()->member->can('organisation.config.update'), 403);
        abort_unless($organisation->configFinancial?->id === $vat->organisation_config_financial_id, 404);

        $vat->delete();

        return response()->json(['message' => 'VAT rate deleted.']);
    }
}
