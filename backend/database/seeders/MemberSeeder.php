<?php

namespace Database\Seeders;

use App\Domain\Member\Models\Group;
use App\Domain\Member\Models\GroupMember;
use App\Domain\Member\Models\Member;
use App\Domain\Organisation\Models\Organisation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $carol = User::where('email', 'carol@example.com')->firstOrFail();
        $dave  = User::where('email', 'dave@example.com')->firstOrFail();

        $tennis  = Organisation::where('seo_name', 'river')->firstOrFail();
        $cycling = Organisation::where('seo_name', 'north')->firstOrFail();

        // ── Additional members for Riverside Tennis ───────────────────────────
        $carolTennis = $this->addMember($carol, $tennis, 'Carol', 'Smith', 'committee');
        $daveTennis  = $this->addMember($dave,  $tennis, 'Dave',  'Jones');

        // ── Additional members for Northside Cycling ──────────────────────────
        $carolCycling = $this->addMember($carol, $cycling, 'Carol', 'Smith');
        $daveCycling  = $this->addMember($dave,  $cycling, 'Dave',  'Jones', 'committee');

        // ── Groups for Riverside Tennis ───────────────────────────────────────
        $seniorGroup = Group::firstOrCreate(
            ['organisation_id' => $tennis->id, 'name' => 'Seniors'],
            ['type' => 'corporate', 'is_active' => true]
        );

        $familyGroup = Group::firstOrCreate(
            ['organisation_id' => $tennis->id, 'name' => 'Smith Family'],
            ['type' => 'family', 'is_active' => true]
        );

        // Alice (owner) + Carol + Dave in Seniors
        $aliceMember = Member::where('organisation_id', $tennis->id)
            ->whereHas('user', fn ($q) => $q->where('email', 'alice@example.com'))
            ->first();

        foreach ([
            [$aliceMember->id, true],
            [$carolTennis->id, false],
            [$daveTennis->id,  false],
        ] as [$memberId, $isAdmin]) {
            GroupMember::firstOrCreate(
                ['group_id' => $seniorGroup->id, 'member_id' => $memberId],
                ['is_admin' => $isAdmin]
            );
        }

        // Carol + Dave in Smith Family group
        foreach ([
            [$carolTennis->id, true],
            [$daveTennis->id,  false],
        ] as [$memberId, $isAdmin]) {
            GroupMember::firstOrCreate(
                ['group_id' => $familyGroup->id, 'member_id' => $memberId],
                ['is_admin' => $isAdmin]
            );
        }

        // ── Groups for Northside Cycling ──────────────────────────────────────
        $roadGroup = Group::firstOrCreate(
            ['organisation_id' => $cycling->id, 'name' => 'Road Riders'],
            ['type' => 'corporate', 'is_active' => true]
        );

        $bobMember = Member::where('organisation_id', $cycling->id)
            ->whereHas('user', fn ($q) => $q->where('email', 'bob@example.com'))
            ->first();

        foreach ([
            [$bobMember->id,    true],
            [$daveCycling->id,  false],
            [$carolCycling->id, false],
        ] as [$memberId, $isAdmin]) {
            GroupMember::firstOrCreate(
                ['group_id' => $roadGroup->id, 'member_id' => $memberId],
                ['is_admin' => $isAdmin]
            );
        }

        $this->command->info('Members and groups seeded.');
    }

    private function addMember(
        User $user,
        Organisation $org,
        string $firstName,
        string $lastName,
        ?string $roleName = null
    ): Member {
        setPermissionsTeamId($org->id);

        $member = Member::firstOrCreate(
            ['user_id' => $user->id, 'organisation_id' => $org->id],
            [
                'email'      => $user->email,
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'is_active'  => true,
                'joined_at'  => now(),
            ]
        );

        if ($roleName) {
            $role = Role::where('name', $roleName)
                ->where('organisation_id', $org->id)
                ->first();

            if ($role) {
                $member->assignRole($role);
            }
        }

        return $member;
    }
}
