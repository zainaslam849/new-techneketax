<?php
require("config/env.php");

$currentDateTime = date('Y-m-d H:i:s');
// email queue
$campaign_statuses = $h->table('campaign')->select()->where('status', '=', 'pending')->fetchAll();
if ($campaign_statuses){
foreach ($campaign_statuses as $campaign_status) {
    $world_time_zonesDetail = $h->table('world_time_zones')->select()->where('id', '=', $campaign_status['timezone'])->fetchAll();
    $timezone = $world_time_zonesDetail[0]['time_zone'];
    $campaignDateTime = new DateTime($campaign_status['date'], new DateTimeZone($timezone));
    $formattedDate = $campaignDateTime->format('Y-m-d H:i:s');
    $currentDateTime = new DateTime('now', new DateTimeZone($timezone));
    if ($currentDateTime >= $campaignDateTime) {
        $campaign_listDatas= $h->table('campaign_list')->select()->where('id', '=', $campaign_status['list_id'])->fetchAll();
        if ($campaign_listDatas[0]['list_type'] == 'email'){
            $campaign_list_detailDatas= $h->table('campaign_list_detail')->select()->where('campaign_list_id', '=', $campaign_status['list_id'])->fetchAll();
            if (!empty($campaign_list_detailDatas)) {
                foreach ($campaign_list_detailDatas as $campaign_list_detailData) {
                    $email_queue = $h->insert('email_queue')->values([
                        'campaign_id' => $campaign_status['id'],
                        'recipient_email' => $campaign_list_detailData['contact'],
                    ])->run();

                }
                $campaign_update = $h->update('campaign')->values([
                    'status' => 'start',
                ])->where('id','=',$campaign_status['id'])->run();
            }
        }else{
            $campaign_list_detailDatas= $h->table('campaign_list_detail')->select()->where('campaign_list_id', '=', $campaign_status['list_id'])->fetchAll();
            if (!empty($campaign_list_detailDatas)) {
                foreach ($campaign_list_detailDatas as $campaign_list_detailData) {
                    $phone_queue = $h->insert('phone_queue')->values([
                        'campaign_id' => $campaign_status['id'],
                        'recipient_phone' => $campaign_list_detailData['contact'],
                    ])->run();
                }
                        $campaign_update = $h->update('campaign')->values([
                            'status' => 'start',
                        ])->where('id','=',$campaign_status['id'])->run();
            }
        }
    }
}
}

