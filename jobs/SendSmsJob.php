<?php

namespace app\jobs;

use yii\base\BaseObject;
use yii\queue\JobInterface;
use app\models\Subscription;
use app\models\Book;
use app\models\Author;

class SendSmsJob extends BaseObject implements JobInterface
{
    public $bookId;
    public $message;

    public function execute($queue)
    {
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
            $this->message = "Новая книга '{$book->title}' автора(ов) {$authorNames} уже доступна!";
        }

        // Отправляем SMS каждому подписчику
        foreach ($subscriptions as $subscription) {
            $this->sendSms($subscription->phone, $this->message);
        }
    }

    private function sendSms($phone, $message)
    {
        // Используем эмулятор API smspilot.ru для отправки SMS
        // В реальном приложении здесь будет реальный API-вызов
        $apiKey = 'A1B2C3D4E5F6A1B2C3D4E5F6'; // тестовый API-ключ (заменить на реальный)
        
        $data = [
            'api_key' => $apiKey,
            'send' => [
                [
                    'phone' => $phone,
                    'text' => $message
                ]
            ]
        ];

        // Для тестирования будем просто логировать отправку
        \Yii::info("Sending SMS to {$phone}: {$message}", 'sms');
        
        // В реальном приложении использовать:
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://smspilot.ru/api.php');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
        ]);
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            \Yii::error("SMS sending error: {$error}", 'sms');
        } elseif ($httpCode !== 200) {
            \Yii::error("SMS sending failed with HTTP code: {$httpCode}, response: {$response}", 'sms');
        } else {
            \Yii::info("SMS successfully sent to {$phone}", 'sms');
        }
    }
}