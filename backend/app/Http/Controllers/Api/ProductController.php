<?php

namespace App\Http\Controllers\Api;

use App\Domain\Organisation\Models\Organisation;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\ProductOption;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductOptionResource;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(Organisation $organisation): AnonymousResourceCollection
    {
        abort_unless(request()->member->can('product.read'), 403);

        return ProductResource::collection(
            Product::where('organisation_id', $organisation->id)->with('category')->orderBy('name')->paginate(50)
        );
    }

    public function store(Request $request, Organisation $organisation): JsonResponse
    {
        abort_unless($request->member->can('product.create'), 403);

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'lookup_id'   => ['nullable', 'uuid', 'exists:lookups,id'],
        ]);

        $product = Product::create(array_merge($validated, ['organisation_id' => $organisation->id]));

        return response()->json(['data' => new ProductResource($product), 'message' => 'Product created.'], 201);
    }

    public function show(Organisation $organisation, Product $product): JsonResponse
    {
        abort_unless($product->organisation_id === $organisation->id, 404);
        abort_unless(request()->member->can('product.read'), 403);

        $product->load(['category', 'options']);

        return response()->json(['data' => new ProductResource($product)]);
    }

    public function update(Request $request, Organisation $organisation, Product $product): JsonResponse
    {
        abort_unless($product->organisation_id === $organisation->id, 404);
        abort_unless($request->member->can('product.update'), 403);

        $validated = $request->validate([
            'name'        => ['sometimes', 'string', 'max:255'],
            'code'        => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'lookup_id'   => ['nullable', 'uuid', 'exists:lookups,id'],
        ]);

        $product->update($validated);

        return response()->json(['data' => new ProductResource($product->fresh()), 'message' => 'Product updated.']);
    }

    public function destroy(Organisation $organisation, Product $product): JsonResponse
    {
        abort_unless($product->organisation_id === $organisation->id, 404);
        abort_unless(request()->member->can('product.delete'), 403);

        $product->delete();

        return response()->json(['message' => 'Product deleted.']);
    }

    // ── Options ───────────────────────────────────────────────────────────────

    public function indexOptions(Organisation $organisation, Product $product): AnonymousResourceCollection
    {
        abort_unless($product->organisation_id === $organisation->id, 404);
        abort_unless(request()->member->can('product.read'), 403);

        return ProductOptionResource::collection($product->options()->with('variants')->get());
    }

    public function storeOption(Request $request, Organisation $organisation, Product $product): JsonResponse
    {
        abort_unless($product->organisation_id === $organisation->id, 404);
        abort_unless($request->member->can('product.create'), 403);

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'group_id'    => ['nullable', 'uuid'],
            'available'   => ['sometimes', 'boolean'],
        ]);

        $option = $product->options()->create(
            array_merge($validated, ['organisation_id' => $organisation->id])
        );

        return response()->json(['data' => new ProductOptionResource($option), 'message' => 'Product option created.'], 201);
    }

    public function updateOption(Request $request, Organisation $organisation, Product $product, ProductOption $option): JsonResponse
    {
        abort_unless($product->organisation_id === $organisation->id, 404);
        abort_unless($option->product_id === $product->id, 404);
        abort_unless($request->member->can('product.update'), 403);

        $validated = $request->validate([
            'name'        => ['sometimes', 'string', 'max:255'],
            'code'        => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'available'   => ['sometimes', 'boolean'],
        ]);

        $option->update($validated);

        return response()->json(['data' => new ProductOptionResource($option->fresh()), 'message' => 'Product option updated.']);
    }

    public function destroyOption(Organisation $organisation, Product $product, ProductOption $option): JsonResponse
    {
        abort_unless($product->organisation_id === $organisation->id, 404);
        abort_unless($option->product_id === $product->id, 404);
        abort_unless(request()->member->can('product.delete'), 403);

        $option->delete();

        return response()->json(['message' => 'Product option deleted.']);
    }
}
