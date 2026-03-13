<?php

namespace App\Http\Controllers\Api;

use App\Domain\Content\Models\Article;
use App\Domain\Content\Models\ArticleCategory;
use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ArticleController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ArticleResource::collection(Article::with('category')->orderBy('title')->paginate(50));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'article_category_id' => ['required', 'uuid', 'exists:article_categories,id'],
            'type'                => ['required', 'string', 'max:50'],
            'title'               => ['required', 'string', 'max:255'],
            'page_title'          => ['nullable', 'string', 'max:255'],
            'seo_name'            => ['required', 'string', 'max:255'],
            'content'             => ['required', 'string'],
            'summary'             => ['nullable', 'string'],
            'seo_description'     => ['nullable', 'string'],
            'featured'            => ['sometimes', 'boolean'],
            'live'                => ['sometimes', 'boolean'],
            'category_live'       => ['sometimes', 'boolean'],
        ]);

        $article = Article::create($validated);

        return response()->json(['data' => new ArticleResource($article), 'message' => 'Article created.'], 201);
    }

    public function show(Article $article): JsonResponse
    {
        $article->load('category', 'tags');

        return response()->json(['data' => new ArticleResource($article)]);
    }

    public function update(Request $request, Article $article): JsonResponse
    {
        $validated = $request->validate([
            'article_category_id' => ['sometimes', 'uuid', 'exists:article_categories,id'],
            'type'                => ['sometimes', 'string', 'max:50'],
            'title'               => ['sometimes', 'string', 'max:255'],
            'page_title'          => ['nullable', 'string', 'max:255'],
            'seo_name'            => ['sometimes', 'string', 'max:255'],
            'content'             => ['sometimes', 'string'],
            'summary'             => ['nullable', 'string'],
            'seo_description'     => ['nullable', 'string'],
            'featured'            => ['sometimes', 'boolean'],
            'live'                => ['sometimes', 'boolean'],
            'category_live'       => ['sometimes', 'boolean'],
        ]);

        $article->update($validated);

        return response()->json(['data' => new ArticleResource($article->fresh()), 'message' => 'Article updated.']);
    }

    public function destroy(Article $article): JsonResponse
    {
        $article->delete();

        return response()->json(['message' => 'Article deleted.']);
    }
}
