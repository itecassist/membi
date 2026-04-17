<?php

use App\Domain\Organisation\Models\Organisation;
use App\Domain\Subscription\Models\Subscription;
use App\Domain\Subscription\Models\SubscriptionPriceOption;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->org          = Organisation::where('seo_name', 'river')->firstOrFail();
    $this->alice        = User::where('email', 'alice@example.com')->firstOrFail();
    $this->subscription = Subscription::where('organisation_id', $this->org->id)->firstOrFail();
    $this->priceOption  = SubscriptionPriceOption::where('subscription_id', $this->subscription->id)->firstOrFail();
});

// ── List ──────────────────────────────────────────────────────────────────────

it('lists subscriptions', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/subscriptions')
         ->assertOk()
         ->assertJsonStructure(['data']);
});

// ── Create ────────────────────────────────────────────────────────────────────

it('creates a subscription', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('post', '/subscriptions', [
        'name'         => 'Test Sub ' . uniqid(),
        'is_membership' => true,
        'period_type'  => 'year',
        'period_count' => 1,
        'renewal_type' => 'manual',
        'pricing_type' => 'flat',
        'published'    => 'published',
    ])->assertStatus(201)
      ->assertJsonStructure(['data' => ['id', 'name']]);
});

// ── Show ──────────────────────────────────────────────────────────────────────

it('shows a subscription', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', "/subscriptions/{$this->subscription->id}")
         ->assertOk()
         ->assertJsonStructure(['data' => ['id', 'name']]);
});

// ── Update ────────────────────────────────────────────────────────────────────

it('updates a subscription', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('put', "/subscriptions/{$this->subscription->id}", [
        'name' => 'Updated Name ' . uniqid(),
    ])->assertOk();
});

// ── Delete ────────────────────────────────────────────────────────────────────

it('deletes a subscription', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/subscriptions', [
        'name'         => 'Delete Sub ' . uniqid(),
        'is_membership' => false,
        'period_type'  => 'none',
        'renewal_type' => 'not_renewable',
        'pricing_type' => 'flat',
        'published'    => 'draft',
    ])->json('data.id');

    $this->orgJson('delete', "/subscriptions/{$id}")
         ->assertOk();
});

// ── Price Options ─────────────────────────────────────────────────────────────

it('lists price options', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', "/subscriptions/{$this->subscription->id}/price-options")
         ->assertOk();
});

it('creates a price option', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('post', "/subscriptions/{$this->subscription->id}/price-options", [
        'label'            => 'Adult',
        'price'            => 50.00,
        'currency_code'    => 'GBP',
        'pricing_type'     => 'flat',
        'eligibility'      => 'adult',
        'instance_type'    => 'primary',
        'use_pro_rata'     => false,
        'allow_instalments' => false,
        'offer_trial'      => false,
        'is_active'        => true,
    ])->assertStatus(201);
});

it('updates a price option', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('put', "/subscriptions/{$this->subscription->id}/price-options/{$this->priceOption->id}", [
        'price' => 55.00,
    ])->assertOk();
});

it('deletes a price option', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', "/subscriptions/{$this->subscription->id}/price-options", [
        'label'        => 'Delete Option',
        'price'        => 10.00,
        'currency_code' => 'GBP',
        'pricing_type' => 'flat',
        'eligibility'  => 'individual',
        'instance_type' => 'primary',
        'is_active'    => true,
    ])->json('data.id');

    $this->orgJson('delete', "/subscriptions/{$this->subscription->id}/price-options/{$id}")
         ->assertOk();
});

// ── Renewal Settings ──────────────────────────────────────────────────────────

it('gets renewal settings', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', "/subscriptions/{$this->subscription->id}/renewal-settings")
         ->assertOk();
});

it('updates renewal settings', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('put', "/subscriptions/{$this->subscription->id}/renewal-settings", [
        'schedule_late_fees' => false,
    ])->assertOk();
});

// ── Auto-Renewal Settings ─────────────────────────────────────────────────────

it('gets auto-renewal settings', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', "/subscriptions/{$this->subscription->id}/auto-renewal-settings")
         ->assertOk();
});

it('updates auto-renewal settings', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('put', "/subscriptions/{$this->subscription->id}/auto-renewal-settings", [
        'enable_auto_renewal'               => false,
        'apply_to_all_subscription_fees'    => false,
        'order_expiry_days'                 => 14,
    ])->assertOk();
});

// ── New Member Settings ───────────────────────────────────────────────────────

it('gets new member settings for a price option', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', "/subscriptions/{$this->subscription->id}/price-options/{$this->priceOption->id}/new-member-settings")
         ->assertOk();
});

it('updates new member settings for a price option', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('put', "/subscriptions/{$this->subscription->id}/price-options/{$this->priceOption->id}/new-member-settings", [
        'enable_rollover'         => false,
        'rollover_period_days'    => 30,
        'enable_pro_rata_pricing' => false,
    ])->assertOk();
});

// ── Late Fees ─────────────────────────────────────────────────────────────────

it('lists late fees for a price option', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', "/subscriptions/{$this->subscription->id}/price-options/{$this->priceOption->id}/late-fees")
         ->assertOk();
});

it('creates a late fee', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('post', "/subscriptions/{$this->subscription->id}/price-options/{$this->priceOption->id}/late-fees", [
        'price'        => 55.00,
        'renewal_date' => '2026-09-01',
        'late_fee'     => 5.00,
        'applies_from' => '2026-10-01',
    ])->assertStatus(201);
});

it('deletes a late fee', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', "/subscriptions/{$this->subscription->id}/price-options/{$this->priceOption->id}/late-fees", [
        'price'        => 60.00,
        'renewal_date' => '2027-09-01',
        'late_fee'     => 10.00,
        'applies_from' => '2027-10-01',
    ])->json('data.id');

    $this->orgJson('delete', "/subscriptions/{$this->subscription->id}/price-options/{$this->priceOption->id}/late-fees/{$id}")
         ->assertOk();
});
