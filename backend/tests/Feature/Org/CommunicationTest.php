<?php

use App\Domain\Organisation\Models\Organisation;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->org   = Organisation::where('seo_name', 'river')->firstOrFail();
    $this->alice = User::where('email', 'alice@example.com')->firstOrFail();
});

it('lists email templates', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/email-templates')
         ->assertOk();
});

it('creates an email template', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('post', '/email-templates', [
        'name'    => 'Welcome Email ' . uniqid(),
        'subject' => 'Welcome to the club!',
        'content' => '<p>Dear {{member_name}}, welcome!</p>',
    ])->assertStatus(201)
      ->assertJsonStructure(['data' => ['id', 'name']]);
});

it('shows an email template', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/email-templates', [
        'name'    => 'Show Template ' . uniqid(),
        'subject' => 'Subject',
        'content' => '<p>Content</p>',
    ])->json('data.id');

    $this->orgJson('get', "/email-templates/{$id}")
         ->assertOk();
});

it('updates an email template', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/email-templates', [
        'name'    => 'Update Template ' . uniqid(),
        'subject' => 'Subject',
        'content' => '<p>Content</p>',
    ])->json('data.id');

    $this->orgJson('put', "/email-templates/{$id}", ['subject' => 'Updated subject'])
         ->assertOk();
});

it('deletes an email template', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/email-templates', [
        'name'    => 'Delete Template ' . uniqid(),
        'subject' => 'Subject',
        'content' => '<p>Content</p>',
    ])->json('data.id');

    $this->orgJson('delete', "/email-templates/{$id}")
         ->assertOk();
});
