<?php

namespace app\interfaces;

interface SmsClientInterface
{
    /**
     * @throws SmsClientException
     */
    public function sendSms(string $phone, string $message): void;
}
