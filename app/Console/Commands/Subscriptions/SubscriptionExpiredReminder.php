<?php

namespace App\Console\Commands\Subscriptions;

use App\Models\Subscription;
use App\Notifications\Subscriptions\SubscriptionExpiredNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SubscriptionExpiredReminder extends Command
{
    protected $signature = 'subscriptions:expired-reminder';

    protected $description = 'Send notifications to users whose subscriptions are expired';

    public function handle()
    {
        if (mailTemplate('subscription_expired')->status) {
            $subscriptions = Subscription::where('expiry_at', '<=', Carbon::now()->subDays(settings('subscription')->expired_reminder))
                ->where('status', Subscription::STATUS_ACTIVE)
                ->notFree()->expiredReminderNotSent()->get();
            if ($subscriptions->count() > 0) {
                foreach ($subscriptions as $subscription) {
                    $subscription->user->notify(new SubscriptionExpiredNotification($subscription));
                    $subscription->update(['expired_reminder' => true]);
                }
            }
        }
    }
}