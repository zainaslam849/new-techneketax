<?php
require("config/env.php");
\Stripe\Stripe::setApiKey($Admin_Stripe_secret_key);
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
    if (!empty(@$plan_end_date)){
       @$dateTime = new DateTime(@$plan_end_date);
        @$formattedDate = @$dateTime->format('l, d F Y');
    }
echo $twig->render('user/plans/index.twig', ['seo' => $seo,'plans' => $plans,'formattedDate' => @$formattedDate]);
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
    if (!empty($loginUserId)) {
        $check = $h->table('subscriptions')->select()->where('user_id', '=', $loginUserId)->where('plan_end_date', '>=', $current_date)->where('status', '=', 'successful');
        if ($check->count() < 1) {
        \Stripe\Stripe::setApiKey($Admin_Stripe_secret_key);

// Retrieve JSON from POST body
        $jsonStr = file_get_contents('php://input');
        $jsonObj = json_decode($jsonStr);

        if ($jsonObj->request_type == 'create_customer_subscription') {
            $subscr_plan_id = $jsonObj->subscr_plan_id;
            $package_duration = $h->table('plans')->select()->where('id', '=', $subscr_plan_id)->fetchAll();

            if ($_SESSION['type'] == 'month') {
                $planPriceCents = $package_duration[0]['monthly_price'] * 100; // Example price (in cents)
                $stripe_product_price = $package_duration[0]['stripe_monthly_price_id']; // Example plan name
            } else {
                $planPriceCents = $package_duration[0]['yearly_price'] * 100; // Example price (in cents)
                $stripe_product_price = $package_duration[0]['stripe_yearly_price_id']; // Example plan name
            }
            $planInterval = $_SESSION['type'];

            try {
                $customer = \Stripe\Customer::create([
                    'name' => $loginUserName,
                    'email' => $loginUserEmail,
                ]);

                $subscription = \Stripe\Subscription::create([
                    'customer' => $customer->id,
                    'items' => [[
                        'price' => $stripe_product_price,
                    ]],
                    'payment_behavior' => 'default_incomplete',
                    'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
                    'expand' => ['latest_invoice.payment_intent'],
                ]);

                $payment_intent = $subscription->latest_invoice->payment_intent;

                echo json_encode([
                    'subscriptionId' => $subscription->id,
                    'transactionId' => $subscription->latest_invoice->id,
                    'clientSecret' => $payment_intent->client_secret,
                    'currentPeriodEnd' => $subscription->current_period_end,
                    'customerId' => $customer->id
                ]);

            } catch (\Stripe\Exception\ApiErrorException $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
        } elseif ($jsonObj->request_type == 'payment_insert') {
            $payment_intent = $jsonObj->payment_intent;
            $subscription_id = $jsonObj->subscription_id;
            $transaction_id = $jsonObj->transaction_id;
            $customer_id = $jsonObj->customer_id;
            $package_form_id = $jsonObj->package_form_id;
            $pricePackage = $jsonObj->pricePackage;
            $current_period_end = $jsonObj->current_period_end;
          $end_plan_date =  date('Y-m-d H:i:s', $current_period_end);
            // Check whether the charge was successful
            if (!empty($payment_intent) && $payment_intent->status == 'succeeded') {
                // Here you can update your database with subscription info
                // Store subscription details in the database

                $insert = $h->insert('admin_transactions')->values([
                    'subscription_id' => $subscription_id,
                    'transaction_id' => $transaction_id,
                    'plan_id' => $package_form_id,
                    'price' => $pricePackage,
                    'pay_with' => 'stripe',
                ])->run();

                if ($insert) {
                    $res = $h->insert('subscriptions')->values([
                        'user_id' => $loginUserId,
                        'total_price' => $pricePackage,
                        'plan_id' => $package_form_id,
                        'plan_type' => $_SESSION['type'],
                        'plan_end_date' => $end_plan_date,
                        'status' => 'successful',
                        'stripe_subscription_id' => $subscription_id, // Store Stripe subscription ID
                    ])->run();
                    $update = $h->update('users')
                        ->values(['plan_id' => $package_form_id, 'plan_end_date' => $end_plan_date])
                        ->where('id', '=', $loginUserId)
                        ->run();
                    echo json_encode(['statusCode' => '200', 'payment_id' => base64_encode($subscription_id)]);
                    exit();
                } else {
                    echo json_encode(['statusCode' => '202', 'error' => 'Failed to store subscription details']);
                    exit();
                }
                // Generate a response to return
                echo json_encode(['statusCode' => '200', 'payment_id' => base64_encode($subscription_id)]);
                exit();
            } else {
                echo json_encode(['error' => 'Transaction failed']);
            }
        }
    }else{
            echo json_encode(['statusCode' => '202', 'error' => 'Plan Already Purchased']);
            exit();
        }
    }
}

