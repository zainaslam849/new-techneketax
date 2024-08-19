<?php
require("config/env.php");
// $val=sendSMS('+18777804236','Hello Zotec Soft ha!');
//var_dump($val);
//die();
if ($loginUserType == "firm") {
    $seo = array(
        'title' => 'Dashboard',
        'description' => $val,
        'keywords' => 'Admin Panel'
    );
    echo $twig->render('user/dashboard.twig', ['seo' => $seo]);
}else{
    $seo = array(
        'title' => 'Dashboard',
        'description' => $val,
        'keywords' => 'Admin Panel'
    );
    $users = $h->table('users')->select()->where('id', '=', $loginUserId)->fetchAll();
    $firm_id = $users[0]['firm_id'];
    $firm_users = $h->table('users')->select()->where('firm_id', '=', $firm_id)->fetchAll();
    $firm_appointments = $h->table('appointment')
        ->select()
        ->where('firm_id', '=', $firm_id)
        ->fetchAll();

    $appointments = [];
    $appointments_count = 0;
    if (!empty($firm_appointments)) {
        foreach ($firm_appointments as $firm_appointment) {
            $client_ids = $firm_appointment['client_id'];

            // Explode the comma-separated string into an array
            $clientIdsArray = explode(',', $client_ids);

            // Check if $loginUserId exists in the array
            if (in_array($loginUserId, $clientIdsArray)) {
                $appointments[] = $firm_appointment;
                $appointments_count++;
            }
        }
    }

    echo $twig->render('user/client_dashboard.twig', [
        'seo' => $seo,
        'appointments' => $appointments,
        'users' => $firm_users,
        'appointments_count' => $appointments_count,
    ]);
}