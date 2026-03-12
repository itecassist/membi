<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * List all permissions (admin sees all).
     */
    public function index(): AnonymousResourceCollection
    {
        $permissions = Permission::orderBy('name')->get();

        return PermissionResource::collection($permissions);
    }

    /**
     * Create a new permission.
     */
    public function store(Request $request): PermissionResource
    {
        $validated = $request->validate([
            'name'                     => ['required', 'string', 'max:255', Rule::unique('permissions', 'name')],
            'guard_name'               => ['nullable', 'string', 'max:255'],
            'visible_to_organisations' => ['nullable', 'boolean'],
        ]);

        $permission = Permission::create([
            'name'                     => $validated['name'],
            'guard_name'               => $validated['guard_name'] ?? 'sanctum',
            'visible_to_organisations' => $validated['visible_to_organisations'] ?? false,
        ]);

        return new PermissionResource($permission);
    }

    /**
     * Show a single permission.
     */
    public function show(Permission $permission): PermissionResource
    {
        return new PermissionResource($permission);
    }

    /**
     * Update a permission's name or visibility.
     */
    public function update(Request $request, Permission $permission): PermissionResource
    {
        $validated = $request->validate([
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('permissions', 'name')->ignore($permission->id),
            ],
            'visible_to_organisations' => ['sometimes', 'boolean'],
        ]);

        $permission->update($validated);

        return new PermissionResource($permission->fresh());
    }

    /**
     * Delete a permission.
     */
    public function destroy(Permission $permission): JsonResponse
    {
        $permission->delete();

        return response()->json(['message' => 'Permission deleted.']);
    }

    /**
     * Toggle visibility to organisations.
     */
    public function toggleVisibility(Permission $permission): PermissionResource
    {
        $permission->update([
            'visible_to_organisations' => ! $permission->visible_to_organisations,
        ]);

        return new PermissionResource($permission->fresh());
    }
}
