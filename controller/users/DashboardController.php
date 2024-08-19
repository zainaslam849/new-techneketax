<?php
require("config/env.php");
// $val=sendSMS('+18777804236','Hello Zotec Soft ha!');
//var_dump($val);
//die();
if ($loginUserType == "firm") {
    $seo = array(
        'title' => 'Dashboard',
        'description' => $val,
        'keywords' => 'Admin Panel'
    );
    echo $twig->render('user/dashboard.twig', ['seo' => $seo]);
}else{
$seo = array(
    'title' => 'Dashboard',
    'description' => $val,
    'keywords' => 'Admin Panel'
);
echo $twig->render('user/client_dashboard.twig', ['seo' => $seo]);
}