<?php
require("config/env.php");
if($route == '/user/plans'):
$seo = array(
    'title' => 'Plans',
    'description' => 'CRM',
    'keywords' => 'Admin Panel'
);
    $plans = $h->table('plans')->select()->where('status','=','active')->fetchAll();

echo $twig->render('user/plans/index.twig', ['seo' => $seo,'plans' => $plans]);
endif;
