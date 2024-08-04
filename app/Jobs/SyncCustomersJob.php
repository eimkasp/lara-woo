<?php 

namespace App\Jobs;

use App\Models\Customer;
use App\Models\Channel;
use Automattic\WooCommerce\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncCustomersJob implements ShouldQueue
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
            $customers = $this->woocommerce->get('customers', ['page' => $page, 'per_page' => 100]);
            $page++;

            foreach ($customers as $customer) {
                // Ensure first_name and last_name are not null
                $firstName = $customer->first_name ?? '';
                $lastName = $customer->last_name ?? '';

                $customerModel = Customer::updateOrCreate(
                    [
                        'email' => $customer->email,
                        'channel_id' => $this->channel->id,
                    ],
                    [
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $customer->email,
                        'channel_id' => $this->channel->id,
                        'channel' => $customer->id,
                        'woocommerce_id' => $customer->id,

                    ]
                );

                \Log::info("Customer Created Or Updated: " . $customer->email . ". Channel: " . $this->channel->name);
                $this->outputMessage("Customer Created Or Updated: " . $customer->email . ". Channel: " . $this->channel->name);

                // Sync polymorphic meta fields for customer
                foreach ($customer->meta_data as $meta) {
                    $value = is_array($meta->value) || is_object($meta->value) ? json_encode($meta->value) : $meta->value;
                    $customerModel->meta()->updateOrCreate(
                        [
                            'key' => $meta->key,
                        ],
                        [
                            'value' => $value,
                        ]
                    );
                }
                $this->outputMessage("Customer Meta Synced: " . $customer->email . ". Channel: " . $this->channel->name);
            }
        } while (count($customers) > 0);
    }

    protected function outputMessage($message)
    {
        if (app()->runningInConsole()) {
            echo $message . PHP_EOL;
        }
    }
}
