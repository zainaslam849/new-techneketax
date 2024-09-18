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

    //CHAT or MESSAGING
    "CAHT_WSS"=>'wss://dev.techneketax.com/ws/',
    "CHAT_PORT"=>"8005"
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
        @$allow=explode(',',@$userInfo['permissions']);
        $loginUserName=$userInfo[0]['fname'] .' '.$userInfo[0]['lname'];
        $loginUserEmail=$userInfo[0]['email'];
        $twig->addGlobal('loginId', $loginUserId);
        $twig->addGlobal('loginType', @$loginUserType);
        $twig->addGlobal('loginName', @$loginUserName);
        $twig->addGlobal('userEmail', @$userInfo[0]['email']);
        $twig->addGlobal('userPhone', @$userInfo[0]['phone']);
        $twig->addGlobal('userProfileImage', @$userInfo[0]['profile_image']);
    if ($loginUserType == "firm"){
        $white_labeling=@$userInfo[0]['white_labeling'];
        $twig->addGlobal('userCompanyImage', @$userInfo[0]['company_image']);
        $twig->addGlobal('userCompanyImageLight', @$userInfo[0]['company_image_light']);
        $twig->addGlobal('whitelabel', @$userInfo[0]['white_labeling']);
        $twig->addGlobal('company_name', @$userInfo[0]['company_name']);
        $plan_id = @$userInfo[0]['plan_id'];
        $plan_end_date = $userInfo[0]['plan_end_date'];
        $memberInfoss = $h->table('users')->select()->where('firm_id', '=', $loginUserId)->where('type', '=', 'member')->fetchAll();
        $twig->addGlobal('memberInfoss', @$memberInfoss);
        $Firm_StripeCredentials = $h->table('firm_stripe_keys')->select()->where('firm_id', '=', $loginUserId)->fetchAll();
        $Firm_Stripe_public_key=@$Firm_StripeCredentials[0]['public_key'];
        $Firm_Stripe_secret_key=@$Firm_StripeCredentials[0]['secret_key'];
    }else{
        $userInfoo = $h->table('users')->select()->where('id', '=', @$userInfo[0]['firm_id'])->fetchAll();
        $white_labeling=@$userInfoo[0]['white_labeling'];
        $twig->addGlobal('userCompanyImage', @$userInfoo[0]['company_image']);
        $twig->addGlobal('userCompanyImageLight', @$userInfoo[0]['company_image_light']);
        $twig->addGlobal('whitelabel', @$userInfoo[0]['white_labeling']);
        $plan_id = @$userInfoo[0]['plan_id'];
        $plan_end_date = @$userInfoo[0]['plan_end_date'];
        $Firm_StripeCredentials = $h->table('firm_stripe_keys')->select()->where('firm_id', '=', $userInfo[0]['firm_id'])->fetchAll();
        $Firm_Stripe_public_key=@$Firm_StripeCredentials[0]['public_key'];
        $Firm_Stripe_secret_key=@$Firm_StripeCredentials[0]['secret_key'];
    }
    $twig->addGlobal('Firm_Stripe_public_key', @$Firm_Stripe_public_key);
    $twig->addGlobal('Firm_Stripe_secret_key', @$Firm_Stripe_secret_key);
    $current_date = date('Y-m-d H:i:s');
    if(!empty($plan_id)){
        $planInfo = $h->table('plans')->select()->where('id', '=', $plan_id)->fetchAll();
            @$key_pointsArray = explode(',', $planInfo[0]['key_points']);
        $permissions = [];
            if (!empty($key_pointsArray)) {
                foreach ($key_pointsArray as $key_point_id) {
                    $permission = $h->table('permissions')->select()->where('id', '=', $key_point_id)->fetchAll();
                    if ($permission) {
                        $permissions[] = $permission;
                    }
                }
            }
        $permissionValues = array_map(function($permission) {
            return $permission[0]['value'];
        }, $permissions);

        $twig->addGlobal('permissionValues', @$permissionValues);
        $twig->addGlobal('CurrentDate', @$current_date);
        $twig->addGlobal('planId', @$plan_id);
        $twig->addGlobal('planEndDate', @$plan_end_date);
    }
    $twig->addFilter(new \Twig\TwigFilter('base64_encode', function ($string) {
        return base64_encode($string);
    }));
    $twig->addFilter(new \Twig\TwigFilter('ucwords', function ($string) {
        return ucwords($string);
    }));
    $twig->addGlobal('Stripe_public_key', 'pk_test_51OgnsKB8z2Dlcg3z0Qz8mYPgaXouytYsnflrzr3hgWNNu91PY8ApCB2A6ZTbR49TZ59ag5KuLfIVIlBo2aCqgoZ900owqKbZDQ');
    $Stripe_secret_key='sk_test_51OgnsKB8z2Dlcg3z6ZQl607w3HUhJ3SQu7FupPI2XWwTaBBLdVZpYA7fpzDQBd8n9jpa9DsBUUuYnKoT9CKRcwV700c0vbYFoi';
endif;
if (!empty($_SESSION['member_id'])){
    $twig->addGlobal('session_member_id', $_SESSION['member_id']);
    $permissionFirm = $h->table('users')->select()->where('id', '=', $loginUserId)->where('associates_id', '=', $_SESSION['member_id'])->fetchAll();
    if(!empty($permissionFirm[0]['permissions'])){
        $permissionFirmValues = explode(',', $permissionFirm[0]['permissions']);
    }else{
        $permissionFirmValues = [];
    }

    $twig->addGlobal('firmPermissionValues', @$permissionFirmValues);

}

//TWILIO SMS API
$twilio_number= '+17609040397';
$account_sid= 'ACd5c325433100e2baf794a9c92caeeb55';
$auth_token='aa8115bbf571ff82e60ae6ef26bdf4fe';
$Admin_StripeCredentials = $h->table('admin_stripe_keys')->select()->where('id', '=', '1')->fetchAll();
$Admin_Stripe_public_key=$Admin_StripeCredentials[0]['public_key'];
$Admin_Stripe_secret_key=$Admin_StripeCredentials[0]['secret_key'];
$twig->addGlobal('Admin_Stripe_public_key', @$Admin_Stripe_public_key);


