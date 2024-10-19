<?php
require("config/env.php");
use Carbon\Carbon;
$email_config = include('config/email_config.php');
if($route == '/user/email/inbox'):
        $seo = array(
            'title' => 'Inbox',
            'description' => 'CRM',
            'keywords' => 'Admin Panel'
        );

    echo $twig->render('user/email/inbox.twig', [
        'seo' => $seo,
    ]);
endif;
if($route == '/user/email/email_reply/$folder/$email_id'):
        $seo = array(
            'title' => 'Email Reply',
            'description' => 'CRM',
            'keywords' => 'Admin Panel'
        );
   $email_body = fetchSpecificEmailById($folder, $email_id);
    $dateTime = Carbon::parse($email_body['date']);
    $formattedDateBeforeAfter = $dateTime->diffForHumans();
    $formattedDate = Carbon::parse($dateTime)->format('d M Y, g:i a');
    $attachmentName = [];
    $attachmentSinglePathName = [];
    if (!empty($email_body['attachments'])) {
            foreach ($email_body['attachments'] as $attachment) {
                $fullName = $attachment->name;
                $nameWithoutExtension = pathinfo($fullName, PATHINFO_FILENAME);
                $attachmentSinglePathName[] =$fullName;
                $attachmentName[] = [
                    "name" => $nameWithoutExtension,
                    "path" => $fullName
                ];
            }
    }else {
        echo "No attachments found.\n";
    }

    echo $twig->render('user/email/email_reply.twig', [
        'seo' => $seo,
        'email_body' => $email_body,
        'formattedDate' => $formattedDateBeforeAfter,
        'formattedDateDMY' => $formattedDate,
        'folder' => $folder,
        'email_id' => $email_id,
        'attachmentNames' => $attachmentName,
        'attachmentSinglePathName' => $attachmentSinglePathName,
    ]);
endif;
if($route == '/user/email/sent'):
    $seo = array(
        'title' => 'Sent',
        'description' => 'CRM',
        'keywords' => 'Admin Panel'
    );

    echo $twig->render('user/email/sent.twig', [
        'seo' => $seo,
    ]);
endif;
if($route == '/user/email/trash'):
    $seo = array(
        'title' => 'Trash',
        'description' => 'CRM',
        'keywords' => 'Admin Panel'
    );

    echo $twig->render('user/email/trash.twig', [
        'seo' => $seo,
    ]);
endif;
if($route == '/user/email/compose' or $route == '/user/email/compose/$email'):

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
     if(!empty($_POST['compose_to']) && !empty($_POST['compose_subject']) && !empty($_POST['body'])){
         $compose_to = $_POST['compose_to'];
         $compose_cc = $_POST['compose_cc'] ?? '';
         $compose_bcc = $_POST['compose_bcc'] ?? '';
         $compose_subject = $_POST['compose_subject'];
         $compose_body = $_POST['body'];
     }else{
         echo json_encode(['success' => false, 'message' => 'Please Fill All the Fields']);
         exit();
     }

//        $imageDirectory = 'uploads/email_attachment/';
//        if (!is_dir($imageDirectory)) {
//            mkdir($imageDirectory, 0755, true);
//        }
//        $pattern = '/data:image\/(jpeg|png|gif);base64,([a-zA-Z0-9\/+=]+)/';
//        $compose_body = preg_replace_callback($pattern, function ($matches) use ($imageDirectory) {
//            global $env;
//            $imageType = $matches[1];
//            $base64Data = $matches[2];
//            $imageData = base64_decode($base64Data);
//            $fileName = uniqid() . '.' . $imageType;
//            $filePath = $imageDirectory . $fileName;
//            file_put_contents($filePath, $imageData);
//          return  '<img src="'.$env['APP_URL'] . $filePath . '" alt="Image" />';
//        }, $compose_body);

        $attachments = !empty($_POST['attachment']) ? $_POST['attachment'] : [];

        $from = @$_SESSION['users']['generated_email'] . '@'.@$domainName;
        $compose_to_array = json_decode($compose_to, true);
        $compose_cc_array = json_decode($compose_cc, true);
        $compose_bcc_array = json_decode($compose_bcc, true);
        $toEmails = [];
        $ccEmails = [];
        $bccEmails = [];

        if (is_array($compose_to_array)) {
            foreach ($compose_to_array as $item) {

                if (isset($item['email'])) {
                    $toEmails[] = $item['email'];

                }
            }
        }


        if (is_array($compose_cc_array)) {
            foreach ($compose_cc_array as $item) {
                if (isset($item['email'])) {
                    $ccEmails[] = $item['email'];
                }
            }
        }
        if (is_array($compose_bcc_array)) {
            foreach ($compose_bcc_array as $item) {
                if (isset($item['email'])) {
                    $bccEmails[] = $item['email'];
                }
            }
        }
        $sendSuccess = false;
        foreach ($toEmails as $toEmail) {
            $sendResult = sendEmail($email_config['smtp'], $from, $toEmail, $ccEmails, $bccEmails, $compose_subject, $compose_body, $attachments);

            if ($sendResult) {
                $sendSuccess = true;
            }
        }
            if ($sendSuccess) {
                echo json_encode(['success' => true, 'message' => 'Email sent successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to send email']);
            }
            exit();

    }else{
        $seo = array(
            'title' => 'Inbox',
            'description' => 'CRM',
            'keywords' => 'Admin Panel'
        );
        $usersDetails = $h->table('users')->select()->where('firm_id', '=', $loginUserId)->fetchAll();

        echo $twig->render('user/email/compose.twig', ['seo' => $seo,'userList' => $usersDetails,'email' => @$email]);
    }
