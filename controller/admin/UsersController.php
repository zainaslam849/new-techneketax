<?php
require("config/env.php");

if($route == '/admin/add_user'):
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if(isset($_POST['email']) && isset($_POST['fname'])&& isset($_POST['lname'])&& isset($_POST['phone'])&& isset($_POST['password']) && isset($_POST['account_type'])):
            echo $response=userRegister($_POST['fname'], $_POST['lname'], $_POST['email'],$_POST['phone'], $_POST['password'], $_POST['account_type'], 'users');
        endif;
        exit();

    }else{
        $seo = array(
            'title' => 'Add User',
            'description' => 'Add User',
            'keywords' => 'Add User'
        );
        echo $twig->render('admin/users/add_user.twig', ['seo'=>$seo, 'csrf'=>set_csrf()]);
    }
endif;
if ($route == '/admin/get_user'):
    if(isset($_POST['edit']) && !empty($_POST['edit'])){
        $id= $_POST['id'];
        $users = $h->table('users')->select()->where('id', '=', $id)->fetchAll();
        echo json_encode($users);
        exit();
    }
endif;
    if ($route == '/admin/user_edit'):
        if (!empty($_POST['fname']) && !empty($_POST['lname'])) {
            @$fname = $_POST['fname'];
            @$lname = $_POST['lname'];
            @$phone = $_POST['phone'];
            @$account_type = $_POST['account_type'];
            @$id = $_POST['id'];
            @$password = $_POST['password'];
            @$confirmPassword = $_POST['cpassword'];
            if (!empty($password) &&  !empty($confirmPassword)) {
                if ($password === $confirmPassword) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $update = $h->update('users')->values([ 'fname' => $fname, 'lname' => $lname, 'phone' => $phone,'account_type' => $account_type, 'password' => $hashedPassword])->where('id','=',$id)->run();
                    echo "1";
                    exit();
                }else{
                    echo "2";
                    exit();
                }

            }else{
                $update = $h->update('users')->values([ 'fname' => $fname, 'lname' => $lname, 'phone' => $phone,'account_type' => $account_type])->where('id','=',$id)->run();
                echo "1";
                exit();
            }
        }else{
            echo "3";
            exit();
        }
endif;
if($route == '/admin/users/members'):
        $seo = array(
            'title' => 'Firm Members',
            'description' => '',
            'keywords' => 'login'
        );
        echo $twig->render('admin/users/members.twig', ['seo'=>$seo, 'csrf'=>set_csrf()]);
endif;
if($route == '/admin/users/firms'):
        $seo = array(
            'title' => 'Firms',
            'description' => '',
            'keywords' => 'login'
        );
        echo $twig->render('admin/users/firms.twig', ['seo'=>$seo, 'csrf'=>set_csrf()]);
endif;
if($route == '/admin/users/clients'):
        $seo = array(
            'title' => 'Firms Clients',
            'description' => '',
            'keywords' => 'login'
        );
        echo $twig->render('admin/users/clients.twig', ['seo'=>$seo, 'csrf'=>set_csrf()]);
endif;