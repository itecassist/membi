<?php

use App\Domain\Organisation\Models\Organisation;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->org   = Organisation::where('seo_name', 'river')->firstOrFail();
    $this->alice = User::where('email', 'alice@example.com')->firstOrFail();
});

// ── Accounting Codes ──────────────────────────────────────────────────────────

it('lists accounting codes', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/accounting-codes')
         ->assertOk();
});

it('creates an accounting code', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('post', '/accounting-codes', [
        'code'        => '4100-' . uniqid(),
        'description' => 'Membership Income',
    ])->assertStatus(201)
      ->assertJsonStructure(['data' => ['id', 'code']]);
});

it('shows an accounting code', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/accounting-codes', [
        'code'        => '4200-' . uniqid(),
        'description' => 'Events Income',
    ])->json('data.id');

    $this->orgJson('get', "/accounting-codes/{$id}")
         ->assertOk();
});

it('updates an accounting code', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/accounting-codes', [
        'code'        => '4300-' . uniqid(),
        'description' => 'Old Desc',
    ])->json('data.id');

    $this->orgJson('put', "/accounting-codes/{$id}", ['description' => 'Membership Fees'])
         ->assertOk();
});

it('deletes an accounting code', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/accounting-codes', [
        'code'        => '4400-' . uniqid(),
        'description' => 'Delete Me',
    ])->json('data.id');

    $this->orgJson('delete', "/accounting-codes/{$id}")
         ->assertOk();
});

// ── VAT Rates ─────────────────────────────────────────────────────────────────

it('lists VAT rates', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/vats')
         ->assertOk();
});

it('creates a VAT rate', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('post', '/vats', [
        'name' => 'Standard Rate',
        'rate' => 20.0,
    ])->assertStatus(201)
      ->assertJsonStructure(['data' => ['id', 'name', 'rate']]);
});

it('shows a VAT rate', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/vats', ['name' => 'Reduced Rate', 'rate' => 5.0])
               ->json('data.id');

    $this->orgJson('get', "/vats/{$id}")
         ->assertOk();
});

it('updates a VAT rate', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/vats', ['name' => 'Update VAT', 'rate' => 10.0])
               ->json('data.id');

    $this->orgJson('put', "/vats/{$id}", ['rate' => 15.0])
         ->assertOk();
});

it('deletes a VAT rate', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/vats', ['name' => 'Delete VAT', 'rate' => 5.0])
               ->json('data.id');

    $this->orgJson('delete', "/vats/{$id}")
         ->assertOk();
});
