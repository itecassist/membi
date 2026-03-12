<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * All platform-level permissions.
     *
     * visible_to_organisations = true  → org admins can assign this to custom roles
     * visible_to_organisations = false → Membix platform use only
     */
    private array $permissions = [
        // ── Organisation ─────────────────────────────────────
        ['name' => 'organisation.read',   'visible_to_organisations' => false],
        ['name' => 'organisation.update', 'visible_to_organisations' => false],
        ['name' => 'organisation.delete', 'visible_to_organisations' => false],

        // ── Members ──────────────────────────────────────────
        ['name' => 'member.create', 'visible_to_organisations' => true],
        ['name' => 'member.read',   'visible_to_organisations' => true],
        ['name' => 'member.update', 'visible_to_organisations' => true],
        ['name' => 'member.delete', 'visible_to_organisations' => true],

        // ── Groups ───────────────────────────────────────────
        ['name' => 'group.create', 'visible_to_organisations' => true],
        ['name' => 'group.read',   'visible_to_organisations' => true],
        ['name' => 'group.update', 'visible_to_organisations' => true],
        ['name' => 'group.delete', 'visible_to_organisations' => true],

        // ── Subscriptions ────────────────────────────────────
        ['name' => 'subscription.create', 'visible_to_organisations' => true],
        ['name' => 'subscription.read',   'visible_to_organisations' => true],
        ['name' => 'subscription.update', 'visible_to_organisations' => true],
        ['name' => 'subscription.delete', 'visible_to_organisations' => true],

        // ── Roles ────────────────────────────────────────────
        ['name' => 'role.manage', 'visible_to_organisations' => true],
    ];

    public function run(): void
    {
        foreach ($this->permissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm['name'], 'guard_name' => 'sanctum'],
                ['visible_to_organisations' => $perm['visible_to_organisations']]
            );
        }

        $this->command->info('Permissions seeded: ' . count($this->permissions));
    }
}
