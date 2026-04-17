<?php

use App\Domain\Organisation\Models\Organisation;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->org   = Organisation::where('seo_name', 'river')->firstOrFail();
    $this->alice = User::where('email', 'alice@example.com')->firstOrFail();
});

it('lists orders', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/orders')
         ->assertOk()
         ->assertJsonStructure(['data']);
});

it('returns 401 listing orders without auth', function () {
    $this->orgJson('get', '/orders')
         ->assertStatus(401);
});

it('shows an order when one exists', function () {
    Sanctum::actingAs($this->alice);

    $orders = $this->orgJson('get', '/orders')->json('data');

    if (empty($orders)) {
        $this->markTestSkipped('No orders seeded.');
    }

    $id = $orders[0]['id'];

    $this->orgJson('get', "/orders/{$id}")
         ->assertOk()
         ->assertJsonStructure(['data' => ['id']]);
});

it('lists subscription items for an order when one exists', function () {
    Sanctum::actingAs($this->alice);

    $orders = $this->orgJson('get', '/orders')->json('data');

    if (empty($orders)) {
        $this->markTestSkipped('No orders seeded.');
    }

    $id = $orders[0]['id'];

    $this->orgJson('get', "/orders/{$id}/subscription-items")
         ->assertOk();
});
