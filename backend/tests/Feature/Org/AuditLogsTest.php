<?php

use App\Domain\Organisation\Models\Organisation;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->org   = Organisation::where('seo_name', 'river')->firstOrFail();
    $this->alice = User::where('email', 'alice@example.com')->firstOrFail();
});

it('lists audit logs', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/audit-logs')
         ->assertOk();
});

it('returns 401 listing audit logs without auth', function () {
    $this->orgJson('get', '/audit-logs')
         ->assertStatus(401);
});

it('shows an audit log entry when one exists', function () {
    Sanctum::actingAs($this->alice);

    $logs = $this->orgJson('get', '/audit-logs')->json('data');

    if (empty($logs)) {
        $this->markTestSkipped('No audit logs seeded.');
    }

    $id = $logs[0]['id'];

    $this->orgJson('get', "/audit-logs/{$id}")
         ->assertOk()
         ->assertJsonStructure(['data' => ['id']]);
});
