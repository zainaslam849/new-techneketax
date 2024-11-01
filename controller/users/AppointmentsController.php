<?php
require("config/env.php");
use Carbon\Carbon;

if($route == '/user/appointments'):
    $seo = array(
        'title' => 'Appointments',
        'description' => 'CRM',
        'keywords' => 'Admin Panel'
    );


    if($loginUserType == 'firm'){
        $usersInfo = $h->table('users')->select()->where('firm_id', '=', $loginUserId)->fetchAll();
        $appointments = $h->table('appointment')
            ->select()
            ->where('firm_id', '=', $loginUserId)
            ->fetchAll();
    }else{
        $users = $h->table('users')->select()->where('id', '=', $loginUserId)->fetchAll();
        $firm_id = $users[0]['firm_id'];
        $usersInfo = $h->table('users')->select()->where('firm_id', '=', $firm_id)->fetchAll();
        $firm_appointments = $h->table('appointment')
            ->select()
            ->where('firm_id', '=', $firm_id)
            ->fetchAll();

        $appointments = [];

        if (!empty($firm_appointments)) {
            foreach ($firm_appointments as $firm_appointment) {
                $client_ids = $firm_appointment['client_id'];

                // Explode the comma-separated string into an array
                $clientIdsArray = explode(',', $client_ids);

                // Check if $loginUserId exists in the array
                if (in_array($loginUserId, $clientIdsArray)) {
                    // If match found, add to matched appointments array
                    $appointments[] = $firm_appointment;
                }
            }
        }

    }
    $TodayDate =date("Y-m-d H:i:s");
    echo $twig->render('user/appointment/index.twig', ['seo' => $seo,'clients' => $usersInfo,'todayDate' => $TodayDate,'appointments' => $appointments]);
endif;



if ($route == '/user/get_appointment'):
    if($loginUserType == 'firm'){
        $appointments = $h->table('appointment')
            ->select()
            ->where('firm_id', '=', $loginUserId)
            ->fetchAll();
    }else{
        $users = $h->table('users')->select()->where('id', '=', $loginUserId)->fetchAll();
        $firm_id = $users[0]['firm_id'];

        $firm_appointments = $h->table('appointment')
            ->select()
            ->where('firm_id', '=', $firm_id)
            ->fetchAll();

        $appointments = [];

        if (!empty($firm_appointments)) {
            foreach ($firm_appointments as $firm_appointment) {
                $client_ids = $firm_appointment['client_id'];

                // Explode the comma-separated string into an array
                $clientIdsArray = explode(',', $client_ids);

                // Check if $loginUserId exists in the array
                if (in_array($loginUserId, $clientIdsArray)) {
                    // If match found, add to matched appointments array
                    $appointments[] = $firm_appointment;
                }
            }
        }

    }

    echo json_encode($appointments);
    exit();
endif;
if ($route == '/user/get_users'):
    $clientIdsString = $_GET['client_id'];
    $appointment_id = $_GET['appointment_id'];
    $appointment = $h->table('appointment')->select()->where('id', $appointment_id)->fetchAll();
    $appointment_date =$appointment[0]['date'];
    $carbonDate = Carbon::parse($appointment_date);
    $formattedDate = $carbonDate->format('l, F j - g:i a');
    $UserInfo= [];
    $clientIdsArray = explode(',', $clientIdsString);
    foreach ($clientIdsArray as $clients) {
        $UserInfoArray = $h->table('users')
            ->select()
            ->where('id', $clients)
            ->fetchAll();
        $UserInfo []= $UserInfoArray;
    }

    $output = '';
    $output .= '  
<div class="text-center mb-8">
    <h1 class="mb-3">' . htmlspecialchars($appointment[0]['title'], ENT_QUOTES, 'UTF-8') . '</h1>
    <div class="text-muted fw-bold fs-5">' . htmlspecialchars($formattedDate, ENT_QUOTES, 'UTF-8') . '</div>
    <a href="/meet/' . htmlspecialchars($appointment[0]['jitsi_link'], ENT_QUOTES, 'UTF-8') . '" class="btn btn-primary fw-bolder w-100 mt-5">
        Join with Techneke Meet
    </a>
    <a class="btn btn-info fw-bolder w-100 mt-5"   href="' . $env['APP_URL'] . 'user/add-to-calendar?event_name=' . urlencode($appointment[0]['title']) .
        '&start_date=' . $appointment_date .'">
        Add to Calendar
    </a>
</div>';

