<?php

namespace App\Http\Controllers\Api;

use App\Domain\Content\Models\ArticleCategory;
use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleCategoryResource;
use App\Http\Resources\ArticleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ArticleCategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ArticleCategoryResource::collection(ArticleCategory::orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'seo_name'     => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'live'         => ['sometimes', 'boolean'],
            'article_live' => ['sometimes', 'boolean'],
            'section_id'   => ['nullable', 'integer'],
        ]);

        $category = ArticleCategory::create($validated);

        return response()->json(['data' => new ArticleCategoryResource($category), 'message' => 'Article category created.'], 201);
    }

    public function show(ArticleCategory $articleCategory): JsonResponse
    {
        return response()->json(['data' => new ArticleCategoryResource($articleCategory)]);
    }

    public function update(Request $request, ArticleCategory $articleCategory): JsonResponse
    {
        $validated = $request->validate([
            'name'         => ['sometimes', 'string', 'max:255'],
            'seo_name'     => ['sometimes', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'live'         => ['sometimes', 'boolean'],
            'article_live' => ['sometimes', 'boolean'],
            'section_id'   => ['nullable', 'integer'],
        ]);

        $articleCategory->update($validated);

        return response()->json(['data' => new ArticleCategoryResource($articleCategory->fresh()), 'message' => 'Article category updated.']);
    }

    public function destroy(ArticleCategory $articleCategory): JsonResponse
    {
        $articleCategory->delete();

        return response()->json(['message' => 'Article category deleted.']);
    }

    public function articles(ArticleCategory $articleCategory): AnonymousResourceCollection
    {
        return ArticleResource::collection($articleCategory->articles()->where('live', true)->orderBy('title')->get());
    }
}
