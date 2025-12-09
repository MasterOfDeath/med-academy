<?php

namespace app\services;

use app\models\Author;
use app\models\Subscription;
use Yii;
use yii\helpers\Html;

class SubscriptionService
{
    public function subscribeToAuthor(int $authorId, string $phone): array
    {
        $author = Author::findOne($authorId);
        if (!$author) {
            return [
                'success' => false,
                'message' => 'Author not found.',
                'type' => 'error'
            ];
        }

        $existingSubscription = Subscription::find()
            ->where(['author_id' => $authorId, 'phone' => $phone])
            ->one();
            
        if ($existingSubscription) {
            return [
                'success' => false,
                'message' => 'You are already subscribed to this author with this phone number.',
                'type' => 'error'
            ];
        } else {
            $subscription = new Subscription();
            $subscription->author_id = $authorId;
            $subscription->phone = $phone;
            
            if ($subscription->validate() && $subscription->save()) {
                return [
                    'success' => true,
                    'message' => 'You have successfully subscribed to new books by ' . Html::encode($author->full_name),
                    'type' => 'success'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error occurred while subscribing. Please check your phone number.',
                    'type' => 'error'
                ];
            }
        }
    }
}
