<?php
require("config/env.php");
if($route == '/user/clients'):
        $seo = array(
            'title' => 'Invoices',
            'description' => 'CRM',
            'keywords' => 'Admin Panel'
        );

        echo $twig->render('user/clients/clients.twig', ['seo' => $seo, 'FirmId' => $loginUserId]);
endif;
if($route == '/user/members'):
    $seo = array(
        'title' => 'Invoices',
        'description' => 'CRM',
        'keywords' => 'Admin Panel'
    );

    echo $twig->render('user/clients/members.twig', ['seo' => $seo, 'FirmId' => $loginUserId]);
endif;
if($route == '/user/send_invite'):
        if (isset($_POST['email'])){
            $firm_id = $_POST['firm_id'];
            if (!empty($_POST['email']) && !empty($_POST['invite'])) {
                $email = $_POST['email'];
                $invite = $_POST['invite'];
            }else{
                echo "2";
                exit();
            }
            $userAvailable = $h->table('users')->select()->where('email', '=', $email);
            if($userAvailable->count() < 1) {
                $users = $h->table('users')->select()->where('id', '=', $firm_id)->fetchAll();

                include "views/email-template/invite.php";
                mailSender($env['SENDER_EMAIL'], $email, 'Invitation From ' . $users[0]['fname'] . ' ' . $users[0]['lname'] . ' at - ' . $env['SITE_NAME'], $message, $mail);
                echo "1";
                exit();
            }else{
                echo "3";
                exit();
            }
        }
endif;