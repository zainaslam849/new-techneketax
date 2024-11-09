<?php
require("config/env.php");
$email_config = include('config/email_config.php');
$social_login_keysData = $h->table('social_login_keys')->select()->where('id', '=', 1)->fetchAll();
require_once 'vendor/autoload.php';
use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use League\OAuth2\Client\Provider\GenericProvider;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
$facebook = new Facebook([
    'app_id' => $social_login_keysData[0]['facebook_app_id'],
    'app_secret' => $social_login_keysData[0]['facebook_app_secret'],
    'default_graph_version' => 'v21.0',
]);
$helper = $facebook->getRedirectLoginHelper();
$permissions = ['email'];
if ($route == '/login/facebook') {
    $loginUrl = $helper->getLoginUrl($env['APP_URL'] . 'login/facebook/callback', $permissions);
    header('Location: ' . $loginUrl);
    exit;
}
if ($route == '/login/facebook/callback') {
    try {
        $accessToken = $helper->getAccessToken();
        if (!$accessToken) {
            throw new Exception('Failed to get access token');
        }

        $response = $facebook->get('/me?fields=id,name,email,first_name,last_name,picture', $accessToken);
        $user = $response->getGraphUser();
        $email = $user->getEmail();
        $fname = $user->getFirstName();
        $lname = $user->getLastName();
        $profile_image = $user->getPicture()->getUrl();
        $phone = ''; // Phone is not provided by Facebook by default

        $dbUser = $h->table('users')->select()->where('email', '=', $email)->fetchAll();

        if ($dbUser) {
            $_SESSION['users'] = $dbUser;
        } else {
            $generatedemail = generateRandomEmail($domainName);
            $generatedemail = strtolower($fname . $lname . $generatedemail);
            $password_email = random_strings(9);
            $createAccount = createEmailAccount($email_config, $generatedemail, $password_email);

            $insert = $h->insert('users')->values([
                'email' => $email,
                'fname' => $fname,
                'lname' => $lname,
                'profile_image' => $profile_image,
                'generated_email' => $generatedemail,
                'generated_email_pass' => $password_email,
                'signup_from' => 'facebook',
            ])->run();

            $newUser = $h->table('users')->select()->where('email', '=', $email)->fetchAll();
            $_SESSION['users'] = $newUser;
        }

        header('Location: /user/dashboard');
        exit;
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
        exit;
    }
}
if ($route == '/login/google') {
    $client = new Google_Client();
    $client->setClientId($social_login_keysData[0]['google_client_id']);
    $client->setClientSecret($social_login_keysData[0]['google_client_secret']);
    $client->setRedirectUri($env['APP_URL'].'login/google');
    $client->addScope('email');
    $client->addScope('profile');
    // Step 1: Redirect to Google’s OAuth 2.0 server.

    if (!isset($_GET['code'])) {
        $authUrl = $client->createAuthUrl();
        header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
        exit;
    } else {
        $client->authenticate($_GET['code']);
        $accessToken = $client->getAccessToken();
        $oauth2 = new Google_Service_Oauth2($client);
        $googleAccountInfo = $oauth2->userinfo->get();
        $email = $googleAccountInfo->email;
        $fname = $googleAccountInfo->givenName;
        $lname = $googleAccountInfo->familyName;
        $profile_image = $googleAccountInfo->picture;

        $dbUser = $h->table('users')->select()->where('email', '=', $email)->fetchAll();
        if ($dbUser) {
            $_SESSION['users'] = $dbUser;
        } else {
            $generatedemail = generateRandomEmail($domainName);
            $generatedemail = strtolower($fname . $lname . $generatedemail);
            $password_email = random_strings(9);
            $createAccount = createEmailAccount($email_config, $generatedemail, $password_email);
            $insert = $h->insert('users')->values([
                'email' => $email,
                'fname' => $fname,
                'lname' => $lname,
                'profile_image' => $profile_image,
                'generated_email' => $generatedemail,
                'generated_email_pass' => $password_email,
                'signup_from' => 'google',
            ])->run();
            $newUser = $h->table('users')->select()->where('email', '=', $email)->fetch();
            $_SESSION['users'] = $newUser;
        }

        header('Location: /user/dashboard');
        exit;
    }
}
if ($route == '/login/microsoft') {
    $provider = new GenericProvider([
        'clientId'                => $social_login_keysData[0]['microsoft_client_id'],
        'clientSecret'            => $social_login_keysData[0]['microsoft_client_secret'],
        'redirectUri'             => $env['APP_URL'].'login/microsoft/callback',
        'urlAuthorize'            => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
        'urlAccessToken'          => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
        'urlResourceOwnerDetails' => 'https://graph.microsoft.com/v1.0/me'
    ]);
    if (!isset($_GET['code'])) {
        $authorizationUrl = $provider->getAuthorizationUrl([
            'scope' => 'openid profile email User.Read offline_access'
        ]);
        $_SESSION['oauth2state'] = $provider->getState();
        header('Location: ' . $authorizationUrl);
        exit;
    } else {
        try {
            $accessToken = $provider->getAccessToken('authorization_code', ['code' => $_GET['code']]);
            $response = $provider->getAuthenticatedRequest('GET', 'https://graph.microsoft.com/v1.0/me', $accessToken);
            $user = json_decode($response->getBody()->getContents());
            $email = $user->mail ?: $user->userPrincipalName;
            $fname = $user->givenName;
            $lname = $user->surname;
            $profile_image = '';
            $phone = '';
            $dbUser = $h->table('users')->select()->where('email', '=', $email)->fetchAll();
            if ($dbUser) {
                $_SESSION['users'] = $dbUser;
            } else {
                $generatedemail = generateRandomEmail($domainName);
                $generatedemail = strtolower($fname . $lname . $generatedemail);
                $password_email = random_strings(9);
                $createAccount = createEmailAccount($email_config, $generatedemail, $password_email);
                $insert = $h->insert('users')->values([
                    'email' => $email,
                    'fname' => $fname,
                    'lname' => $lname,
                    'profile_image' => $profile_image,
                    'generated_email' => $generatedemail,
                    'generated_email_pass' => $password_email,
                    'signup_from' => 'microsoft',
                ])->run();
                $newUser = $h->table('users')->select()->where('email', '=', $email)->fetchAll();
                $_SESSION['users'] = $newUser;
            }
            header('Location: /user/dashboard');
            exit;
        } catch (Exception $e) {
            exit('Failed to get access token: ' . $e->getMessage());
        }
    }
}
if ($route == '/login/apple') {
    $clientID = $social_login_keysData[0]['apple_client_id'];
    $teamID = $social_login_keysData[0]['apple_team_id'];
    $keyID = $social_login_keysData[0]['apple_key_id'];
    $redirectUri = $env['APP_URL'].'login/apple/callback';
    $privateKey = file_get_contents('/path/to/your/apple_private_key.p8');

    // Step 1: Create JWT
    $header = [
        'alg' => 'ES256',
        'kid' => $keyID
    ];

    $claims = [
        'iss' => $teamID,
        'iat' => time(),
        'exp' => time() + 86400 * 180,
        'aud' => 'https://appleid.apple.com',
        'sub' => $clientID
    ];

    $jwt = JWT::encode($claims, $privateKey, 'ES256', $keyID);

    // Step 2: Redirect to Apple’s OAuth server
    $authorizationUrl = 'https://appleid.apple.com/auth/authorize?' . http_build_query([
            'response_type' => 'code',
            'client_id' => $clientID,
            'redirect_uri' => $redirectUri,
            'scope' => 'email name',
            'response_mode' => 'form_post'
        ]);

    header('Location: ' . $authorizationUrl);
    exit;
}
if ($route == '/login/apple/callback') {
    if (isset($_POST['code'])) {
        $code = $_POST['code'];

        $tokenUrl = 'https://appleid.apple.com/auth/token';
        $response = file_get_contents($tokenUrl, false, stream_context_create([
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query([
                    'client_id'     => $clientID,
                    'client_secret' => $jwt,
                    'code'          => $code,
                    'grant_type'    => 'authorization_code',
                    'redirect_uri'  => $redirectUri
                ]),
            ]
        ]));

        $data = json_decode($response, true);
        $accessToken = $data['access_token'];
        $idToken = $data['id_token'];
        $user = JWT::decode($idToken, new Key($publicKey, 'ES256'));

        $email = $user->email;
        $fname = $user->name->firstName ?? 'AppleUser';
        $lname = $user->name->lastName ?? 'AppleUser';
        $profile_image = ''; // Apple doesn't provide profile image
        $phone = ''; // Apple doesn’t provide phone by default

        $dbUser = $h->table('users')->select()->where('email', '=', $email)->fetchAll();

        if ($dbUser) {
            $_SESSION['users'] = $dbUser;
        } else {
            $generatedemail = generateRandomEmail($domainName);
            $generatedemail = strtolower($fname . $lname . $generatedemail);
            $password_email = random_strings(9);
            $createAccount = createEmailAccount($email_config, $generatedemail, $password_email);

            $insert = $h->insert('users')->values([
                'email' => $email,
                'fname' => $fname,
                'lname' => $lname,
                'profile_image' => $profile_image,
                'generated_email' => $generatedemail,
                'generated_email_pass' => $password_email,
                'signup_from' => 'apple',
            ])->run();
            $newUser = $h->table('users')->select()->where('email', '=', $email)->fetchAll();
            $_SESSION['users'] = $newUser;
        }

        header('Location: /user/dashboard');
        exit;
    } else {
        exit('Authorization code not provided.');
    }
}
if($route == '/admin'):
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //LOGIN
        if(isset($_POST['email']) && isset($_POST['password'])):
            $email = $_POST['email'];

            $usercheck = $h->table('users')->select()->where('email', '=', $email)->fetchAll();
            if ($usercheck){
                if ($usercheck[0]['twofa_status'] == 'on'){
                    echo $response=TwoFA($_POST['email'], $_POST['password'], 'users');
                }else{
                    echo $response=Login($_POST['email'], $_POST['password'] , 'users');
                }
            }else{
                echo  json_encode(array("statusCode" => 202, "message"=>"Invalid Email!"));
                exit();
            }
        endif;
        exit();

    }else{

        if (!empty($_SESSION['users'])){
            if ($_SESSION['users']['type'] == 'firm' || $_SESSION['users']['type'] == 'client'|| $_SESSION['users']['type'] == 'member'){
                header("Location: /user/dashboard");
            }elseif ($_SESSION['users']['type'] == 'admin'){
                header("Location: /admin/dashboard");
            }
        }

        $seo = array(
            'title' => 'Login | Techneketax',
            'description' => 'Enter your username or email address to log in.',
            'keywords' => 'login, sign in'
        );
        echo $twig->render('auth/adminLogin.twig', ['seo'=>$seo, 'csrf'=>set_csrf()]);
    }
