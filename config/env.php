<?php
/////////////////////////
// ZOTEC FRAMEWORK
// admin@zotecsoft.com
/////////////////////////

// Load main configuration file
require_once("./config/main.php");

// Environment configuration
$env = array(
    "ENV_TYPE" => "local", // local or production
    "SITE_NAME" => "CRM",
    "DESCRIPTION" => "description",
    "KEYWORDS" => "keywords",
    "APP_URL" => "https://techneketax.local/",
    "ADMIN_EMAIL" => "info@crm.com",
    "SENDER_EMAIL" => "info@crm.com",
    "TIME_ZONE" => "Asia/Karachi",

    // Production database credentials
    "DATABASE_HOST" => "localhost",
    "DATABASE_NAME" => "dev_tecgneketax",
    "DATABASE_USERNAME" => "dev_tecgneketax",
    "DATABASE_PASSWORD" => "7L36?3mue",

    // Local database credentials
    "LC_DATABASE_HOST" => "localhost",
    "LC_DATABASE_NAME" => "techneketax",
    "LC_DATABASE_USERNAME" => "root",
    "LC_DATABASE_PASSWORD" => "root",

    // SMTP credentials
    "SMTP_HOST" => "smtp.mailgun.org",
    "SMTP_USERNAME" => "no-reply@mg.techneketax.com",
    "SMTP_PASSWORD" => "64fe7fa24fa4ce87d40483bd9eae2d3b-a26b1841-7a394aef",
    "SMTP_ENC" => "tls",
    "SMTP_PORT" => "587",

    // Chat or messaging configuration
    "CAHT_WSS" => 'wss://dev.techneketax.com/ws/',
    "CHAT_PORT" => "8005"
);

// Assets URL
$assets_url = "https://crm-cu.local";

// CycleORM Database Manager Setup
use Cycle\Database;
use Cycle\Database\Config;

$dbal = new Database\DatabaseManager(
    new Config\DatabaseConfig([
        'default' => 'default',
        'databases' => [
            'default' => ['connection' => 'mysql']
        ],
        'connections' => [
            'mysql' => new Config\MySQLDriverConfig(
                connection: new Config\MySQL\TcpConnectionConfig(
                    database: ($env['ENV_TYPE'] == 'production') ? $env['DATABASE_NAME'] : $env['LC_DATABASE_NAME'],
                    host: ($env['ENV_TYPE'] == 'production') ? $env['DATABASE_HOST'] : $env['LC_DATABASE_HOST'],
                    port: 3306,
                    user: ($env['ENV_TYPE'] == 'production') ? $env['DATABASE_USERNAME'] : $env['LC_DATABASE_USERNAME'],
                    password: ($env['ENV_TYPE'] == 'production') ? $env['DATABASE_PASSWORD'] : $env['LC_DATABASE_PASSWORD']
                ),
                queryCache: true
            ),
        ]
    ])
);

// Database connection instance
$h = $dbal->database('default');
$GLOBALS['h'] = $h;

// Start session handling and set default timezone
ob_start();
date_default_timezone_set($env['TIME_ZONE']);

// Twig template engine setup
$loader = new \Twig\Loader\FilesystemLoader('views/');
if ($env['ENV_TYPE'] == 'production') {
    $twig = new \Twig\Environment($loader, [
        'cache' => 'cache',
    ]);
} else {
    $twig = new \Twig\Environment($loader);
}

// Add global variables to Twig
$twig->addGlobal('env', $env);
$twig->addGlobal('route', @$route);
$twig->addGlobal('currentYear', date("Y"));

// PHPMailer setup
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
$mail = new PHPMailer(true);

// Fetch SMTP settings from database
$mail_settings = $h->table('smtp_setting')->select()->where('id', '=', 1)->fetchAll();

$mail->isSMTP();                                          // Use SMTP
$mail->Host = $mail_settings[0]['mail_host'];             // SMTP server
$mail->SMTPAuth = true;                                   // Enable SMTP authentication
$mail->Username = $mail_settings[0]['mail_username'];     // SMTP username
$mail->Password = $mail_settings[0]['mail_password'];     // SMTP password
$mail->SMTPSecure = $mail_settings[0]['mail_enc'];        // TLS encryption
$mail->Port = $mail_settings[0]['mail_port'];             // SMTP port

// Domain name setup
@$domainName = 'techneke.com';
$settings = $h->table('site_setting')->select()->where('id', '=', 1)->fetchAll();
$twig->addGlobal('logo', $settings[0]['logo']);

// Session user handling
if (isset($_SESSION['users']) && !empty($_SESSION['users'])):
    // Fetch logged-in user details
    $loginUserId = $_SESSION['users']['id'];
    $loginUserType = $_SESSION['users']['type'];
    $loginUserGeneratedEmail = @$_SESSION['users']['generated_email'] . '@' . @$domainName;

    $twig->addGlobal('loginUserGeneratedEmail', $loginUserGeneratedEmail);

    // Fetch user information
    $userInfo = $h->table('users')->select()->where('id', '=', $loginUserId)->fetchAll();
    @$allow = explode(',', @$userInfo['permissions']);
    $loginUserName = $userInfo[0]['fname'] . ' ' . $userInfo[0]['lname'];
    $loginUserEmail = $userInfo[0]['email'];

    // Add user information to Twig
    $twig->addGlobal('loginId', $loginUserId);
    $twig->addGlobal('loginType', @$loginUserType);
    $twig->addGlobal('loginName', @$loginUserName);
    $twig->addGlobal('userEmail', @$userInfo[0]['email']);
    $twig->addGlobal('userPhone', @$userInfo[0]['phone']);
    $twig->addGlobal('userProfileImage', @$userInfo[0]['profile_image']);

    // Add firm-specific or user-specific logic here...

endif;

// Twilio SMS API configuration
$twilio_number = '+14322872361';
$account_sid = 'AC4502a0299ad90a09f253b48f3b871d43';
$auth_token = '2e1cd0fbf62aa1f5e63928268fe03bd8';

// Stripe API keys setup
$Admin_StripeCredentials = $h->table('admin_stripe_keys')->select()->where('id', '=', '1')->fetchAll();
$Admin_Stripe_public_key = $Admin_StripeCredentials[0]['public_key'];
$Admin_Stripe_secret_key = $Admin_StripeCredentials[0]['secret_key'];

$twig->addGlobal('Admin_Stripe_public_key', @$Admin_Stripe_public_key);
// End of global settings
?>
