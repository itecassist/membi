<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->admin = User::where('email', 'admin@membix.com')->firstOrFail();
    $this->alice = User::where('email', 'alice@example.com')->firstOrFail();
});

// ── Article Categories ────────────────────────────────────────────────────────

it('creates an article category as admin', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->postJson('/api/admin/article-categories', [
        'name'         => 'Getting Started',
        'seo_name'     => 'getting-started-' . uniqid(),
        'live'         => true,
        'article_live' => true,
    ]);

    $response->assertStatus(201)
             ->assertJsonStructure(['data' => ['id']]);
});

it('returns 403 when non-admin creates article category', function () {
    Sanctum::actingAs($this->alice);

    $this->postJson('/api/admin/article-categories', [
        'name'     => 'Test',
        'seo_name' => 'test',
        'live'     => true,
    ])->assertStatus(403);
});

it('updates an article category as admin', function () {
    Sanctum::actingAs($this->admin);

    $created = $this->postJson('/api/admin/article-categories', [
        'name'         => 'Update Me',
        'seo_name'     => 'update-me-' . uniqid(),
        'live'         => true,
        'article_live' => true,
    ])->json('data.id');

    $this->putJson("/api/admin/article-categories/{$created}", ['live' => false])
         ->assertOk();
});

it('deletes an article category as admin', function () {
    Sanctum::actingAs($this->admin);

    $created = $this->postJson('/api/admin/article-categories', [
        'name'         => 'Delete Me',
        'seo_name'     => 'delete-me-' . uniqid(),
        'live'         => true,
        'article_live' => true,
    ])->json('data.id');

    $this->deleteJson("/api/admin/article-categories/{$created}")
         ->assertOk();
});

// ── Articles ──────────────────────────────────────────────────────────────────

it('creates an article as admin', function () {
    Sanctum::actingAs($this->admin);

    $category = $this->postJson('/api/admin/article-categories', [
        'name'         => 'Help',
        'seo_name'     => 'help-' . uniqid(),
        'live'         => true,
        'article_live' => true,
    ])->json('data.id');

    $this->postJson('/api/admin/articles', [
        'article_category_id' => $category,
        'type'                => 'help',
        'title'               => 'How to register',
        'seo_name'            => 'how-to-register-' . uniqid(),
        'content'             => '<p>Step by step guide.</p>',
        'live'                => true,
    ])->assertStatus(201);
});

it('updates an article as admin', function () {
    Sanctum::actingAs($this->admin);

    $category = $this->postJson('/api/admin/article-categories', [
        'name'         => 'Cat',
        'seo_name'     => 'cat-' . uniqid(),
        'live'         => true,
        'article_live' => true,
    ])->json('data.id');

    $article = $this->postJson('/api/admin/articles', [
        'article_category_id' => $category,
        'type'                => 'help',
        'title'               => 'Article',
        'seo_name'            => 'article-' . uniqid(),
        'content'             => '<p>Content.</p>',
        'live'                => true,
    ])->json('data.id');

    $this->putJson("/api/admin/articles/{$article}", ['live' => false])
         ->assertOk();
});

it('deletes an article as admin', function () {
    Sanctum::actingAs($this->admin);

    $category = $this->postJson('/api/admin/article-categories', [
        'name'         => 'Cat2',
        'seo_name'     => 'cat2-' . uniqid(),
        'live'         => true,
        'article_live' => true,
    ])->json('data.id');

    $article = $this->postJson('/api/admin/articles', [
        'article_category_id' => $category,
        'type'                => 'help',
        'title'               => 'Delete Article',
        'seo_name'            => 'del-article-' . uniqid(),
        'content'             => '<p>.</p>',
        'live'                => true,
    ])->json('data.id');

    $this->deleteJson("/api/admin/articles/{$article}")
         ->assertOk();
});

// ── FAQ Categories ────────────────────────────────────────────────────────────

it('creates a FAQ category as admin', function () {
    Sanctum::actingAs($this->admin);

    $this->postJson('/api/admin/faq-categories', [
        'description' => 'Membership FAQs',
        'sort_order'  => 1,
    ])->assertStatus(201);
});

it('updates a FAQ category as admin', function () {
    Sanctum::actingAs($this->admin);

    $id = $this->postJson('/api/admin/faq-categories', [
        'description' => 'Update FAQ Cat',
        'sort_order'  => 1,
    ])->json('data.id');

    $this->putJson("/api/admin/faq-categories/{$id}", ['sort_order' => 2])
         ->assertOk();
});

it('deletes a FAQ category as admin', function () {
    Sanctum::actingAs($this->admin);

    $id = $this->postJson('/api/admin/faq-categories', [
        'description' => 'Delete FAQ Cat',
        'sort_order'  => 99,
    ])->json('data.id');

    $this->deleteJson("/api/admin/faq-categories/{$id}")
         ->assertOk();
});

// ── FAQs ──────────────────────────────────────────────────────────────────────

it('creates a FAQ as admin', function () {
    Sanctum::actingAs($this->admin);

    $catId = $this->postJson('/api/admin/faq-categories', [
        'description' => 'FAQ Cat for FAQs',
        'sort_order'  => 1,
    ])->json('data.id');

    $this->postJson('/api/admin/faqs', [
        'faq_category_id'  => $catId,
        'question'         => 'How do I cancel my membership?',
        'answer'           => 'Contact your club administrator.',
        'sort_order'       => 1,
        'display_on_help'  => true,
    ])->assertStatus(201);
});

it('updates a FAQ as admin', function () {
    Sanctum::actingAs($this->admin);

    $catId = $this->postJson('/api/admin/faq-categories', [
        'description' => 'FAQ Cat',
        'sort_order'  => 1,
    ])->json('data.id');

    $faqId = $this->postJson('/api/admin/faqs', [
        'faq_category_id' => $catId,
        'question'        => 'Q?',
        'answer'          => 'A.',
        'sort_order'      => 1,
        'display_on_help' => false,
    ])->json('data.id');

    $this->putJson("/api/admin/faqs/{$faqId}", ['paused' => true])
         ->assertOk();
});

it('deletes a FAQ as admin', function () {
    Sanctum::actingAs($this->admin);

    $catId = $this->postJson('/api/admin/faq-categories', [
        'description' => 'FAQ Cat',
        'sort_order'  => 1,
    ])->json('data.id');

    $faqId = $this->postJson('/api/admin/faqs', [
        'faq_category_id' => $catId,
        'question'        => 'Delete Q?',
        'answer'          => 'A.',
        'sort_order'      => 1,
        'display_on_help' => false,
    ])->json('data.id');

    $this->deleteJson("/api/admin/faqs/{$faqId}")
         ->assertOk();
});
