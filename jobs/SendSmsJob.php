<?php

namespace app\jobs;

use yii\base\BaseObject;
use yii\queue\JobInterface;
use app\models\Subscription;
use app\models\Book;
use app\models\Author;
use app\interfaces\SmsClientInterface;

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
        
        // Получаем книгу и автора
        $book = Book::findOne($this->bookId);
        if (!$book) {
            return;
        }

        // Находим всех подписчиков авторов книги
        $authorIds = $book->getAuthorIds();
        $subscriptions = Subscription::find()
            ->where(['author_id' => $authorIds])
            ->all();

        // Формируем сообщение
        if (!$this->message) {
            $authors = [];
            foreach ($book->authors as $author) {
                $authors[] = $author->full_name;
            }
            $authorNames = implode(', ', $authors);
            $this->message = "New book '{$book->title}' by author(s) {$authorNames} is now available!";
        }

        // Отправляем SMS каждому подписчику
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