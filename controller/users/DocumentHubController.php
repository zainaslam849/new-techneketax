<?php


function createUserFolderAndUploadDocument($userId, $userName, $file, $newFileName)
{
    $baseDir = '/uploads/file-manager/';
    $folderName = sprintf("%02d-%s", $userId, $userName);
    $userFolder = $baseDir . '/' . $folderName;

    // Create the user folder if it does not exist
    if (!is_dir($userFolder)) {
        if (!mkdir($userFolder, 0755, true)) {
            return json_encode(['status' => 'error', 'message' => 'Failed to create user folder']);
        }
    }

    // Validate and handle file upload
    if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
        $originalFileName = basename($file['name']);
        $uploadFile = $userFolder . '/' . $newFileName;
        $filePath = $uploadFile;
        $fileName = pathinfo($newFileName, PATHINFO_FILENAME);
        $fileExt = pathinfo($newFileName, PATHINFO_EXTENSION);
        $counter = 1;

        while (file_exists($filePath)) {
            $filePath = $userFolder . '/' . $fileName . '-' . $counter . '.' . $fileExt;
            $counter++;
        }

        // Move and rename the uploaded file
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return json_encode([
                'status' => 'success',
                'message' => 'File uploaded successfully',
                'fileName' => basename($filePath),
                'fileDirectory' => $filePath
            ]);
        } else {
            return json_encode(['status' => 'error', 'message' => 'Failed to upload file']);
        }
    } else {
        return json_encode(['status' => 'error', 'message' => 'No file uploaded or upload error']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = isset($_POST['userId']) ? intval($_POST['userId']) : 0;
    $userName = isset($_POST['userName']) ? $_POST['userName'] : '';

    $file = isset($_FILES['document']) ? $_FILES['document'] : null;

    // New file name passed as a parameter
    $newFileName = isset($_POST['newFileName']) ? $_POST['newFileName'] : basename($file['name']);

    echo createUserFolderAndUploadDocument($userId, $userName, $file, $newFileName);
}