endif;
if($route == '/login'):
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //LOGIN
        if(isset($_POST['email']) && isset($_POST['password'])):
            $email = $_POST['email'];

            $usercheck = $h->table('users')->select()->where('email', '=', $email)->fetchAll();
            if ($usercheck){
                if ($usercheck[0]['twofa_status'] == 'on'){
                    echo $response=TwoFA($_POST['email'], $_POST['password'] , 'users');
                }else{
                    echo $response=Login($_POST['email'], $_POST['password'] , 'users');
                }
            }else{
                echo  json_encode(array("statusCode" => 202, "message"=>"Invalid Email!"));
                exit();
            }
        endif;
        exit();

    }else{

        if (!empty($_SESSION['users'])){
            if ($_SESSION['users']['type'] == 'firm' || $_SESSION['users']['type'] == 'client'|| $_SESSION['users']['type'] == 'member'){
                header("Location: /user/dashboard");
            }elseif ($_SESSION['users']['type'] == 'admin'){
                header("Location: /admin/dashboard");
            }
        }
        $seo = array(
            'title' => 'Login | Techneketax',
            'description' => 'Enter your username or email address to log in.',
            'keywords' => 'login, sign in'
        );
        echo $twig->render('auth/login.twig', ['seo'=>$seo, 'csrf'=>set_csrf()]);
    }
