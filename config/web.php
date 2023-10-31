<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],

    'components' => [
        'user' => [
            'identityClass' => 'yii\web\User', // User must implement the IdentityInterface
            'enableAutoLogin' => true,

        ],

        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'c6-4hajpzzcAxnszAfSD8iqQrPu8CuqW',
        ],
            'cache' => [
               // 'class' => 'yii\redis\Cache',
                'class' => 'yii\caching\MemCache',
                'useMemcached'=> false ,
                'servers' => [
                            [
                                'host' => '127.0.0.1',
                                'port' => 11211,
                                'weight' => 60,
                            ],

                        ],
                    ],


        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.yandex.ru',
                'password' => getenv('APP_MAILER_PASSWORD'),
                'port' => 465,
                'encryption' => 'ssl',
            ],
            'enableSwiftMailerLogging' =>false,
            'useFileTransport' => false,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                [
                    'pattern' => 'geo-ip/country/<ip>',
                    'route' => 'geo-ip/country',
                    'defaults' => [ 'ip' => null],
                ],
                [
                    'pattern' => 'geo-ip/network/<ip>',
                    'route' => 'geo-ip/network',
                    'defaults' => [ 'ip' => null],
                ],

            ]


        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
