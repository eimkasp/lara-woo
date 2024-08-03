<?php 

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Channel;
use App\Jobs\SyncProductsJob;
use App\Jobs\SyncCustomersJob;
use App\Jobs\SyncOrdersJob;
use Illuminate\Support\Carbon;

class SyncWooCommerceData extends Command
{
    protected $signature = 'sync:woocommerce {--channel=}';
    protected $description = 'Sync WooCommerce data with Laravel';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $channels = Channel::all();

        if ($this->option('channel')) {
            $channelId = $this->option('channel');
            $channels = $channelId ? Channel::where('id', $channelId)->get() : Channel::all();
        }

        foreach ($channels as $channel) {
            $channel->setMeta('last_sync_status', 'running');

            try {
                // Dispatch jobs for each sync process
                SyncProductsJob::dispatch($channel);
                SyncCustomersJob::dispatch($channel);
                SyncOrdersJob::dispatch($channel);

                // Save last sync meta data
                $channel->setMeta('last_sync_time', Carbon::now());
                $channel->setMeta('last_sync_status', 'success');
                $channel->save();

                $this->info("Sync jobs dispatched successfully for channel: {$channel->name}");
            } catch (\Exception $e) {
                // Save last sync status as failed
                $channel->setMeta('last_sync_status', 'failed');
                $channel->save();

                $this->error("Sync failed for channel: {$channel->name}. Error: " . $e->getMessage());
            }
        }
    }
}

