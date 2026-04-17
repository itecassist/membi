<?php

use App\Domain\Organisation\Models\Organisation;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->org   = Organisation::where('seo_name', 'river')->firstOrFail();
    $this->alice = User::where('email', 'alice@example.com')->firstOrFail();
});

// ── Product Categories ────────────────────────────────────────────────────────

it('lists product categories', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/product-categories')
         ->assertOk();
});

it('creates a product category', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('post', '/product-categories', ['name' => 'Clothing ' . uniqid()])
         ->assertStatus(201)
         ->assertJsonStructure(['data' => ['id', 'name']]);
});

it('shows a product category', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/product-categories', ['name' => 'Show Cat ' . uniqid()])
               ->json('data.id');

    $this->orgJson('get', "/product-categories/{$id}")
         ->assertOk();
});

it('updates a product category', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/product-categories', ['name' => 'Update Cat ' . uniqid()])
               ->json('data.id');

    $this->orgJson('put', "/product-categories/{$id}", ['name' => 'Apparel ' . uniqid()])
         ->assertOk();
});

it('deletes a product category', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/product-categories', ['name' => 'Delete Cat ' . uniqid()])
               ->json('data.id');

    $this->orgJson('delete', "/product-categories/{$id}")
         ->assertOk();
});

// ── Products ──────────────────────────────────────────────────────────────────

it('lists products', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('get', '/products')
         ->assertOk();
});

it('creates a product', function () {
    Sanctum::actingAs($this->alice);

    $this->orgJson('post', '/products', [
        'name'        => 'Club T-Shirt ' . uniqid(),
        'code'        => 'TSHIRT-' . uniqid(),
        'description' => 'Official club t-shirt',
    ])->assertStatus(201)
      ->assertJsonStructure(['data' => ['id', 'name']]);
});

it('shows a product', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/products', [
        'name' => 'Show Product ' . uniqid(),
        'code' => 'SHOW-' . uniqid(),
    ])->json('data.id');

    $this->orgJson('get', "/products/{$id}")
         ->assertOk();
});

it('updates a product', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/products', [
        'name' => 'Update Product ' . uniqid(),
        'code' => 'UPD-' . uniqid(),
    ])->json('data.id');

    $this->orgJson('put', "/products/{$id}", ['name' => 'Updated ' . uniqid()])
         ->assertOk();
});

it('deletes a product', function () {
    Sanctum::actingAs($this->alice);

    $id = $this->orgJson('post', '/products', [
        'name' => 'Delete Product ' . uniqid(),
        'code' => 'DEL-' . uniqid(),
    ])->json('data.id');

    $this->orgJson('delete', "/products/{$id}")
         ->assertOk();
});

// ── Product Options ───────────────────────────────────────────────────────────

it('lists product options', function () {
    Sanctum::actingAs($this->alice);

    $productId = $this->orgJson('post', '/products', [
        'name' => 'Options Product ' . uniqid(),
        'code' => 'OPT-' . uniqid(),
    ])->json('data.id');

    $this->orgJson('get', "/products/{$productId}/options")
         ->assertOk();
});

it('creates a product option', function () {
    Sanctum::actingAs($this->alice);

    $productId = $this->orgJson('post', '/products', [
        'name' => 'Product for Options ' . uniqid(),
        'code' => 'PFO-' . uniqid(),
    ])->json('data.id');

    $this->orgJson('post', "/products/{$productId}/options", [
        'name'      => 'Small',
        'code'      => 'S-' . uniqid(),
        'available' => true,
    ])->assertStatus(201);
});

it('updates a product option', function () {
    Sanctum::actingAs($this->alice);

    $productId = $this->orgJson('post', '/products', [
        'name' => 'Opt Update Product ' . uniqid(),
        'code' => 'OPU-' . uniqid(),
    ])->json('data.id');

    $optionId = $this->orgJson('post', "/products/{$productId}/options", [
        'name'      => 'Large',
        'code'      => 'L-' . uniqid(),
        'available' => true,
    ])->json('data.id');

    $this->orgJson('put', "/products/{$productId}/options/{$optionId}", ['available' => false])
         ->assertOk();
});

it('deletes a product option', function () {
    Sanctum::actingAs($this->alice);

    $productId = $this->orgJson('post', '/products', [
        'name' => 'Opt Delete Product ' . uniqid(),
        'code' => 'OPD-' . uniqid(),
    ])->json('data.id');

    $optionId = $this->orgJson('post', "/products/{$productId}/options", [
        'name'      => 'XL',
        'code'      => 'XL-' . uniqid(),
        'available' => true,
    ])->json('data.id');

    $this->orgJson('delete', "/products/{$productId}/options/{$optionId}")
         ->assertOk();
});
