<?php

namespace app\components;

use app\exceptions\SmsClientException;
use app\interfaces\SmsClientInterface;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;

class SmsClient extends BaseObject implements SmsClientInterface
{
    public string $apiKey;

    public function init()
    {
        parent::init();

        if (empty($this->apiKey)) {
            throw new InvalidConfigException('apiKey is required');
        }
    }

    public function sendSms(string $phone, string $message): void
    {
        // Используем эмулятор API smspilot.ru для отправки SMS
        $data = [
            'api_key' => $this->apiKey,
            'send' => [
                [
                    'phone' => $phone,
                    'text' => $message,
                ],
            ],
        ];

        // Для тестирования будем просто логировать отправку
        Yii::info("Sending SMS to {$phone}: {$message}", 'sms');
        
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
            Yii::error("SMS sending error: {$error}", 'sms');

            throw new SmsClientException("SMS sending error: {$error}");
        } elseif ($httpCode !== 200) {
            Yii::error("SMS sending failed with HTTP code: {$httpCode}, response: {$response}", 'sms');

            throw new SmsClientException("SMS sending failed with HTTP code: {$httpCode}, response: {$response}");
        } else {
            Yii::info("SMS successfully sent to {$phone}", 'sms');
        }
    }
}
