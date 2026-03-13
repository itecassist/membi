<?php

namespace App\Http\Controllers\Api;

use App\Domain\Organisation\Models\Organisation;
use App\Domain\Organisation\Models\OrganisationList;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrganisationListResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrganisationListController extends Controller
{
    public function index(Organisation $organisation): AnonymousResourceCollection
    {
        abort_unless(request()->member->can('organisation.update'), 403);

        return OrganisationListResource::collection(
            OrganisationList::where('organisation_id', $organisation->id)->orderBy('name')->get()
        );
    }

    public function store(Request $request, Organisation $organisation): JsonResponse
    {
        abort_unless($request->member->can('organisation.update'), 403);

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'query'       => ['required', 'string'],
        ]);

        $list = OrganisationList::create(array_merge($validated, ['organisation_id' => $organisation->id]));

        return response()->json(['data' => new OrganisationListResource($list), 'message' => 'List created.'], 201);
    }

    public function show(Organisation $organisation, OrganisationList $list): JsonResponse
    {
        abort_unless($list->organisation_id === $organisation->id, 404);
        abort_unless(request()->member->can('organisation.update'), 403);

        return response()->json(['data' => new OrganisationListResource($list)]);
    }

    public function update(Request $request, Organisation $organisation, OrganisationList $list): JsonResponse
    {
        abort_unless($list->organisation_id === $organisation->id, 404);
        abort_unless($request->member->can('organisation.update'), 403);

        $validated = $request->validate([
            'name'        => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'query'       => ['sometimes', 'string'],
        ]);

        $list->update($validated);

        return response()->json(['data' => new OrganisationListResource($list->fresh()), 'message' => 'List updated.']);
    }

    public function destroy(Organisation $organisation, OrganisationList $list): JsonResponse
    {
        abort_unless($list->organisation_id === $organisation->id, 404);
        abort_unless(request()->member->can('organisation.update'), 403);

        $list->delete();

        return response()->json(['message' => 'List deleted.']);
    }
}
