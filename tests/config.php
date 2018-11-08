<?php

return [
    'id' => 'devzyj/yii2-attribute-cache-behavior',
    'class' => yii\console\Application::className(),
    'language' => 'en-US',
    'basePath' => Yii::getAlias('@tests'),
    'runtimePath' => Yii::getAlias('@tests/_output'),
    'bootstrap' => ['log'],
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;port=3306;dbname=testdb',
            'username' => 'root',
            'password' => '123456',
            'charset' => 'utf8',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
            'keyPrefix' => 'DevzyjYii2CacheBehavior',
        ],
        'log' => [
            'traceLevel' => 3,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                ],
            ],
        ],
    ],
    'params' => [],
];
