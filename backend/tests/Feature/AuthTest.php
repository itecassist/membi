<?php

use App\Domain\Organisation\Models\Organisation;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->org  = Organisation::where('seo_name', 'river')->firstOrFail();
    $this->alice = User::where('email', 'alice@example.com')->firstOrFail();
});

// ── Register ─────────────────────────────────────────────────────────────────

it('registers a new user and returns a token', function () {
    $response = $this->orgJson('post', '/auth/register', [
        'email'                 => 'newuser@example.com',
        'password'              => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertStatus(201)
             ->assertJsonStructure(['token']);
});

it('returns 422 when register payload is missing fields', function () {
    $this->orgJson('post', '/auth/register', [])
         ->assertStatus(422);
});

// ── Login ─────────────────────────────────────────────────────────────────────

it('logs in with valid credentials and returns a token', function () {
    $response = $this->orgJson('post', '/auth/login', [
        'email'    => 'alice@example.com',
        'password' => 'password',
    ]);

    $response->assertOk()
             ->assertJsonStructure(['token']);
});

it('returns 401 with invalid credentials', function () {
    $this->orgJson('post', '/auth/login', [
        'email'    => 'alice@example.com',
        'password' => 'wrong',
    ])->assertStatus(401);
});

it('logs in as Membix admin', function () {
    $response = $this->orgJson('post', '/auth/login', [
        'email'    => 'admin@membix.com',
        'password' => 'password',
    ]);

    $response->assertOk()
             ->assertJsonStructure(['token']);
});

// ── Me ────────────────────────────────────────────────────────────────────────

it('returns the authenticated user', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/auth/me')
         ->assertOk()
         ->assertJsonStructure(['data' => ['id', 'email']]);
});

it('returns 401 on /auth/me without token', function () {
    $this->orgJson('get', '/auth/me')
         ->assertStatus(401);
});

// ── My Organisations ──────────────────────────────────────────────────────────

it('returns organisations for the authenticated user', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/auth/organisations')
         ->assertOk();
});

// ── Logout ────────────────────────────────────────────────────────────────────

it('logs out and revokes the current token', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('post', '/auth/logout')
         ->assertOk();
});

it('logs out of all devices', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('post', '/auth/logout-all')
         ->assertOk();
});
