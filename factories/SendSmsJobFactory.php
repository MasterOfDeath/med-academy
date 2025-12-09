<?php

namespace app\factories;

use app\jobs\SendSmsJob;
use app\interfaces\SmsClientInterface;

class SendSmsJobFactory
{
    private SmsClientInterface $smsClient;

    public function __construct(SmsClientInterface $smsClient)
    {
        $this->smsClient = $smsClient;
    }

    public function create(array $config = []): SendSmsJob
    {
        $job = new SendSmsJob($config);
        $job->setSmsClient($this->smsClient);
        return $job;
    }
}