<?php

namespace App\Http\Controllers\Api;

use App\Domain\Form\Models\VirtualField;
use App\Domain\Form\Models\VirtualForm;
use App\Http\Controllers\Controller;
use App\Http\Resources\VirtualFieldResource;
use App\Http\Resources\VirtualFormResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VirtualFormController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return VirtualFormResource::collection(VirtualForm::orderBy('category')->orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:255'],
            'name'     => ['required', 'string', 'max:255'],
        ]);

        $form = VirtualForm::create($validated);

        return response()->json(['data' => new VirtualFormResource($form), 'message' => 'Virtual form created.'], 201);
    }

    public function show(VirtualForm $virtualForm): JsonResponse
    {
        $virtualForm->load('fields');

        return response()->json(['data' => new VirtualFormResource($virtualForm)]);
    }

    public function update(Request $request, VirtualForm $virtualForm): JsonResponse
    {
        $validated = $request->validate([
            'category' => ['sometimes', 'string', 'max:255'],
            'name'     => ['sometimes', 'string', 'max:255'],
        ]);

        $virtualForm->update($validated);

        return response()->json(['data' => new VirtualFormResource($virtualForm->fresh()), 'message' => 'Virtual form updated.']);
    }

    public function destroy(VirtualForm $virtualForm): JsonResponse
    {
        $virtualForm->delete();

        return response()->json(['message' => 'Virtual form deleted.']);
    }

    // ── Fields ────────────────────────────────────────────────────────────────

    public function indexFields(VirtualForm $virtualForm): AnonymousResourceCollection
    {
        return VirtualFieldResource::collection($virtualForm->fields()->orderBy('sort_order')->get());
    }

    public function storeField(Request $request, VirtualForm $virtualForm): JsonResponse
    {
        $validated = $request->validate([
            'field_name'     => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'required'       => ['sometimes', 'boolean'],
            'type'           => ['required', 'string', 'max:50'],
            'options'        => ['nullable', 'array'],
            'gdpr_sensitive' => ['sometimes', 'boolean'],
            'active'         => ['sometimes', 'boolean'],
            'sort_order'     => ['sometimes', 'integer', 'min:0'],
        ]);

        $field = $virtualForm->fields()->create($validated);

        return response()->json(['data' => new VirtualFieldResource($field), 'message' => 'Field created.'], 201);
    }

    public function updateField(Request $request, VirtualForm $virtualForm, VirtualField $virtualField): JsonResponse
    {
        abort_unless($virtualField->virtual_form_id === $virtualForm->id, 404);

        $validated = $request->validate([
            'field_name'     => ['sometimes', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'required'       => ['sometimes', 'boolean'],
            'type'           => ['sometimes', 'string', 'max:50'],
            'options'        => ['nullable', 'array'],
            'gdpr_sensitive' => ['sometimes', 'boolean'],
            'active'         => ['sometimes', 'boolean'],
            'sort_order'     => ['sometimes', 'integer', 'min:0'],
        ]);

        $virtualField->update($validated);

        return response()->json(['data' => new VirtualFieldResource($virtualField->fresh()), 'message' => 'Field updated.']);
    }

    public function destroyField(VirtualForm $virtualForm, VirtualField $virtualField): JsonResponse
    {
        abort_unless($virtualField->virtual_form_id === $virtualForm->id, 404);

        $virtualField->delete();

        return response()->json(['message' => 'Field deleted.']);
    }
}
