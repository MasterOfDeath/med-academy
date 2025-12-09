<?php

namespace app\jobs;

use app\models\Book;
use app\models\Subscription;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class SendSmsJob extends BaseObject implements JobInterface
{
    public $bookId;
    public $message;

    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    public function execute($queue)
    {
        \Yii::info("Starting SMS sending job for book ID: {$this->bookId}", 'sms');

        $book = Book::findOne($this->bookId);
        if (!$book) {
            return;
        }

        $authorIds = $book->getAuthorIds();
        $subscriptions = Subscription::find()
            ->where(['author_id' => $authorIds])
            ->all();

        if (!$this->message) {
            $authors = [];
            foreach ($book->authors as $author) {
                $authors[] = $author->full_name;
            }
            $authorNames = implode(', ', $authors);
            $this->message = "New book '{$book->title}' by author(s) {$authorNames} is now available!";
        }

        $count = 0;
        $totalSubscriptions = count($subscriptions);

        foreach ($subscriptions as $subscription) {
            try {
                \Yii::$container->get(\app\interfaces\SmsClientInterface::class)->sendSms($subscription->phone, $this->message);
                \Yii::info("SMS sent successfully to {$subscription->phone}", 'sms');
            } catch (\app\exceptions\SmsClientException $e) {
                \Yii::error("Failed to send SMS to {$subscription->phone}: {$e->getMessage()}", 'sms');
            }

            $count++;

            // Логируем прогресс каждые 10 отправок
            if ($count % 10 === 0) {
                \Yii::info("Processed {$count}/{$totalSubscriptions} subscriptions", 'sms');
            }
        }

        \Yii::info("Completed processing all {$totalSubscriptions} subscriptions", 'sms');

        \Yii::info("SMS sending job completed for book ID: {$this->bookId}", 'sms');
    }

    public function setBookId($bookId): void
    {
        $this->bookId = $bookId;
    }
}
