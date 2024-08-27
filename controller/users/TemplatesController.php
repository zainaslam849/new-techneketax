<?php
require("config/env.php");
if ($route == '/user/templates/send_request'  && $_SERVER['REQUEST_METHOD'] === 'POST'):
    if(isset($_POST['template_id']) && !empty($_POST['template_id']) && isset($_POST['client_id']) && !empty($_POST['client_id'])){
        $template_id= $_POST['template_id'];
        $client_id= $_POST['client_id'];
        $checkData= $h->table('template_request')->select()->where('user_id', '=', $client_id)->where('template_id', '=', $template_id)->fetchAll();
        if(empty($checkData)){
            $res=$h->insert('template_request')->values(['user_id' => $client_id,'template_id' => $template_id])->run();
            if($res){
                echo 1;
                exit();
            }else{
                echo 0;
                exit();
            }
        }else{
            echo "3";
            exit();
        }
    }else{
        echo 2;
        exit();
    }
endif;
if ($route == '/user/template/interview-list/$slug'):
    $seo = array(
        'title' => 'Templates',
        'description' => 'CRM',
        'keywords' => 'User Panel'
    );
    echo $twig->render('user/templates/interviews_list.twig', ['seo' => $seo,'slug' => $slug]);
endif;
if ($route == '/client/template/request'):
    $seo = array(
        'title' => 'Templates',
        'description' => 'CRM',
        'keywords' => 'User Panel'
    );
    echo $twig->render('user/templates/template_request.twig', ['seo' => $seo]);
endif;


if ($route == '/user/template/get'):
    if(isset($_POST['edit']) && !empty($_POST['edit'])){
        $id= $_POST['id'];
        $users = $h->table('users')->select()->where('id', '=', $id)->fetchAll();
        echo json_encode($users);
        exit();
    }
endif;


if($route == '/user/template/display-data/$userId/$templateId'):
    $seo = array(
        'title' => 'Templates',
        'description' => 'CRM',
        'keywords' => 'User Panel'
    );
    $template = $h->table('templates')->select()->where('id', '=', $templateId)->fetchAll();

// Path to the JSON file
    $filePath = 'uploads/templates/' . $templateId . '.json';
    $data = [];
    $userDetails = [];
    $sectionDetails = [];

    if (file_exists($filePath)) {
        $jsonData = file_get_contents($filePath);
        $records = json_decode($jsonData, true);

        if ($records !== null) {
            foreach ($records as $record) {
                if ($record['user_id'] != $userId) {
                    continue; // Skip records that do not match the specific user_id
                }

                if (!isset($userDetails[$userId])) {
                    $userDetails[$userId] = $h->table('users')->select()->where('id', $userId)->fetchAll()[0];
                }

                $formattedRecord = [
                    'user' => $userDetails[$userId],
                    'sections' => []
                ];
                foreach ($record['sections'] as $section) {
                    $sectionId = $section['section_id'];
                    if (!isset($sectionDetails[$sectionId])) {
                        $sectionDetails[$sectionId] = $h->table('sections')->select()->where('id', $sectionId)->fetchAll()[0];
                    }

                    $formattedSection = [
                        'section_name' => $sectionDetails[$sectionId]['title'],
                        'responses' => []
                    ];
                    foreach ($section['responses'] as $item) {
                        if (isset($item['value'])) {
                            $value = json_decode($item['value'], true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $item['value'] = $value;
                            }
                        }
                        $formattedSection['responses'][] = $item;
                    }
                    $formattedRecord['sections'][] = $formattedSection;
                }
                $data[] = $formattedRecord;
            }
        } else {
            $data = ['error' => 'Error decoding JSON data.'];
        }
    } else {
        $data = ['error' => 'Template data not found.'];
    }

    echo $twig->render('user/templates/display_data.twig', [
        'seo' => $seo,
        'template' => $template,
        'data' => $data
    ]);
endif;
if($route == '/user/template/view/$slug'):
    $seo = array(
        'title' => 'Templates',
        'description' => 'CRM',
        'keywords' => 'User Panel'
    );

$templateId = $slug ?? null;

$template = [];
$templateSections = [];
$sections = [];
$totalInputs = 0;

if ($templateId) {
    $template_request = $h->table('template_request')->select()->where('user_id', '=', $loginUserId)->fetchAll();
    if(!empty($template_request)){
        $template = $h->table('templates')->select()->where('slug', '=', $templateId)->fetchAll();
        $templateSections = $h->table('template_sections')->select('section_id')->where('template_id', '=', $template[0]['id'])->fetchAll();
        foreach ($templateSections as $templateSection) {
            $section = $h->table('sections')->select()->where('id', $templateSection['section_id'])->fetchAll();
            $section['questions'] = $h->table('questions')->select()->where('section_id', '=', $section[0]['id'])->fetchAll();
            $totalInputs += count($section['questions']);

            foreach ($section['questions'] as &$question) {
                $question['options'] = $h->table('options')->select()->where('question_id', '=', $question['id'])->fetchAll();
            }
            $sections[] = $section;
        }
    }
}

$csrf = set_csrf(); // Generate your CSRF token
$totalSteps = ceil($totalInputs / 5);

echo $twig->render('user/templates/view.twig', [
    'csrf' => $seo,
    'seo' => $csrf,
    'template' => $template,
    'sections' => $sections,
    'totalSteps' => $totalSteps,
    'template_request' => $template_request,
    'totalInputs' => $totalInputs
]);

