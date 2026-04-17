<?php

use App\Domain\Organisation\Models\Organisation;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->alice = User::where('email', 'alice@example.com')->firstOrFail();
    $this->org   = Organisation::where('seo_name', 'river')->firstOrFail();
});

// ── Countries (public) ────────────────────────────────────────────────────────

it('lists countries without auth', function () {
    $this->getJson('/api/countries')
         ->assertOk();
});

it('shows a country without auth', function () {
    $countries = $this->getJson('/api/countries')->json('data');

    if (empty($countries)) {
        $this->markTestSkipped('No countries seeded.');
    }

    $id = $countries[0]['id'];
    $this->getJson("/api/countries/{$id}")
         ->assertOk()
         ->assertJsonStructure(['data' => ['id']]);
});

it('lists zones for a country', function () {
    $countries = $this->getJson('/api/countries')->json('data');

    if (empty($countries)) {
        $this->markTestSkipped('No countries seeded.');
    }

    $id = $countries[0]['id'];
    $this->getJson("/api/countries/{$id}/zones")
         ->assertOk();
});

// ── Tax Rates (auth, org-scoped) ──────────────────────────────────────────────

it('lists tax rates when authenticated', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/tax-rates')
         ->assertOk();
});

it('returns 401 for tax rates without auth', function () {
    $this->orgJson('get', '/tax-rates')
         ->assertStatus(401);
});

// ── Virtual Forms (read) ──────────────────────────────────────────────────────

it('lists virtual forms when authenticated', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/virtual-forms')
         ->assertOk();
});

// ── Article Categories (read) ─────────────────────────────────────────────────

it('lists article categories when authenticated', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/article-categories')
         ->assertOk();
});

// ── FAQ Categories (read) ─────────────────────────────────────────────────────

it('lists faq categories when authenticated', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/faq-categories')
         ->assertOk();
});
