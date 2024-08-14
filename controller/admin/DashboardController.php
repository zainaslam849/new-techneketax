<?php
require("config/env.php");
$seo = array(
    'title' => 'Dashboard',
    'description' => 'CRM',
    'keywords' => 'Admin Panel'
);
echo $twig->render('admin/dashboard.twig', ['seo'=>$seo]);