<?php
require("config/env.php");
    $seo = array(
        'title' => 'Meeting',
        'description' => 'CRM',
        'keywords' => 'Admin Panel'
    );
    $appointment = $h->table('appointment')->select()->where('jitsi_link', '=', $room_id)->fetchAll();

    echo $twig->render('user/appointment/meet.twig', ['seo' => $seo, 'meet'=>$appointment[0]]);

