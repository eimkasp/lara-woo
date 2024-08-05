<?php 

namespace App\Jobs;

use App\Models\Order;
use App\Models\Channel;
use App\Models\Product;
use App\Models\Customer;
use Automattic\WooCommerce\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncOrdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $channel;

    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
    }

    public function handle()
    {
        $woocommerce = new Client(
            $this->channel->base_url,
            $this->channel->consumer_key,
            $this->channel->consumer_secret,
            ['version' => 'wc/v3']
        );

        $page = 1;

        do {
            $orders = $woocommerce->get('orders', ['page' => $page, 'per_page' => 100]);
            $page++;

            foreach ($orders as $order) {
                $customer = Customer::where('email', $order->billing->email)->first();
                
                if (!$customer) {
                    continue;
                }
                // Save order details including billing information and original order date
                $orderModel = Order::updateOrCreate(
                    [
                        'woocommerce_id' => $order->id,
                        'channel_id' => $this->channel->id,
                    ],
                    [
                        'total' => $order->total,
                        'status' => $order->status,
                        'customer_id' => $customer->id,
                        'channel_id' => $this->channel->id,
                        'channel' => $order->id,
                        'woocommerce_id' => $order->id,
                        'billing_first_name' => $order->billing->first_name,
                        'billing_last_name' => $order->billing->last_name,
                        'billing_address_1' => $order->billing->address_1,
                        'billing_address_2' => $order->billing->address_2,
                        'billing_city' => $order->billing->city,
                        'billing_state' => $order->billing->state,
                        'billing_postcode' => $order->billing->postcode,
                        'billing_country' => $order->billing->country,
                        'billing_email' => $order->billing->email,
                        'billing_phone' => $order->billing->phone,
                        'original_order_date' => $order->date_created,
                    ]
                );

                // Sync line items with associated products
                foreach ($order->line_items as $item) {
                    $product = Product::where('sku', $item->sku)
                        ->where('channel_id', $this->channel->id)
                        ->first();

                    if ($product) {
                        $orderModel->products()->syncWithoutDetaching([
                            $product->id => ['quantity' => $item->quantity],
                        ]);
                    }
                }
            }
        } while (count($orders) > 0);
    }
}