$email_queueDetails = $h->table('email_queue')->select()->where('status', '=', 'pending')->where('attempts', '<=', '3')->limit(10)->fetchAll();
if (!empty($email_queueDetails)){
foreach ($email_queueDetails as $email_queueDetail) {
    $campaign_id = $email_queueDetail['campaign_id'];
    $attempts = $email_queueDetail['attempts'];
    $campaignDetails = $h->table('campaign')->select()->where('id', '=', $campaign_id)->fetchAll();
    $firm_id = $campaignDetails[0]['firm_id'];
    if ($campaignDetails[0]['status'] == 'start'){
    if (!empty($firm_id)){
        $companyInfo = $h->table('users')->select()->where('id', '=', $firm_id)->fetchAll();
        if ($companyInfo[0]['type'] == 'firm' && $companyInfo[0]['white_labeling'] == 'yes'){
            @$company_name =  @$companyInfo[0]['company_name'];
            @$company_phone =  @$companyInfo[0]['phone'];
            @$company_email =  @$companyInfo[0]['email'];
            @$company_address =  @$companyInfo[0]['address'];
            @$company_linkedin =  @$companyInfo[0]['linkedin'];
            @$company_tweet =  @$companyInfo[0]['tweet'];
            @$company_facebook =  @$companyInfo[0]['facebook'];
            @$company_github =  @$companyInfo[0]['github'];
            @$imgUrl = $env['APP_URL'].'uploads/profile'.@$companyInfo[0]['company_image'];
        }else{
            $AdminInfo = $h->table('users')->select()->where('type', '=', 'admin')->fetchAll();
            @$company_name =  @$AdminInfo[0]['fname'].' '.@$AdminInfo[0]['lname'];
            @$company_phone =  @$AdminInfo[0]['phone'];
            @$company_email =  @$AdminInfo[0]['email'];
            @$company_address =  @$AdminInfo[0]['address'];
            @$company_linkedin =  @$AdminInfo[0]['linkedin'];
            @$company_tweet =  @$AdminInfo[0]['tweet'];
            @$company_facebook =  @$AdminInfo[0]['facebook'];
            @$company_github =  @$AdminInfo[0]['github'];
            @$imgUrl = $env['APP_URL'].'assets/techneketax-black.png';
        }
    }else{
        $AdminInfo = $h->table('users')->select()->where('type', '=', 'admin')->fetchAll();
        @$company_name =  @$AdminInfo[0]['fname'].' '.@$AdminInfo[0]['lname'];
        @$company_phone =  @$AdminInfo[0]['phone'];
        @$company_email =  @$AdminInfo[0]['email'];
        @$company_address =  @$AdminInfo[0]['address'];
        @$company_linkedin =  @$AdminInfo[0]['linkedin'];
        @$company_tweet =  @$AdminInfo[0]['tweet'];
        @$company_facebook =  @$AdminInfo[0]['facebook'];
        @$company_github =  @$AdminInfo[0]['github'];
        @$imgUrl = $env['APP_URL'].'assets/techneketax-black.png';
    }

        $email_logs_check = $h->table('email_logs')->select()->where('campaign_id', '=', $email_queueDetail['campaign_id'])->where('recipient_email', '=', $email_queueDetail['recipient_email'])->where('status', '=', 'sent');
   if($email_logs_check->count() >= 1){
       $email_response =true;
   }else{
          if ($campaignDetails[0]['template_type'] == 'your_email_template'){
              $emailTemplate = $h->table('email_template')->select()->where('id', '=', $campaignDetails[0]['template_id'])->fetchAll();
              $subject = $campaignDetails[0]['subject'];
              $htmlContent = base64_decode($emailTemplate[0]['content']);
              $decodedContent = base64_decode($htmlContent);
              $message = mb_convert_encoding($decodedContent, 'UTF-8', 'auto');
              $email_response = mailSender1(@$company_email, $email_queueDetail['recipient_email'], $subject, $message, $mail);
          }else{
              $subject = $campaignDetails[0]['subject'];
              $body = $campaignDetails[0]['body'];
              include "views/email-template/campaign.php";
              $email_response = mailSender1(@$company_email, $email_queueDetail['recipient_email'], $subject, $message, $mail);
          }
   }
   if ($email_response){
       $delete_email_queue =  $h->table('email_queue')->delete()->where('id', $email_queueDetail['id'])->run();
       $email_logs = $h->insert('email_logs')->values([
           'campaign_id' => $campaign_id,
           'recipient_email' => $email_queueDetail['recipient_email'],
           'sent_at' => $currentDateTime,
           'status' => 'sent',
       ])->run();
   }else{

       $email_queue_update = $h->update('email_queue')->values([
           'attempts' => $attempts + 1,
       ])->where('id','=',$email_queueDetail['id'])->run();
       $email_logs = $h->insert('email_logs')->values([
           'campaign_id' => $campaign_id,
           'recipient_email' => $email_queueDetail['recipient_email'],
           'sent_at' => $currentDateTime,
           'status' => 'failed',
       ])->run();
   }

    }
}
}
// phone queue
$phone_queueDetails = $h->table('phone_queue')->select()->where('status', '=', 'pending')->where('attempts', '<=', '3')->limit(50)->fetchAll();
if (!empty($phone_queueDetails)){
    foreach ($phone_queueDetails as $phone_queueDetail) {
        $campaign_id = $phone_queueDetail['campaign_id'];
        $attempts = $phone_queueDetail['attempts'];
        $campaignPhoneDetails = $h->table('campaign')->select()->where('id', '=', $campaign_id)->fetchAll();
        $firm_id = $campaignPhoneDetails[0]['firm_id'];
        if ($campaignPhoneDetails[0]['status'] == 'start'){
            $subject = $campaignPhoneDetails[0]['subject'];
            $body = $campaignPhoneDetails[0]['body'];
            $phone_logs_check = $h->table('phone_logs')->select()->where('campaign_id', '=', $phone_queueDetail['campaign_id'])->where('status', '=', 'sent');
            if($phone_logs_check->count() >= 1){
                $phone_response =true;
            }else{
                $phone_response =  sendSMS($phone_queueDetail['recipient_phone'],$subject.''. $body);
            }
            if ($phone_response){
                $delete_phone_queue =  $h->table('phone_queue')->delete()->where('id', $phone_queueDetail['id'])->run();
                $phone_logs = $h->insert('phone_logs')->values([
                    'campaign_id' => $campaign_id,
                    'recipient_phone' => $phone_queueDetail['recipient_phone'],
                    'sent_at' => $currentDateTime,
                    'status' => 'sent',
                ])->run();
            }else{
                $phone_queue_update = $h->update('phone_queue')->values([
                    'attempts' => $attempts + 1,
                ])->where('id','=',$phone_queueDetail['id'])->run();
                $phone_logs = $h->insert('phone_logs')->values([
                    'campaign_id' => $campaign_id,
                    'recipient_phone' => $phone_queueDetail['recipient_phone'],
                    'sent_at' => $currentDateTime,
                    'status' => 'failed',
                ])->run();
            }
        }
    }
}
// campaign status change
$campaigns = $h->table('campaign')->select()->fetchAll();
if (!empty($campaigns)){
    foreach ($campaigns as $campaign) {
        $campaign_list = $h->table('campaign_list')->select()->where('id','=',$campaign['list_id'])->fetchAll();
        if ($campaign_list[0]['list_type'] == 'email'){
            $check = $h->table('email_queue')->select()->where('campaign_id', '=', $campaign['id'])->where('status', '=', 'pending');
            $email_logs = $h->table('email_logs')->select()->where('campaign_id', '=', $campaign['id']);
            if ($check->count() == 0 && $email_logs->count() >= 1){
                $campaign_update = $h->update('campaign')->values([
                    'status' => 'ended',
                ])->where('id','=',$campaign['id'])->run();
            }
        }else{
            $check = $h->table('phone_queue')->select()->where('campaign_id', '=', $campaign['id'])->where('status', '=', 'pending');
            $phone_logs = $h->table('phone_logs')->select()->where('campaign_id', '=', $campaign['id']);
            if ($check->count() == 0 && $phone_logs->count() >= 1){
                $campaign_update = $h->update('campaign')->values([
                    'status' => 'ended',
                ])->where('id','=',$campaign['id'])->run();
            }
        }

    }
}
// delete email_queue after three attempts
$email_queueDetails_attempts = $h->table('email_queue')->select()->where('attempts', '>', '3')->fetchAll();
if (!empty($email_queueDetails_attempts)){
    foreach ($email_queueDetails_attempts as $email_queueDetails_attempt) {
        $attempts  = $email_queueDetails_attempt['attempts'];
            $delete_email_queue_attempts = $h->table('email_queue')->delete()->where('id', $email_queueDetails_attempt['id'])->run();
    }
}
// delete phone_queue after three attempts
$phone_queueDetails_attempts = $h->table('phone_queue')->select()->where('attempts', '>', '3')->fetchAll();
if (!empty($phone_queueDetails_attempts)){
    foreach ($phone_queueDetails_attempts as $phone_queueDetails_attempt) {
        $attempts  = $phone_queueDetails_attempt['attempts'];
            $delete_phone_queue_attempts = $h->table('phone_queue')->delete()->where('id', $phone_queueDetails_attempt['id'])->run();
    }
}
