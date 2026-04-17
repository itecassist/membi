<?php

use App\Domain\Organisation\Models\Organisation;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->org   = Organisation::where('seo_name', 'river')->firstOrFail();
    $this->alice = User::where('email', 'alice@example.com')->firstOrFail();
});

it('returns my subscriptions via portal', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/portal/subscriptions')
         ->assertOk();
});

it('returns my orders via portal', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/portal/orders')
         ->assertOk();
});

it('returns my groups via portal', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/portal/groups')
         ->assertOk();
});

it('returns my profile via portal', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/portal/profile')
         ->assertOk()
         ->assertJsonStructure(['data' => ['id']]);
});

it('updates my profile via portal', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('put', '/portal/profile', [
        'first_name'    => 'Alice',
        'last_name'     => 'Updated',
        'mobile_phone'  => '+447700900123',
        'date_of_birth' => '1990-06-15',
    ])->assertOk();
});

it('returns 401 for portal routes without auth', function () {
    $this->orgJson('get', '/portal/subscriptions')
         ->assertStatus(401);
});
