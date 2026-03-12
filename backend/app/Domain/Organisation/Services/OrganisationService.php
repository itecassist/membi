<?php

namespace App\Domain\Organisation\Services;

use App\Domain\Organisation\Models\Organisation;
use App\Domain\Member\Models\Member;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class OrganisationService
{
    public function list(int $perPage = 15): LengthAwarePaginator
    {
        return Organisation::query()
            ->withCount('members')
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data, User $creator): Organisation
    {
        $organisation = Organisation::create($data);

        // Set org context for Spatie so roles are scoped to this org
        setPermissionsTeamId($organisation->id);

        // super-admin is reserved for Membix staff — created here but not assigned yet.
        // It is assigned via the Membix admin API only.
        Role::create([
            'name'            => 'super-admin',
            'guard_name'      => 'sanctum',
            'organisation_id' => $organisation->id,
        ]);

        // Creator gets the owner role — sync all org-visible permissions onto it automatically.
        $owner = Role::create([
            'name'            => 'owner',
            'guard_name'      => 'sanctum',
            'organisation_id' => $organisation->id,
        ]);

        $owner->syncPermissions(
            Permission::where('visible_to_organisations', true)
                ->where('guard_name', 'sanctum')
                ->get()
        );

        $member = Member::create([
            'user_id'         => $creator->id,
            'organisation_id' => $organisation->id,
            'email'           => $creator->email,
            'is_active'       => true,
            'joined_at'       => now(),
        ]);

        $member->assignRole($owner);

        return $organisation;
    }

    public function update(Organisation $organisation, array $data): Organisation
    {
        $organisation->update($data);

        return $organisation->fresh();
    }

    public function delete(Organisation $organisation): void
    {
        $organisation->delete();
    }
}
