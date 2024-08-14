<?php
require("config/env.php");

if ($route == '/user/get_user'):
    if(isset($_POST['edit']) && !empty($_POST['edit'])){
        $id= $_POST['id'];
        $users = $h->table('users')->select()->where('id', '=', $id)->fetchAll();
        echo json_encode($users);
        exit();
    }
endif;
    if ($route == '/user/user_edit'):
        if (!empty($_POST['fname']) && !empty($_POST['lname'])) {
            @$fname = $_POST['fname'];
            @$lname = $_POST['lname'];
            @$phone = $_POST['phone'];
            @$id = $_POST['id'];
            @$password = $_POST['password'];
            @$confirmPassword = $_POST['cpassword'];
            if (!empty($password) &&  !empty($confirmPassword)) {
                if ($password === $confirmPassword) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $update = $h->update('users')->values([ 'fname' => $fname, 'lname' => $lname, 'phone' => $phone, 'password' => $hashedPassword])->where('id','=',$id)->run();
                    echo "1";
                    exit();
                }else{
                    echo "2";
                    exit();
                }

            }else{
                $update = $h->update('users')->values([ 'fname' => $fname, 'lname' => $lname, 'phone' => $phone])->where('id','=',$id)->run();
                echo "1";
                exit();
            }
        }else{
            echo "3";
            exit();
        }
endif;
