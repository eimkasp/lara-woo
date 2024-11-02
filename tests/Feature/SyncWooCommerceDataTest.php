<?php

use App\Console\Commands\SyncWooCommerceData;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SyncProductsJob;
use App\Jobs\SyncCustomersJob;
use App\Jobs\SyncOrdersJob;
use App\Jobs\UpdateStockQuantitiesJob;
use App\Models\Channel;

it('dispatches the correct jobs for each channel', function () {
    Queue::fake();

    $channel = Channel::factory()->create();

    Artisan::call('sync:woocommerce', ['--channel' => $channel->id]);

    Queue::assertPushed(SyncProductsJob::class, function ($job) use ($channel) {
        return $job->channel->id === $channel->id;
    });

    Queue::assertPushed(SyncCustomersJob::class, function ($job) use ($channel) {
        return $job->channel->id === $channel->id;
    });

    Queue::assertPushed(SyncOrdersJob::class, function ($job) use ($channel) {
        return $job->channel->id === $channel->id;
    });

    Queue::assertPushed(UpdateStockQuantitiesJob::class, function ($job) use ($channel) {
        return $job->channel->id === $channel->id;
    });
});

it('updates stock quantities for products and variations', function () {
    $channel = Channel::factory()->create();
    $woocommerce = Mockery::mock('overload:Automattic\WooCommerce\Client');
    $woocommerce->shouldReceive('get')
        ->with('products', ['page' => 1, 'per_page' => 100])
        ->andReturn([
            (object)[
                'sku' => 'test-sku',
                'stock_quantity' => 10,
                'variations' => [
                    (object)[
                        'sku' => 'test-variation-sku',
                        'stock_quantity' => 5,
                    ],
                ],
            ],
        ]);

    $command = new SyncWooCommerceData();
    $command->updateStockQuantities($woocommerce, $channel);

    $product = Product::where('sku', 'test-sku')->where('channel_id', $channel->id)->first();
    expect($product->stock_quantity)->toBe(10);

    $variation = ProductVariation::where('sku', 'test-variation-sku')->where('product_id', $product->id)->first();
    expect($variation->stock_quantity)->toBe(5);
});
