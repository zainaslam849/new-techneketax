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
            if (!empty($percentage) && $percentage != '') {
                $discount = $percentage;
                $monthly_price_final = $monthly_price - ($monthly_price * $discount / 100);
                $yearly_price_final = $yearly_price - ($yearly_price * $discount / 100);
            }else{
                $monthly_price_final = $monthly_price;
                $yearly_price_final = $yearly_price;
            }
            try {
                $product = \Stripe\Product::create([
                    'name' => $name,
                ]);
                $stripe_product_id = $product->id;
                $planPriceCents = round($monthly_price_final * 100);
                $monthlyPriceObj = \Stripe\Price::create([
                    'unit_amount' => $planPriceCents,
                    'currency' => 'usd',
                    'recurring' => [
                        'interval' => 'month',
                    ],
                    'product' => $stripe_product_id,
                ]);
                $stripe_monthly_price_id = $monthlyPriceObj->id;
                $yearlyPriceCents = round($yearly_price_final * 100);
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
        $name = $_POST['name'];
        $id = $_POST['id'];
        $plans = $h->table('plans')->select()->where('id', '=', $id)->fetchAll();

        $stripe_product_id = $plans[0]['stripe_product_id'];
        $stripe_monthly_price_id = $plans[0]['stripe_monthly_price_id'];
        $stripe_yearly_price_id = $plans[0]['stripe_yearly_price_id'];
        $percentage = $_POST['percentage'];
        $basic = $_POST['basic'];
        $slug = slugify($name) . random_strings(5);

        // Handling the basic array input
        $tags = !empty($basic) ? implode(',', array_column(json_decode($basic, true), 'value')) : '';

        // Handling key points input
        $key_points = !empty($_POST['key_points']) ? implode(',', array_filter($_POST['key_points'])) : '';

        // Handling prices
        if (empty($_POST['monthly_price']) || empty($_POST['yearly_price'])) {
            echo "3"; // Error code for missing price input
            exit();
        }

        $monthly_price = $_POST['monthly_price'];
        $yearly_price = $_POST['yearly_price'];

            try {
                $existing_plan = $h->table('plans')->select()->where('id', '=', $id)->fetchAll();
                $existing_plan_id = $existing_plan[0]['stripe_product_id'];

                if (!$existing_plan_id) {
                    throw new Exception("Stripe product ID not found for the selected plan.");
                }

                // Calculate final prices with discount (if applicable)
                $discount = $percentage ?? 0;
                $monthly_price_final = $monthly_price - ($monthly_price * $discount / 100);
                $yearly_price_final = $yearly_price - ($yearly_price * $discount / 100);

//                // Update product in Stripe
//                \Stripe\Product::update($stripe_product_id, ['name' => $name]);
//
//                // Create new monthly price in Stripe
//                $newMonthlyPriceObj = \Stripe\Price::create([
//                    'unit_amount' => round($monthly_price_final * 100),
//                    'currency' => 'usd',
//                    'recurring' => ['interval' => 'month'],
//                    'product' => $existing_plan_id,
//                ]);
//                // Create new yearly price in Stripe
//                $newYearlyPriceObj = \Stripe\Price::create([
//                    'unit_amount' => round($yearly_price_final * 100),
//                    'currency' => 'usd',
//                    'recurring' => ['interval' => 'year'],
//                    'product' => $existing_plan_id,
//                ]);

                \Stripe\Price::update($stripe_monthly_price_id, [
                    'active' => false,
                ]);

                // Create new monthly price in Stripe
                $planPriceCents = round($monthly_price_final * 100); // Convert to cents
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
                $yearlyPriceCents = round($yearly_price_final * 100); // Convert to cents
                $newYearlyPriceObj = \Stripe\Price::create([
                    'unit_amount' => $yearlyPriceCents,
                    'currency' => 'usd',
                    'recurring' => [
                        'interval' => 'year',
                    ],
                    'product' => $stripe_product_id,
                ]);
                $new_stripe_yearly_price_id = $newYearlyPriceObj->id;
                // Retrieve active subscriptions
                $subscriptions = \Stripe\Subscription::all(['limit' => 100, 'status' => 'active']);
                // Filter subscriptions by product and update them
                foreach ($subscriptions->data as $subscription) {
                    foreach ($subscription->items->data as $item) {
                        if ($item->price->product === $existing_plan_id) {
                            $new_price = ($item->price->recurring->interval === 'month') ? $newMonthlyPriceObj : $newYearlyPriceObj;
                            // Update subscription price
                            \Stripe\Subscription::update(
                                $subscription->id,
                                [
                                    'items' => [
                                        ['id' => $item->id, 'price' => $new_price->id],
                                    ],
                                    'proration_behavior' => 'none',
                                ]
                            );
                        }
                    }
                }
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
            } catch (PDOException $e) {
                echo "0"; // Database error
            } catch (\Stripe\Exception\ApiErrorException $e) {
                echo "Stripe Error: " . $e->getMessage(); // Stripe API error
            }

    } else {
        echo "2"; // Error code for missing plan name
    }


endif;
