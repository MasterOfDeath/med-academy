<?php

namespace app\components;

use app\exceptions\SmsClientException;
use app\interfaces\SmsClientInterface;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Client\ClientInterface;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;

class SmsClient extends BaseObject implements SmsClientInterface
{
    public string $apiKey;

    public function __construct(private ClientInterface $httpClient, $config = [])
    {
        parent::__construct($config);
    }

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

        try {
            $request = new \GuzzleHttp\Psr7\Request(
                'POST',
                'https://smspilot.ru/api.php',
                [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                http_build_query($data)
            );

            $response = $this->httpClient->sendRequest($request);

            $httpCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();

            if ($httpCode !== 200) {
                Yii::error("SMS sending failed with HTTP code: {$httpCode}, response: {$responseBody}", 'sms');

                throw new SmsClientException("SMS sending failed with HTTP code: {$httpCode}, response: {$responseBody}");
            } else {
                Yii::info("SMS successfully sent to {$phone}", 'sms');
            }
        } catch (RequestException $e) {
            $errorMessage = $e->getMessage();
            Yii::error("SMS sending error: {$errorMessage}", 'sms');

            throw new SmsClientException("SMS sending error: {$errorMessage}", 0, $e);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            Yii::error("SMS sending error: {$errorMessage}", 'sms');

            throw new SmsClientException("SMS sending error: {$errorMessage}", 0, $e);
        }
    }
}
