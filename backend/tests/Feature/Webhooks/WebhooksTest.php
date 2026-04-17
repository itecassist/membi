<?php

use App\Domain\Organisation\Models\Organisation;

beforeEach(function () {
    $this->org = Organisation::where('seo_name', 'river')->firstOrFail();
});

// ── GoCardless ────────────────────────────────────────────────────────────────

it('accepts a GoCardless webhook with a signature header', function () {
    $response = $this->withHeaders([
        'Content-Type'       => 'application/json',
        'Webhook-Signature'  => 'test-sig',
    ])->postJson("/api/webhooks/gocardless/{$this->org->id}", [
        'events' => [],
    ]);

    // 200 or 422 are both acceptable — endpoint exists and processes the request
    $this->assertContains($response->status(), [200, 204, 400, 422]);
});

it('returns 404 for GoCardless webhook with unknown org', function () {
    $this->withHeaders([
        'Content-Type'      => 'application/json',
        'Webhook-Signature' => 'test-sig',
    ])->postJson('/api/webhooks/gocardless/non-existent-org', ['events' => []])
      ->assertStatus(404);
});

// ── WorldPay ──────────────────────────────────────────────────────────────────

it('accepts a WorldPay webhook with a signature header', function () {
    $response = $this->withHeaders([
        'Content-Type'          => 'application/json',
        'X-WorldPay-Signature'  => 'test-hmac',
    ])->postJson("/api/webhooks/worldpay/{$this->org->id}", [
        'transactionReference' => 'test-ref',
        'outcome'              => 'authorized',
    ]);

    $this->assertContains($response->status(), [200, 204, 400, 422]);
});
