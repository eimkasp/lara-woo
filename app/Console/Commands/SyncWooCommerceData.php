<?php

namespace App\Console\Commands;

use App\Models\ProductImage;
use App\Models\ProductVariation;
use Illuminate\Console\Command;
use Automattic\WooCommerce\Client;
use App\Models\Product;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Channel;

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
                [
                    'version' => 'wc/v3',
                ]
            );

            $this->syncProducts($woocommerce, $channel);
            $this->syncOrders($woocommerce, $channel);
            $this->syncCustomers($woocommerce, $channel);
        }
    }

    protected function syncProducts($woocommerce, $channel)
    {
        $products = $woocommerce->get('products');

        foreach ($products as $product) {
            // Handle main product
            $productModel = Product::updateOrCreate(
                [
                    'sku' => $product->sku,
                    'channel_id' => $channel->id,
                ],
                [
                    'name' => $product->name,
                    'price' => $product->price,
                    'stock_quantity' => $product->stock_quantity,
                    'channel' => $product->id, // Use product ID for tracking
                ]
            );

            // Handle variations
            $variations = $woocommerce->get("products/{$product->id}/variations");
            foreach ($variations as $variation) {
                ProductVariation::updateOrCreate(
                    [
                        'product_id' => $productModel->id,
                        'sku' => $variation->sku,
                    ],
                    [
                        'name' => $variation->name,
                        'price' => $variation->price,
                        'stock_quantity' => $variation->stock_quantity,
                    ]
                );
            }

            // Handle images
            $images = $woocommerce->get("products/{$product->id}/images");
            foreach ($images as $image) {
                ProductImage::updateOrCreate(
                    [
                        'product_id' => $productModel->id,
                        'url' => $image->src,
                    ],
                    [
                        'is_primary' => $image->position === 0, // Assuming first image is primary
                    ]
                );
            }
        }
    }


    protected function syncOrders($woocommerce, $channel)
    {
        $page = 1;

        do {
            $orders = $woocommerce->get('orders', ['page' => $page, 'per_page' => 100]);
            $page++;

            foreach ($orders as $order) {
                $customer = Customer::firstOrCreate(
                    [
                        'email' => $order->billing->email,
                        'channel_id' => $channel->id,
                    ],
                    [
                        'first_name' => $order->billing->first_name,
                        'last_name' => $order->billing->last_name,
                        'email' => $order->billing->email,
                        'channel_id' => $channel->id,
                        'channel' => $order->customer_id, // Store WooCommerce ID in 'channel'
                    ]
                );

                $orderModel = Order::updateOrCreate(
                    [
                        'channel' => $order->id,
                        'channel_id' => $channel->id,
                    ],
                    [
                        'total' => $order->total,
                        'status' => $order->status,
                        'customer_id' => $customer->id,
                        'channel_id' => $channel->id,
                    ]
                );

                $this->info("Order imported/updated: ID {$orderModel->id}, Total {$orderModel->total}, Channel ID {$orderModel->channel}");

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
            }
        } while (count($orders) > 0);
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
                        'channel' => $customer->id, // Store WooCommerce ID in 'channel'
                    ]
                );

                $this->info("Customer imported/updated: ID {$customerModel->id}, Email {$customerModel->email}, Channel ID {$customerModel->channel}");
            }
        } while (count($customers) > 0);
    }
}
