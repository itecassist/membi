<?php

use App\Domain\Organisation\Models\Organisation;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->org   = Organisation::where('seo_name', 'river')->firstOrFail();
    $this->alice = User::where('email', 'alice@example.com')->firstOrFail();
});

// ── List ──────────────────────────────────────────────────────────────────────

it('lists payment methods', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/payment-methods')
         ->assertOk();
});

// ── Create ────────────────────────────────────────────────────────────────────

it('creates a payment method', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('post', '/payment-methods', [
        'type'                  => 'bank_transfer',
        'class'                 => 'one_off',
        'name'                  => 'Bank Transfer ' . uniqid(),
        'explanation'           => 'Pay by bank transfer.',
        'checkout_text'         => 'You will receive instructions.',
        'success_text'          => 'Thank you!',
        'is_active'             => true,
        'is_default'            => false,
        'admin_only'            => false,
        'requires_confirmation' => true,
        'surcharge_percentage'  => 0,
        'surcharge_fixed'       => 0,
    ])->assertStatus(201)
      ->assertJsonStructure(['data' => ['id', 'name']]);
});

// ── Show ──────────────────────────────────────────────────────────────────────

it('shows a payment method', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/payment-methods', [
        'type'                  => 'cheque',
        'class'                 => 'one_off',
        'name'                  => 'Cheque ' . uniqid(),
        'explanation'           => 'Pay by cheque.',
        'checkout_text'         => 'Post your cheque.',
        'success_text'          => 'Received!',
        'is_active'             => true,
        'is_default'            => false,
        'admin_only'            => true,
        'requires_confirmation' => true,
        'surcharge_percentage'  => 0,
        'surcharge_fixed'       => 0,
    ])->json('data.id');

    $this->orgJson('get', "/payment-methods/{$id}")
         ->assertOk();
});

// ── Update ────────────────────────────────────────────────────────────────────

it('updates a payment method', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/payment-methods', [
        'type'                  => 'cash',
        'class'                 => 'one_off',
        'name'                  => 'Cash ' . uniqid(),
        'explanation'           => 'Cash only.',
        'checkout_text'         => 'Pay in person.',
        'success_text'          => 'Paid!',
        'is_active'             => true,
        'is_default'            => false,
        'admin_only'            => true,
        'requires_confirmation' => false,
        'surcharge_percentage'  => 0,
        'surcharge_fixed'       => 0,
    ])->json('data.id');

    $this->orgJson('put', "/payment-methods/{$id}", [
        'name'       => 'Cash Updated',
        'is_active'  => true,
        'is_default' => true,
    ])->assertOk();
});

// ── Delete ────────────────────────────────────────────────────────────────────

it('deletes a payment method', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/payment-methods', [
        'type'                  => 'bank_transfer',
        'class'                 => 'one_off',
        'name'                  => 'Delete PM ' . uniqid(),
        'explanation'           => 'Pay by bank transfer.',
        'checkout_text'         => '.',
        'success_text'          => '.',
        'is_active'             => false,
        'is_default'            => false,
        'admin_only'            => false,
        'requires_confirmation' => false,
        'surcharge_percentage'  => 0,
        'surcharge_fixed'       => 0,
    ])->json('data.id');

    $this->orgJson('delete', "/payment-methods/{$id}")
         ->assertOk();
});
