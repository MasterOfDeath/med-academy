<?php

namespace app\factories;

use app\jobs\SendSmsJob;

class SendSmsJobFactory
{
    public function create(array $config = []): SendSmsJob
    {
        return new SendSmsJob($config);
    }
}
