<?php

use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

it('can display stock management page', function () {
    $response = get('/admin/stock-management');

    $response->assertStatus(200);
    $response->assertSee('Stock Management');
});

it('can update stock quantity from stock management page', function () {
    $product = Product::factory()->create();
    $variation = ProductVariation::factory()->create(['product_id' => $product->id]);

    $response = post('/admin/stock-management/update', [
        'product_id' => $product->id,
        'variation_id' => $variation->id,
        'stock_quantity' => 10,
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('product_variations', [
        'id' => $variation->id,
        'stock_quantity' => 10,
    ]);
});
