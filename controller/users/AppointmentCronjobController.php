<?php
require("config/env.php");
$currentTime = date('Y-m-d H:i:s');
$oneHourLater = date('Y-m-d H:i:s', strtotime('+1 hour'));
$appointments = $h->table('appointment')->select()->where('date', '>=', $currentTime)
    ->andWhere('date', '<=', $oneHourLater)->andWhere('email_sent', '=', 0)->fetchAll();
foreach ($appointments as $appointment) {
    $clientIds = explode(',', $appointment['client_id']);
    $firmId = $appointment['firm_id'];
    $date = new DateTime($appointment['date']);
    $formattedDate = $date->format('l, d F Y, h:i a');
    $clientEmails = [];
    foreach ($clientIds as $clientId) {
        $clientQuery =  $h->table('users')->select()->where('id', '=', $clientId)->fetchAll();
        $clientEmail = $clientQuery[0]['email'];
        if ($clientEmail) {
            $clientEmails[] = $clientEmail;
        }
    }
    $firmQuery = $h->table('users')->select()->where('id', '=', $firmId)->fetchAll();
    $firmEmail = $firmQuery[0]['email'];
    $firmName = $firmQuery[0]['fname']. ' ' . $firmQuery[0]['lname'];
    foreach ($clientEmails as $clientEmail) {
        include "views/email-template/send_appointment_notification.php";
        mailSender($firmEmail, $clientEmail, $firmName . 'You have just one hour left in your appointment with '.$firmName.' at - ' . $env['SITE_NAME'], $message, $mail);
    }
     $h->update('appointment')->values(['email_sent' => 1])->where('id','=',$appointment['id'])->run();
}
echo "Emails sent successfully.";
