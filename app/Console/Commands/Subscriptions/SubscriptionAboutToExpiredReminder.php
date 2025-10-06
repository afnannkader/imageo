<?php

namespace App\Console\Commands\Subscriptions;

use App\Models\Subscription;
use App\Notifications\Subscriptions\SubscriptionAboutToExpiredNotification;
use Illuminate\Console\Command;

class SubscriptionAboutToExpiredReminder extends Command
{
    protected $signature = 'subscriptions:expiring-reminder';

    protected $description = 'Send notifications to users whose subscriptions are about to expire';

    public function handle()
    {
        if (mailTemplate('subscription_about_expired')->status) {
            $subscriptions = Subscription::notFree()->isAboutToExpire()->aboutToExpireReminderNotSent()->get();
            if ($subscriptions->count() > 0) {
                foreach ($subscriptions as $subscription) {
                    $subscription->user->notify(new SubscriptionAboutToExpiredNotification($subscription));
                    $subscription->update(['about_to_expire_reminder' => true]);
                }
            }
        }
    }
}