<?php

namespace App\Domain\Member\Services;

use App\Domain\Member\Models\Group;
use App\Domain\Organisation\Models\Organisation;
use Illuminate\Pagination\LengthAwarePaginator;

class GroupService
{
    public function listForOrganisation(Organisation $organisation, int $perPage = 15): LengthAwarePaginator
    {
        return $organisation->groups()
            ->withCount('members')
            ->latest()
            ->paginate($perPage);
    }

    public function create(Organisation $organisation, array $data): Group
    {
        return $organisation->groups()->create($data);
    }

    public function update(Group $group, array $data): Group
    {
        $group->update($data);

        return $group->fresh();
    }

    public function delete(Group $group): void
    {
        $group->delete();
    }

    public function addMember(Group $group, string $memberId, bool $isAdmin = false): void
    {
        $group->members()->syncWithoutDetaching([
            $memberId => ['is_admin' => $isAdmin],
        ]);
    }

    public function removeMember(Group $group, string $memberId): void
    {
        $group->members()->detach($memberId);
    }
}
