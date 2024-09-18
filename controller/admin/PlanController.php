<?php
require("config/env.php");
\Stripe\Stripe::setApiKey($Admin_Stripe_secret_key);
if($route == '/admin/plans'):
$seo = array(
    'title' => 'Plans | Admin Panel',
    'description' => 'CRM',
    'keywords' => 'Admin Panel'
);
    $permissions = $h->table('permissions')->select()->orderBy('id', 'desc')->fetchAll();
echo $twig->render('admin/plans/list.twig', ['seo'=>$seo,'permissions'=>$permissions]);
endif;

if($route == '/admin/add_plan'):

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (!empty($_POST['name'])) {
            @$name = $_POST['name'];
            @$tags = $_POST['tags'];
            $percentage = $_POST['percentage'];
            $basic = $_POST['basic'];
            $slug = slugify($name) . random_strings(5);
            if (!empty($basic)) {
                $basicArray = json_decode($basic, true);
                $values = array_column($basicArray, 'value');
                $tags = implode(',', $values);
            } else {
                $tags = '';
            }
            if (!empty($_POST['key_points'])) {
                $key_points = implode(',', array_filter($_POST['key_points']));
            } else {
                $key_points = '';
            }
            if (!empty($_POST['monthly_price']) && !empty($_POST['yearly_price'])) {
                @$monthly_price = $_POST['monthly_price'];
                @$yearly_price = $_POST['yearly_price'];
            } else {
                echo "3";
                exit();
            }
            try {
                $product = \Stripe\Product::create([
                    'name' => $name,
                ]);
                $stripe_product_id = $product->id;
                $planPriceCents = round($monthly_price * 100);
                $monthlyPriceObj = \Stripe\Price::create([
                    'unit_amount' => $planPriceCents,
                    'currency' => 'usd',
                    'recurring' => [
                        'interval' => 'month',
                    ],
                    'product' => $stripe_product_id,
                ]);
                $stripe_monthly_price_id = $monthlyPriceObj->id;
                $yearlyPriceCents = round($yearly_price * 100);
                $yearlyPriceObj = \Stripe\Price::create([
                    'unit_amount' => $yearlyPriceCents,
                    'currency' => 'usd',
                    'recurring' => [
                        'interval' => 'year',
                    ],
                    'product' => $stripe_product_id,
                ]);
                $stripe_yearly_price_id = $yearlyPriceObj->id;
                $insert = $h->insert('plans')->values([
                    'slug' => $slug,
                    'name' => $name,
                    'tags' => $tags,
                    'key_points' => $key_points,
                    'percentage' => $percentage,
                    'monthly_price' => $monthly_price,
                    'yearly_price' => $yearly_price,
                    'stripe_product_id' => $stripe_product_id,
                    'stripe_monthly_price_id' => $stripe_monthly_price_id,
                    'stripe_yearly_price_id' => $stripe_yearly_price_id
                ])->run();

                echo "1";
                exit();
            } catch (PDOException $e) {
                echo "0";
                exit();
            } catch (\Stripe\Exception\ApiErrorException $e) {
                echo "Stripe Error: " . $e->getMessage();
                exit();
            }
        } else {
            echo "2";
            exit();
        }

    }else{
        $seo = array(
            'title' => 'Add Plan | Admin Panel',
            'description' => 'Add User',
            'keywords' => 'Admin Panel'
        );
        $permissions = $h->table('permissions')->select()->orderBy('id', 'desc')->fetchAll();
        echo $twig->render('admin/plans/add_plan.twig', ['seo'=>$seo,'permissions'=>$permissions, 'csrf'=>set_csrf()]);
    }
endif;
if ($route == '/admin/get_plan'):
    if(isset($_POST['edit']) && !empty($_POST['edit'])){
        $id= $_POST['id'];
        $plans = $h->table('plans')->select()->where('id', '=', $id)->fetchAll();
        echo json_encode($plans);
        exit();
    }
endif;
if ($route == '/admin/plan_edit'):
    if (!empty($_POST['name'])) {
        @$name = $_POST['name'];
        @$id = $_POST['id'];
        $plans = $h->table('plans')->select()->where('id','=',$id)->fetchAll();
        @$stripe_product_id = $plans[0]['stripe_product_id'];
        @$stripe_monthly_price_id =$plans[0]['stripe_monthly_price_id'];
        @$stripe_yearly_price_id =$plans[0]['stripe_yearly_price_id'];
        $percentage = $_POST['percentage'];
        $basic = $_POST['basic'];
        $slug = slugify($name) . random_strings(5);

        // Handling the basic array input
        if (!empty($basic)) {
            $basicArray = json_decode($basic, true);
            $values = array_column($basicArray, 'value');
            $tags = implode(',', $values);
        } else {
            $tags = '';
        }

        // Handling key points input
        if (!empty($_POST['key_points'])) {
            @$key_points = implode(',', array_filter($_POST['key_points']));
        } else {
            @$key_points = '';
        }

        // Handling prices
        if (!empty($_POST['monthly_price']) && !empty($_POST['yearly_price'])) {
            @$monthly_price = $_POST['monthly_price'];
            @$yearly_price = $_POST['yearly_price'];
        } else {
            echo "3"; // Error code for missing price input
            exit();
        }

        try {
            // Update the product in Stripe
            \Stripe\Product::update($stripe_product_id, [
                'name' => $name,
            ]);

            // Archive old monthly price in Stripe
            \Stripe\Price::update($stripe_monthly_price_id, [
                'active' => false,
            ]);

            // Create new monthly price in Stripe
            $planPriceCents = round($monthly_price * 100); // Convert to cents
            $newMonthlyPriceObj = \Stripe\Price::create([
                'unit_amount' => $planPriceCents,
                'currency' => 'usd',
                'recurring' => [
                    'interval' => 'month',
                ],
                'product' => $stripe_product_id,
            ]);
            $new_stripe_monthly_price_id = $newMonthlyPriceObj->id;

            // Archive old yearly price in Stripe
            \Stripe\Price::update($stripe_yearly_price_id, [
                'active' => false,
            ]);

            // Create new yearly price in Stripe
            $yearlyPriceCents = round($yearly_price * 100); // Convert to cents
            $newYearlyPriceObj = \Stripe\Price::create([
                'unit_amount' => $yearlyPriceCents,
                'currency' => 'usd',
                'recurring' => [
                    'interval' => 'year',
                ],
                'product' => $stripe_product_id,
            ]);
            $new_stripe_yearly_price_id = $newYearlyPriceObj->id;

            // Update the plan in your database
            $update = $h->update('plans')->values([
                'slug' => $slug,
                'name' => $name,
                'tags' => $tags,
                'key_points' => $key_points,
                'percentage' => $percentage,
                'monthly_price' => $monthly_price,
                'yearly_price' => $yearly_price,
                'stripe_monthly_price_id' => $new_stripe_monthly_price_id,
                'stripe_yearly_price_id' => $new_stripe_yearly_price_id
            ])->where('id', '=', $id)->run();

            echo "1"; // Success
            exit();
        } catch (PDOException $e) {
            echo "0"; // Database error
            exit();
        } catch (\Stripe\Exception\ApiErrorException $e) {
            echo "Stripe Error: " . $e->getMessage(); // Stripe API error
            exit();
        }
    } else {
        echo "2"; // Error code for missing plan name
        exit();
    }

endif;
