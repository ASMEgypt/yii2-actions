<?php
$params = [];

return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'vendorPath' => realpath(__DIR__ .'/../../../../'),
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
    ],
    'bootstrap' => ['log'],
    'modules' => [],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'baseUrl' => ''
        ],
        'assetManager' => [
            'basePath' => __DIR__ . '/../../tests/assets/',
            'bundles' => [
                \execut\actions\widgets\MassHandlerButtonAsset::class => [
                    'sourcePath' => null,
                    'js' => [],
                    'css' => [],
                    'depends' => [],
                ],
                'yii\\web\\JqueryAsset' => [
                    'sourcePath' => null,
                    'js' => [],
                    'css' => [],
                    'depends' => [],
                ],
                'yii\\bootstrap\\BootstrapAsset' => [
                    'sourcePath' => null,
                    'js' => [],
                    'css' => [],
                    'depends' => [],
                ],
                'kartik\\daterange\\DateRangePickerAsset' => [
                    'depends' => [],
                    'js' => [],
                    'sourcePath' => null,
                    'basePath' => null,
                    'baseUrl' => null,
                    'css' => [],
                    'jsOptions' => [],
                    'cssOptions' => [],
                    'publishOptions' => [],
                ],
            ],
        ],
//        'cache' => [
//            'class' => 'yii\caching\FileCache',
//        ],
        'i18n' => [
//            'translations' => [
//                'app'=>[
//                    'class' => 'yii\i18n\PhpMessageSource',
//                    'basePath' => "@app/messages",
//                    'sourceLanguage' => 'en_US',
//                    'fileMap' => [
//                        'app'=>'app.php',
//                    ]
//                ],
//                'common.modules.catalog.models' => [
//                    'class' => 'yii\i18n\PhpMessageSource',
//                    'basePath' => '@app/modules/catalog/messages',
//                    'sourceLanguage' => 'en',
//                    'fileMap' => [
//                        'common.modules.catalog.models' => 'models.php',
//                    ],
//                ],
//            ],
        ],
    ],
    'params' => $params,
];
