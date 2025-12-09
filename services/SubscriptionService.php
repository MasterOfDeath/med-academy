<?php

namespace app\services;

use app\exceptions\SubscriptionException;
use app\models\Author;
use app\models\Subscription;

class SubscriptionService
{
    public function subscribeToAuthor(int $authorId, string $phone): void
    {
        $author = Author::findOne($authorId);
        if (!$author) {
            throw new SubscriptionException('Author not found.');
        }

        $existingSubscription = Subscription::find()
            ->where(['author_id' => $authorId, 'phone' => $phone])
            ->one();

        if ($existingSubscription) {
            throw new SubscriptionException('You are already subscribed to this author with this phone number.');
        }

        $subscription = new Subscription();
        $subscription->author_id = $authorId;
        $subscription->phone = $phone;

        if (!$subscription->validate() || !$subscription->save()) {
            throw new SubscriptionException('Error occurred while subscribing. Please check your phone number.');
        }
    }
}
