<?php

namespace App\Http\Controllers\Api;

use App\Domain\Organisation\Models\AccountingCode;
use App\Domain\Organisation\Models\Organisation;
use App\Http\Controllers\Controller;
use App\Http\Resources\AccountingCodeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AccountingCodeController extends Controller
{
    public function index(Organisation $organisation): AnonymousResourceCollection
    {
        abort_unless(request()->member->can('organisation.config.update'), 403);

        $config = $organisation->configFinancial;
        abort_if(! $config, 404);

        return AccountingCodeResource::collection($config->accountingCodes);
    }

    public function store(Request $request, Organisation $organisation): JsonResponse
    {
        abort_unless($request->member->can('organisation.config.update'), 403);

        $config = $organisation->configFinancial;
        abort_if(! $config, 422, 'Financial config not set up for this organisation.');

        $validated = $request->validate([
            'code'        => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        $code = $config->accountingCodes()->create($validated);

        return response()->json(['data' => new AccountingCodeResource($code), 'message' => 'Accounting code created.'], 201);
    }

    public function show(Organisation $organisation, AccountingCode $accountingCode): JsonResponse
    {
        abort_unless(request()->member->can('organisation.config.update'), 403);
        abort_unless($organisation->configFinancial?->id === $accountingCode->organisation_config_financial_id, 404);

        return response()->json(['data' => new AccountingCodeResource($accountingCode)]);
    }

    public function update(Request $request, Organisation $organisation, AccountingCode $accountingCode): JsonResponse
    {
        abort_unless($request->member->can('organisation.config.update'), 403);
        abort_unless($organisation->configFinancial?->id === $accountingCode->organisation_config_financial_id, 404);

        $validated = $request->validate([
            'code'        => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string', 'max:255'],
        ]);

        $accountingCode->update($validated);

        return response()->json(['data' => new AccountingCodeResource($accountingCode->fresh()), 'message' => 'Accounting code updated.']);
    }

    public function destroy(Organisation $organisation, AccountingCode $accountingCode): JsonResponse
    {
        abort_unless(request()->member->can('organisation.config.update'), 403);
        abort_unless($organisation->configFinancial?->id === $accountingCode->organisation_config_financial_id, 404);

        $accountingCode->delete();

        return response()->json(['message' => 'Accounting code deleted.']);
    }
}
