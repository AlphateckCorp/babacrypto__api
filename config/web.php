<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'KG4d3CLTpxRyGLtOWBTBimMob-QpWTsk',
        ],
        
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
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
            'class' => 'yii\web\UrlManager',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
             'coinlist/index' => 'coins-list/',
             'coinlist' => 'coins-list/',
             'storecoinlist' => 'coins-list/store-coin',
            //  'storecoin' => 'site/store-coins',
            //  'coinmarket' => 'coins-list/fetch',
            //  'coindtlsd' => 'site/fetchs',
             'yours' => 'coins-list/your',
             'storeexchangelist' => 'coins-list/store-exchange-list',
             'exchangelist' => 'coins-list/exchange-list',


             'exchangecoin' => 'coins-list/exchange-coin-list',
             'exchangemarketlist' => 'coins-list/exchange-market-list',
             'onlymarket' => 'coins-list/only-market',
             'exchangeMarket' => 'coins-list/market',
            //  'tests' => 'tests/make',
            ],
            // 'rules' => [
            //     'class' => 'yii\rest\UrlRule', 
            //     'controller' => ['coinlist' => 'coins-list/']

            // ],
        ],
        
	// 'urlManager' => [
 //        'class' => 'yii\web\UrlManager',
 //        // Disable index.php
 //        'showScriptName' => false,
 //        // Disable r= routes
 //        'enablePrettyUrl' => true,
 //        'rules' => array(
 //                '<controller:\w+>/<id:\d+>' => '<controller>/view',
 //                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
 //                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
 //                'coinlist' => 'coin-list'
 //        ),
 //        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
