<?php
require("config/env.php");
$seo = array(
    'title' => 'Dashboard',
    'description' => 'CRM',
    'keywords' => 'Admin Panel'
);
echo $twig->render('user/dashboard.twig', ['seo' => $seo]);