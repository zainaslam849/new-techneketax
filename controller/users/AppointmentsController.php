<?php
require("config/env.php");


if($route == '/user/appointments'):
$seo = array(
    'title' => 'Appointments',
    'description' => 'CRM',
    'keywords' => 'Admin Panel'
);
    $users = $h->table('users')->select()->where('firm_id', '=', $loginUserId)->fetchAll();
    $appointments = $h->table('appointment')
        ->select()
        ->where('firm_id', '=', $loginUserId)
        ->fetchAll();
    $TodayDate =date("Y-m-d H:i:s");
    echo $twig->render('user/appointment/index.twig', ['seo' => $seo,'clients' => $users,'todayDate' => $TodayDate,'appointments' => $appointments]);
endif;



if ($route == '/user/get_appointment'):
    $appointments = $h->table('appointment')
        ->select()
        ->where('firm_id', '=', $loginUserId)
        ->fetchAll();
    echo json_encode($appointments);
    exit();
endif;



if($route == '/user/add/appointments'):
    if (!empty($_POST['title'])) {
        if (!empty($_POST['title']) && !empty($_POST['dateTime'])&& !empty($_POST['client_id'])&& !empty($_POST['purpose'])) {
            @$title = $_POST['title'];
            @$dateTime = $_POST['dateTime'];
            @$client_id = implode(', ', $_POST['client_id']);
            @$purpose = $_POST['purpose'];
           @$jitsi_link = random_strings(10);
        }else{
            echo "2";
            exit();
        }
        try {
            $insert = $h->insert('appointment')->values([
                'firm_id' => $loginUserId,
                'title' => $title,
                'date' => $dateTime,
                'client_id' => $client_id,
                'purpose' => $purpose,
                'jitsi_link'=> $jitsi_link
            ])->run();
            $client_ids =  explode(',', $client_id);
            if (!empty($client_ids)){
            foreach ($client_ids as $client_id) {
                $ClientInfo = $h->table('users')->select()->where('id', '=', $client_id)->fetchAll();
                $date = new DateTime($dateTime);
                $formattedDate = $date->format('l, d F Y, h:i a');
                include "views/email-template/add_appointment.php";
                mailSender($_SESSION['users']['email'], $ClientInfo[0]['email'], $loginUserName . 'Set an appointment at '. $formattedDate .' at - ' . $env['SITE_NAME'], $message, $mail);
            }
            }
            echo "1";
            exit();
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
        @$client_id = implode(', ', $_POST['client_id']);
        @$purpose = $_POST['purpose'];
        try {
            $update = $h->update('appointment')->values(['title' => $title, 'date' => $dateTime, 'client_id' => $client_id, 'purpose' => $purpose])->where('id','=',$id)->run();
            $AppointmentInfo = $h->table('appointment')->select()->where('id', '=', $id)->fetchAll();
            $client_ids =  explode(',', $client_id);
            if (!empty($client_ids)){
                foreach ($client_ids as $client_id) {
                    $ClientInfo = $h->table('users')->select()->where('id', '=', $client_id)->fetchAll();
                    $date = new DateTime($dateTime);
                    $formattedDate = $date->format('l, d F Y, h:i a');
                    include "views/email-template/update_appointment.php";
                    mailSender($_SESSION['users']['email'], $ClientInfo[0]['email'], $loginUserName . 'Make changes in appointment at - ' . $env['SITE_NAME'], $message, $mail);
                }
            }
            echo "1";
            exit();
        } catch (PDOException $e) {
            echo "0";
            exit();
        }
    }else{
        echo "2";
        exit();
    }
endif;

