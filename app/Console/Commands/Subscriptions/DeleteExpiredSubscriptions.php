<?php

namespace App\Console\Commands\Subscriptions;

use App\Models\Subscription;
use App\Notifications\Subscriptions\SubscriptionDeletedNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteExpiredSubscriptions extends Command
{
    protected $signature = 'subscriptions:expired-delete';

    protected $description = 'Delete expired subscriptions';

    public function handle()
    {
        $days = settings('subscription')->delete_expired;
        $subscriptions = Subscription::where([['expiry_at', '<', Carbon::now()->subDays($days)], ['status', Subscription::STATUS_ACTIVE]])->notFree()->get();
        if ($subscriptions->count() > 0) {
            foreach ($subscriptions as $subscription) {
                if ($subscription->user->generated_images->count() > 0) {
                    foreach ($subscription->user->generated_images as $generatedImage) {
                        $handler = $generatedImage->storageProvider->handler;
                        $delete = $handler::delete($generatedImage->path);
                        $generatedImage->delete();
                    }
                }
                $subscription->user->notify(new SubscriptionDeletedNotification($subscription));
                $subscription->delete();
            }
        }
    }
}