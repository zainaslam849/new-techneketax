<?php
require("config/env.php");
if($route == '/admin/profile'):
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!empty($_POST['fname'])){
            $id = $_POST['basic_id'];
            if (!empty($_POST['fname']) && !empty($_POST['lname'])) {
                $fname = $_POST['fname'];
                $lname = $_POST['lname'];

            }else{
                echo "2";
                exit();
            }
            $address = $_POST['address'];
            $linkedin = $_POST['linkedin'];
            $tweet = $_POST['tweet'];
            $facebook = $_POST['facebook'];
            $github = $_POST['github'];
            $profile_image = upload('filepond','uploads/profile/');
            $UserInfo=$h->table('users')->select()->where('id', '=', $loginUserId)->fetchAll();
            if ($profile_image == 'null' || $profile_image == ''){
                $profile_image = $UserInfo[0]['profile_image'];
            }
            try {
                $update = $h->update('users')->values([
                    'profile_image' => $profile_image,
                    'fname' => $fname,
                    'lname' => $lname,
                    'address' => $address,
                    'linkedin' => $linkedin,
                    'tweet' => $tweet,
                    'facebook' => $facebook,
                    'github' => $github,
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

        echo $twig->render('admin/profile/profile.twig', ['seo' => $seo,'userinfo' => $UserInfo,'csrf'=>set_csrf()]);
}
endif;
if ($route == '/admin/fetch_profile'):
    if(isset($_POST['edit']) && !empty($_POST['edit'])){
        $id= $_POST['id'];
        //fetch user
        $users = $h->table('users')->select()->where('id', '=', $id)->fetchAll();
        $currentDate =  date('Y-m-d H:i:s');

            $appointments_count = $h->table('appointment')
                ->select()->where('date', '>', $currentDate)
                ->count();

            $invoiceUnpaid = $h->table('invoice')->select()->where('status', '=', 'unpaid')->count();
            $invoicePaid = $h->table('invoice')->select()->where('status', '=', 'paid')->count();


        echo json_encode(array("users"=>$users ,"appointment"=>$appointments_count,"invoiceUnpaid"=>$invoiceUnpaid,"invoicePaid"=>$invoicePaid));
        exit();
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
if($route == '/admin/profile/security'):
    $seo = array(
        'title' => 'Profile Security',
        'description' => 'CRM',
        'keywords' => 'Admin Panel'
    );
    $UserInfo=$h->table('users')->select()->where('id', '=', $loginUserId)->fetchAll();

    echo $twig->render('admin/profile/security.twig', ['seo' => $seo,'userinfo' => $UserInfo,'csrf'=>set_csrf()]);
endif;
if($route == '/admin/profile/paymentMethod'):
    $seo = array(
        'title' => 'Profile',
        'description' => 'CRM',
        'keywords' => 'Admin Panel'
    );

    $UserInfo = $h->table('users')->select()->where('id', '=', $loginUserId)->fetchAll();
    $UserInfoFirm_stripe_keys = $h->table('admin_stripe_keys')->select()->where('id', '=', 1)->fetchAll();

    echo $twig->render('admin/profile/payment_method.twig', [
        'seo' => $seo,
        'userinfo' => $UserInfo,
        'stripe_keys' => $UserInfoFirm_stripe_keys,
        'csrf' => set_csrf()
    ]);
endif;
if($route == '/admin/profile/change_stripe_keys'):
    if (isset($_POST['public_key'])){
        $public_key = $_POST['public_key'];
        $secret_key = $_POST['secret_key'];
        try {
                $insert = $h->update('admin_stripe_keys')->values([
                    'public_key' => $public_key,
                    'secret_key' => $secret_key,
                ])->where('id','=',1)->run();
                echo "1";
                exit();

        } catch (PDOException $e) {
            echo "0";
            exit();
        }
    }
endif;