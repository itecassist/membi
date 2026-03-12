<?php

namespace App\Domain\Member\Services;

use App\Domain\Member\Models\Member;
use App\Domain\Organisation\Models\Organisation;
use Illuminate\Pagination\LengthAwarePaginator;

class MemberService
{
    public function listForOrganisation(Organisation $organisation, int $perPage = 15): LengthAwarePaginator
    {
        return $organisation->members()
            ->with('user')
            ->latest()
            ->paginate($perPage);
    }

    public function create(Organisation $organisation, array $data): Member
    {
        return $organisation->members()->create($data);
    }

    public function update(Member $member, array $data): Member
    {
        $member->update($data);

        return $member->fresh();
    }

    public function delete(Member $member): void
    {
        $member->delete();
    }
}
