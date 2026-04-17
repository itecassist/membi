<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->admin = User::where('email', 'admin@membix.com')->firstOrFail();
    $this->alice = User::where('email', 'alice@example.com')->firstOrFail();
});

// ── List ──────────────────────────────────────────────────────────────────────

it('lists permissions as admin', function () {
    Sanctum::actingAs($this->admin);

    $this->getJson('/api/admin/permissions')
         ->assertOk();
});

it('returns 403 when listing permissions as non-admin', function () {
    Sanctum::actingAs($this->alice);

    $this->getJson('/api/admin/permissions')
         ->assertStatus(403);
});

// ── Create ────────────────────────────────────────────────────────────────────

it('creates a permission as admin', function () {
    Sanctum::actingAs($this->admin);

    $this->postJson('/api/admin/permissions', [
        'name'                     => 'report.read.' . uniqid(),
        'visible_to_organisations' => true,
    ])->assertStatus(201)
      ->assertJsonStructure(['data' => ['id', 'name']]);
});

it('returns 422 when creating permission with missing name', function () {
    Sanctum::actingAs($this->admin);

    $this->postJson('/api/admin/permissions', [])
         ->assertStatus(422);
});

// ── Show ──────────────────────────────────────────────────────────────────────

it('shows a permission as admin', function () {
    Sanctum::actingAs($this->admin);

    $permission = Permission::where('guard_name', 'sanctum')->first();
    expect($permission)->not->toBeNull();

    $this->getJson("/api/admin/permissions/{$permission->id}")
         ->assertOk()
         ->assertJsonStructure(['data' => ['id', 'name']]);
});

// ── Update ────────────────────────────────────────────────────────────────────

it('updates a permission as admin', function () {
    Sanctum::actingAs($this->admin);

    $permission = Permission::create([
        'name'                     => 'tmp.perm.' . uniqid(),
        'guard_name'               => 'sanctum',
        'visible_to_organisations' => false,
    ]);

    $this->putJson("/api/admin/permissions/{$permission->id}", [
        'name'                     => $permission->name,
        'visible_to_organisations' => true,
    ])->assertOk();
});

// ── Delete ────────────────────────────────────────────────────────────────────

it('deletes a permission as admin', function () {
    Sanctum::actingAs($this->admin);

    $permission = Permission::create([
        'name'       => 'delete.me.' . uniqid(),
        'guard_name' => 'sanctum',
    ]);

    $this->deleteJson("/api/admin/permissions/{$permission->id}")
         ->assertOk();
});

// ── Toggle Visibility ─────────────────────────────────────────────────────────

it('toggles permission visibility as admin', function () {
    Sanctum::actingAs($this->admin);

    $permission = Permission::where('guard_name', 'sanctum')->first();
    expect($permission)->not->toBeNull();

    $this->patchJson("/api/admin/permissions/{$permission->id}/toggle-visibility")
         ->assertOk();
});
