<?php
require("config/env.php");
 $val=sendSMS('+18777804236','Hello Zotec Soft ha!');
var_dump($val);
die();
$seo = array(
    'title' => 'Dashboard',
    'description' => $val,
    'keywords' => 'Admin Panel'
);
echo $twig->render('user/dashboard.twig', ['seo' => $seo]);