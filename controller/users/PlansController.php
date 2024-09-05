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

