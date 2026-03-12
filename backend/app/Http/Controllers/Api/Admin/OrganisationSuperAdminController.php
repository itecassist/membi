<?php

namespace App\Http\Controllers\Api\Admin;

use App\Domain\Member\Models\Member;
use App\Domain\Organisation\Models\Organisation;
use App\Http\Controllers\Controller;
use App\Http\Resources\MemberResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\Permission\Models\Role;

/**
 * Manages super-admin assignment for organisations.
 *
 * super-admin is a Membix-controlled role that gives a user full unrestricted
 * access to an organisation. It bypasses all permission checks (Gate::before).
 * Only Membix platform admins (is_admin = true) can be assigned this role.
 */
class OrganisationSuperAdminController extends Controller
{
    /**
     * List all super-admins for an organisation.
     */
    public function index(Organisation $organisation): AnonymousResourceCollection
    {
        setPermissionsTeamId($organisation->id);

        $role = Role::where('name', 'super-admin')
            ->where('organisation_id', $organisation->id)
            ->firstOrFail();

        $members = Member::whereHas('roles', fn ($q) => $q->where('id', $role->id))
            ->where('organisation_id', $organisation->id)
            ->with('user')
            ->get();

        return MemberResource::collection($members);
    }

    /**
     * Assign super-admin to a Membix admin user for this organisation.
     * Creates a member record if the user doesn't have one yet.
     */
    public function store(Request $request, Organisation $organisation): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'uuid', 'exists:users,id'],
        ]);

        $user = \App\Models\User::findOrFail($validated['user_id']);

        if (! $user->is_admin) {
            return response()->json([
                'message' => 'Only Membix platform admins can be assigned the super-admin role.',
            ], 422);
        }

        setPermissionsTeamId($organisation->id);

        $member = Member::firstOrCreate(
            ['user_id' => $user->id, 'organisation_id' => $organisation->id],
            ['email' => $user->email, 'is_active' => true, 'joined_at' => now()]
        );

        $superAdmin = Role::where('name', 'super-admin')
            ->where('organisation_id', $organisation->id)
            ->firstOrFail();

        $member->assignRole($superAdmin);

        return response()->json([
            'data'    => new MemberResource($member->load('user')),
            'message' => 'super-admin assigned.',
        ], 201);
    }

    /**
     * Remove super-admin from a member for this organisation.
     */
    public function destroy(Organisation $organisation, Member $member): JsonResponse
    {
        abort_unless($member->organisation_id === $organisation->id, 404);

        setPermissionsTeamId($organisation->id);

        $superAdmin = Role::where('name', 'super-admin')
            ->where('organisation_id', $organisation->id)
            ->firstOrFail();

        $member->removeRole($superAdmin);

        return response()->json(['message' => 'super-admin removed.']);
    }
}