endif;
if($route == '/2fa/login'):
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //LOGIN
        if(isset($_POST['email']) && isset($_POST['password'])&& isset($_POST['twofa'])):

            echo $response=userLogin($_POST['email'], $_POST['password'],$_POST['twofa'] , 'users');
        endif;
        exit();
    }else{
        if (!empty($_SESSION['users'])){
            if ($_SESSION['users']['type'] == 'user'){
                header("Location: /user/dashboard");
            }elseif ($_SESSION['users']['type'] == 'admin'){
                header("Location: /admin/dashboard");
            }
        }
        $seo = array(
            'title' => '2FA | Techneketax',
            'description' => 'Enter your username or email address to log in.',
            'keywords' => '2FA, sign in'
        );
        echo $twig->render('auth/2fa_login.twig', ['seo'=>$seo,'loginemail'=>$_SESSION['loginemail'],'loginpassword'=>$_SESSION['loginpassword'], 'csrf'=>set_csrf()]);
    }
endif;
if($route == '/forget-password'):
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //LOGIN
        if(isset($_POST['email'])):
            echo $response=forgetPassword($_POST['email'], 'users');
        endif;
        exit();

    }else {
        $seo = array(
            'title' => 'Forgot Your Password? Reset It Here',
            'description' => '',
            'keywords' => 'forgot password, reset password'
        );
        echo $twig->render('auth/forget_password.twig', ['seo' => $seo , 'csrf'=>set_csrf()]);
    }
