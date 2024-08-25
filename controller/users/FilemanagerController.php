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

if($route == '/user/files'){

    if(!empty($userInfo[0]['company_image'])){
        $company_logo=$env['APP_URL']."uploads/profile/".$userInfo[0]['company_image'];
    }else{
        $company_logo="https://avatar.iran.liara.run/username?username=".$userInfo[0]['company_name'];
    }
    $company_name=$userInfo[0]['company_name'];
    echo $twig->render('user/document_hub/files.twig',['company_logo'=>$company_logo, 'company_name'=>$company_name]);

}

?>