// Check if $UserInfo is not empty and contains at least one non-empty sub-array
    $hasValidUserInfo = false;

    foreach ($UserInfo as $user) {
        if (!empty($user)) {
            $hasValidUserInfo = true;
            break;
        }
    }

    if ($hasValidUserInfo) {
        $output .= '<div class="mb-5">
                    <div class="fs-6 fw-bold mb-2">Meeting Members</div>
                    <div class="mh-300px scroll-y me-n7 pe-7">';

        foreach ($UserInfo as $user) {
            if (!empty($user)) {
                $email = $user[0]['email'];
                if ($loginUserType != 'client') {
                    $maskedEmail = $user[0]['email'];
                } else {
                    $maskedEmail = substr($email, 0, 3) . str_repeat('*', strlen($email) - 6) . substr($email, -3);
                }

                $output .= '   
                    <!--begin::User-->
                    <div class="d-flex flex-stack py-4 border-bottom border-gray-300 border-bottom-dashed">
                        <!--begin::Details-->
                        <div class="d-flex align-items-center">
                            <!--begin::Avatar-->
                            <div class="symbol symbol-35px symbol-circle">';

                if (empty($user[0]['profile_image']) || $user[0]['profile_image'] == 'null') {
                    $output .= '<img alt="Pic" src="' . $env['APP_URL'] . 'uploads/profile/avatar.png" />';
                } else {
                    $output .= '<img alt="Pic" src="' . $env['APP_URL'] . 'uploads/profile/' . $user[0]['profile_image'] . '" />';
                }

                $output .= '            </div>
                            <!--end::Avatar-->
                            <!--begin::Details-->
                            <div class="ms-5">
                                <a href="#" class="fs-5 fw-bolder text-gray-900 text-hover-primary mb-2">'
                    . $user[0]['fname'] . ' ' . $user[0]['lname'] . '</a>
                                <div class="fw-bold text-muted">' . $maskedEmail . '</div>
                            </div>
                            <!--end::Details-->
                        </div>
                    </div>
                    <!--end::User-->';
            }
        }

        $output .= '</div></div>';
    }

    echo $output;
    exit();
endif;



if($route == '/user/add/appointments'):
    if (!empty($_POST['title'])) {
        if (!empty($_POST['title']) && !empty($_POST['dateTime']) && !empty($_POST['purpose'])) {
            @$title = $_POST['title'];
            @$dateTime = $_POST['dateTime'];

            @$purpose = $_POST['purpose'];
            @$jitsi_link = random_strings(10);
        }else{
            echo "2";
            exit();
        }
        if (!empty($_POST['client_id'])) {
            @$client_ids = implode(', ', $_POST['client_id']);
        }else{
            @$client_ids = null;
        }
        try {
            if($loginUserType == 'firm') {
                $insert = $h->insert('appointment')->values([
                    'firm_id' => $loginUserId,
                    'title' => $title,
                    'date' => $dateTime,
                    'client_id' => $client_ids,
                    'purpose' => $purpose,
                    'jitsi_link' => $jitsi_link
                ])->run();
                if (!empty($_POST['client_id'])) {
                    foreach ($_POST['client_id'] as $client_id) {
                        $ClientInfo = $h->table('users')->select()->where('id', '=', $client_id)->fetchAll();
                        $date = new DateTime($dateTime);
                        $formattedDate = $date->format('l, d F Y, h:i a');
                        if (!empty($loginUserId)){
                            $companyInfo = $h->table('users')->select()->where('id', '=', $loginUserId)->fetchAll();
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
                        sendSMS($ClientInfo[0]['phone'],'Your Appointment Has Been Scheduled - ' .$title.'\n\n Title : '.$title.' \n Date & Time : '.$formattedDate.' \n Purpose of this appointment is '.$purpose.'.');
                        include "views/email-template/add_appointment.php";
                        mailSender($_SESSION['users']['email'], $ClientInfo[0]['email'], 'Your Appointment Has Been Scheduled - ' .$title, $message, $mail);
                    }
                }
                echo "1";
                exit();
            }else{
                echo "3";
                exit();
            }
        } catch (PDOException $e) {
            echo "0";
            exit();
        }
    }else{
        echo "2";
        exit();
    }
endif;


if($route == '/user/update/appointments'):
    if (!empty($_POST['title'])) {
        @$id = $_POST['id'];
        @$title = $_POST['title'];
        @$dateTime = $_POST['dateTime'];
        if (!empty($_POST['client_id'])) {
            @$client_ids = implode(', ', $_POST['client_id']);
        }else{
            @$client_ids = null;
        }
        @$purpose = $_POST['purpose'];
        try {
            if($loginUserType == 'firm') {
                $update = $h->update('appointment')->values(['title' => $title, 'date' => $dateTime, 'client_id' => $client_ids, 'purpose' => $purpose])->where('id', '=', $id)->run();
                $AppointmentInfo = $h->table('appointment')->select()->where('id', '=', $id)->fetchAll();

                if (!empty($_POST['client_id'])) {
                    foreach ($_POST['client_id'] as $client_id) {
                        $ClientInfo = $h->table('users')->select()->where('id', '=', $client_id)->fetchAll();
                        $date = new DateTime($dateTime);
                        $formattedDate = $date->format('l, d F Y, h:i a');
                        if (!empty($loginUserId)){
                            $companyInfo = $h->table('users')->select()->where('id', '=', $loginUserId)->fetchAll();
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
                        sendSMS($ClientInfo[0]['phone'],'Your Appointment Has Been Rescheduled - '.$title.'\n\n Title : '.$title.' \n Date & Time : '.$formattedDate.' \n Purpose of this appointment is '.$purpose.'.');
                        include "views/email-template/update_appointment.php";
                        mailSender($_SESSION['users']['email'], $ClientInfo[0]['email'], 'Your Appointment Has Been Rescheduled - '.$title, $message, $mail);
                    }
                }
                echo "1";
                exit();
            }else{
                echo "3";
                exit();
            }
        } catch (PDOException $e) {
            echo "0";
            exit();
        }
    }else{
        echo "2";
        exit();
    }
endif;
if ($route == '/user/add-to-calendar'):

    $event_name = $_GET['event_name'];
    $start_date = $_GET['start_date'];
    generateICS($event_name, $start_date);
endif;

