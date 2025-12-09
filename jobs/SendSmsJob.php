<?php

namespace app\jobs;

use app\interfaces\SmsClientInterface;
use app\models\Book;
use app\models\Subscription;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class SendSmsJob extends BaseObject implements JobInterface
{
    private $bookId;
    private $message;

    private ?SmsClientInterface $smsClient = null;

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

        foreach ($subscriptions as $subscription) {
            try {
                $this->smsClient->sendSms($subscription->phone, $this->message);
                \Yii::info("SMS sent successfully to {$subscription->phone}", 'sms');
            } catch (\app\exceptions\SmsClientException $e) {
                \Yii::error("Failed to send SMS to {$subscription->phone}: {$e->getMessage()}", 'sms');
            }
        }

        \Yii::info("SMS sending job completed for book ID: {$this->bookId}", 'sms');
    }

    public function setSmsClient(SmsClientInterface $smsClient): void
    {
        $this->smsClient = $smsClient;
    }

    public function setBookId($bookId): void
    {
        $this->bookId = $bookId;
    }
}
