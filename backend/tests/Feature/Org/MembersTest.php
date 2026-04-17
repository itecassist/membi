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

it('lists members for an org', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/members')
         ->assertOk()
         ->assertJsonStructure(['data']);
});

it('returns 401 listing members without auth', function () {
    $this->orgJson('get', '/members')
         ->assertStatus(401);
});

// ── Create ────────────────────────────────────────────────────────────────────

it('creates a member', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('post', '/members', [
        'email'         => 'newmember-' . uniqid() . '@example.com',
        'first_name'    => 'Jane',
        'last_name'     => 'Doe',
        'date_of_birth' => '1990-06-15',
    ])->assertStatus(201)
      ->assertJsonStructure(['data' => ['id', 'email']]);
});

it('returns 422 when creating member with missing required fields', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('post', '/members', [])
         ->assertStatus(422);
});

// ── Show ──────────────────────────────────────────────────────────────────────

it('shows a member', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', "/members/{$this->aliceMember->id}")
         ->assertOk()
         ->assertJsonStructure(['data' => ['id', 'email']]);
});

it('returns 404 for a member in a different org', function () {
    Sanctum::actingAs($this->alice);

    $otherOrg    = Organisation::where('seo_name', 'north')->firstOrFail();
    $otherMember = Member::where('organisation_id', $otherOrg->id)->first();

    if (! $otherMember) {
        $this->markTestSkipped('No members in north org.');
    }

    $this->orgJson('get', "/members/{$otherMember->id}")
         ->assertStatus(404);
});

// ── Update ────────────────────────────────────────────────────────────────────

it('updates a member', function () {
    Sanctum::actingAs($this->alice);

    $memberId = $this->orgJson('post', '/members', [
        'email'      => 'upd-' . uniqid() . '@example.com',
        'first_name' => 'Jane',
        'last_name'  => 'Doe',
    ])->json('data.id');

    $this->orgJson('put', "/members/{$memberId}", [
        'first_name' => 'Jane',
        'last_name'  => 'Smith',
    ])->assertOk();
});

// ── Delete ────────────────────────────────────────────────────────────────────

it('deletes a member', function () {
    Sanctum::actingAs($this->alice);

    $memberId = $this->orgJson('post', '/members', [
        'email'      => 'del-' . uniqid() . '@example.com',
        'first_name' => 'Delete',
        'last_name'  => 'Me',
    ])->json('data.id');

    $this->orgJson('delete', "/members/{$memberId}")
         ->assertOk();
});

// ── Payment Methods ───────────────────────────────────────────────────────────

it('lists payment methods for a member', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', "/members/{$this->aliceMember->id}/payment-methods")
         ->assertOk();
});

// ── Subscriptions ─────────────────────────────────────────────────────────────

it("lists a member's subscriptions", function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', "/members/{$this->aliceMember->id}/subscriptions")
         ->assertOk();
});