endif;
if($route == '/user/email/upload_attachment'):

        $uploadDir ='uploads/email_attachment/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $response = [
            'success' => false,
            'fileName' => '',
            'message' => ''
        ];

        if (isset($_FILES['file'])) {
            $file = $_FILES['file'];
            $originalName = $file['name'];
            $fileTmpPath = $file['tmp_name'];
            $fileExtension = pathinfo($originalName, PATHINFO_EXTENSION);

            // Generate a random file name
            $randomFileName = uniqid() . '.' . $fileExtension;
            $destination = $uploadDir . $randomFileName;

            // Move the uploaded file to the destination
            if (move_uploaded_file($fileTmpPath, $destination)) {
                $response['success'] = true;
                $response['fileName'] = $randomFileName;
            } else {
                $response['message'] = 'Error while saving the uploaded file.';
            }
        } else {
            $response['message'] = 'No file uploaded.';
        }

        // Send the response back as JSON
        echo json_encode($response);

endif;
if($route == '/user/email/get_users'):
    try {
    if ($loginUserType == 'firm'){
        $usersDetails = $h->table('users')->select()->where('firm_id', '=', $loginUserId)->fetchAll();
    }else{
        $usersDetails = $h->table('users')->select()->where('id', '=', @$userInfo[0]['firm_id'])->fetchAll();
    }


        $response = [];
        foreach ($usersDetails as $user) {
            if (@$userInfo[0]['profile_image'] != 'null' && @$userInfo[0]['profile_image'] != ''){
                $avatar = $env['APP_URL'].'uploads/profile/'.@$userInfo[0]['profile_image'];
            }else{
                $avatar = 'https://avatar.iran.liara.run/username?username='.$loginUserName;
            }

            $response[] = [
                'value' => $user['id'],
                'name' => $user['fname'].' '.$user['lname'],
                'avatar' => $avatar,
                'email' => $user['generated_email'].'@'.$domainName
            ];
        }
        echo json_encode($response);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
endif;
if($route == '/user/email/sentToTrash'):
$emailId = $_POST['id'];
$folder = $_POST['folder'];
   $return = moveEmailToTrash($folder, $emailId);
   if ($return){
       echo json_encode(array("statusCode" => "1","message" =>  $return));
       exit();
   }else{
       echo json_encode(array("statusCode" => "0","message" =>  'Something Went Wrong'));
       exit();
   }
endif;
if($route == '/user/email/sentToTrashBulk'):
    $emailIds = $_POST['ids'];
    $folder = $_POST['folder'];
    if (!empty($emailIds)){
        foreach ($emailIds as $email_id) {
            $return = moveEmailToTrash($folder, $email_id);
            if ($return) {
                $sendSuccess = true;
            }
        }
        if ($sendSuccess){
            echo json_encode(array("statusCode" => "1","message" =>  'Emails moved to Trash.'));
            exit();
        }else{
            echo json_encode(array("statusCode" => "0","message" =>  'Something Went Wrong'));
            exit();
        }
    }else{
        echo json_encode(array("statusCode" => "0","message" =>  'Something Went Wrong'));
        exit();
    }


endif;
   if($route == '/user/email/deleteEmailFromTrash'):
$emailId = $_POST['id'];
   $return = deleteEmailFromTrash($emailId);
   if ($return){
       echo json_encode(array("statusCode" => "1","message" =>  $return));
       exit();
   }else{
       echo json_encode(array("statusCode" => "0","message" =>  'Something Went Wrong'));
       exit();
   }
endif;
   if($route == '/user/email/deleteEmailFromTrashBulk'):
$emailIds = $_POST['ids'];

       if (!empty($emailIds)){
           foreach ($emailIds as $email_id) {
               $return = deleteEmailFromTrash($email_id);
               if ($return) {
                   $sendSuccess = true;
               }
           }
           if ($sendSuccess){
               echo json_encode(array("statusCode" => "1","message" =>  'Emails Deleted.'));
               exit();
           }else{
               echo json_encode(array("statusCode" => "0","message" =>  'Something Went Wrong'));
               exit();
           }
       }else{
           echo json_encode(array("statusCode" => "0","message" =>  'Something Went Wrong'));
           exit();
       }
endif;
if($route == '/user/email/attachment'):
    if (!empty($_POST['attachmentNames'])) {
        $attachmentNames = $_POST['attachmentNames'];

        if (!empty($attachmentNames)) {
            $zip = new ZipArchive();
            $zip_filename = 'Attachment' . time() . '.zip';

            if ($zip->open($zip_filename, ZipArchive::CREATE) !== TRUE) {
                echo "2";
                exit();
            }

            foreach ($attachmentNames as $attachmentName) {
                $file_path = 'uploads/email_attachment/'.$attachmentName;
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