<?php

require("config/env.php");
if($route == '/user/document'):
    $seo = array(
        'title' => 'Document Hub',
        'description' => 'CRM',
        'keywords' => 'Admin Panel'
    );
    $users = $h->table('users')->select()->where('firm_id', '=', $loginUserId)->fetchAll();
    $documents = $h->table('firm_upload_file')->select()->where('firm_id', '=', $loginUserId)->fetchAll();
    $templates= $h->table('templates')->select()->where('status', '=', 'active')->orderBy('id', 'desc')->fetchAll();
    echo $twig->render('user/document_hub/firm_index.twig', ['seo' => $seo,'clients' => $users,'documents' => $documents,'templates' => $templates]);
endif;
if($route == '/client/document'):
    $seo = array(
        'title' => 'Document Hub',
        'description' => 'CRM',
        'keywords' => 'Admin Panel'
    );
    $document_hub = $h->table('document_hub')->select()->where('client_id', '=', $loginUserId)->fetchAll();
    echo $twig->render('user/document_hub/client_index.twig', ['seo' => $seo,'client' => $document_hub]);
endif;

if($route == '/client/document/add'):
    if (!empty($_POST['firm_id'])) {
        $firm_id = $_POST['firm_id'];
        $id = $_POST['id'];
        $client_des = $_POST['client_des'];
        $firmInfo = $h->table('users')->select()->where('id', '=', $firm_id)->fetchAll();
        $ClientInfo = $h->table('users')->select()->where('id', '=', $loginUserId)->fetchAll();

        $firmName= $firmInfo[0]['id'].'-'.$firmInfo[0]['fname'].$firmInfo[0]['lname'];
        $userName= $ClientInfo[0]['id'].'-'.$ClientInfo[0]['fname'].$ClientInfo[0]['lname'];

        if (!empty($_FILES['file']) && $_FILES['file'] != ''){
            $files = $_FILES['file'];
        }else{
            echo "2";
            exit();
        }

        $file = uploadFile($firmName, $userName, $files);
        try {
            $insert = $h->update('document_hub')->values(['client_des' => $client_des,'file' => $file['file_path'],'status' => 'yes'])->where('id','=',$id)->run();
            echo "1";
            exit();
        } catch (PDOException $e) {
            echo "0";
            exit();
        }
    }
endif;
    if($route == '/client/download/document'):
        if (!empty($_POST['document_id'])) {
            $document_ids = explode(', ', $_POST['document_id']);
            if (!empty($document_ids)) {
                $zip = new ZipArchive();
                $zip_filename = 'documents_' . time() . '.zip';

                if ($zip->open($zip_filename, ZipArchive::CREATE) !== TRUE) {
                    echo "2";
                    exit();
                }

                foreach ($document_ids as $document_id) {
                    $documentInfo = $h->table('firm_upload_file')->select()->where('id', '=', $document_id)->fetchAll();
                    $file_path = $documentInfo[0]['file'];
                    $zip->addFile($file_path, basename($file_path));
                }

                $zip->close();

                header('Content-Type: application/zip');
                header('Content-disposition: attachment; filename=' . $zip_filename);
                header('Content-Length: ' . filesize($zip_filename));
                readfile($zip_filename);

                // Delete the zip file after download
                unlink($zip_filename);
                exit();
            }
        } else {
            echo "2";
            exit();
        }
