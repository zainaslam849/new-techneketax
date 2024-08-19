<?php
require("config/env.php");
if($route == '/user/invoices'):
$seo = array(
    'title' => 'Invoices',
    'description' => 'CRM',
    'keywords' => 'Admin Panel'
);

echo $twig->render('user/invoice/list.twig', ['seo' => $seo]);
endif;

if($route == '/user/invoice/add'):
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!empty($_POST['firm_name'])) {
            if (!empty($_POST['firm_id']) && !empty($_POST['firm_name']) && !empty($_POST['firm_email']) && !empty($_POST['firm_address']) && !empty($_POST['firm_phone'])) {
                // firm Info
                $firm_id = $_POST['firm_id'];
                $firm_name = $_POST['firm_name'];
                $firm_email = $_POST['firm_email'];
                $firm_address = $_POST['firm_address'];
                $firm_phone = $_POST['firm_phone'];
            } else {
                echo json_encode(array("statusCode" => "2"));
                exit();
            }
            if (!empty($_POST['client_id']) && !empty($_POST['client_email']) && !empty($_POST['client_address']) && !empty($_POST['client_phone'])) {
                // client info
                $client_id = $_POST['client_id'];
                $client_email = $_POST['client_email'];
                $client_address = $_POST['client_address'];
                $client_phone = $_POST['client_phone'];
            } else {
                echo json_encode(array("statusCode" => "3"));
                exit();
            }
            if (!empty($_POST['invoice_number']) && !empty($_POST['invoice_date']) && !empty($_POST['due_date'])) {
                // invoice basic details
                $invoice_number = $_POST['invoice_number'];
                $invoice_date = $_POST['invoice_date'];
                $due_date = $_POST['due_date'];
            } else {
                echo json_encode(array("statusCode" => "4"));
                exit();
            }

            // products details
            if (!empty($_POST['item_des'])) {
                @$item_des = implode(',', $_POST['item_des']);
            } else {
                @$item_des = '';
            }
            if (!empty($_POST['additional_details'])) {
                @$additional_details = implode(',', $_POST['additional_details']);
            } else {
                @$additional_details = '';
            }
            if (!empty($_POST['price'])) {
                @$price = implode(',', $_POST['price']);
            } else {
                @$price = '';
            }
            if (!empty($_POST['quantity'])) {
                @$quantity = implode(',', $_POST['quantity']);
            } else {
                @$quantity = '';
            }
            if (!empty($_POST['amount_item'])) {
                @$amount_item = implode(',', $_POST['amount_item']);
            } else {
                @$amount_item = '';
            }
            // amount details
            $subtotal = $_POST['subtotal'];
            $discount_type = $_POST['discount_type'];
            $discount_amount = $_POST['discount_amount'];
            if ($_POST['discount_amount_total'] != 'undefined'){
                $discount_amount_total = $_POST['discount_amount_total'];
            }else{
                $discount_amount_total = 0;
            }


            $final_total = $_POST['final_total'];
            $invoice_detail_notes = $_POST['invoice_detail_notes'];
            $currency = $_POST['currency'];
            if($final_total > '0'){
                $invoiceStatus='unpaid';
            }else{
                $invoiceStatus='paid';
            }
            // Firm Bank details
            if (!empty($_POST['account_number']) && !empty($_POST['bank_name']) && !empty($_POST['swift_code']) && !empty($_POST['billing_country'])) {
                $account_number = $_POST['account_number'];
                $bank_name = $_POST['bank_name'];
                $swift_code = $_POST['swift_code'];
                $billing_country = $_POST['billing_country'];
            } else {
                echo json_encode(array("statusCode" => "6"));
                exit();
            }
            try {
                $insert = $h->insert('invoice')->values([
                    // firm Info
                    'firm_id' => $firm_id, 'firm_name' => $firm_name, 'firm_email' => $firm_email, 'firm_address' => $firm_address, 'firm_phone' => $firm_phone,
                    // client info
                    'client_id' => $client_id, 'client_email' => $client_email, 'client_address' => $client_address, 'client_phone' => $client_phone,
                    // invoice basic details
                    'invoice_number' => $invoice_number, 'invoice_date' => $invoice_date, 'due_date' => $due_date,
                    // products details
                    'item_des' => $item_des, 'additional_details' => $additional_details, 'price' => $price, 'quantity' => $quantity, 'amount_item' => $amount_item,
                    // products details
                    'account_number' => $account_number, 'bank_name' => $bank_name, 'swift_code' => $swift_code, 'billing_country' => $billing_country,
                    // amount details
                    'subtotal' => $subtotal, 'discount_type' => $discount_type, 'discount_amount' => $discount_amount, 'discount_amount_total' => $discount_amount_total, 'final_total' => $final_total, 'invoice_detail_notes' => $invoice_detail_notes, 'currency' => $currency,'status' => $invoiceStatus,
                ])->run();
                $send_email = $_POST['send_email'];
                $send_message = $_POST['send_message'];
                if (!empty($send_email) && !empty($send_message)) {
                    include "views/email-template/invoice.php";
                    mailSender($firm_email, $send_email, $firm_name . 'Send You An Invoice at - ' . $env['SITE_NAME'], $message, $mail);
                }
                echo json_encode(array("statusCode" => "1", "id" => "$insert"));
                exit();
            } catch (PDOException $e) {
                echo json_encode(array("statusCode" => "0", "id" => 'notFound'));
                exit();
            }
        }
    }else{
        $seo = array(
            'title' => 'Add New Invoice',
            'description' => 'CRM',
            'keywords' => 'Admin Panel'
        );

        $users = $h->table('users')->select()->where('firm_id', '=', $loginUserId)->where('type', '=', 'client')->fetchAll();
        $invoiceNumber = "#".rand(100000,1000000);
        $CountriesInfo = $h->table('countries')->select()->fetchAll();
        $firmPaymentInfo = $h->table('user_payment_method')->select()->where('user_id', '=', $loginUserId)->where('status', '=', 'primary')->fetchAll();
        $firmBillingInfo = $h->table('billing_address')->select()->where('user_id', '=', $loginUserId)->where('status', '=', 'primary')->fetchAll();
        $firmInfo = $h->table('users')->select()->where('id', '=', $loginUserId)->fetchAll();
        $CompanyInfos = $h->table('users')->select()->where('id', '=', $loginUserId)->fetchAll();
        $AdminInfos = $h->table('users')->select()->where('type', '=', 'admin')->fetchAll();
        if ($CompanyInfos[0]['white_labeling'] == 'yes'){
            $company_name = $CompanyInfos[0]['company_name'];
            $company_email = $CompanyInfos[0]['email'];
            $company_phone = $CompanyInfos[0]['phone'];
            $profile_image = $CompanyInfos[0]['profile_image'];
        }else{
            $company_name = $AdminInfos[0]['company_name'];
            $company_email = $AdminInfos[0]['email'];
            $company_phone = $AdminInfos[0]['phone'];
            $profile_image = $AdminInfos[0]['profile_image'];
        }
        $CompanyInfo = array(
            'company_name' => $company_name,
            'email' => $company_email,
            'phone' => $company_phone,
            'profile_image' => $profile_image
        );
        echo $twig->render('user/invoice/add.twig', ['seo' => $seo,'firmInfo' => $firmInfo,'CompanyInfo' => $CompanyInfo,'firmPaymentInfo' => $firmPaymentInfo,'firmBillingInfo' => $firmBillingInfo,'clients' => $users,'invoiceNumber' => $invoiceNumber,'countries' => $CountriesInfo]);
    }
