<?php

use App\Domain\Organisation\Models\Organisation;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->org   = Organisation::where('seo_name', 'river')->firstOrFail();
    $this->alice = User::where('email', 'alice@example.com')->firstOrFail();
});

// ── Lookups ───────────────────────────────────────────────────────────────────

it('lists lookups', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/lookups')
         ->assertOk();
});

it('creates a lookup', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('post', '/lookups', [
        'name'  => 'Sport',
        'value' => 'tennis-' . uniqid(),
    ])->assertStatus(201)
      ->assertJsonStructure(['data' => ['id']]);
});

it('shows a lookup', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/lookups', ['name' => 'Sport', 'value' => 'squash-' . uniqid()])
               ->json('data.id');

    $this->orgJson('get', "/lookups/{$id}")
         ->assertOk();
});

it('updates a lookup', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/lookups', ['name' => 'Sport', 'value' => 'badminton-' . uniqid()])
               ->json('data.id');

    $this->orgJson('put', "/lookups/{$id}", ['value' => 'squash-' . uniqid()])
         ->assertOk();
});

it('deletes a lookup', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/lookups', ['name' => 'Sport', 'value' => 'delete-' . uniqid()])
               ->json('data.id');

    $this->orgJson('delete', "/lookups/{$id}")
         ->assertOk();
});

// ── Organisation Lists ────────────────────────────────────────────────────────

it('lists organisation lists', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/lists')
         ->assertOk();
});

it('creates an organisation list', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('post', '/lists', [
        'name'        => 'Active Adults ' . uniqid(),
        'description' => 'All active adult members',
        'query'       => '{}',
    ])->assertStatus(201);
});

it('shows an organisation list', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/lists', [
        'name'  => 'Show List ' . uniqid(),
        'query' => '{}',
    ])->json('data.id');

    $this->orgJson('get', "/lists/{$id}")
         ->assertOk();
});

it('updates an organisation list', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/lists', [
        'name'  => 'Update List ' . uniqid(),
        'query' => '{}',
    ])->json('data.id');

    $this->orgJson('put', "/lists/{$id}", ['name' => 'Updated ' . uniqid()])
         ->assertOk();
});

it('deletes an organisation list', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/lists', [
        'name'  => 'Delete List ' . uniqid(),
        'query' => '{}',
    ])->json('data.id');

    $this->orgJson('delete', "/lists/{$id}")
         ->assertOk();
});
