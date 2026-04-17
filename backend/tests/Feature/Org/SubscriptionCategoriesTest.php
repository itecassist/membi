<?php

use App\Domain\Organisation\Models\Organisation;
use App\Domain\Subscription\Models\Subscription;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->org          = Organisation::where('seo_name', 'river')->firstOrFail();
    $this->alice        = User::where('email', 'alice@example.com')->firstOrFail();
    $this->subscription = Subscription::where('organisation_id', $this->org->id)->firstOrFail();
});

it('lists subscription categories', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/subscription-categories')
         ->assertOk();
});

it('creates a subscription category', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('post', '/subscription-categories', [
        'name'       => 'Membership ' . uniqid(),
        'sort_order' => 1,
    ])->assertStatus(201)
      ->assertJsonStructure(['data' => ['id', 'name']]);
});

it('shows a subscription category', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/subscription-categories', [
        'name'       => 'Show Cat ' . uniqid(),
        'sort_order' => 1,
    ])->json('data.id');

    $this->orgJson('get', "/subscription-categories/{$id}")
         ->assertOk();
});

it('updates a subscription category', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/subscription-categories', [
        'name'       => 'Update Cat ' . uniqid(),
        'sort_order' => 1,
    ])->json('data.id');

    $this->orgJson('put', "/subscription-categories/{$id}", [
        'name'       => 'Membership Updated ' . uniqid(),
        'sort_order' => 2,
    ])->assertOk();
});

it('deletes a subscription category', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/subscription-categories', [
        'name'       => 'Delete Cat ' . uniqid(),
        'sort_order' => 99,
    ])->json('data.id');

    $this->orgJson('delete', "/subscription-categories/{$id}")
         ->assertOk();
});

it('syncs subscriptions in a category', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/subscription-categories', [
        'name'       => 'Sync Cat ' . uniqid(),
        'sort_order' => 1,
    ])->json('data.id');

    $this->orgJson('put', "/subscription-categories/{$id}/subscriptions", [
        'subscription_ids' => [$this->subscription->id],
    ])->assertOk();
});
