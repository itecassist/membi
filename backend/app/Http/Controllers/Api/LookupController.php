<?php

namespace App\Http\Controllers\Api;

use App\Domain\Organisation\Models\Lookup;
use App\Domain\Organisation\Models\Organisation;
use App\Http\Controllers\Controller;
use App\Http\Resources\LookupResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LookupController extends Controller
{
    public function index(Organisation $organisation): AnonymousResourceCollection
    {
        abort_unless(request()->member->can('organisation.update'), 403);

        $lookups = Lookup::where('organisation_id', $organisation->id)->orderBy('name')->get();

        return LookupResource::collection($lookups);
    }

    public function store(Request $request, Organisation $organisation): JsonResponse
    {
        abort_unless($request->member->can('organisation.update'), 403);

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'value'       => ['required', 'string', 'max:255'],
        ]);

        $lookup = Lookup::create(array_merge($validated, ['organisation_id' => $organisation->id]));

        return response()->json(['data' => new LookupResource($lookup), 'message' => 'Lookup created.'], 201);
    }

    public function show(Organisation $organisation, Lookup $lookup): JsonResponse
    {
        abort_unless((int) $lookup->organisation_id === (int) $organisation->id, 404);
        abort_unless(request()->member->can('organisation.update'), 403);

        return response()->json(['data' => new LookupResource($lookup)]);
    }

    public function update(Request $request, Organisation $organisation, Lookup $lookup): JsonResponse
    {
        abort_unless((int) $lookup->organisation_id === (int) $organisation->id, 404);
        abort_unless($request->member->can('organisation.update'), 403);

        $validated = $request->validate([
            'name'        => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'value'       => ['sometimes', 'string', 'max:255'],
        ]);

        $lookup->update($validated);

        return response()->json(['data' => new LookupResource($lookup->fresh()), 'message' => 'Lookup updated.']);
    }

    public function destroy(Organisation $organisation, Lookup $lookup): JsonResponse
    {
        abort_unless((int) $lookup->organisation_id === (int) $organisation->id, 404);
        abort_unless(request()->member->can('organisation.update'), 403);

        $lookup->delete();

        return response()->json(['message' => 'Lookup deleted.']);
    }
}
