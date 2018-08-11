<?php echo "<?php\n"?>
return [
    'id' => 'app-<?=$generator->appname?>-tests',
    'components' => [
        'assetManager' => [
            'basePath' => __DIR__ . '/../web/assets',
        ],
        'urlManager' => [
            'showScriptName' => true,
        ],
    ],
];