endif;
if ($route == '/user/get_client_invoice'):
    if(isset($_POST['edit']) && !empty($_POST['edit'])){
        $id= $_POST['id'];
        $users = $h->table('users')->select()->where('id', '=', $id)->fetchAll();
        $billing_address = $h->table('billing_address')->select()->where('user_id', '=', $id)->where('status', '=', 'primary')->fetchAll();
        if (!empty($billing_address)){
            echo json_encode(array("userInfo" => $users, "billingAddress"=>$billing_address));
            exit();
        }else{
            echo json_encode(array("userInfo" => $users, "billingAddress"=>"2"));
            exit();
        }

    }
endif;
if($route == '/user/invoice/update/$id'):
        $seo = array(
            'title' => 'Update Invoice',
            'description' => 'CRM',
            'keywords' => 'Admin Panel'
        );
        $users = $h->table('users')->select()->where('firm_id', '=', $loginUserId)->where('type', '=', 'client')->fetchAll();
        $invoiceInfo = $h->table('invoice')->select()->where('id', '=', $id)->fetchAll();
        $firm_id = $invoiceInfo[0]['firm_id'];
        $client_id = $invoiceInfo[0]['client_id'];
        $firmInfo = $h->table('users')->select()->where('id', '=', $firm_id)->fetchAll();
        $clientInfo = $h->table('users')->select()->where('id', '=', $client_id)->fetchAll();
        $ProductDetails = [
            'item_des' => $invoiceInfo[0]['item_des'],
            'additional_details' => $invoiceInfo[0]['additional_details'],
            'price' => $invoiceInfo[0]['price'],
            'quantity' => $invoiceInfo[0]['quantity'],
            'amount_item' => $invoiceInfo[0]['amount_item']
        ];
    $CountriesInfo = $h->table('countries')->select()->fetchAll();
        echo $twig->render('user/invoice/update.twig', ['seo' => $seo,'clients' => $users,'invoiceInfo' => $invoiceInfo,'firmInfo' => $firmInfo,'clientInfo' => $clientInfo, 'ProductDetails' => $ProductDetails,'countries' => $CountriesInfo]);
