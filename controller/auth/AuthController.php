<?php
require("config/env.php");
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

            if ($_SESSION['users']['type'] == 'user'){
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

            if ($_SESSION['users']['type'] == 'user'){
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
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //LOGIN
        if(isset($_POST['email']) && isset($_POST['fname'])&& isset($_POST['lname'])&& isset($_POST['phone'])&& isset($_POST['password'])):
            echo $response=userRegister($_POST['fname'], $_POST['lname'], $_POST['email'],$_POST['phone'], $_POST['password'], 'users');
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
if($route == '/join/$firm_id/$invite'):
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
                try {

                    $h->table('users')->insertOne([
                        'firm_id' => $firm_id,
                        'fname' => $fname,
                        'lname' => $lname,
                        'email' => $email,
                        'phone' => $phone,
                        'password' => $hashed_password,
                        'type' => $invite,
                    ]);
                    include "views/email-template/WelcomeRegister.php";
                    mailSender($env['SENDER_EMAIL'],$email,'Welcome at - '.$env['SITE_NAME'],$message,$mail);
                    echo "1";
                    exit();
                } catch (PDOException $e) {
                    echo "0";
                    exit();
                }
            }else{
                echo "0";
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
        echo $twig->render('auth/register.twig', ['seo'=>$seo , 'firm_id'=>$firm_id,'invite'=>$invite, 'csrf'=>set_csrf()]);
    }
endif;