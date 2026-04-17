<?php

use App\Domain\Organisation\Models\Organisation;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->admin = User::where('email', 'admin@membix.com')->firstOrFail();
    $this->alice = User::where('email', 'alice@example.com')->firstOrFail();
    $this->org   = Organisation::where('seo_name', 'river')->firstOrFail();
});

// ── List (public) ─────────────────────────────────────────────────────────────

it('lists organisations without auth', function () {
    $this->getJson('/api/organisations')
         ->assertOk();
});

// ── Show (public) ─────────────────────────────────────────────────────────────

it('shows an organisation without auth', function () {
    $this->getJson("/api/organisations/{$this->org->id}")
         ->assertOk()
         ->assertJsonStructure(['data' => ['id', 'name']]);
});

// ── Create ────────────────────────────────────────────────────────────────────

it('creates an organisation when authenticated', function () {
    Sanctum::actingAs($this->alice);

    $this->postJson('/api/organisations', [
        'name'     => 'My Test Club',
        'seo_name' => 'my-test-club-' . uniqid(),
        'email'    => 'info@mytestclub.com',
        'timezone' => 'Europe/London',
        'currency' => 'GBP',
        'locale'   => 'en-GB',
    ])->assertStatus(201)
      ->assertJsonStructure(['data' => ['id', 'name']]);
});

it('returns 401 when creating org without auth', function () {
    $this->postJson('/api/organisations', [
        'name'     => 'Unauthorized Club',
        'seo_name' => 'unauthorized-club',
        'email'    => 'info@unauthorized.com',
        'timezone' => 'Europe/London',
    ])->assertStatus(401);
});

// ── Update ────────────────────────────────────────────────────────────────────

it('updates an organisation when authenticated', function () {
    Sanctum::actingAs($this->alice);

    $this->putJson("/api/organisations/{$this->org->id}", [
        'name' => 'Riverside Tennis Club (Updated)',
    ])->assertOk();
});

it('returns 401 when updating org without auth', function () {
    $this->putJson("/api/organisations/{$this->org->id}", ['name' => 'X'])
         ->assertStatus(401);
});

// ── Delete ────────────────────────────────────────────────────────────────────

it('deletes an organisation when authenticated as admin', function () {
    Sanctum::actingAs($this->admin);

    $org = Organisation::create([
        'name'      => 'Temp Club',
        'seo_name'  => 'temp-' . uniqid(),
        'email'     => 'tmp@example.com',
        'timezone'  => 'Europe/London',
        'is_active' => true,
    ]);

    $this->deleteJson("/api/organisations/{$org->id}")
         ->assertOk();
});
