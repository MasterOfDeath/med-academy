<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'queue'],
    'controllerNamespace' => 'app\commands',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
        '@tests' => '@app/tests',
    ],
    'components' => [
        'cache' => [
            'class' => \yii\redis\Cache::class,
            'redis' => 'redis', 
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info', 'error', 'warning'],
                    'categories' => ['sms'],
                    'logFile' => 'php://stdout',
                    'exportInterval' => 1,
                    'logVars' => [],
                    'prefix' => function ($message) {
                        return '';
                    },
                ],
            ],
        ],
        'db' => $db,
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
        ],
        'queue' => [
            'class' => \yii\queue\redis\Queue::class,
            'redis' => 'redis',
            'channel' => 'default',
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => $params['redis_hostname'],
            'port' => $params['redis_port'],
            'database' => 0,
        ],
    ],
    'container' => [
        'definitions' => [
            'app\interfaces\SmsClientInterface' => [
                'class' => 'app\components\SmsClient',
                'apiKey' => $params['smspilot_api_key'] ?? '',
            ],
            \Psr\Http\Client\ClientInterface::class => [
                'class' => \GuzzleHttp\Client::class,
            ],
            \yii\queue\Queue::class => function () {
                return \Yii::$app->get('queue');
            },
        ],
    ],
    'params' => $params,
    'controllerMap' => [
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'db' => $db,
        ],
        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
    ],
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
    // configuration adjustments for 'dev' environment
    // requires version `2.1.21` of yii2-debug module
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
