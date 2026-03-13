<?php

namespace App\Http\Controllers\Api;

use App\Domain\Organisation\Models\Organisation;
use App\Domain\Product\Models\ProductCategory;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCategoryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductCategoryController extends Controller
{
    public function index(Organisation $organisation): AnonymousResourceCollection
    {
        abort_unless(request()->member->can('product.read'), 403);

        return ProductCategoryResource::collection(
            ProductCategory::where('organisation_id', $organisation->id)->orderBy('name')->get()
        );
    }

    public function store(Request $request, Organisation $organisation): JsonResponse
    {
        abort_unless($request->member->can('product.create'), 403);

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'uuid', 'exists:product_categories,id'],
        ]);

        $category = ProductCategory::create(array_merge($validated, ['organisation_id' => $organisation->id]));

        return response()->json(['data' => new ProductCategoryResource($category), 'message' => 'Product category created.'], 201);
    }

    public function show(Organisation $organisation, ProductCategory $productCategory): JsonResponse
    {
        abort_unless($productCategory->organisation_id === $organisation->id, 404);
        abort_unless(request()->member->can('product.read'), 403);

        return response()->json(['data' => new ProductCategoryResource($productCategory)]);
    }

    public function update(Request $request, Organisation $organisation, ProductCategory $productCategory): JsonResponse
    {
        abort_unless($productCategory->organisation_id === $organisation->id, 404);
        abort_unless($request->member->can('product.update'), 403);

        $validated = $request->validate([
            'name'      => ['sometimes', 'string', 'max:255'],
            'parent_id' => ['nullable', 'uuid', 'exists:product_categories,id'],
        ]);

        $productCategory->update($validated);

        return response()->json(['data' => new ProductCategoryResource($productCategory->fresh()), 'message' => 'Product category updated.']);
    }

    public function destroy(Organisation $organisation, ProductCategory $productCategory): JsonResponse
    {
        abort_unless($productCategory->organisation_id === $organisation->id, 404);
        abort_unless(request()->member->can('product.delete'), 403);

        $productCategory->delete();

        return response()->json(['message' => 'Product category deleted.']);
    }
}
