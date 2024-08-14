<?php
require("config/env.php");
if($route == '/admin/login'):
        $seo = array(
            'title' => 'Admin Login',
            'description' => '',
            'keywords' => 'login'
        );
        echo $twig->render('admin/auth/login.twig', ['seo'=>$seo, 'csrf'=>set_csrf()]);
endif;

if($route == '/forget-password'):
    $seo = array(
        'title' => 'Forgot Your Password? Reset It Here',
        'description' => 'Reset your Chaisbek Real Estate account password securely. Follow simple steps to regain access to your account and continue exploring our extensive property listings.',
        'keywords' => 'forgot password, reset password, Chaisbek Real Estate, password recovery'
    );
    echo $twig->render('public/auth/forget_password.twig', ['seo'=>$seo]);
endif;