endif;
if(@$_SESSION['reset'] != ''):
    if($route == '/reset/login'):
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //LOGIN
            if(isset($_POST['email']) && isset($_POST['password']) && isset($_POST['code'])):
                echo $response=setPassword($_POST['email'], $_POST['password'], $_POST['code'], 'users');
            endif;
            exit();

        }else {
            $seo = array(
                'title' => 'Reset Your Password',
                'description' => '',
                'keywords' => 'forgot password, reset password'
            );

            echo $twig->render('auth/password-reset.twig', ['seo' => $seo, 'csrf'=>set_csrf(),'Reset' => @$_SESSION['reset']] );
        }
    endif;
endif;
if($route == '/register'):
    if (!empty($_SESSION['users'])){
        if ($_SESSION['users']['type'] == 'firm' || $_SESSION['users']['type'] == 'client'|| $_SESSION['users']['type'] == 'member'){
            header("Location: /user/dashboard");
        }elseif ($_SESSION['users']['type'] == 'admin'){
            header("Location: /admin/dashboard");
        }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        //LOGIN
        if(isset($_POST['email']) && isset($_POST['fname'])&& isset($_POST['lname'])&& isset($_POST['phone'])&& isset($_POST['password']) && isset($_POST['account_type'])):
            echo $response=userRegister($_POST['fname'], $_POST['lname'], $_POST['email'],$_POST['phone'], $_POST['password'], $_POST['account_type'], 'users');
        endif;
        exit();

    }else{
        if (!empty($_SESSION['users'])){
            if ($_SESSION['users']['type'] == 'user'){
                header("Location: /user/dashboard");
            }elseif ($_SESSION['users']['type'] == 'admin'){
                header("Location: /admin/dashboard");
            }
        }
        $seo = array(
            'title' => 'Join Techneketax',
            'description' => '',
            'keywords' => 'sign up, register, create account'
        );
        echo $twig->render('auth/register.twig', ['seo'=>$seo , 'csrf'=>set_csrf()]);
    }