endif;

if ($route == '/user/template/view' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $templateId = $_POST['template_id'];
    $userId = $_POST['user_id'];
    $questions = $_POST['questions'];

    $data = [
        'user_id' => $userId,
        'sections' => []
    ];

    // Directory where files will be uploaded
    $uploadDir = 'uploads/template_files/' . $templateId;

    // Ensure the upload directory exists
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Organize questions by their section IDs
    $sections = [];
    foreach ($questions as $questionId => $question) {
        $sectionId = $question['section_id'];

        // Initialize the section array if not already set
        if (!isset($sections[$sectionId])) {
            $sections[$sectionId] = [
                'section_id' => $sectionId,
                'responses' => []
            ];
        }

        // Handle file upload if the question type is 'file'
        if (isset($_FILES['questions']['name'][$questionId]['value']) && $_FILES['questions']['name'][$questionId]['value'] != '') {
            $file = [
                'name' => $_FILES['questions']['name'][$questionId]['value'],
                'tmp_name' => $_FILES['questions']['tmp_name'][$questionId]['value']
            ];
            $uploadedFilePath = uploadTemplateFile($file, $uploadDir);
            $value = $uploadedFilePath ? $uploadedFilePath : 'File upload failed';
        } elseif (isset($question['value']) && strpos($question['value'], 'data:image/png;base64,') === 0) {
            // Handle signature if the value is a base64 encoded image
            $signatureData = $question['value'];
            $signatureImage = uniqid(rand(100, 100000)) . '.png';
            $signatureImagePath = $uploadDir . '/' . $signatureImage;
            $decodedSignature = base64_decode(str_replace('data:image/png;base64,', '', $signatureData));
            file_put_contents($signatureImagePath, $decodedSignature);
            $value = $signatureImagePath;
        } else {
            $value = isset($question['value']) ? (is_array($question['value']) ? json_encode($question['value']) : $question['value']) : null;
        }

        // Add the response to the corresponding section
        $sections[$sectionId]['responses'][] = [
            'label' => $question['label'],
            'value' => $value
        ];
    }

    // Re-index the sections array to ensure correct ordering and structure
    $data['sections'] = array_values($sections);

    // Update the template request status
    $h->update('template_request')->values(['status' => 'completed'])->where('user_id', '=', $userId)->where('template_id', '=', $templateId)->run();

    // Path to the JSON file
    $filePath = 'uploads/templates/' . $templateId . '.json';

    // Check if the file exists
    if (file_exists($filePath)) {
        // Read existing data
        $existingData = json_decode(file_get_contents($filePath), true);
    } else {
        // Create a new array if the file doesn't exist
        $existingData = [];
    }

    // Append the new data to the existing data
    $existingData[] = $data;

    // Save data to the JSON file
    file_put_contents($filePath, json_encode($existingData, JSON_PRETTY_PRINT));

    echo 'Data saved successfully!';
    exit();
}


if($route == '/user/template/all'):
    $seo = array(
        'title' => 'Templates',
        'description' => 'CRM',
        'keywords' => 'User Panel'
    );
    $clients = $h->table('users')->select()->where('type', '=', 'client')->where('firm_id', '=', $loginUserId)->fetchAll();
    echo $twig->render('user/templates/all.twig', ['seo' => $seo,'clients' => $clients]);
endif;
if($route == '/user/template/create' || $route == '/user/template/create/$templateId' ):
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
// Validate CSRF token here
//        $csrf = $_POST['csrf'];
        $templateId = $_POST['template_id'];
        $templateName = $_POST['template_name'];
        $slug = slugify($_POST['template_name']);
        $sections = $_POST['sections'];
        $description = $_POST['description'];
        if (!empty($templateId)) {
            $check = $h->table('templates')->select()->where('slug', '=', $slug)->where('id', '!=', $templateId)->fetchAll();
            if(!empty($check)){
                $slug = $slug.rand();
            }
            // Update existing template
            $h->update('templates')->values(['name' => $templateName,'slug' => $slug,'description' => $description])->where('id', '=', $templateId)->run();

            // Remove existing section associations
            $h->table('template_sections')->delete()->where('template_id', '=', $templateId)->run();
        } else {
            $check = $h->table('templates')->select()->where('slug', '=', $slug)->fetchAll();
            if(!empty($check)){
                $slug = $slug.rand();
            }
            // Insert new template
            $stmt = $h->insert('templates')->values(['name' => $templateName,'slug' => $slug,'description' => $description])->run();
            $templateId = $stmt;
        }

// Insert new section associations
        foreach ($sections as $sectionId) {
            $h->insert('template_sections')->values(['template_id' => $templateId, 'section_id' => $sectionId])->run();
        }

        echo 'Template saved successfully!';
    }else{
        $templateId = $templateId ?? null; // Get the template ID if available
        $template = [];
        $templateSections = [];

        if ($templateId) {
            $template = $h->table('templates')->select()->where('id', '=', $templateId)->fetchAll();
            $templateSections = $h->table('template_sections')->select('section_id')->where('template_id', '=', $templateId)->fetchAll(PDO::FETCH_COLUMN);
        }

        $sections = $h->table('sections')->select()->fetchAll();

        echo $twig->render('user/templates/create.twig', [
            'template' => $template,
            'sections' => $sections,
            'template_sections' => $templateSections
        ]);
    }

endif;
