<?php
require("config/env.php");
if($route == '/admin/profile'):
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['fname'])){
        $id = $_POST['id'];
        if (!empty($_POST['fname']) && !empty($_POST['lname'])) {
            $fname = $_POST['fname'];
            $lname = $_POST['lname'];
            $phone = $_POST['phone'];
        }else{
            echo "2";
            exit();
        }
        try {
            $update = $h->update('users')->values([
                'fname' => $fname,
                'lname' => $lname,
                'phone' => $phone,

            ])->where('id','=',$id)->run();
            echo "1";
            exit();
        } catch (PDOException $e) {
            echo "0";
            exit();
        }
    }
}else{
    $seo = array(
        'title' => 'Profile',
        'description' => 'CRM',
        'keywords' => 'Admin Panel'
    );
    $UserInfo=$h->table('users')->select()->where('id', '=', $loginUserId)->fetchAll();
    $userinfo = array(
        'id' => $UserInfo[0]['id'],
        'fname' => $UserInfo[0]['fname'],
        'lname' => $UserInfo[0]['lname'],
        'email' => $UserInfo[0]['email'],
        'phone' => $UserInfo[0]['phone'],
    );
    echo $twig->render('admin/profile/profile.twig', ['seo' => $seo,'userinfo' => $userinfo, 'csrf'=>set_csrf()]);
}
endif;
if($route == '/admin/profile/password_change'):
    if (!empty($_POST['current_password'])){
        $id = $_POST['id'];
        $current_password= $_POST['current_password'];
        $UserInfo = $h->table('users')->select()->where('id','=',$id)->fetchAll();
        if (password_verify($current_password, $UserInfo[0]['password'])) {
            $new_password = $_POST['new_password'];
            $hashed_password= password_hash($new_password, PASSWORD_DEFAULT);
        }else{
            echo "2";
            exit();
        }

        try {
            $update = $h->update('users')->values([
                'password' => $hashed_password,
            ])->where('id','=',$id)->run();
            echo "1";
            exit();
        } catch (PDOException $e) {
            echo "0";
            exit();
        }
    }
endif;
if($route == '/admin/security'):
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!empty($_POST['fname'])){
            $id = $_POST['id'];
            if (!empty($_POST['fname']) && !empty($_POST['lname'])) {
                $fname = $_POST['fname'];
                $lname = $_POST['lname'];
                $phone = $_POST['phone'];
            }else{
                echo "2";
                exit();
            }
            try {
                $update = $h->update('users')->values([
                    'fname' => $fname,
                    'lname' => $lname,
                    'phone' => $phone,

                ])->where('id','=',$id)->run();
                echo "1";
                exit();
            } catch (PDOException $e) {
                echo "0";
                exit();
            }
        }
    }else{
        $seo = array(
            'title' => 'Profile',
            'description' => 'CRM',
            'keywords' => 'Admin Panel'
        );

        $UserInfo=$h->table('users')->select()->where('id', '=', $loginUserId)->fetchAll();
        $userinfo = array(
            'id' => $UserInfo[0]['id'],
            'twofa_status' => $UserInfo[0]['twofa_status'],

        );
        echo $twig->render('admin/profile/security.twig', ['seo' => $seo,'userinfo' => $userinfo, 'csrf'=>set_csrf()]);
    }
endif;