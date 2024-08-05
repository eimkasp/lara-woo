<?php

namespace App\Jobs;

use App\Models\Review;
use App\Models\Channel;
use App\Models\Product;
use App\Models\Customer;
use Automattic\WooCommerce\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncWooCommerceReviews implements ShouldQueue
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
            $reviews = $woocommerce->get('products/reviews', ['page' => $page, 'per_page' => 100]);
            $page++;

            foreach ($reviews as $review) {
                $product = Product::where('channel_id', $this->channel->id)
                    ->where('woocommerce_id', $review->product_id)
                    ->first();

                $customer = Customer::firstOrCreate(
                    ['email' => $review->reviewer_email],
                    ['first_name' => $review->reviewer_name, 'channel_id' => $this->channel->id]
                );

                if ($product && $customer) {
                    Review::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'customer_id' => $customer->id,
                            'channel_id' => $this->channel->id,
                        ],
                        [
                            'rating' => $review->rating,
                            'review' => $review->review,
                            'verified' => $review->verified,
                        ]
                    );
                }
            }
        } while (count($reviews) > 0);
    }
}

