<?php
require("config/env.php");
if($route == '/user/clients'):
        $seo = array(
            'title' => 'Clients List',
            'description' => 'CRM',
            'keywords' => 'Admin Panel'
        );

        echo $twig->render('user/clients/clients.twig', ['seo' => $seo, 'FirmId' => $loginUserId]);
endif;
if($route == '/user/members'):
    $seo = array(
        'title' => 'Associates List',
        'description' => 'CRM',
        'keywords' => 'Admin Panel'
    );
    echo $twig->render('user/clients/members.twig', ['seo' => $seo, 'FirmId' => $loginUserId, 'permissions' => $permissions]);
endif;
if($route == '/user/manage/clients'):
    $seo = array(
        'title' => 'Clients List',
        'description' => 'CRM',
        'keywords' => 'Admin Panel'
    );
    echo $twig->render('user/clients/manage_clients.twig', ['seo' => $seo, 'FirmId' => $loginUserId, 'permissions' => $permissions]);
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
            if (!empty($_POST['associates_id'])) {
                $associates_id = $_POST['associates_id'];
            }else{
                $associates_id = NULL;
            }
            $userAvailable = $h->table('users')->select()->where('email', '=', $email);
            if($userAvailable->count() < 1) {
                $users = $h->table('users')->select()->where('id', '=', $firm_id)->fetchAll();
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
                if ($invite == 'client'){
                    $invite_link = $env['APP_URL'] . "join/" . $firm_id . '/' . $associates_id . '/' . $email . '/' . $invite;
                }else{
                    $invite_link = $env['APP_URL'] . "join/" . $firm_id . '/null/' . $email . '/' . $invite;
                }
                include "views/email-template/invite.php";
                mailSender($env['SENDER_EMAIL'], $email, 'Invitation to Join '.@$company_name.' - '.$companyInfo[0]['fname'].' '.$companyInfo[0]['lname'], $message, $mail);
                echo "1";
                exit();
            }else{
                echo "3";
                exit();
            }
        }
endif;
if($route == '/user/user_per'):
    if(!empty($_POST['permissions'])){
        @$permissions = implode(',', array_filter($_POST['permissions']));
    }else{
        @$permissions = '';
    }
  $id = $_POST['id_per'];

            $update = $h->update('users')->values([ 'permissions' => $permissions])->where('id','=',$id)->run();
            echo "1";
            exit();
endif;