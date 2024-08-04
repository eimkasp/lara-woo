<?php 

namespace App\Jobs;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use App\Models\Channel;
use Automattic\WooCommerce\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $channel;
    protected $woocommerce;

    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
        $this->woocommerce = new Client(
            $channel->base_url,
            $channel->consumer_key,
            $channel->consumer_secret,
            ['version' => 'wc/v3']
        );
    }

    public function handle()
    {
        $page = 1;

        do {
            $products = $this->woocommerce->get('products', ['page' => $page, 'per_page' => 100]);
            $page++;

            foreach ($products as $product) {
                if (empty($product->sku)) {
                    \Log::error("Product skipped: No SKU found. Channel: " . $this->channel->name);
                    $this->outputMessage("Product skipped: No SKU found. Channel: " . $this->channel->name);
                    continue;
                }

                $productModel = Product::updateOrCreate(
                    [
                        'sku' => $product->sku,
                        'channel_id' => $this->channel->id,
                    ],
                    [
                        'name' => $product->name,
                        'price' => $product->price,
                        'stock_quantity' => $product->stock_quantity,
                        'channel_id' => $this->channel->id,
                    ]
                );

                \Log::info("Product Created Or Updated: " . $product->sku . ". Channel: " . $this->channel->name);
                $this->outputMessage("Product Created Or Updated: " . $product->sku . ". Channel: " . $this->channel->name);

                // Sync product images
                foreach ($product->images as $index => $image) {
                    ProductImage::updateOrCreate(
                        [
                            'product_id' => $productModel->id,
                            'url' => $image->src,
                        ],
                        [
                            'is_primary' => $index === 0,
                        ]
                    );
                }
                $this->outputMessage("Product Images Synced: " . $product->sku . ". Channel: " . $this->channel->name);

                // Sync polymorphic meta fields
                foreach ($product->meta_data as $meta) {
                    $value = is_array($meta->value) || is_object($meta->value) ? json_encode($meta->value) : $meta->value;
                    $productModel->meta()->updateOrCreate(
                        [
                            'key' => $meta->key,
                        ],
                        [
                            'value' => $value,
                        ]
                    );
                }
                $this->outputMessage("Product Meta Synced: " . $product->sku . ". Channel: " . $this->channel->name);

                // Handle variations
                foreach ($product->variations as $variation) {
                    if (empty($variation->sku)) {
                        \Log::error("Variation skipped: No SKU found. Channel: " . $this->channel->name);
                        $this->outputMessage("Variation skipped: No SKU found. Channel: " . $this->channel->name);
                        continue;
                    }

                    $variationModel = ProductVariation::updateOrCreate(
                        [
                            'product_id' => $productModel->id,
                            'sku' => $variation->sku ?? '0',
                        ],
                        [
                            'name' => implode(' - ', array_column($variation->attributes, 'option')),
                            'price' => $variation->price ?? 0,
                            'stock_quantity' => $variation->stock_quantity ?? 0,
                        ]
                    );

                    // Sync polymorphic meta fields for variation
                    foreach ($variation->meta_data as $meta) {
                        $value = is_array($meta->value) || is_object($meta->value) ? json_encode($meta->value) : $meta->value;
                        $variationModel->meta()->updateOrCreate(
                            [
                                'key' => $meta->key,
                            ],
                            [
                                'value' => $value,
                            ]
                        );
                    }
                }
                $this->outputMessage("Product Variations Synced: " . $product->sku . ". Channel: " . $this->channel->name);
            }
        } while (count($products) > 0);
    }

    protected function outputMessage($message)
    {
        if (app()->runningInConsole()) {
            echo $message . PHP_EOL;
        }
    }
}

