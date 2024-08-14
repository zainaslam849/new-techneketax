<?php
require("config/env.php");
if($route == '/terms') {
    $seo = array(
        'title' => 'Terms and Conditions | Chaisbek Real Estate',
        'description' => 'Explore a vast selection of homes, properties, and condos for sale with Chaisbek Real Estate. Whether you are buying or selling, we offer expert guidance and personalized service to meet your real estate needs. Invest in your future with Chaisbek today!',
        'keywords' => 'real estate, properties for sale, homes for sale, condos for sale, buy properties, sell properties, investment properties, residential real estate, commercial real estate'
    );
    echo $twig->render('public/terms.twig', ['seo'=>$seo]);
}

if($route == '/privacy-policy') {
    $seo = array(
        'title' => 'Privacy Policy | Chaisbek Real Estate',
        'description' => 'Explore a vast selection of homes, properties, and condos for sale with Chaisbek Real Estate. Whether you are buying or selling, we offer expert guidance and personalized service to meet your real estate needs. Invest in your future with Chaisbek today!',
        'keywords' => 'real estate, properties for sale, homes for sale, condos for sale, buy properties, sell properties, investment properties, residential real estate, commercial real estate'
    );
    echo $twig->render('public/privacy.twig', ['seo'=>$seo]);
}

if($route == '/cookies-policy') {
    $seo = array(
        'title' => 'Cookies Policy | Chaisbek Real Estate',
        'description' => 'Explore a vast selection of homes, properties, and condos for sale with Chaisbek Real Estate. Whether you are buying or selling, we offer expert guidance and personalized service to meet your real estate needs. Invest in your future with Chaisbek today!',
        'keywords' => 'real estate, properties for sale, homes for sale, condos for sale, buy properties, sell properties, investment properties, residential real estate, commercial real estate'
    );
    echo $twig->render('public/cookies-policy.twig', ['seo'=>$seo]);
}