endif;
if($route == '/join/$firm_id/$associates_id/$email/$invite'):
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //LOGIN
        if(isset($_POST['email']) && isset($_POST['fname'])&& isset($_POST['lname'])&& isset($_POST['phone'])&& isset($_POST['password'])):
            if(!empty($_POST['firm_id']) ){
                $firm_id = $_POST['firm_id'];
            }else{
                $firm_id = '';
            }
            if(!empty($_POST['invite']) ){
                $invite = $_POST['invite'];
            }else{
                $invite = '';
            }
            if(!empty($_POST['associates_id']) && $_POST['associates_id'] !='null') {
                $associates_id = $_POST['associates_id'];
                $work_status = "assigned";
            }else{
                $associates_id = NULL;
                $work_status = "unassigned";
            }
            $fname = $_POST['fname'];
            $lname = $_POST['lname'];
            $phone = $_POST['phone'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            if (isset($password) && !empty($password)) {
                $uppercase = preg_match('@[A-Z]@', $password);
                $lowercase = preg_match('@[a-z]@', $password);
                $number = preg_match('@[0-9]@', $password);

                if (!$uppercase || !$lowercase || !$number || strlen($_POST['password']) < 8) {
                    echo "3";
                    exit();
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                }
            } else {
                echo "4";
                exit();
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $userAvailable = $h->table('users')->select()->where('email', '=', $email);
            if($userAvailable->count() < 1) {
                $generatedemail =  generateRandomEmail($domainName);
                $password_email =  random_strings(9);
                $createAccount = createEmailAccount($email_config, $generatedemail, $password_email);
                try {

                    $h->table('users')->insertOne([
                        'associates_id' => $associates_id,
                        'firm_id' => $firm_id,
                        'fname' => $fname,
                        'lname' => $lname,
                        'email' => $email,
                        'phone' => $phone,
                        'password' => $hashed_password,
                        'type' => $invite,
                        'work_status' => $work_status,
                        'generated_email'=> $generatedemail,
                        'generated_email_pass'=> $password_email
                    ]);
                    $AdminInfo = $h->table('users')->select()->where('type', '=', 'admin')->fetchAll();
                    @$company_name =  @$AdminInfo[0]['fname'].' '.@$AdminInfo[0]['lname'];
                    @$company_phone =  @$AdminInfo[0]['phone'];
                    @$company_email =  @$AdminInfo[0]['email'];
                    @$company_address =  @$AdminInfo[0]['address'];
                    @$company_linkedin =  @$AdminInfo[0]['linkedin'];
                    @$company_tweet =  @$AdminInfo[0]['tweet'];
                    @$company_facebook =  @$AdminInfo[0]['facebook'];
                    @$company_github =  @$AdminInfo[0]['github'];
                    @$imgUrl = $env['APP_URL'].'assets/techneketax-black.png';
                    $UserInfo = $h->table('users')->select()->where('email', '=', $email)->fetchAll();
                    include "views/email-template/WelcomeRegister.php";
                    mailSender($env['SENDER_EMAIL'],$email,'Welcome at - '.$env['SITE_NAME'],$message,$mail);
                    echo "1";
                    exit();
                } catch (PDOException $e) {
                    echo "0";
                    exit();
                }
            }else{
                echo "2";
                exit();
            }
        endif;
    }else{
        if (!empty($_SESSION['users'])){
            if ($_SESSION['users']['type'] == 'user'){
                header("Location: /user/dashboard");
            }elseif ($_SESSION['users']['type'] == 'admin'){
                header("Location: /admin/dashboard");
            }
        }
        $seo = array(
            'title' => 'Join Techneketax',
            'description' => '',
            'keywords' => 'sign up, register, create account'
        );
        echo $twig->render('auth/register.twig', ['seo'=>$seo , 'email'=>$email,'associates_id'=>$associates_id, 'firm_id'=>$firm_id,'invite'=>$invite, 'csrf'=>set_csrf()]);
    }
endif;
if($route == '/user/member/login_as_client'):
    $client_id = $_POST['client_id'];
unset($_SESSION['member_id']);
    $_SESSION['member_id'] = $loginUserId;
            $clientData = $h->table('users')->select()->where('id', '=', $client_id)->fetchAll();
    unset($_SESSION['users']);
    $_SESSION['users'] = $clientData[0];
    if (!empty($_SESSION['users'])){
        echo '1';
        exit();
    }else{
        echo '0';
        exit();
    }
endif;
if($route == '/user/member/login_as_member'):
    unset($_SESSION['member_id']);
    $member_id = $_POST['member_id'];
    $clientData = $h->table('users')->select()->where('id', '=', $member_id)->fetchAll();
    unset($_SESSION['users']);
    $_SESSION['users'] = $clientData[0];
    if (!empty($_SESSION['users'])){
        echo '1';
        exit();
    }else{
        echo '0';
        exit();
    }
endif;