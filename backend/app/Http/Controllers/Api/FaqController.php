<?php

namespace App\Http\Controllers\Api;

use App\Domain\Content\Models\Faq;
use App\Http\Controllers\Controller;
use App\Http\Resources\FaqResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FaqController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return FaqResource::collection(Faq::with('category')->orderBy('sort_order')->paginate(50));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'faq_category_id' => ['required', 'uuid', 'exists:faq_categories,id'],
            'question'        => ['required', 'string'],
            'answer'          => ['required', 'string'],
            'sort_order'      => ['sometimes', 'integer', 'min:0'],
            'display_on_help' => ['sometimes', 'boolean'],
            'paused'          => ['sometimes', 'boolean'],
        ]);

        $faq = Faq::create($validated);

        return response()->json(['data' => new FaqResource($faq), 'message' => 'FAQ created.'], 201);
    }

    public function show(Faq $faq): JsonResponse
    {
        $faq->load('category', 'tags');

        return response()->json(['data' => new FaqResource($faq)]);
    }

    public function update(Request $request, Faq $faq): JsonResponse
    {
        $validated = $request->validate([
            'faq_category_id' => ['sometimes', 'uuid', 'exists:faq_categories,id'],
            'question'        => ['sometimes', 'string'],
            'answer'          => ['sometimes', 'string'],
            'sort_order'      => ['sometimes', 'integer', 'min:0'],
            'display_on_help' => ['sometimes', 'boolean'],
            'paused'          => ['sometimes', 'boolean'],
        ]);

        $faq->update($validated);

        return response()->json(['data' => new FaqResource($faq->fresh()), 'message' => 'FAQ updated.']);
    }

    public function destroy(Faq $faq): JsonResponse
    {
        $faq->delete();

        return response()->json(['message' => 'FAQ deleted.']);
    }
}
