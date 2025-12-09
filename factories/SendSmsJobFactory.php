<?php

namespace app\factories;

use app\interfaces\SmsClientInterface;
use app\jobs\SendSmsJob;

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
