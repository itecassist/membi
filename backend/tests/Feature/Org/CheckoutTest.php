<?php

use App\Domain\Member\Models\Member;
use App\Domain\Organisation\Models\Organisation;
use App\Domain\Subscription\Models\SubscriptionPriceOption;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->org         = Organisation::where('seo_name', 'river')->firstOrFail();
    $this->alice       = User::where('email', 'alice@example.com')->firstOrFail();
    $this->priceOption = SubscriptionPriceOption::whereHas(
        'subscription',
        fn ($q) => $q->where('organisation_id', $this->org->id)
    )->firstOrFail();
});

// ── Create Basket ─────────────────────────────────────────────────────────────

it('creates a basket', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('post', '/baskets', ['type' => 'member'])
         ->assertStatus(201)
         ->assertJsonStructure(['data' => ['id']]);
});

it('returns 401 creating a basket without auth', function () {
    $this->orgJson('post', '/baskets', ['type' => 'member'])
         ->assertStatus(401);
});

// ── Show Basket ───────────────────────────────────────────────────────────────

it('shows a basket', function () {
    Sanctum::actingAs($this->alice);

    $basketId = $this->orgJson('post', '/baskets', ['type' => 'member'])->json('data.id');

    $this->orgJson('get', "/baskets/{$basketId}")
         ->assertOk()
         ->assertJsonStructure(['data' => ['id']]);
});

// ── Add Item ──────────────────────────────────────────────────────────────────

it('adds an item to a basket', function () {
    Sanctum::actingAs($this->alice);

    $basketId = $this->orgJson('post', '/baskets', ['type' => 'member'])->json('data.id');

    $this->orgJson('post', "/baskets/{$basketId}/items", [
        'price_option_id' => $this->priceOption->id,
        'adult_quantity'  => 1,
        'junior_quantity' => 0,
    ])->assertStatus(201);
});

// ── Remove Item ───────────────────────────────────────────────────────────────

it('removes an item from a basket', function () {
    Sanctum::actingAs($this->alice);

    $basketId = $this->orgJson('post', '/baskets', ['type' => 'member'])->json('data.id');

    $itemId = $this->orgJson('post', "/baskets/{$basketId}/items", [
        'price_option_id' => $this->priceOption->id,
        'adult_quantity'  => 1,
        'junior_quantity' => 0,
    ])->json('data.id');

    $this->orgJson('delete', "/baskets/{$basketId}/items/{$itemId}")
         ->assertOk();
});

// ── Start Checkout ────────────────────────────────────────────────────────────

it('starts a checkout session', function () {
    Sanctum::actingAs($this->alice);

    $basketId = $this->orgJson('post', '/baskets', ['type' => 'member'])->json('data.id');

    $this->orgJson('post', "/baskets/{$basketId}/items", [
        'price_option_id' => $this->priceOption->id,
        'adult_quantity'  => 1,
        'junior_quantity' => 0,
    ]);

    $this->orgJson('post', "/baskets/{$basketId}/checkout", ['mode' => 'member'])
         ->assertOk();
});

// ── Capture Email ─────────────────────────────────────────────────────────────

it('captures email in checkout', function () {
    Sanctum::actingAs($this->alice);

    $basketId = $this->orgJson('post', '/baskets', ['type' => 'member'])->json('data.id');

    $this->orgJson('post', "/baskets/{$basketId}/items", [
        'price_option_id' => $this->priceOption->id,
        'adult_quantity'  => 1,
        'junior_quantity' => 0,
    ]);

    $this->orgJson('post', "/baskets/{$basketId}/checkout", ['mode' => 'member']);

    $this->orgJson('post', "/baskets/{$basketId}/checkout/email", [
        'email' => 'guest@example.com',
    ])->assertOk();
});

// ── Set Allocations ───────────────────────────────────────────────────────────

it('sets allocations in checkout', function () {
    Sanctum::actingAs($this->alice);

    $member   = Member::where('organisation_id', $this->org->id)->firstOrFail();
    $basketId = $this->orgJson('post', '/baskets', ['type' => 'member'])->json('data.id');

    $itemId = $this->orgJson('post', "/baskets/{$basketId}/items", [
        'price_option_id' => $this->priceOption->id,
        'adult_quantity'  => 1,
        'junior_quantity' => 0,
    ])->json('data.id');

    $this->orgJson('post', "/baskets/{$basketId}/checkout", ['mode' => 'member']);

    $this->orgJson('put', "/baskets/{$basketId}/checkout/allocations", [
        'allocations' => [$itemId => $member->id],
    ])->assertOk();
});
