<?php
require("config/env.php");
header('Location: /login');
if($route='home'){
$seo = array(
    'title' => 'Chaisbek Real Estate | Buy, Sell, and Invest in Homes, Properties, and Condos',
    'description' => 'Explore a vast selection of homes, properties, and condos for sale with Chaisbek Real Estate. Whether you are buying or selling, we offer expert guidance and personalized service to meet your real estate needs. Invest in your future with Chaisbek today!',
    'keywords' => 'real estate, properties for sale, homes for sale, condos for sale, buy properties, sell properties, investment properties, residential real estate, commercial real estate'
);

echo $twig->render('public/home.twig', ['seo'=>$seo,'csrf'=>set_csrf()]);
}
if($route='users'){
    $seo = array(
        'title' => 'Chaisbek Real Estate | Buy, Sell, and Invest in Homes, Properties, and Condos',
        'description' => 'Explore a vast selection of homes, properties, and condos for sale with Chaisbek Real Estate. Whether you are buying or selling, we offer expert guidance and personalized service to meet your real estate needs. Invest in your future with Chaisbek today!',
        'keywords' => 'real estate, properties for sale, homes for sale, condos for sale, buy properties, sell properties, investment properties, residential real estate, commercial real estate'
    );

    echo $twig->render('public/home.twig', ['seo'=>$seo,'csrf'=>set_csrf()]);

}