<?php
require("config/env.php");
$seo = array(
    'title' => '404 Page Not Found',
    'description' => 'Explore a vast selection of homes, properties, and condos for sale with Chaisbek Real Estate. Whether you are buying or selling, we offer expert guidance and personalized service to meet your real estate needs. Invest in your future with Chaisbek today!',
    'keywords' => 'real estate, properties for sale, homes for sale, condos for sale, buy properties, sell properties, investment properties, residential real estate, commercial real estate'
);
echo $twig->render('404.twig', ['seo'=>$seo]);