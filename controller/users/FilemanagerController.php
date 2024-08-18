<?php


require("config/env.php");

if($route == '/file'){
    require 'file-manager/php/autoload.php';
    $opts = [
        'roots' => [
            [
                'driver' => 'LocalFileSystem',
                'path' => 'uploads/file-manager/',
                'URL' => dirname($_SERVER['PHP_SELF']) . '/uploads/file-manager/',
                'accessControl' => 'access',
            ],
        ],
    ];

    $connector = new elFinderConnector(new elFinder($opts));
    $connector->run();
}

if($route == '/user/file'){
    echo $twig->render('user/document_hub/filemanager.twig');
}

?>