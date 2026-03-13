<?php

namespace App\Http\Controllers\Api;

use App\Domain\Content\Models\FaqCategory;
use App\Http\Controllers\Controller;
use App\Http\Resources\FaqCategoryResource;
use App\Http\Resources\FaqResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FaqCategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return FaqCategoryResource::collection(FaqCategory::orderBy('sort_order')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'sort_order'  => ['sometimes', 'integer', 'min:0'],
        ]);

        $category = FaqCategory::create($validated);

        return response()->json(['data' => new FaqCategoryResource($category), 'message' => 'FAQ category created.'], 201);
    }

    public function show(FaqCategory $faqCategory): JsonResponse
    {
        return response()->json(['data' => new FaqCategoryResource($faqCategory)]);
    }

    public function update(Request $request, FaqCategory $faqCategory): JsonResponse
    {
        $validated = $request->validate([
            'description' => ['sometimes', 'string', 'max:255'],
            'sort_order'  => ['sometimes', 'integer', 'min:0'],
        ]);

        $faqCategory->update($validated);

        return response()->json(['data' => new FaqCategoryResource($faqCategory->fresh()), 'message' => 'FAQ category updated.']);
    }

    public function destroy(FaqCategory $faqCategory): JsonResponse
    {
        $faqCategory->delete();

        return response()->json(['message' => 'FAQ category deleted.']);
    }

    public function faqs(FaqCategory $faqCategory): AnonymousResourceCollection
    {
        return FaqResource::collection($faqCategory->faqs()->where('paused', false)->orderBy('sort_order')->get());
    }
}
