<?php

use App\Domain\Organisation\Models\Organisation;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->org   = Organisation::where('seo_name', 'river')->firstOrFail();
    $this->alice = User::where('email', 'alice@example.com')->firstOrFail();
});

// ── Member Config ─────────────────────────────────────────────────────────────

it('gets member config', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/config/member')
         ->assertOk();
});

it('updates member config', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('put', '/config/member', [
        'has_junior_members'                      => true,
        'junior_member_max_age'                   => 18,
        'has_membership_numbers'                  => true,
        'does_membership_numbers_auto_increment'  => true,
    ])->assertOk();
});

it('returns 401 getting member config without auth', function () {
    $this->orgJson('get', '/config/member')
         ->assertStatus(401);
});

// ── Subscription Config ───────────────────────────────────────────────────────

it('gets subscription config', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/config/subscription')
         ->assertOk();
});

it('updates subscription config', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('put', '/config/subscription', [
        'renew_annual_subscription_months' => 3,
        'auto_renewal_order_days'          => 30,
    ])->assertOk();
});

// ── Financial Config ──────────────────────────────────────────────────────────

it('gets financial config', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/config/financial')
         ->assertOk();
});

it('updates financial config', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('put', '/config/financial', [
        'currency'   => '£',
        'vat_status' => false,
    ])->assertOk();
});
