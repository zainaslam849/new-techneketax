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
    $firm_Id = $firmQuery[0]['id'];

    $firmName = $firmQuery[0]['fname']. ' ' . $firmQuery[0]['lname'];
    foreach ($clientEmails as $clientEmail) {
        if (!empty($firm_Id)){
            $companyInfo = $h->table('users')->select()->where('id', '=', $firm_Id)->fetchAll();
            if ($companyInfo[0]['type'] == 'firm' && $companyInfo[0]['white_labeling'] == 'yes'){
                @$company_name =  @$companyInfo[0]['company_name'];
                @$company_phone =  @$companyInfo[0]['phone'];
                @$company_email =  @$companyInfo[0]['email'];
                @$imgUrl = $env['APP_URL'].'uploads/profile'.@$companyInfo[0]['company_image'];
            }else{
                $AdminInfo = $h->table('users')->select()->where('type', '=', 'admin')->fetchAll();
                @$company_name =  @$AdminInfo[0]['fname'].' '.@$AdminInfo[0]['lname'];
                @$company_phone =  @$AdminInfo[0]['phone'];
                @$company_email =  @$AdminInfo[0]['email'];
                @$imgUrl = $env['APP_URL'].'assets/techneketax-black.png';
            }
        }else{
            $AdminInfo = $h->table('users')->select()->where('type', '=', 'admin')->fetchAll();
            @$company_name =  @$AdminInfo[0]['fname'].' '.@$AdminInfo[0]['lname'];
            @$company_phone =  @$AdminInfo[0]['phone'];
            @$company_email =  @$AdminInfo[0]['email'];
            @$imgUrl = $env['APP_URL'].'assets/techneketax-black.png';
        }
        include "views/email-template/send_appointment_notification.php";
        mailSender($firmEmail, $clientEmail,  'You have just one hour left in your appointment with '.$company_name.' at - ' . $env['SITE_NAME'], $message, $mail);
    }
     $h->update('appointment')->values(['email_sent' => 1])->where('id','=',$appointment['id'])->run();
}
echo "Emails sent successfully.";
