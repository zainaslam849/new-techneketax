<?php
require("config/env.php");
if($route == '/user/profile'):
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

        echo $twig->render('user/profile/profile.twig', ['seo' => $seo,'userinfo' => $UserInfo,'csrf'=>set_csrf()]);

    }
endif;
if($route == '/user/firm-info'):
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!empty($_POST['contact_name'])) {
            $UserInfo = $h->table('users')->select()->where('id', '=', $loginUserId)->fetchAll();
            if (!empty($_POST['contact_name']) && !empty($_POST['company_name']) && !empty($_POST['representative_name']) && !empty($_POST['owner_of_organization']) && !empty($_POST['type_of_organization'])) {
                $id = $_POST['id_company'];
                $contact_name = $_POST['contact_name'];
                $company_name = $_POST['company_name'];
                $representative_name = $_POST['representative_name'];
                $owner_of_organization = $_POST['owner_of_organization'];
                $type_of_organization = $_POST['type_of_organization'];
            } else {
                echo "2";
                exit();
            }
            $company_image = upload('filepond1', 'uploads/profile/');
            if ($company_image == 'null' || $company_image == '') {
                $company_image = $UserInfo[0]['company_image'];
            }
            try {
                $update = $h->update('users')->values([
                    'company_image' => $company_image,
                    'contact_name' => $contact_name,
                    'company_name' => $company_name,
                    'representative_name' => $representative_name,
                    'owner_of_organization' => $owner_of_organization,
                    'type_of_organization' => $type_of_organization,
                ])->where('id', '=', $id)->run();
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
        echo $twig->render('user/profile/firm.twig', ['seo' => $seo,'userinfo' => $UserInfo,'csrf'=>set_csrf()]);
    }
endif;
if($route == '/user/profile/security'):
        $seo = array(
            'title' => 'Profile Security',
            'description' => 'CRM',
            'keywords' => 'Admin Panel'
        );
    $UserInfo=$h->table('users')->select()->where('id', '=', $loginUserId)->fetchAll();
        echo $twig->render('user/profile/security.twig', ['seo' => $seo,'userinfo' => $UserInfo,'csrf'=>set_csrf()]);
endif;
if($route == '/user/profile/billing'):
    $seo = array(
        'title' => 'Profile Billing',
        'description' => 'CRM',
        'keywords' => 'Admin Panel'
    );

    $UserInfo = $h->table('users')->select()->where('id', '=', $loginUserId)->fetchAll();
    $CountriesInfo = $h->table('countries')->select()->fetchAll();
    $Billing_address = $h->table('billing_address')->select()->where('user_id', '=', $loginUserId)->fetchAll();
    $UserInfoUser_payment_method = $h->table('user_payment_method')->select()->where('user_id', '=', $loginUserId)->fetchAll();

    echo $twig->render('user/profile/billing.twig', [
        'seo' => $seo,
        'userinfo' => $UserInfo,
        'countries' => $CountriesInfo,
        'billingAddresses' => $Billing_address,
        'paymentMethods' => $UserInfoUser_payment_method,
        'csrf' => set_csrf()
    ]);
    endif;
if($route == '/user/profile/paymentMethod'):
    $seo = array(
        'title' => 'Profile',
        'description' => 'CRM',
        'keywords' => 'Admin Panel'
    );

    $UserInfo = $h->table('users')->select()->where('id', '=', $loginUserId)->fetchAll();
    $CountriesInfo = $h->table('countries')->select()->fetchAll();
    $UserInfoUser_payment_method = $h->table('user_payment_method')->select()->where('user_id', '=', $loginUserId)->fetchAll();

    echo $twig->render('user/profile/payment_method.twig', [
        'seo' => $seo,
        'userinfo' => $UserInfo,
        'countries' => $CountriesInfo,
        'paymentMethods' => $UserInfoUser_payment_method,
        'csrf' => set_csrf()
    ]);
endif;
if ($route == '/user/fetch_profile'):
    if(isset($_POST['edit']) && !empty($_POST['edit'])){
        $id= $_POST['id'];
        //fetch user
        $users = $h->table('users')->select()->where('id', '=', $id)->fetchAll();
      $currentDate =  date('Y-m-d H:i:s');
        //Appointment
        if($loginUserType == 'firm'){
            $appointments_count = $h->table('appointment')
                ->select()
                ->where('firm_id', '=', $id)->where('date', '>', $currentDate)
                ->count();
        }else{

            $users = $h->table('users')->select()->where('id', '=', $id)->fetchAll();
            $firm_id = $users[0]['firm_id'];

            $firm_appointments = $h->table('appointment')
                ->select()
                ->where('firm_id', '=', $firm_id)->where('date', '>', $currentDate)
                ->fetchAll();

            $appointments_count = 0;

            if (!empty($firm_appointments)) {
                foreach ($firm_appointments as $firm_appointment) {
                    $client_ids = $firm_appointment['client_id'];

                    // Explode the comma-separated string into an array
                    $clientIdsArray = explode(',', $client_ids);

                    // Check if $loginUserId exists in the array
                    if (in_array($id, $clientIdsArray)) {
                        $appointments_count++;
                    }
                }
            }

        }
//invoice unpaid
        if($loginUserType == 'firm'){
            $invoiceUnpaid = $h->table('invoice')->select()->where('firm_id', '=', $id)->where('status', '=', 'unpaid')->count();
            $invoicePaid = $h->table('invoice')->select()->where('firm_id', '=', $id)->where('status', '=', 'paid')->count();
        }else{
            $invoiceUnpaid = $h->table('invoice')->select()->where('client_id', '=', $id)->where('status', '=', 'unpaid')->count();
            $invoicePaid = $h->table('invoice')->select()->where('client_id', '=', $id)->where('status', '=', 'paid')->count();
        }

        echo json_encode(array("users"=>$users ,"appointment"=>$appointments_count,"invoiceUnpaid"=>$invoiceUnpaid,"invoicePaid"=>$invoicePaid));
        exit();
    }
endif;
if($route == '/user/company/profile'):

endif;
if($route == '/user/bank/profile'):
    if (!empty($_POST['account_number'])){
        if (!empty($_POST['account_number']) && !empty($_POST['bank_name'])&& !empty($_POST['swift_code'])&& !empty($_POST['bank_country'])) {
            $id = $_POST['id_company'];
            $account_number = $_POST['account_number'];
            $bank_name = $_POST['bank_name'];
            $swift_code = $_POST['swift_code'];
            $bank_country = $_POST['bank_country'];
        }else{
            echo "2";
            exit();
        }

        try {
            $update = $h->update('users')->values([
                'account_number' => $account_number,
                'bank_name' => $bank_name,
                'swift_code' => $swift_code,
                'bank_country' => $bank_country
            ])->where('id','=',$id)->run();
            echo "1";
            exit();
        } catch (PDOException $e) {
            echo "0";
            exit();
        }
    }
endif;
if($route == '/user/profile/password_change'):
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
if($route == '/user/add_billing_address'):
            if (isset($_POST['billing_name'])){
                $user_id = $_POST['user_id'];
                if (!empty($_POST['billing_name']) && !empty($_POST['billing_email'])&& !empty($_POST['billing_address'])&& !empty($_POST['billing_city'])&& !empty($_POST['billing_country'])&& !empty($_POST['billing_zip'])) {
                    $billing_name = $_POST['billing_name'];
                    $billing_email = $_POST['billing_email'];
                    $billing_address = $_POST['billing_address'];
                    $billing_city = $_POST['billing_city'];
                    $billing_country = $_POST['billing_country'];
                    $billing_zip = $_POST['billing_zip'];
                }else{
                    echo "2";
                    exit();
                }
                $UserInfoBilling_address=$h->table('billing_address')->select()->where('user_id', '=', $loginUserId);
                if ($UserInfoBilling_address->count() < 1){
                    $status = 'primary';
                }else{
                    $status = 'secondary';
                }
                try {
                    $insert = $h->insert('billing_address')->values([
                        'user_id' => $user_id,
                        'billing_name' => $billing_name,
                        'billing_email' => $billing_email,
                        'billing_address' => $billing_address,
                        'billing_city' => $billing_city,
                        'billing_country' => $billing_country,
                        'billing_zip' => $billing_zip,
                        'status' => $status,
                    ])->run();
                    echo "1";
                    exit();
                } catch (PDOException $e) {
                    echo "0";
                    exit();
                }
            }
endif;
if ($route == '/user/get_billing_address'):
    if(isset($_POST['edit']) && !empty($_POST['edit'])){
        $id= $_POST['id'];
        $billing_address = $h->table('billing_address')->select()->where('id', '=', $id)->fetchAll();
        echo json_encode($billing_address);
        exit();
    }
endif;
if($route == '/user/update_billing_address'):
    if (isset($_POST['billing_name'])){
        $id = $_POST['id'];
        if (!empty($_POST['billing_name']) && !empty($_POST['billing_email'])&& !empty($_POST['billing_address'])&& !empty($_POST['billing_city'])&& !empty($_POST['billing_country'])&& !empty($_POST['billing_zip'])) {
            $billing_name = $_POST['billing_name'];
            $billing_email = $_POST['billing_email'];
            $billing_address = $_POST['billing_address'];
            $billing_city = $_POST['billing_city'];
            $billing_country = $_POST['billing_country'];
            $billing_zip = $_POST['billing_zip'];
        }else{
            echo "2";
            exit();
        }
        try {
            $update = $h->update('billing_address')->values([
                'billing_name' => $billing_name,
                'billing_email' => $billing_email,
                'billing_address' => $billing_address,
                'billing_city' => $billing_city,
                'billing_country' => $billing_country,
                'billing_zip' => $billing_zip,
            ])->where('id','=',$id)->run();
            echo "1";
            exit();
        } catch (PDOException $e) {
            echo "0";
            exit();
        }
    }
endif;
if($route == '/user/add_payment_method'):
    if (isset($_POST['card_brand'])){
        $user_id = $_POST['user_id'];
        if (!empty($_POST['card_brand']) && !empty($_POST['card_number'])&& !empty($_POST['holder_name'])&& !empty($_POST['cvv'])&& !empty($_POST['card_expiry'])) {
            $card_brand = $_POST['card_brand'];
            $card_number = $_POST['card_number'];
            $holder_name = $_POST['holder_name'];
            $cvv = $_POST['cvv'];
            $card_expiry = $_POST['card_expiry'];
        }else{
            echo "2";
            exit();
        }
        $UserInfoUser_payment_method=$h->table('user_payment_method')->select()->where('user_id', '=', $loginUserId);
if ($UserInfoUser_payment_method->count() < 1){
$status = 'primary';
}else{
    $status = 'secondary';
}
        try {
            $insert = $h->insert('user_payment_method')->values([
                'user_id' => $user_id,
                'card_brand' => $card_brand,
                'card_number' => $card_number,
                'holder_name' => $holder_name,
                'cvv' => $cvv,
                'card_expiry' => $card_expiry,
                'status' => $status,
            ])->run();
            echo "1";
            exit();
        } catch (PDOException $e) {
            echo "0";
            exit();
        }
    }
endif;
if ($route == '/user/get_payment_method'):
    if(isset($_POST['edit']) && !empty($_POST['edit'])){
        $id= $_POST['id'];
        $user_payment_method = $h->table('user_payment_method')->select()->where('id', '=', $id)->fetchAll();
        echo json_encode($user_payment_method);
        exit();
    }
endif;
if($route == '/user/update_payment_method'):
    if (isset($_POST['card_brand'])){
        $id = $_POST['id'];
        if (!empty($_POST['card_brand']) && !empty($_POST['card_number'])&& !empty($_POST['holder_name'])&& !empty($_POST['cvv'])&& !empty($_POST['card_expiry'])) {
            $card_brand = $_POST['card_brand'];
            $card_number = $_POST['card_number'];
            $holder_name = $_POST['holder_name'];
            $cvv = $_POST['cvv'];
            $card_expiry = $_POST['card_expiry'];
        }else{
            echo "2";
            exit();
        }
        try {
            $insert = $h->update('user_payment_method')->values([
                'card_brand' => $card_brand,
                'card_number' => $card_number,
                'holder_name' => $holder_name,
                'cvv' => $cvv,
                'card_expiry' => $card_expiry,
            ])->where('id','=',$id)->run();
            echo "1";
            exit();
        } catch (PDOException $e) {
            echo "0";
            exit();
        }
    }
endif;