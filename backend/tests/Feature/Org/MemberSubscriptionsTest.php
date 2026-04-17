<?php

use App\Domain\Member\Models\Member;
use App\Domain\Organisation\Models\Organisation;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->org         = Organisation::where('seo_name', 'river')->firstOrFail();
    $this->alice       = User::where('email', 'alice@example.com')->firstOrFail();
    $this->aliceMember = Member::where('organisation_id', $this->org->id)
        ->where('email', 'alice@example.com')
        ->firstOrFail();
});

it('lists member subscriptions', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/member-subscriptions')
         ->assertOk();
});

it('lists subscriptions for a specific member', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', "/members/{$this->aliceMember->id}/subscriptions")
         ->assertOk();
});

it('shows a member subscription when one exists', function () {
    Sanctum::actingAs($this->alice);

    $list = $this->orgJson('get', '/member-subscriptions')->json('data');

    if (empty($list)) {
        $this->markTestSkipped('No member subscriptions seeded.');
    }

    $id = $list[0]['id'];

    $this->orgJson('get', "/member-subscriptions/{$id}")
         ->assertOk()
         ->assertJsonStructure(['data' => ['id']]);
});

it('returns 401 listing member subscriptions without auth', function () {
    $this->orgJson('get', '/member-subscriptions')
         ->assertStatus(401);
});

it('lists renewal queue', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/renewals')
         ->assertOk();
});
