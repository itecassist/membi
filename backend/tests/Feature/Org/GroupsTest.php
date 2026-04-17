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

it('lists groups', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/groups')
         ->assertOk();
});

// ── Create ────────────────────────────────────────────────────────────────────

it('creates a group', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('post', '/groups', [
        'name' => 'Juniors ' . uniqid(),
        'type' => 'corporate',
    ])->assertStatus(201)
      ->assertJsonStructure(['data' => ['id', 'name']]);
});

// ── Show ──────────────────────────────────────────────────────────────────────

it('shows a group', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/groups', [
        'name' => 'Show Group ' . uniqid(),
        'type' => 'corporate',
    ])->json('data.id');

    $this->orgJson('get', "/groups/{$id}")
         ->assertOk()
         ->assertJsonStructure(['data' => ['id', 'name']]);
});

// ── Update ────────────────────────────────────────────────────────────────────

it('updates a group', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/groups', [
        'name' => 'Update Group ' . uniqid(),
        'type' => 'corporate',
    ])->json('data.id');

    $this->orgJson('put', "/groups/{$id}", ['name' => 'Junior Members ' . uniqid()])
         ->assertOk();
});

// ── Delete ────────────────────────────────────────────────────────────────────

it('deletes a group', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/groups', [
        'name' => 'Delete Group ' . uniqid(),
        'type' => 'corporate',
    ])->json('data.id');

    $this->orgJson('delete', "/groups/{$id}")
         ->assertOk();
});

// ── Members ───────────────────────────────────────────────────────────────────

it('lists members in a group', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/groups', [
        'name' => 'Group Members ' . uniqid(),
        'type' => 'corporate',
    ])->json('data.id');

    $this->orgJson('get', "/groups/{$id}/members")
         ->assertOk();
});

it('adds a member to a group', function () {
    Sanctum::actingAs($this->alice);

    $groupId = $this->orgJson('post', '/groups', [
        'name' => 'Group Add ' . uniqid(),
        'type' => 'corporate',
    ])->json('data.id');

    $this->orgJson('post', "/groups/{$groupId}/members", [
        'member_id' => $this->aliceMember->id,
        'is_admin'  => false,
    ])->assertStatus(201);
});

it('removes a member from a group', function () {
    Sanctum::actingAs($this->alice);

    $groupId = $this->orgJson('post', '/groups', [
        'name' => 'Group Remove ' . uniqid(),
        'type' => 'corporate',
    ])->json('data.id');

    $this->orgJson('post', "/groups/{$groupId}/members", [
        'member_id' => $this->aliceMember->id,
        'is_admin'  => false,
    ]);

    $this->orgJson('delete', "/groups/{$groupId}/members/{$this->aliceMember->id}")
         ->assertOk();
});
