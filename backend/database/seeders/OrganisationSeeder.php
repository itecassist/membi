<?php

namespace Database\Seeders;

use App\Domain\Member\Models\Member;
use App\Domain\Organisation\Models\Organisation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class OrganisationSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@membix.com')->firstOrFail();
        $alice = User::where('email', 'alice@example.com')->firstOrFail();
        $bob   = User::where('email', 'bob@example.com')->firstOrFail();

        // ── Organisation 1: Riverside Tennis Club (owned by Alice) ───────────
        $tennis = Organisation::firstOrCreate(
            ['seo_name' => 'riverside-tennis'],
            [
                'name'      => 'Riverside Tennis Club',
                'email'     => 'info@riverside-tennis.example.com',
                'timezone'  => 'Europe/London',
                'is_active' => true,
            ]
        );

        $this->setupOrg($tennis, $alice, $admin);

        // ── Organisation 2: Northside Cycling Club (owned by Bob) ─────────────
        $cycling = Organisation::firstOrCreate(
            ['seo_name' => 'northside-cycling'],
            [
                'name'      => 'Northside Cycling Club',
                'email'     => 'info@northside-cycling.example.com',
                'timezone'  => 'Europe/London',
                'is_active' => true,
            ]
        );

        $this->setupOrg($cycling, $bob, $admin);

        $this->command->info('Organisations seeded.');
    }

    /**
     * Bootstrap an organisation with its system roles, assign owner, and
     * assign super-admin (Membix admin) so it can be managed from day one.
     */
    private function setupOrg(Organisation $org, User $owner, User $superAdminUser): void
    {
        setPermissionsTeamId($org->id);

        // ── System roles ─────────────────────────────────────────────────────
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'super-admin', 'organisation_id' => $org->id],
            ['guard_name' => 'sanctum']
        );

        $ownerRole = Role::firstOrCreate(
            ['name' => 'owner', 'organisation_id' => $org->id],
            ['guard_name' => 'sanctum']
        );

        // ── Custom role: committee ────────────────────────────────────────────
        $committeeRole = Role::firstOrCreate(
            ['name' => 'committee', 'organisation_id' => $org->id],
            ['guard_name' => 'sanctum']
        );

        // Give owner + committee all org-visible permissions
        $orgPermissions = Permission::where('visible_to_organisations', true)
            ->where('guard_name', 'sanctum')
            ->get();

        $ownerRole->syncPermissions($orgPermissions);
        $committeeRole->syncPermissions(
            $orgPermissions->whereIn('name', [
                'member.read', 'member.update',
                'group.read',  'group.update',
                'subscription.read',
            ])
        );

        // ── Owner member ──────────────────────────────────────────────────────
        [$firstName, $lastName] = $this->splitName($owner->email);

        $ownerMember = Member::firstOrCreate(
            ['user_id' => $owner->id, 'organisation_id' => $org->id],
            [
                'email'      => $owner->email,
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'is_active'  => true,
                'joined_at'  => now(),
            ]
        );
        $ownerMember->syncRoles([$ownerRole]);

        // ── Membix super-admin member ─────────────────────────────────────────
        $superAdminMember = Member::firstOrCreate(
            ['user_id' => $superAdminUser->id, 'organisation_id' => $org->id],
            [
                'email'      => $superAdminUser->email,
                'first_name' => 'Membix',
                'last_name'  => 'Admin',
                'is_active'  => true,
                'joined_at'  => now(),
            ]
        );
        $superAdminMember->syncRoles([$superAdminRole]);
    }

    private function splitName(string $email): array
    {
        $local = explode('@', $email)[0];
        return [ucfirst($local), ''];
    }
}
