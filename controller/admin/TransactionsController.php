<?php
require("config/env.php");
$seo = array(
    'title' => 'Transactions',
    'description' => 'CRM',
    'keywords' => 'Admin Panel'
);
echo $twig->render('admin/transactions/index.twig', ['seo'=>$seo]);