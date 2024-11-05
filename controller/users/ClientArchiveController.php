<?php
require("config/env.php");
if($route == '/user/clients/archive/all'):
    $seo = array(
        'title' => 'Clients Archive',
        'description' => 'CRM',
        'keywords' => 'Admin Panel'
    );
    echo $twig->render('user/client_archive/all.twig', ['seo' => $seo, 'FirmId' => $loginUserId]);
endif;
if($route == '/user/clients/archive/assigned'):
    $seo = array(
        'title' => 'Clients Archive',
        'description' => 'CRM',
        'keywords' => 'Admin Panel'
    );
    echo $twig->render('user/client_archive/assigned.twig', ['seo' => $seo, 'FirmId' => $loginUserId]);
endif;
if($route == '/user/clients/archive/unassigned'):
    $seo = array(
        'title' => 'Clients Archive',
        'description' => 'CRM',
        'keywords' => 'Admin Panel'
    );
    echo $twig->render('user/client_archive/unassigned.twig', ['seo' => $seo, 'FirmId' => $loginUserId]);
endif;
if($route == '/user/clients/archive/inprogress'):
    $seo = array(
        'title' => 'Clients Archive',
        'description' => 'CRM',
        'keywords' => 'Admin Panel'
    );
    echo $twig->render('user/client_archive/inprogress.twig', ['seo' => $seo, 'FirmId' => $loginUserId]);
endif;
if($route == '/user/clients/archive/completed'):
    $seo = array(
        'title' => 'Clients Archive',
        'description' => 'CRM',
        'keywords' => 'Admin Panel'
    );
    echo $twig->render('user/client_archive/completed.twig', ['seo' => $seo, 'FirmId' => $loginUserId]);
endif;