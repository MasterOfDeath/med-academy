<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'user.passwordResetTokenExpire' => 3600,
    'smspilot_api_key' => $_ENV['SMS_PILOT_API_KEY'] ?? null,
    'redis_hostname' => $_ENV['REDIS_HOST'] ?? 'redis',
    'redis_port' => $_ENV['REDIS_PORT'] ?? 6379,
    'cookie_validation_key' => $_ENV['COOKIE_VALIDATION_KEY'],
];
