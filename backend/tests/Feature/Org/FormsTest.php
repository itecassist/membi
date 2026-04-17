<?php

use App\Domain\Member\Models\Member;
use App\Domain\Organisation\Models\Organisation;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->org   = Organisation::where('seo_name', 'river')->firstOrFail();
    $this->alice = User::where('email', 'alice@example.com')->firstOrFail();
    $this->aliceMember = Member::where('organisation_id', $this->org->id)
        ->where('email', 'alice@example.com')
        ->firstOrFail();
});

it('lists virtual records', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/virtual-records')
         ->assertOk();
});

it('creates a virtual record', function () {
    Sanctum::actingAs($this->alice);

    // First create a virtual form via admin
    $admin  = User::where('email', 'admin@membix.com')->firstOrFail();
    Sanctum::actingAs($admin);

    $formId = $this->postJson('/api/admin/virtual-forms', [
        'category' => 'membership',
        'name'     => 'Test Form ' . uniqid(),
    ])->json('data.id');

    Sanctum::actingAs($this->alice);

    $this->orgJson('post', '/virtual-records', [
        'virtual_form_id' => $formId,
        'member_id'       => $this->aliceMember->id,
        'data'            => ['emergency_contact' => 'Jane Doe — 07700900000'],
    ])->assertStatus(201);
});

it('shows a virtual record', function () {
    Sanctum::actingAs($this->alice);

    $admin = User::where('email', 'admin@membix.com')->firstOrFail();
    Sanctum::actingAs($admin);

    $formId = $this->postJson('/api/admin/virtual-forms', [
        'category' => 'membership',
        'name'     => 'Show Form ' . uniqid(),
    ])->json('data.id');

    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/virtual-records', [
        'virtual_form_id' => $formId,
        'member_id'       => $this->aliceMember->id,
        'data'            => ['note' => 'test'],
    ])->json('data.id');

    $this->orgJson('get', "/virtual-records/{$id}")
         ->assertOk();
});

it('updates a virtual record', function () {
    Sanctum::actingAs($this->alice);

    $admin = User::where('email', 'admin@membix.com')->firstOrFail();
    Sanctum::actingAs($admin);

    $formId = $this->postJson('/api/admin/virtual-forms', [
        'category' => 'membership',
        'name'     => 'Update Form ' . uniqid(),
    ])->json('data.id');

    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/virtual-records', [
        'virtual_form_id' => $formId,
        'member_id'       => $this->aliceMember->id,
        'data'            => ['note' => 'original'],
    ])->json('data.id');

    $this->orgJson('put', "/virtual-records/{$id}", [
        'data' => ['note' => 'updated'],
    ])->assertOk();
});

it('deletes a virtual record', function () {
    Sanctum::actingAs($this->alice);

    $admin = User::where('email', 'admin@membix.com')->firstOrFail();
    Sanctum::actingAs($admin);

    $formId = $this->postJson('/api/admin/virtual-forms', [
        'category' => 'membership',
        'name'     => 'Delete Form ' . uniqid(),
    ])->json('data.id');

    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/virtual-records', [
        'virtual_form_id' => $formId,
        'member_id'       => $this->aliceMember->id,
        'data'            => ['note' => 'delete me'],
    ])->json('data.id');

    $this->orgJson('delete', "/virtual-records/{$id}")
         ->assertOk();
});
