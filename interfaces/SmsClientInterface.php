<?php

namespace app\interfaces;

use app\exceptions\SmsClientException;

interface SmsClientInterface
{
    /**
     * @throws SmsClientException
     */
    public function sendSms(string $phone, string $message): void;
}