endif;
if($route == '/user/invoice/update'):
if (!empty($_POST['firm_name'])){
    // firm Info
    $id = $_POST['id'];
    if (!empty($_POST['firm_id']) && !empty($_POST['firm_name']) && !empty($_POST['firm_email']) && !empty($_POST['firm_address']) && !empty($_POST['firm_phone'])) {
    $firm_id = $_POST['firm_id'];
    $firm_name = $_POST['firm_name'];
    $firm_email = $_POST['firm_email'];
    $firm_address = $_POST['firm_address'];
    $firm_phone = $_POST['firm_phone'];
    } else {
        echo "2";
        exit();
    }
    if (!empty($_POST['client_id']) && !empty($_POST['client_email']) && !empty($_POST['client_address']) && !empty($_POST['client_phone'])) {
    // client info
    $client_id = $_POST['client_id'];
    $client_email = $_POST['client_email'];
    $client_address = $_POST['client_address'];
    $client_phone = $_POST['client_phone'];
    } else {
        echo "3";
        exit();
    }
    if (!empty($_POST['invoice_number']) && !empty($_POST['invoice_date']) && !empty($_POST['due_date'])) {
    // invoice basic details
    $invoice_number = $_POST['invoice_number'];
    $invoice_date = $_POST['invoice_date'];
    $due_date = $_POST['due_date'];
    } else {
        echo "4";
        exit();
    }
    // products details
    if(!empty($_POST['item_des'])){
        @$item_des = implode(',',$_POST['item_des']);
    }else{
        @$item_des = '';
    }
    if(!empty($_POST['additional_details'])){
        @$additional_details = implode(',',$_POST['additional_details']);
    }else{
        @$additional_details = '';
    }
    if(!empty($_POST['price'])){
        @$price = implode(',',$_POST['price']);
    }else{
        @$price = '';
    }
    if(!empty($_POST['quantity'])){
        @$quantity = implode(',',$_POST['quantity']);
    }else{
        @$quantity = '';
    }
    if(!empty($_POST['amount_item'])){
        @$amount_item = implode(',',$_POST['amount_item']);
    }else{
        @$amount_item = '';
    }
    // amount details
    $subtotal = $_POST['subtotal'];
    $discount_type = $_POST['discount_type'];
    $discount_amount = $_POST['discount_amount'];
    if ($_POST['discount_amount_total'] != 'undefined'){
        $discount_amount_total = $_POST['discount_amount_total'];
    }else{
        $discount_amount_total = 0;
    }
    $final_total = $_POST['final_total'];
    $invoice_detail_notes = $_POST['invoice_detail_notes'];
    $currency = $_POST['currency'];
    if($final_total >'0'){
        $invoiceStatus='unpaid';
    }else{
        $invoiceStatus='paid';
    }
    // Firm Bank details
    if (!empty($_POST['account_number']) && !empty($_POST['bank_name']) && !empty($_POST['swift_code']) && !empty($_POST['billing_country'])) {
    $account_number = $_POST['account_number'];
    $bank_name = $_POST['bank_name'];
    $swift_code = $_POST['swift_code'];
    $billing_country = $_POST['billing_country'];
    } else {
        echo "5";
        exit();
    }
    try {

        $update = $h->update('invoice')->values([
            // firm Info
            'firm_id' => $firm_id,'firm_name' => $firm_name, 'firm_email' => $firm_email,'firm_address' => $firm_address, 'firm_phone' => $firm_phone,
            // client info
            'client_id' => $client_id, 'client_email' => $client_email, 'client_address' => $client_address,'client_phone' => $client_phone,
            // invoice basic details
            'invoice_number' => $invoice_number, 'invoice_date' => $invoice_date, 'due_date' => $due_date,
            // products details
            'item_des' => $item_des, 'additional_details' => $additional_details, 'price' => $price,'quantity' => $quantity,'amount_item' => $amount_item,
            // products details
            'account_number' => $account_number, 'bank_name' => $bank_name, 'swift_code' => $swift_code,'billing_country' => $billing_country,
            // amount details
            'subtotal' => $subtotal, 'discount_type' => $discount_type, 'discount_amount' => $discount_amount, 'discount_amount_total' => $discount_amount_total,'final_total' => $final_total,'invoice_detail_notes' => $invoice_detail_notes,'currency' => $currency,'status' => $invoiceStatus
        ])->where('id','=',$id)->run();
        $send_email = $_POST['send_email'];
        $send_message = $_POST['send_message'];
        if (!empty($send_email) && !empty($send_message)) {
            $insert = $_POST['id'];
            include "views/email-template/invoice.php";
            mailSender($firm_email, $send_email, $firm_name . 'Send You An Invoice at - ' . $env['SITE_NAME'], $message, $mail);
        }
        echo "1";
        exit();
    } catch (PDOException $e) {
        echo "0";
        exit();
    }
}
endif;
if($route == '/user/invoice/view/$invoice_id'):
    $seo = array(
        'title' => 'View Invoice',
        'description' => 'CRM',
        'keywords' => 'Admin Panel'
    );
    $invoiceInfo = $h->table('invoice')->select()->where('id', '=', $invoice_id)->fetchAll();
    $firm_id = $invoiceInfo[0]['firm_id'];
    $client_id = $invoiceInfo[0]['client_id'];
    $firmInfo = $h->table('users')->select()->where('id', '=', $firm_id)->fetchAll();
    $clientInfo = $h->table('users')->select()->where('id', '=', $client_id)->fetchAll();
    $ProductDetails = [
        'item_des' => $invoiceInfo[0]['item_des'],
        'additional_details' => $invoiceInfo[0]['additional_details'],
        'price' => $invoiceInfo[0]['price'],
        'quantity' => $invoiceInfo[0]['quantity'],
        'amount_item' => $invoiceInfo[0]['amount_item']
    ];
    echo $twig->render('user/invoice/view_invoice.twig', ['seo' => $seo,'invoiceInfo' => $invoiceInfo,'firmInfo' => $firmInfo,'clientInfo' => $clientInfo, 'ProductDetails' => $ProductDetails]);
