<?php
require("config/env.php");
use Carbon\Carbon;
if($route == '/user/email-template/add'):
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!empty($_POST['name'])){
            $templateName = $_POST['name'];
        }else{
            echo 2;
            exit();
        }
        if (!empty($_POST['content'])){
            $templateContent = $_POST['content'];
        }else{
            echo 3;
            exit();
        }
        $design_json = $_POST['design_json'];
        $design_json = json_encode($design_json);
        $insert = $h->insert('email_template')->values(['firm_id' => $loginUserId,'name' => $templateName, 'content' => $templateContent, 'design_json' => $design_json])->run();
        if ($insert){
            echo 1;
            exit();
        }else{
            echo 0;
            exit();
        }
    }else{
        $seo = array(
            'title' => 'Add Email Template',
            'description' => 'CRM',
            'keywords' => 'Admin Panel'
        );
        echo $twig->render('user/email_template/add.twig', ['seo' => $seo,]);
    }
endif;
// Route to handle template creation and editing
if ($route === '/user/email-template/edit/$id') {
    $templateId = $id;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!empty($_POST['name'])){
            $templateName = $_POST['name'];
        }else{
              echo json_encode(['statusCode' => 2, 'message' => 'Please add Template name']);
            exit();
        }
        if (!empty($_POST['content'])){
            $templateContent = $_POST['content'];
        }else{
            echo json_encode(['statusCode' => 3, 'message' => 'Please make Template']);
            exit();
        }
        $design_json = $_POST['design_json'];
        $design_json = json_encode($design_json);
        if ($templateId) {
            $update = $h->update('email_template')
                ->values(['name' => $templateName, 'content' => $templateContent, 'design_json' => $design_json])
                ->where('id', '=', $templateId)->run();

            if ($update) {
                echo json_encode(['statusCode' => 1, 'message' => 'Template updated successfully.']);
                exit();
            } else {
                echo json_encode(['statusCode' => 0, 'message' => 'Failed to update template.']);
                exit();
            }
        } else {
            // Insert new template
            $insert = $h->insert('email_template')
                ->values(['firm_id' => $loginUserId, 'name' => $templateName, 'content' => $templateContent, 'design_json' => $design_json])
                ->run();

            if ($insert) {
                echo json_encode(['statusCode' => 1, 'message' => 'Template created successfully.']);
                exit();
            } else {
                echo json_encode(['statusCode' => 0, 'message' => 'Failed to create template.']);
                exit();
            }
        }
    } else {
        // Load the template edit page for GET requests
        $seo = array(
            'title' => 'Edit Email Template',
            'description' => 'CRM',
            'keywords' => 'Admin Panel'
        );

        // Render edit page with template ID for existing templates
        echo $twig->render('user/email_template/edit.twig', ['seo' => $seo, 'id' => $templateId]);
    }
}
if ($route === '/get-template') {
    $id = $_GET['id'];
    $email_template = $h->table('email_template')->select()->where('id', '=', $id)->fetchAll();
    echo json_encode($email_template);
    exit();
}

if($route == '/user/email-template/all'):
        $seo = array(
            'title' => 'Email Templates',
            'description' => 'CRM',
            'keywords' => 'Admin Panel'
        );
        echo $twig->render('user/email_template/all.twig', ['seo' => $seo]);
endif;
if ($route == '/user/get_email_template'):
    if(isset($_POST['edit']) && !empty($_POST['edit'])){
        $id= $_POST['id'];
        $email_template = $h->table('email_template')->select()->where('id', '=', $id)->fetchAll();
        echo json_encode($email_template);
        exit();
    }
endif;