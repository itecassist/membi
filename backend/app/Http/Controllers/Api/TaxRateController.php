<?php

namespace App\Http\Controllers\Api;

use App\Domain\Organisation\Models\Organisation;
use App\Domain\Organisation\Models\TaxRate;
use App\Http\Controllers\Controller;
use App\Http\Resources\TaxRateResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaxRateController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return TaxRateResource::collection(TaxRate::with(['country', 'zone'])->orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'country_id'  => ['nullable', 'uuid', 'exists:countries,id'],
            'zone_id'     => ['nullable', 'uuid', 'exists:zones,id'],
            'rate'        => ['required', 'numeric', 'min:0', 'max:99.9999'],
        ]);

        $taxRate = TaxRate::create($validated);

        return response()->json(['data' => new TaxRateResource($taxRate), 'message' => 'Tax rate created.'], 201);
    }

    public function show(TaxRate $taxRate): JsonResponse
    {
        $taxRate->load(['country', 'zone']);

        return response()->json(['data' => new TaxRateResource($taxRate)]);
    }

    public function update(Request $request, TaxRate $taxRate): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'country_id'  => ['nullable', 'uuid', 'exists:countries,id'],
            'zone_id'     => ['nullable', 'uuid', 'exists:zones,id'],
            'rate'        => ['sometimes', 'numeric', 'min:0', 'max:99.9999'],
        ]);

        $taxRate->update($validated);

        return response()->json(['data' => new TaxRateResource($taxRate->fresh()), 'message' => 'Tax rate updated.']);
    }

    public function destroy(TaxRate $taxRate): JsonResponse
    {
        $taxRate->delete();

        return response()->json(['message' => 'Tax rate deleted.']);
    }
}
