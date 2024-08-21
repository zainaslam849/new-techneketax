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
    $formattedDate = $date->format('l, d F Y, h:i a');
    $firmQuery = $h->table('users')->select()->where('id', '=', $firmId)->fetchAll();
    $firmEmail = $firmQuery[0]['email'];
    $firm_Id = $firmQuery[0]['id'];
        $companyInfoFirm = $h->table('users')->select()->where('id', '=', $firm_Id)->fetchAll();
        if ($companyInfoFirm[0]['type'] == 'firm' && $companyInfoFirm[0]['white_labeling'] == 'yes'){
            @$company_name =  @$companyInfoFirm[0]['company_name'];
            @$company_phone =  @$companyInfoFirm[0]['phone'];
            @$company_email =  @$companyInfoFirm[0]['email'];
            @$company_address =  @$companyInfoFirm[0]['address'];
            @$imgUrl = $env['APP_URL'].'uploads/profile'.@$companyInfoFirm[0]['company_image'];
        }else{
            $AdminInfo = $h->table('users')->select()->where('type', '=', 'admin')->fetchAll();
            @$company_name =  @$AdminInfo[0]['fname'].' '.@$AdminInfo[0]['lname'];
            @$company_phone =  @$AdminInfo[0]['phone'];
            @$company_email =  @$AdminInfo[0]['email'];
            @$company_address =  @$AdminInfo[0]['address'];
            @$imgUrl = $env['APP_URL'].'assets/techneketax-black.png';
        }

    sendSMS($companyInfoFirm[0]['phone'],'An Hour left in meeting \n\n Title : '.$appointment['title'].' \n Date & Time : '.$formattedDate.' \n Purpose of this appointment is '.$appointment['purpose'].'.');

    include "views/email-template/send_appointment_notification.php";
    mailSender($env['SENDER_EMAIL'], $firmEmail,  'You have just one hour left in your appointment at - ' . $env['SITE_NAME'], $message, $mail);
    foreach ($clientIds as $clientId) {
        $clientQuery =  $h->table('users')->select()->where('id', '=', $clientId)->fetchAll();
        $clientEmail = $clientQuery[0]['email'];
        if (!empty($clientId)){
            $companyInfo = $h->table('users')->select()->where('id', '=', $clientId)->fetchAll();
            if ($companyInfo[0]['type'] == 'firm' && $companyInfo[0]['white_labeling'] == 'yes'){
                @$company_name =  @$companyInfo[0]['company_name'];
                @$company_phone =  @$companyInfo[0]['phone'];
                @$company_email =  @$companyInfo[0]['email'];
                @$company_address =  @$companyInfo[0]['address'];
                @$imgUrl = $env['APP_URL'].'uploads/profile'.@$companyInfo[0]['company_image'];
            }else{
                $AdminInfo = $h->table('users')->select()->where('type', '=', 'admin')->fetchAll();
                @$company_name =  @$AdminInfo[0]['fname'].' '.@$AdminInfo[0]['lname'];
                @$company_phone =  @$AdminInfo[0]['phone'];
                @$company_email =  @$AdminInfo[0]['email'];
                @$company_address =  @$AdminInfo[0]['address'];
                @$imgUrl = $env['APP_URL'].'assets/techneketax-black.png';
            }
        }else{
            $AdminInfo = $h->table('users')->select()->where('type', '=', 'admin')->fetchAll();
            @$company_name =  @$AdminInfo[0]['fname'].' '.@$AdminInfo[0]['lname'];
            @$company_phone =  @$AdminInfo[0]['phone'];
            @$company_email =  @$AdminInfo[0]['email'];
            @$company_address =  @$AdminInfo[0]['address'];
            @$imgUrl = $env['APP_URL'].'assets/techneketax-black.png';
        }
        sendSMS($companyInfo[0]['phone'],'An Hour left in meeting with '.$company_name.'\n\n Title : '.$appointment['title'].' \n Date & Time : '.$formattedDate.' \n Purpose of this appointment is '.$appointment['purpose'].'.');

        include "views/email-template/send_appointment_notification.php";
        mailSender($env['SENDER_EMAIL'], $clientEmail,  'You have just one hour left in your appointment with '.$company_name.' at - ' . $env['SITE_NAME'], $message, $mail);

    }
    $h->update('appointment')->values(['email_sent' => 1])->where('id','=',$appointments['id'])->run();
}
echo "Emails sent successfully.";