endif;
if($route == '/invoice/view/$invoice_id'):
    $seo = array(
        'title' => 'View Invoice',
        'description' => 'CRM',
        'keywords' => 'Admin Panel'
    );
    $invoiceInfo = $h->table('invoice')->select()->where('id', '=', $invoice_id)->fetchAll();
    $firm_id = $invoiceInfo[0]['firm_id'];
    $client_id = $invoiceInfo[0]['client_id'];
    $firmInfo = $h->table('users')->select()->where('id', '=', $firm_id)->fetchAll();
    $clientInfo = $h->table('users')->select()->where('id', '=', $client_id)->fetchAll();
    $ProductDetails = [
        'item_des' => $invoiceInfo[0]['item_des'],
        'additional_details' => $invoiceInfo[0]['additional_details'],
        'price' => $invoiceInfo[0]['price'],
        'quantity' => $invoiceInfo[0]['quantity'],
        'amount_item' => $invoiceInfo[0]['amount_item']
    ];
    echo $twig->render('user/invoice/view_invoice_email.twig', ['seo' => $seo,'invoiceInfo' => $invoiceInfo,'firmInfo' => $firmInfo,'clientInfo' => $clientInfo, 'ProductDetails' => $ProductDetails]);
endif;

if($route == '/stripe/pay-invoice'):
    require 'vendor/autoload.php';
    $invoiceID = $_POST['invoiceID'];
    $invoiceInfo = $h->table('invoice')->select()->where('id', '=', $invoiceID)->fetchAll();
    $token = $_POST['stripeToken'];
    $finalCent= $invoiceInfo[0]['final_total'] * 100;
    try {
        $stripe = new \Stripe\StripeClient($Stripe_secret_key);
        $charge = $stripe->charges->create([
            'amount' => $finalCent,
            'currency' => 'usd',
            'description' => "Invoice #" . rand(99999, 999999999),
            'source' => $token,
        ]);
        $id = $charge['id'];
        $amount = $charge['amount'];
        $balance_transaction = $charge['balance_transaction'];
        $currency = $charge['currency'];
        $status = $charge['status'];
        if($status == 'succeeded'){
            $insert = $h->update('invoice')->values([ 'transaction_id' => $balance_transaction,'status' => 'paid'])->where('id','=',$invoiceID)->run();
            if ($insert) {
                $insert = $h->insert('transactions')->values([ 'transaction_id' => $balance_transaction,'invoice_id' => $invoiceID,'client_id' => $invoiceInfo[0]['client_id'],'price' => $invoiceInfo[0]['final_total'],'pay_with'=>'stripe'])->run();
//                include "views/email-template/invoice_paid.php";
//                mailSender($env['SENDER_EMAIL'],$email,'Congratulation  - '.$env['SITE_NAME'],$message,$mail);
                http_response_code(200);
                echo json_encode(array("statusCode" => 200, "message"=>"Invoice payment processed successfully"));
            } else {
                http_response_code(202);
                echo json_encode(array("statusCode" => 202, "message"=>"Something Went Wrong!"));
            }
        }else{
            http_response_code(202);
            echo json_encode(array("statusCode" => 202, "message"=>"Something Went Wrong!"));
        }
    }catch (\Stripe\Exception\CardException $e) {
        http_response_code(202);
        echo json_encode(array("statusCode" => 202, "message"=>$e->getMessage()));
    } catch (Exception $e) {
        http_response_code(202);
        echo json_encode(array("statusCode" => 202, "message"=>$e->getMessage()));
    }
endif;