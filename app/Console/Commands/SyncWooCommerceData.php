<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Automattic\WooCommerce\Client;
use App\Models\Product;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Channel;
use App\Models\ProductImage;
use App\Models\ProductVariation;

class SyncWooCommerceData extends Command
{
    protected $signature = 'sync:woocommerce';
    protected $description = 'Sync WooCommerce data with Laravel';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $channels = Channel::all();

        foreach ($channels as $channel) {
            $woocommerce = new Client(
                $channel->base_url,
                $channel->consumer_key,
                $channel->consumer_secret,
                ['version' => 'wc/v3']
            );

            // Sync products first
            $this->syncProducts($woocommerce, $channel);

            // Sync customers next
            $this->syncCustomers($woocommerce, $channel);

            // Finally, sync orders
            $this->syncOrders($woocommerce, $channel);
        }
    }

    protected function syncProducts($woocommerce, $channel)
    {
        $page = 1;

        do {
            $products = $woocommerce->get('products', ['page' => $page, 'per_page' => 100]);
            $page++;

            foreach ($products as $product) {
                if (empty($product->sku)) {
                    $this->error("Product skipped: No SKU found");
                    continue;
                }

                $productModel = Product::updateOrCreate(
                    [
                        'sku' => $product->sku,
                        'channel_id' => $channel->id,
                    ],
                    [
                        'name' => $product->name,
                        'price' => $product->price,
                        'stock_quantity' => $product->stock_quantity,
                        'channel_id' => $channel->id,
                    ]
                );

                $this->info("Product Created Or Updated: " . $product->sku);


                // Sync product images
                $this->syncProductImages($productModel, $product->images);

                $this->info("Product Images Synced " . $product->sku);


                // Sync polymorphic meta fields
                $this->syncMetaFields($productModel, $product->meta_data);

                $this->info("Product Meta Synced " . $product->sku);


                // Handle variations
                foreach ($product->variations as $variation) {
                    if (empty($variation->sku)) {
                        $this->error("Variation skipped: No SKU found");
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
                    $this->syncMetaFields($variationModel, $variation->meta_data);
                }
            }
        } while (count($products) > 0);
    }

    protected function syncCustomers($woocommerce, $channel)
    {
        $page = 1;

        do {
            $customers = $woocommerce->get('customers', ['page' => $page, 'per_page' => 100]);
            $page++;

            foreach ($customers as $customer) {
                $customerModel = Customer::updateOrCreate(
                    [
                        'email' => $customer->email,
                        'channel_id' => $channel->id,
                    ],
                    [
                        'first_name' => $customer->first_name,
                        'last_name' => $customer->last_name,
                        'email' => $customer->email,
                        'channel_id' => $channel->id,
                    ]
                );

                // Sync polymorphic meta fields for customer
                $this->syncMetaFields($customerModel, $customer->meta_data);
            }
        } while (count($customers) > 0);
    }

    protected function syncOrders($woocommerce, $channel)
    {
        $page = 1;

        do {
            $orders = $woocommerce->get('orders', ['page' => $page, 'per_page' => 100]);
            $page++;

            foreach ($orders as $order) {
                $customer = Customer::where('email', $order->billing->email)
                    ->where('channel_id', $channel->id)
                    ->first();

                if (!$customer) {
                    $this->error("Order skipped: Customer not found for Order ID {$order->id}");
                    continue;
                }

                $orderModel = Order::updateOrCreate(
                    [
                        'channel' => $channel->id,
                        'channel_id' => $order->id ?? 5,
                    ],
                    [
                        'total' => $order->total ?? 0,
                        'status' => $order->status ?? 'unknown',
                        'customer_id' => $customer->id ?? 5,
                        'channel_id' => $channel->id ?? 5,
                        'woocommerce_id' => $order->id,
                        'channel' => $order->id ?? 0,
                    ]
                );

                // Sync line items with associated products
                foreach ($order->line_items as $item) {
                    $product = Product::where('sku', $item->sku)
                        ->where('channel_id', $channel->id)
                        ->first();

                    if ($product) {
                        $orderModel->products()->syncWithoutDetaching([
                            $product->id => ['quantity' => $item->quantity],
                        ]);

                        $this->info("Order item linked: Order ID {$orderModel->id}, Product ID {$product->id}, Quantity {$item->quantity}");
                    }
                }

                // Sync polymorphic meta fields for order
                $this->syncMetaFields($orderModel, $order->meta_data);
            }
        } while (count($orders) > 0);
    }

    protected function syncProductImages($productModel, $images)
    {
        foreach ($images as $index => $image) {
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
    }

    protected function syncMetaFields($model, $metaData)
    {
        foreach ($metaData as $meta) {
            // Check if the value is an array or object and JSON encode it
            $value = is_array($meta->value) || is_object($meta->value) ? json_encode($meta->value) : $meta->value;

            $model->meta()->updateOrCreate(
                [
                    'key' => $meta->key,
                ],
                [
                    'value' => $value,
                ]
            );
        }
    }

}
