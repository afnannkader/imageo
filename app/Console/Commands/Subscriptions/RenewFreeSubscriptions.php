<?php

namespace App\Console\Commands\Subscriptions;

use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RenewFreeSubscriptions extends Command
{
    protected $signature = 'subscriptions:renew-free';

    protected $description = 'Renewing the free subscriptions';

    public function handle()
    {
        $subscriptions = Subscription::where('expiry_at', '<=', Carbon::now()->subHour())->free()->active()->get();
        if ($subscriptions->count() > 0) {
            foreach ($subscriptions as $subscription) {
                $expiry_at = ($subscription->plan->interval == 1) ? Carbon::parse($subscription->expiry_at)->addMonth() : Carbon::parse($subscription->expiry_at)->addYear();
                $subscription->expiry_at = $expiry_at;
                $subscription->update();
            }
        }
    }
}