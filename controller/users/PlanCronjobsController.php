<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("config/env.php");
require 'vendor/autoload.php';
$check_plan_status = $h->table('subscriptions')->select()->where('plan_end_date', '<', $current_date)->where('status', '=', 'successful')->fetchAll();
if (!empty($check_plan_status)){
  $id =  $check_plan_status['id'];
    $res = $h->table('subscriptions')->update(['status' => 'expire'])->where('id', '=', $id)->run();
}


\Stripe\Stripe::setApiKey($Admin_Stripe_secret_key);
$subscriptions = $h->table('subscriptions')->select()->where('plan_end_date', '<', $current_date)->where('status', '=', 'expire')->fetchAll();
if (!empty($subscriptions)) {
    foreach ($subscriptions as $subscription) {
        $subscriptions_id = $subscription['id'];
        $subscriptionId = $subscription['stripe_subscription_id'];

        if (!empty($subscriptionId)) {
            try {
                // Fetch all invoices for the subscription
                $invoices = \Stripe\Invoice::all([
                    'subscription' => $subscriptionId,
                ]);

                $allInvoicesPaid = true;
                $mostRecentInvoicePaid = false;

                foreach ($invoices->data as $invoice) {
                    $check_transaction = $h->table('admin_transactions')->select()->where('subscription_id', '=', $subscriptionId)->where('transaction_id', '=', $invoice->id)->fetchAll();

                    if (empty($check_transaction)) {
                        $invoice_amount=$invoice->amount_due /100;
                        // Insert the invoice into the transactions table if it doesn't already exist
                        $insert = $h->insert('admin_transactions')->values([
                            'subscription_id' => $subscriptionId,
                            'transaction_id' => $invoice->id,
                            'plan_id' => $subscription['plan_id'],
                            'price' => $invoice_amount,
                            'pay_with' => 'stripe'
                        ])->run();
                    }

                    // Check if the invoice is paid or not
                    if ($invoice->status !== 'paid') {
                        $allInvoicesPaid = false;
                    }

                    // Track the payment status of the most recent invoice
                    if ($invoice === reset($invoices->data)) {
                        $mostRecentInvoicePaid = ($invoice->status === 'paid');
                    }
                }

                // Update the subscription status based on the most recent invoice
                if ($mostRecentInvoicePaid) {
                    $res = $h->table('subscriptions')->update(['status' => 'successful'])->where('id', '=', $subscriptions_id)->run();
                } else {
                    $res = $h->table('subscriptions')->update(['status' => 'expire'])->where('id', '=', $subscriptions_id)->run();
                }

                // Update the current period end in your database
                $current_period_end = $invoices->data[0]->period_end; // Use the most recent invoice's period end
                $next_invoice_date = (new DateTime())->setTimestamp($current_period_end);
                $res = $h->table('subscriptions')->update(['plan_end_date' => $next_invoice_date->format('Y-m-d H:i:s')])->where('id', '=', $subscriptions_id)->run();

            } catch (\Stripe\Exception\ApiErrorException $e) {
                echo 'Error fetching invoices: ' . $e->getMessage() . PHP_EOL;
            }
        }
    }
}

