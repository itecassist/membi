<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->admin = User::where('email', 'admin@membix.com')->firstOrFail();
    $this->alice = User::where('email', 'alice@example.com')->firstOrFail();
});

// ── Virtual Forms ─────────────────────────────────────────────────────────────

it('creates a virtual form as admin', function () {
    Sanctum::actingAs($this->admin);

    $this->postJson('/api/admin/virtual-forms', [
        'category' => 'membership',
        'name'     => 'Junior Application Form',
    ])->assertStatus(201)
      ->assertJsonStructure(['data' => ['id', 'name']]);
});

it('returns 403 when non-admin creates a virtual form', function () {
    Sanctum::actingAs($this->alice);

    $this->postJson('/api/admin/virtual-forms', [
        'category' => 'membership',
        'name'     => 'Test Form',
    ])->assertStatus(403);
});

it('updates a virtual form as admin', function () {
    Sanctum::actingAs($this->admin);

    $id = $this->postJson('/api/admin/virtual-forms', [
        'category' => 'membership',
        'name'     => 'Form to Update',
    ])->json('data.id');

    $this->putJson("/api/admin/virtual-forms/{$id}", [
        'name' => 'Form Updated v2',
    ])->assertOk();
});

it('deletes a virtual form as admin', function () {
    Sanctum::actingAs($this->admin);

    $id = $this->postJson('/api/admin/virtual-forms', [
        'category' => 'membership',
        'name'     => 'Form to Delete',
    ])->json('data.id');

    $this->deleteJson("/api/admin/virtual-forms/{$id}")
         ->assertOk();
});

// ── Fields ────────────────────────────────────────────────────────────────────

it('adds a field to a virtual form', function () {
    Sanctum::actingAs($this->admin);

    $formId = $this->postJson('/api/admin/virtual-forms', [
        'category' => 'membership',
        'name'     => 'Form With Fields',
    ])->json('data.id');

    $this->postJson("/api/admin/virtual-forms/{$formId}/fields", [
        'field_name'    => 'emergency_contact',
        'description'   => 'Emergency contact name and phone',
        'required'      => true,
        'type'          => 'text',
        'gdpr_sensitive' => false,
        'active'        => true,
        'sort_order'    => 1,
    ])->assertStatus(201);
});

it('updates a field on a virtual form', function () {
    Sanctum::actingAs($this->admin);

    $formId = $this->postJson('/api/admin/virtual-forms', [
        'category' => 'membership',
        'name'     => 'Form Field Update',
    ])->json('data.id');

    $fieldId = $this->postJson("/api/admin/virtual-forms/{$formId}/fields", [
        'field_name' => 'medical_notes',
        'description' => 'Medical notes',
        'required'   => false,
        'type'       => 'text',
        'active'     => true,
        'sort_order' => 1,
    ])->json('data.id');

    $this->putJson("/api/admin/virtual-forms/{$formId}/fields/{$fieldId}", [
        'active' => false,
    ])->assertOk();
});

it('deletes a field from a virtual form', function () {
    Sanctum::actingAs($this->admin);

    $formId = $this->postJson('/api/admin/virtual-forms', [
        'category' => 'membership',
        'name'     => 'Form Field Delete',
    ])->json('data.id');

    $fieldId = $this->postJson("/api/admin/virtual-forms/{$formId}/fields", [
        'field_name' => 'delete_me',
        'description' => 'Delete me',
        'required'   => false,
        'type'       => 'text',
        'active'     => true,
        'sort_order' => 1,
    ])->json('data.id');

    $this->deleteJson("/api/admin/virtual-forms/{$formId}/fields/{$fieldId}")
         ->assertOk();
});
