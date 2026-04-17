<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->admin = User::where('email', 'admin@membix.com')->firstOrFail();
    $this->alice = User::where('email', 'alice@example.com')->firstOrFail();
});

it('creates a tax rate as admin', function () {
    Sanctum::actingAs($this->admin);

    $this->postJson('/api/admin/tax-rates', [
        'name' => 'UK Standard',
        'rate' => 20.0,
    ])->assertStatus(201)
      ->assertJsonStructure(['data' => ['id', 'name', 'rate']]);
});

it('returns 403 when non-admin creates a tax rate', function () {
    Sanctum::actingAs($this->alice);

    $this->postJson('/api/admin/tax-rates', [
        'name' => 'Test Rate',
        'rate' => 5.0,
    ])->assertStatus(403);
});

it('updates a tax rate as admin', function () {
    Sanctum::actingAs($this->admin);

    $id = $this->postJson('/api/admin/tax-rates', [
        'name' => 'Rate to Update',
        'rate' => 10.0,
    ])->json('data.id');

    $this->putJson("/api/admin/tax-rates/{$id}", ['rate' => 15.0])
         ->assertOk();
});

it('deletes a tax rate as admin', function () {
    Sanctum::actingAs($this->admin);

    $id = $this->postJson('/api/admin/tax-rates', [
        'name' => 'Rate to Delete',
        'rate' => 5.0,
    ])->json('data.id');

    $this->deleteJson("/api/admin/tax-rates/{$id}")
         ->assertOk();
});
