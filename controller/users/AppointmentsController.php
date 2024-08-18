<?php
require("config/env.php");


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
    $formattedDate = date('jS M, Y - h:i a', strtotime($appointment[0]['date']));
    $appointment_date =$appointment[0]['date'];
    $appointmentDateTime = new DateTime($appointment_date);
    $currentDateTime = new DateTime();
    $UserInfo= [];
    $clientIdsArray = explode(',', $clientIdsString);
    foreach ($clientIdsArray as $clients) {
        $UserInfoArray = $h->table('users')
            ->select()
            ->where('id', $clients)
            ->fetchAll();
        $UserInfo []= $UserInfoArray;
    }
    $output='';
    $output.='  <div class="text-center mb-13">
                                <h1 class="mb-3">'.$appointment[0]['title'].'</h1>
                                <div class="text-muted fw-bold fs-5">'.$formattedDate.'
                            </div>

                            <a href="/meet/'.$appointment[0]['jitsi_link'].'" class="btn btn-primary fw-bolder w-100 mb-8 mt-5">
                                                Join Meeting
                            </a>



                            </div>
                          ';
    $output .= '<div class="mb-10">
                       
                <div class="fs-6 fw-bold mb-2">Meeting Members</div>
                             
                <div class="mh-300px scroll-y me-n7 pe-7">';
    foreach ($UserInfo as $user) {
        $output .= '   
                    <!--begin::User-->
                    <div class="d-flex flex-stack py-4 border-bottom border-gray-300 border-bottom-dashed">
                        <!--begin::Details-->
                        <div class="d-flex align-items-center">
                            <!--begin::Avatar-->
                            <div class="symbol symbol-35px symbol-circle">';

        if (empty($user[0]['profile_image']) || $user[0]['profile_image'] == 'null') {
            $output .= '<img alt="Pic" src="'.$env['APP_URL'].'uploads/profile/avatar.png" />';
        } else {
            $output .= '<img alt="Pic" src="'.$env['APP_URL'].'uploads/profile/'. $user[0]['profile_image'] .'" />';
        }

        $output .= '            </div>
                            <!--end::Avatar-->
                            <!--begin::Details-->
                            <div class="ms-5">
                                <a href="#" class="fs-5 fw-bolder text-gray-900 text-hover-primary mb-2">'
            . $user[0]['fname'] . ' ' . $user[0]['lname'] . '</a>
                                <div class="fw-bold text-muted">' . $user[0]['email'] . '</div>
                            </div>
                            <!--end::Details-->
                        </div>
                    </div>
                    <!--end::User-->
                </div>
                
';
    }


    echo $output;
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
            if($loginUserType == 'firm') {
                $insert = $h->insert('appointment')->values([
                    'firm_id' => $loginUserId,
                    'title' => $title,
                    'date' => $dateTime,
                    'client_id' => $client_id,
                    'purpose' => $purpose,
                    'jitsi_link' => $jitsi_link
                ])->run();
                $client_ids = explode(',', $client_id);
                if (!empty($client_ids)) {
                    foreach ($client_ids as $client_id) {
                        $ClientInfo = $h->table('users')->select()->where('id', '=', $client_id)->fetchAll();
                        $date = new DateTime($dateTime);
                        $formattedDate = $date->format('l, d F Y, h:i a');
                        include "views/email-template/add_appointment.php";
                        mailSender($_SESSION['users']['email'], $ClientInfo[0]['email'], $loginUserName . 'Set an appointment at ' . $formattedDate . ' at - ' . $env['SITE_NAME'], $message, $mail);
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
        @$client_id = implode(', ', $_POST['client_id']);
        @$purpose = $_POST['purpose'];
        try {
            if($loginUserType == 'firm') {
                $update = $h->update('appointment')->values(['title' => $title, 'date' => $dateTime, 'client_id' => $client_id, 'purpose' => $purpose])->where('id', '=', $id)->run();
                $AppointmentInfo = $h->table('appointment')->select()->where('id', '=', $id)->fetchAll();
                $client_ids = explode(',', $client_id);
                if (!empty($client_ids)) {
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

