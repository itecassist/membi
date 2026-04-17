<?php

use App\Domain\Organisation\Models\Organisation;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->org   = Organisation::where('seo_name', 'river')->firstOrFail();
    $this->alice = User::where('email', 'alice@example.com')->firstOrFail();
});

// ── Gateway Config ────────────────────────────────────────────────────────────

it('stores a WorldPay gateway config', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('post', '/gateway-configs', [
        'type'      => 'worldpay',
        'is_active' => false,
        'config'    => [
            'merchant_entity_id' => 'test-merchant',
            'api_key'            => 'test-key',
            'api_secret'         => 'test-secret',
            'webhook_secret'     => '',
            'environment'        => 'sandbox',
        ],
    ])->assertStatus(201);
});

it('stores a GoCardless gateway config', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('post', '/gateway-configs', [
        'type'      => 'gocardless',
        'is_active' => false,
        'config'    => [
            'access_token'   => 'sandbox_test_token',
            'webhook_secret' => '',
            'environment'    => 'sandbox',
        ],
    ])->assertStatus(201);
});

it('returns 401 storing gateway config without auth', function () {
    $this->orgJson('post', '/gateway-configs', [
        'type'      => 'worldpay',
        'is_active' => false,
        'config'    => [],
    ])->assertStatus(401);
});

// ── Cancel Member Payment Method ──────────────────────────────────────────────

it('returns 404 cancelling a non-existent member payment method', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('delete', '/member-payment-methods/non-existent-id')
         ->assertStatus(404);
});
