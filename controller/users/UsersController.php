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
    if ($route == '/user/add_user'):
        if (!empty($_POST['fname']) && !empty($_POST['lname'])) {
            @$fname = $_POST['fname'];
            @$lname = $_POST['lname'];
            @$phone = $_POST['phone'];
            @$firm_id = $_POST['firm_id'];
            @$type = $_POST['type'];
                $email=$_POST['email'];
            @$password = $_POST['password'];
            $uppercase = preg_match('@[A-Z]@', $password);
            $lowercase = preg_match('@[a-z]@', $password);
            $number = preg_match('@[0-9]@', $password);

            if (!$uppercase || !$lowercase || !$number || strlen($_POST['password']) < 8) {
                echo "2";
                exit();
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            }
            $userAvailable = $h->table('users')->select()->where('email', '=', $email);
            if($userAvailable->count() < 1){
            try {
            $insert = $h->insert('users')->values([ 'firm_id' => $firm_id,'fname' => $fname, 'lname' => $lname, 'email' => $email, 'phone' => $phone,'type' => $type, 'password' => $hashed_password])->run();
            echo "1";
            exit();
        } catch (PDOException $e) {
        echo "0";
        exit();
    }
            }else{
       echo  "4";
       exit();
        }
        }else{
            echo "3";
            exit();
        }
endif;
