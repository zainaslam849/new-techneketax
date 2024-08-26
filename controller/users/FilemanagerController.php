<?php
require("config/env.php");
if($route == '/file'){
    require 'file-manager/php/autoload.php';
    $opts = [
        'roots' => [
            [
                'driver' => 'LocalFileSystem',
                'path' => 'uploads/file-manager/',
                'URL' => dirname($_SERVER['PHP_SELF']) . '/uploads/file-manager/',
                'accessControl' => 'access',
            ],
        ],
    ];

    $connector = new elFinderConnector(new elFinder($opts));
    $connector->run();
}

if($route == '/user/file'){
    echo $twig->render('user/document_hub/filemanager.twig');
}

if($route == '/user/files'){

    if(!empty($userInfo[0]['company_image'])){
        $company_logo=$env['APP_URL']."uploads/profile/".$userInfo[0]['company_image'];
    }else{
        $company_logo="https://avatar.iran.liara.run/username?username=".$userInfo[0]['company_name'];
    }
    $company_name=$userInfo[0]['company_name'];
    if(!empty($_GET['folder'])){
        $directory= base64_decode($_GET['folder']);
    }else{
        $directory="uploads/firm_document/".$loginUserId."-".$loginUserName;
    }
    $contents = getDirectoryContents($directory);

    echo $twig->render('user/document_hub/files.twig',
        [
        'company_logo'=>$company_logo,
        'company_name'=>$company_name,
        'files'=>$contents,
        'current_directory'=>$directory
    ]);

}
if($route == "/user/file/del"){
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $filePath = isset($_POST['filePath']) ? $_POST['filePath'] : '';
        $response = [
            'status' => 'error',
            'message' => 'File deletion failed',
        ];
        if (!empty($filePath) && file_exists($filePath)) {
            if (unlink($filePath)) {
                $response['status'] = 'success';
                $response['message'] = 'File "' . basename($filePath) . '" deleted successfully.';
            } else {
                $response['message'] = 'Could not delete the file "' . basename($filePath) . '".';
            }
        } else {
            $response['message'] = 'File "' . basename($filePath) . '" not found.';
        }
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}


if($route == "/user/file/upload"){

    function handleUpload($current_directory)
    {
        $maxFileSize = 100 * 1024 * 1024; // 100 MB

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xlsx', 'zip'];

        if (empty($_FILES['file'])) {
            return jsonResponse('error', 'No files uploaded.');
        }

        $file = $_FILES['file'];
        $filename = basename($file['name']);
        $fileSize = $file['size'];
        $fileTmpPath = $file['tmp_name'];
        $fileExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if ($fileSize > $maxFileSize) {
            return jsonResponse('error', 'File size exceeds the maximum limit of 100 MB.');
        }

        if (!in_array($fileExtension, $allowedExtensions)) {
            return jsonResponse('error', 'Invalid file type.');
        }

        $targetDir = rtrim($current_directory, '/') . '/';
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0777, true)) {
                return jsonResponse('error', 'Failed to create the target directory.');
            }
        }
        $targetFilePath = $targetDir . $filename;
        if (move_uploaded_file($fileTmpPath, $targetFilePath)) {
            return jsonResponse('success', 'File "' . $filename . '" uploaded successfully.');
        } else {
            return jsonResponse('error', 'Failed to upload the file.');
        }
    }

    function jsonResponse($status, $message)
    {
        header('Content-Type: application/json');
        echo json_encode(['status' => $status, 'message' => $message]);
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $current_directory = isset($_POST['current_directory']) ? $_POST['current_directory'] : '';
        handleUpload($current_directory);
    } else {
        jsonResponse('error', 'Invalid request method.');
    }


}
?>