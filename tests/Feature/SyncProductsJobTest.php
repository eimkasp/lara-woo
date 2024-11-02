<?php

use App\Jobs\SyncProductsJob;
use App\Models\Channel;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Support\Facades\Queue;
use Automattic\WooCommerce\Client;

it('syncs products and variations from WooCommerce', function () {
    Queue::fake();

    $channel = Channel::factory()->create();
    $woocommerce = Mockery::mock('overload:Automattic\WooCommerce\Client');
    $woocommerce->shouldReceive('get')
        ->with('products', ['page' => 1, 'per_page' => 100])
        ->andReturn([
            (object)[
                'sku' => 'test-sku',
                'name' => 'Test Product',
                'price' => 100,
                'stock_quantity' => 10,
                'variations' => [
                    (object)[
                        'sku' => 'test-variation-sku',
                        'attributes' => [
                            (object)['option' => 'Size M'],
                        ],
                        'price' => 50,
                        'stock_quantity' => 5,
                    ],
                ],
                'images' => [],
                'meta_data' => [],
            ],
        ]);

    SyncProductsJob::dispatch($channel);

    Queue::assertPushed(SyncProductsJob::class, function ($job) use ($channel) {
        return $job->channel->id === $channel->id;
    });

    $product = Product::where('sku', 'test-sku')->where('channel_id', $channel->id)->first();
    expect($product)->not->toBeNull();
    expect($product->name)->toBe('Test Product');
    expect($product->price)->toBe(100);
    expect($product->stock_quantity)->toBe(10);

    $variation = ProductVariation::where('sku', 'test-variation-sku')->where('product_id', $product->id)->first();
    expect($variation)->not->toBeNull();
    expect($variation->name)->toBe('Size M');
    expect($variation->price)->toBe(50);
    expect($variation->stock_quantity)->toBe(5);
});
