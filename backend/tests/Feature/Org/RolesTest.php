<?php

use App\Domain\Member\Models\Member;
use App\Domain\Organisation\Models\Organisation;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->org   = Organisation::where('seo_name', 'river')->firstOrFail();
    $this->alice = User::where('email', 'alice@example.com')->firstOrFail();
    $this->aliceMember = Member::where('organisation_id', $this->org->id)
        ->where('email', 'alice@example.com')
        ->firstOrFail();
});

// ── List ──────────────────────────────────────────────────────────────────────

it('lists roles for an org', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/roles')
         ->assertOk();
});

it('returns 401 listing roles without auth', function () {
    $this->orgJson('get', '/roles')
         ->assertStatus(401);
});

// ── Available Permissions ─────────────────────────────────────────────────────

it('lists available permissions for an org', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/available-permissions')
         ->assertOk();
});

// ── Create ────────────────────────────────────────────────────────────────────

it('creates a role for an org', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('post', '/roles', ['name' => 'treasurer-' . uniqid()])
         ->assertStatus(201)
         ->assertJsonStructure(['data' => ['id', 'name']]);
});

// ── Show ──────────────────────────────────────────────────────────────────────

it('shows a role', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/roles', ['name' => 'show-role-' . uniqid()])
               ->json('data.id');

    $this->orgJson('get', "/roles/{$id}")
         ->assertOk()
         ->assertJsonStructure(['data' => ['id', 'name']]);
});

// ── Update ────────────────────────────────────────────────────────────────────

it('updates a role', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/roles', ['name' => 'role-update-' . uniqid()])
               ->json('data.id');

    $this->orgJson('put', "/roles/{$id}", ['name' => 'role-updated-' . uniqid()])
         ->assertOk();
});

// ── Delete ────────────────────────────────────────────────────────────────────

it('deletes a role', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/roles', ['name' => 'role-delete-' . uniqid()])
               ->json('data.id');

    $this->orgJson('delete', "/roles/{$id}")
         ->assertOk();
});

// ── Sync Permissions ──────────────────────────────────────────────────────────

it('syncs permissions on a role', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/roles', ['name' => 'role-perms-' . uniqid()])
               ->json('data.id');

    $this->orgJson('put', "/roles/{$id}/permissions", [
        'permissions' => ['member.read'],
    ])->assertOk();
});

// ── Assign / Remove Role from Member ─────────────────────────────────────────

it('assigns a role to a member', function () {
    Sanctum::actingAs($this->alice);

    $roleId = $this->orgJson('post', '/roles', ['name' => 'assign-role-' . uniqid()])
                   ->json('data.id');

    $this->orgJson('post', "/roles/{$roleId}/members", [
        'member_id' => $this->aliceMember->id,
    ])->assertOk();
});

it('removes a role from a member', function () {
    Sanctum::actingAs($this->alice);

    $roleId = $this->orgJson('post', '/roles', ['name' => 'remove-role-' . uniqid()])
                   ->json('data.id');

    $this->orgJson('post', "/roles/{$roleId}/members", [
        'member_id' => $this->aliceMember->id,
    ]);

    $this->orgJson('delete', "/roles/{$roleId}/members", [
        'member_id' => $this->aliceMember->id,
    ])->assertOk();
});
