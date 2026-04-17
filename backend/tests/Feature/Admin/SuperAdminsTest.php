<?php

use App\Domain\Member\Models\Member;
use App\Domain\Organisation\Models\Organisation;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->admin = User::where('email', 'admin@membix.com')->firstOrFail();
    $this->alice = User::where('email', 'alice@example.com')->firstOrFail();
    $this->org   = Organisation::where('seo_name', 'river')->firstOrFail();
});

// ── List ──────────────────────────────────────────────────────────────────────

it('lists super-admins for an organisation', function () {
    Sanctum::actingAs($this->admin);

    $this->getJson("/api/admin/organisations/{$this->org->id}/super-admins")
         ->assertOk();
});

it('returns 403 when non-admin lists super-admins', function () {
    Sanctum::actingAs($this->alice);

    $this->getJson("/api/admin/organisations/{$this->org->id}/super-admins")
         ->assertStatus(403);
});

// ── Add ───────────────────────────────────────────────────────────────────────

it('adds a super-admin to an organisation', function () {
    Sanctum::actingAs($this->admin);

    $carol = User::where('email', 'carol@example.com')->firstOrFail();

    $this->postJson("/api/admin/organisations/{$this->org->id}/super-admins", [
        'user_id' => $carol->id,
    ])->assertStatus(201);
});

// ── Remove ────────────────────────────────────────────────────────────────────

it('removes a super-admin from an organisation', function () {
    Sanctum::actingAs($this->admin);

    $adminMember = Member::where('organisation_id', $this->org->id)
        ->whereHas('user', fn ($q) => $q->where('email', 'admin@membix.com'))
        ->firstOrFail();

    $this->deleteJson("/api/admin/organisations/{$this->org->id}/super-admins/{$adminMember->id}")
         ->assertOk();
});
