<?php
/////////////////////////
///////ZOTEC FRAMEWORK
//////admin@zotecsoft.com
////////////////////////
///

require_once("./config/main.php");
$env=array(
    "ENV_TYPE"=>"local", // local or production
    "SITE_NAME"=>"CRM",
    "DESCRIPTION"=>"description",
    "KEYWORDS"=>"keywords",
    "APP_URL"=> "https://techneketax.local/",
    "ADMIN_EMAIL"=> "info@crm.com",
    "SENDER_EMAIL"=> "info@crm.com",
    "TIME_ZONE"=> "Asia/Karachi",

    //PRODUCTION DATABASE CREDENTIALS
    "DATABASE_HOST"=>"localhost",
    "DATABASE_NAME"=>"dev_tecgneketax",
    "DATABASE_USERNAME"=>"dev_tecgneketax",
    "DATABASE_PASSWORD"=>"7L36?3mue",


    //LOCAL DATABASE CREDENTIALS
    "LC_DATABASE_HOST"=>"localhost",
    "LC_DATABASE_NAME"=>"techneketax",
    "LC_DATABASE_USERNAME"=>"root",
    "LC_DATABASE_PASSWORD"=>"root",

    //SMTP CREDENTIALS
    "SMTP_HOST"=>"smtp.mailgun.org",
    "SMTP_USERNAME"=>"no-reply@mg.techneketax.com",
    "SMTP_PASSWORD"=>"64fe7fa24fa4ce87d40483bd9eae2d3b-a26b1841-7a394aef",
    "SMTP_ENC"=>"tls",
    "SMTP_PORT"=>"587",
);
$assets_url="https://crm-cu.local";

use Cycle\Database;
use Cycle\Database\Config;
//use Cycle\Database\Query\SelectQuery;
$dbal = new Database\DatabaseManager(
    new Config\DatabaseConfig([
        'default' => 'default',
        'databases' => [
            'default' => ['connection' => 'mysql']
        ],
        'connections' => [
            'mysql' => new Config\MySQLDriverConfig(
                connection: new Config\MySQL\TcpConnectionConfig(
                    database: ($env['ENV_TYPE'] == 'production') ? $env['DATABASE_NAME'] : $env['LC_DATABASE_NAME'] ,
                    host: ($env['ENV_TYPE'] == 'production') ? $env['DATABASE_HOST'] : $env['LC_DATABASE_HOST'] ,
                    port: 3306,
                    user: ($env['ENV_TYPE'] == 'production') ? $env['DATABASE_USERNAME'] : $env['LC_DATABASE_USERNAME'] ,
                    password: ($env['ENV_TYPE'] == 'production') ? $env['DATABASE_PASSWORD'] : $env['LC_DATABASE_PASSWORD'] ,
                ),
                queryCache: true
            ),

        ]
    ])
);
$h=$dbal->database('default');
$GLOBALS['h']=$h;
//SESSION
ob_start();
date_default_timezone_set($env['TIME_ZONE']);
//VIEWS LOADER BY TWIG
$loader = new \Twig\Loader\FilesystemLoader('views/');
if($env['ENV_TYPE'] == 'production'){
    $twig = new \Twig\Environment($loader, [
        'cache' => 'cache',
    ]);
}else{
    $twig = new \Twig\Environment($loader);
}
$twig->addGlobal('env', $env);
$twig->addGlobal('route', @$route);
$twig->addGlobal('currentYear', date("Y"));

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
$mail = new PHPMailer(true);
//SMTP CREDENTIALS

$mail_settings = $h->table('smtp_setting')->select()->where('id', '=', 1)->fetchAll();

$mail->isSMTP();                                            //Send using SMTP
$mail->Host       = $mail_settings[0]['mail_host'];                     //Set the SMTP server to send through
$mail->SMTPAuth   = true;                                   //Enable SMTP authentication
$mail->Username   = $mail_settings[0]['mail_username'];                     //SMTP username
$mail->Password   = $mail_settings[0]['mail_password'];                               //SMTP password
$mail->SMTPSecure = $mail_settings[0]['mail_enc'];            //Enable implicit TLS encryption
$mail->Port       = $mail_settings[0]['mail_port'];
//END OF VIEWS LOADER BY TWIG

$settings = $h->table('site_setting')->select()->where('id', '=', 1)->fetchAll();
$twig->addGlobal('logo', $settings[0]['logo']);
//DEFINE YOUR GLOBAL STUFF HERE
if(isset($_SESSION['users']) && !empty($_SESSION['users'])):
    $loginUserId=$_SESSION['users']['id'];
    $loginUserType=$_SESSION['users']['type'];
    $userInfo = $h->table('users')->select()->where('id', '=', $loginUserId)->fetchAll();
    $loginUserName=$userInfo[0]['fname'] .' '.$userInfo[0]['lname'];
    $twig->addGlobal('loginId', $loginUserId);
    $twig->addGlobal('loginType', @$loginUserType);
    $twig->addGlobal('loginName', @$loginUserName);
    $twig->addGlobal('userEmail', @$userInfo[0]['email']);
    $twig->addGlobal('userPhone', @$userInfo[0]['phone']);
    $twig->addGlobal('userProfileImage', @$userInfo[0]['profile_image']);
    if ($loginUserType == "firm"){
        $twig->addGlobal('userCompanyImage', @$userInfo[0]['company_image']);
        $twig->addGlobal('whitelabel', @$userInfo[0]['white_labeling']);
    }else{
        $userInfo = $h->table('users')->select()->where('id', '=', @$userInfo[0]['firm_id'])->fetchAll();
        $twig->addGlobal('userCompanyImage', @$userInfo[0]['company_image']);
        $twig->addGlobal('whitelabel', @$userInfo[0]['white_labeling']);
    }

    $twig->addGlobal('Stripe_public_key', 'pk_test_51OgnsKB8z2Dlcg3z0Qz8mYPgaXouytYsnflrzr3hgWNNu91PY8ApCB2A6ZTbR49TZ59ag5KuLfIVIlBo2aCqgoZ900owqKbZDQ');
    $Stripe_secret_key='sk_test_51OgnsKB8z2Dlcg3z6ZQl607w3HUhJ3SQu7FupPI2XWwTaBBLdVZpYA7fpzDQBd8n9jpa9DsBUUuYnKoT9CKRcwV700c0vbYFoi';
endif;
//TWILIO SMS API
$twilio_number= '+17609040397';
$account_sid= 'ACd5c325433100e2baf794a9c92caeeb55';
$auth_token='aa8115bbf571ff82e60ae6ef26bdf4fe';