endif;
if($route == '/user/request_for_document'):
    if (!empty($_POST['client_id'])) {
        if (!empty($_POST['client_id'])){
            $client_id = $_POST['client_id'];
        }else{
            echo "3";
            exit();
        }
        $document_type = $_POST['document_type'];
        if ($document_type == 'interviews'){
            if (!empty($_POST['document_interviews_id'])){
                $document_id = $_POST['document_interviews_id'];
            }else{
                echo "4";
                exit();
            }
        }else{
            if (!empty($_POST['document_id'])) {
                @$document_id = implode(', ', $_POST['document_id']);
            }else{
                echo "5";
                exit();
            }
        }
        $firm_des = $_POST['firm_des'];

        try {
            if($document_type == 'interviews'){
                    $checkData= $h->table('template_request')->select()->where('user_id', '=', $client_id)->where('template_id', '=', $document_id)->fetchAll();
                    if(empty($checkData)){
                        $insert = $h->insert('document_hub')->values(['firm_id' => $loginUserId,'client_id' => $client_id,'document_type' => $document_type,'firm_des' => $firm_des,'document_id' => $document_id])->run();
                        $res=$h->insert('template_request')->values(['doc_hub_id' => $insert,'user_id' => $client_id,'template_id' => $document_id])->run();
                        if($res){
                            echo 1;
                            $ClientInfo = $h->table('users')->select()->where('id', '=', $client_id)->fetchAll();
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
                            sendSMS($companyInfo[0]['phone'],'Invitation to Complete Your Interview Form \n We kindly invite you to complete the interview form as part of our ongoing process. Your insights are highly valued and will greatly assist us in tailoring our services to your needs.');
                            include "views/email-template/interview_request.php";
                            mailSender($_SESSION['users']['email'], $ClientInfo[0]['email'], 'Invitation to Complete Your Interview Form', $message, $mail);
                            exit();
                        }else{
                            echo 0;
                            exit();
                        }
                    }else{
                        echo "6";
                        exit();
                    }

            }else{
                $insert = $h->insert('document_hub')->values([ 'firm_id' => $loginUserId,'client_id' => $client_id,'document_type' => $document_type,'firm_des' => $firm_des,'document_id' => $document_id])->run();
                $usersInfo = $h->table('users')->select()->where('id', '=', $client_id)->fetchAll();
                $email = $usersInfo[0]['email'];
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
                sendSMS($companyInfo[0]['phone'],''.@$company_name.' Request for document\n\n '.$firm_des.' \n');

                include "views/email-template/firmRequestDocument.php";
                mailSender($env['SENDER_EMAIL'],$email,'Request For Document - '.$env['SITE_NAME'],$message,$mail);
                echo "1";
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
if($route == '/client/dochubdetails/$id'):
    $seo = array(
        'title' => 'Document Hub',
        'description' => 'CRM',
        'keywords' => 'Admin Panel'
    );
    $document_hub = $h->table('document_hub')->select()->where('id', '=', $id)->fetchAll();
    $client_id = $document_hub[0]['client_id'];
    $firm_id = $document_hub[0]['firm_id'];
    $firmDetails = $h->table('users')->select()->where('id', '=', $firm_id)->fetchAll();
    $clientDetails = $h->table('users')->select()->where('id', '=', $client_id)->fetchAll();
    echo $twig->render('user/document_hub/client_index_detail.twig', ['seo' => $seo,'documentHub' => $document_hub,'client_details' => $clientDetails,'firmDetail' => $firmDetails]);
endif;


if($route == '/user/dochubdetails/$id'):
    $seo = array(
        'title' => 'Document Hub',
        'description' => 'CRM',
        'keywords' => 'Admin Panel'
    );
    $document_hub = $h->table('document_hub')->select()->where('id', '=', $id)->fetchAll();
    $client_id = $document_hub[0]['client_id'];
    $firm_id = $document_hub[0]['firm_id'];
    $firmDetails = $h->table('users')->select()->where('id', '=', $firm_id)->fetchAll();

    $clientDetails = $h->table('users')->select()->where('id', '=', $client_id)->fetchAll();
    echo $twig->render('user/document_hub/firm_index_details.twig', ['seo' => $seo,'documentHub' => $document_hub,'client_details' => $clientDetails,'firmDetail' => $firmDetails]);
endif;
if($route == '/user/upload/document/all'):
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!empty($_POST['firm_id'])) {
            if (!empty($_FILES['file'])){
                $file = $_FILES['file'];
            }else{
                echo json_encode(array("statusCode" => "4","message" =>  "Please Upload File"));
                exit();
            }
            $firm_id = $_POST['firm_id'];
            $firm_des = $_POST['firm_des'];
            $firmInfo = $h->table('users')->select()->where('id', '=', $firm_id)->fetchAll();
            $firmName= $firmInfo[0]['id'].'-'.$firmInfo[0]['fname'].$firmInfo[0]['lname'];
            $userFolder = $firmName;

            $uploadResponse = uploadFirmDocumentFile($userFolder, $file);
if ($uploadResponse['file_path'] == '' || empty($uploadResponse['file_path'])){
    echo json_encode(array("statusCode" => "3","message" =>  $uploadResponse['message']));
    exit();
}
            try {
                $insert = $h->insert('firm_upload_file')->values(['firm_id' => $firm_id,'description' => $firm_des,'file_name' => $uploadResponse['file_name'],'file' => $uploadResponse['file_path']])->run();
                echo json_encode(array("statusCode" => "1","message" =>  $uploadResponse['message']));
                exit();
            } catch (PDOException $e) {
                echo "0";
                exit();
            }
        }else{
            echo "2";
            exit();
        }



    }else{
        $seo = array(
            'title' => 'Document Hub',
            'description' => 'CRM',
            'keywords' => 'Admin Panel'
        );
        echo $twig->render('user/document_hub/uploaded_document.twig', ['seo' => $seo,'firm_id' => $loginUserId ]);
    }

endif;