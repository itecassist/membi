<?php

namespace App\Http\Controllers\Api;

use App\Domain\Form\Models\VirtualForm;
use App\Domain\Form\Models\VirtualRecord;
use App\Domain\Organisation\Models\Organisation;
use App\Http\Controllers\Controller;
use App\Http\Resources\VirtualRecordResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VirtualRecordController extends Controller
{
    public function index(Organisation $organisation): AnonymousResourceCollection
    {
        abort_unless(request()->member->can('form.read'), 403);

        return VirtualRecordResource::collection(
            VirtualRecord::where('organisation_id', $organisation->id)
                ->with(['form', 'member'])
                ->latest()
                ->paginate(50)
        );
    }

    public function store(Request $request, Organisation $organisation): JsonResponse
    {
        abort_unless($request->member->can('form.create'), 403);

        $validated = $request->validate([
            'virtual_form_id' => ['required', 'uuid', 'exists:virtual_forms,id'],
            'member_id'       => ['nullable', 'uuid', 'exists:members,id'],
            'data'            => ['required', 'array'],
        ]);

        $record = VirtualRecord::create(array_merge($validated, ['organisation_id' => $organisation->id]));

        return response()->json(['data' => new VirtualRecordResource($record), 'message' => 'Record created.'], 201);
    }

    public function show(Organisation $organisation, VirtualRecord $virtualRecord): JsonResponse
    {
        abort_unless($virtualRecord->organisation_id === $organisation->id, 404);
        abort_unless(request()->member->can('form.read'), 403);

        $virtualRecord->load(['form', 'member']);

        return response()->json(['data' => new VirtualRecordResource($virtualRecord)]);
    }

    public function update(Request $request, Organisation $organisation, VirtualRecord $virtualRecord): JsonResponse
    {
        abort_unless($virtualRecord->organisation_id === $organisation->id, 404);
        abort_unless($request->member->can('form.update'), 403);

        $validated = $request->validate([
            'data' => ['required', 'array'],
        ]);

        $virtualRecord->update($validated);

        return response()->json(['data' => new VirtualRecordResource($virtualRecord->fresh()), 'message' => 'Record updated.']);
    }

    public function destroy(Organisation $organisation, VirtualRecord $virtualRecord): JsonResponse
    {
        abort_unless($virtualRecord->organisation_id === $organisation->id, 404);
        abort_unless(request()->member->can('form.delete'), 403);

        $virtualRecord->delete();

        return response()->json(['message' => 'Record deleted.']);
    }
}
