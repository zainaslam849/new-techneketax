<?php
require("config/env.php");
use Carbon\Carbon;
if($route == '/user/email-template/test'):
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['templateZip']) && $_FILES['templateZip']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['templateZip']['tmp_name'];
        $fileName = $_FILES['templateZip']['name'];
        $uploadDir = 'uploads/email_templates/';
        $uploadFilePath = $uploadDir . $fileName;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        if (move_uploaded_file($fileTmpPath, $uploadFilePath)) {
            $zip = new ZipArchive();
            if ($zip->open($uploadFilePath) === true) {
                $extractDir = $uploadDir . pathinfo($fileName, PATHINFO_FILENAME) . '/';
                $zip->extractTo($extractDir);
                $zip->close();
                $htmlFile = glob($extractDir . '*.html')[0] ?? null;
                if ($htmlFile) {
                    echo json_encode([
                        'status' => 'success',
                        'htmlFile' => $env['APP_URL'].$htmlFile,
                        'extractDir' => $extractDir
                    ]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'No HTML file found in the zip.']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Could not open zip file.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to move uploaded file.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No file uploaded or upload error occurred.']);
    }
} else{
        $seo = array(
            'title' => 'Add Email Template',
            'description' => 'CRM',
            'keywords' => 'Admin Panel'
        );
        echo $twig->render('user/email_template/test.twig', ['seo' => $seo,]);
    }
endif;
if($route == '/user/email-template/add'):
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);
        $templateName = $data['name'] ?? null;
        $content = mb_convert_encoding($data['content'] ?? '', 'UTF-8', 'auto');
        $htmlContent = base64_encode($content);
        $design = base64_encode(json_encode($data['design_json'] ?? null));
        $insert = $h->insert('email_template')->values(['firm_id' => $loginUserId,'name' => $templateName, 'content' => $htmlContent, 'design_json' => $design])->run();
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
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        $templateName = $data['name'] ?? null;
        $content = mb_convert_encoding($data['content'] ?? '', 'UTF-8', 'auto');
        $htmlContent = base64_encode($content);
        $design = base64_encode(json_encode($data['design_json'] ?? null));
        if ($templateId) {
            $update = $h->update('email_template')
                ->values(['name' => $templateName, 'content' => $htmlContent, 'design_json' => $design])
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