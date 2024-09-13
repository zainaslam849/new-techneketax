<?php
require("config/env.php");
if($route == '/user/plans'):
$seo = array(
    'title' => 'Plans',
    'description' => 'CRM',
    'keywords' => 'Admin Panel'
);
    $plans = $h->table('plans')->select()->where('status','=','active')->fetchAll();

    foreach ($plans as &$plan) {
        $key_pointsArray = explode(',', $plan['key_points']);
        $key_points_titles = [];

        if (!empty($key_pointsArray)) {
            foreach ($key_pointsArray as $key_point_id) {
                $permission = $h->table('permissions')->select()->where('id', '=', $key_point_id)->fetchAll();
                if ($permission) {
                    $key_points_titles[] = $permission[0]['title'];
                }
            }
        }

        // Attach key points titles to the plan array
        $plan['key_points_titles'] = $key_points_titles;
    }
echo $twig->render('user/plans/index.twig', ['seo' => $seo,'plans' => $plans]);
endif;

if($route == '/user/plan/get_plan'):
    $type = $_POST['type'];
    $slug = $_POST['slug'];
unset($_SESSION['type']);
$_SESSION['type'] = $type;

    echo json_encode(array("statusCode" => "1", "slug"=>$slug));
endif;
if ($route == '/user/plans_details/$slug') {
    $seo = array(
        'title' => 'Plans',
        'description' => 'CRM',
        'keywords' => 'Admin Panel'
    );

    // Fetch the plan based on the slug
    $plans = $h->table('plans')->select()->where('slug', '=', $slug)->fetchAll();

    $key_pointsArray = explode(',', $plans[0]['key_points']);
    $permissions = [];  // Array to hold permissions

    if (!empty($key_pointsArray)) {
        foreach ($key_pointsArray as $key_point_id) {
            $permission = $h->table('permissions')->select()->where('id', '=', $key_point_id)->fetchAll();
            if ($permission) {
                $permissions[] = $permission;
            }
        }
    }
    if ($_SESSION['type'] == 'month'){
        $planPrice = $plans[0]['monthly_price'];
    }else{
        $planPrice = $plans[0]['yearly_price'];
    }
    if (!empty($plans[0]['percentage']) && $plans[0]['percentage'] != '') {
        $discount = $plans[0]['percentage'];
        $totalPrice = $planPrice - ($planPrice * $discount / 100);
    }else{
        $totalPrice = $planPrice;
    }
    $Billing_address = $h->table('billing_address')->select()->where('user_id', '=', $loginUserId)->where('status', '=', 'primary')->fetchAll();
    echo $twig->render('user/plans/plan_details.twig', [
        'seo' => $seo,
        'plans' => $plans,
        'permissions' => $permissions,
        'totalPrice' => $totalPrice,
        'Billing_address' => $Billing_address,
        'type' => $_SESSION['type']
    ]);
}
if ($route == '/user/plan/checkout') {
    if (!empty($_POST['user_id'])) {
        $current_date = date('Y-m-d H:i:s');
        $user_id = $_POST['user_id'];
        if (!empty($_POST['billing_address_id'])){
            $billing_address_id = $_POST['billing_address_id'];
        }else{
            echo "2";
            exit();
        }
        $plan_id = $_POST['plan_id'];
        $plan_type = $_POST['plan_type'];
        $total_price = $_POST['total_price'];
        if ($plan_type == 'month'){
            $date = new DateTime($current_date);
            $date->modify('+1 month');
            $plan_end_date = $date->format('Y-m-d H:i:s');
        }else{
            $date = new DateTime($current_date);
            $date->modify('+1 year');
            $plan_end_date = $date->format('Y-m-d H:i:s');
        }
        $check = $h->table('subscriptions')->select()->where('user_id', '=', $user_id)->where('status', '=', 'successful');
        if ($check->count() < 1){
            try {
                $insert = $h->insert('subscriptions')->values(['user_id' => $user_id,'total_price' => $total_price,'plan_id' => $plan_id,'billing_address_id' => $billing_address_id,'plan_type' => $plan_type,'plan_end_date' => $plan_end_date,'status' => 'successful'])->run();
            if ($insert){
                $update = $h->update('users')->values(['plan_id' => $plan_id,'plan_end_date' => $plan_end_date])->where('id', '=', $user_id)->run();

                echo "1";
                exit();
            }
            } catch (PDOException $e) {
                echo "0";
                exit();
            }
        }else{
            echo "3";
            exit();
        }


    }
}

