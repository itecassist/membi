<?php

namespace App\Http\Controllers\Api;

use App\Domain\Organisation\Models\Organisation;
use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Manages custom roles within an organisation.
 */
class RoleController extends Controller
{
    public function index(Organisation $organisation): AnonymousResourceCollection
    {
        abort_unless(request()->member->can('role.manage'), 403);

        $roles = Role::where('organisation_id', $organisation->id)
            ->with('permissions')
            ->get();

        return RoleResource::collection($roles);
    }

    public function store(Request $request, Organisation $organisation): JsonResponse
    {
        abort_unless($request->member->can('role.manage'), 403);
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'not_regex:/^(super-admin|owner)$/i',
                Rule::unique('roles', 'name')->where('organisation_id', $organisation->id),
            ],
        ]);

        $role = Role::create([
            'name'            => $validated['name'],
            'guard_name'      => 'sanctum',
            'organisation_id' => $organisation->id,
        ]);

        return response()->json([
            'data'    => new RoleResource($role->load('permissions')),
            'message' => 'Role created.',
        ], 201);
    }

    public function show(Organisation $organisation, Role $role): JsonResponse
    {
        abort_unless($role->organisation_id === $organisation->id, 404);
        abort_unless(request()->member->can('role.manage'), 403);

        return response()->json([
            'data' => new RoleResource($role->load('permissions')),
        ]);
    }

    public function update(Request $request, Organisation $organisation, Role $role): JsonResponse
    {
        abort_unless($role->organisation_id === $organisation->id, 404);
        abort_unless($request->member->can('role.manage'), 403);
        abort_if(in_array($role->name, ['super-admin', 'owner']), 422, 'The ' . $role->name . ' role cannot be renamed.');

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->where('organisation_id', $organisation->id)->ignore($role->id),
            ],
        ]);

        $role->update(['name' => $validated['name']]);

        return response()->json([
            'data' => new RoleResource($role->fresh()->load('permissions')),
        ]);
    }

    public function destroy(Organisation $organisation, Role $role): JsonResponse
    {
        abort_unless($role->organisation_id === $organisation->id, 404);
        abort_unless(request()->member->can('role.manage'), 403);
        abort_if(in_array($role->name, ['super-admin', 'owner']), 422, 'The ' . $role->name . ' role cannot be deleted.');

        $role->delete();

        return response()->json(['message' => 'Role deleted.']);
    }

    public function syncPermissions(Request $request, Organisation $organisation, Role $role): JsonResponse
    {
        abort_unless($role->organisation_id === $organisation->id, 404);
        abort_unless($request->member->can('role.manage'), 403);

        $validated = $request->validate([
            'permissions'   => ['required', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        // Org admins can only assign permissions that Membix has made visible
        $allowedPermissions = Permission::where('visible_to_organisations', true)
            ->whereIn('name', $validated['permissions'])
            ->pluck('name')
            ->toArray();

        $denied = array_diff($validated['permissions'], $allowedPermissions);
        if (! empty($denied)) {
            return response()->json([
                'message' => 'Some permissions are not available to organisations.',
                'denied'  => array_values($denied),
            ], 422);
        }

        $role->syncPermissions($allowedPermissions);

        return response()->json([
            'data'    => new RoleResource($role->fresh()->load('permissions')),
            'message' => 'Permissions updated.',
        ]);
    }

    public function assignToMember(Request $request, Organisation $organisation, Role $role): JsonResponse
    {
        abort_unless($role->organisation_id === $organisation->id, 404);
        abort_unless($request->member->can('role.manage'), 403);
        abort_if($role->name === 'super-admin', 403, 'super-admin can only be assigned by Membix administrators.');

        $validated = $request->validate([
            'member_id' => ['required', 'uuid', 'exists:members,id'],
        ]);

        $member = \App\Domain\Member\Models\Member::where('id', $validated['member_id'])
            ->where('organisation_id', $organisation->id)
            ->firstOrFail();

        $member->assignRole($role);

        return response()->json(['message' => 'Role assigned.']);
    }

    public function removeFromMember(Request $request, Organisation $organisation, Role $role): JsonResponse
    {
        abort_unless($role->organisation_id === $organisation->id, 404);
        abort_unless($request->member->can('role.manage'), 403);
        abort_if($role->name === 'super-admin', 403, 'super-admin can only be managed by Membix administrators.');

        $validated = $request->validate([
            'member_id' => ['required', 'uuid', 'exists:members,id'],
        ]);

        $member = \App\Domain\Member\Models\Member::where('id', $validated['member_id'])
            ->where('organisation_id', $organisation->id)
            ->firstOrFail();

        $member->removeRole($role);

        return response()->json(['message' => 'Role removed.']);
    }
}
