<?php
require("config/env.php");
require 'vendor/autoload.php';
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
if($route == '/user/campaign/list'):
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!empty($_POST['name'])){
            if (!empty($_POST['name'])) {
                $name = $_POST['name'];
            }else{
                echo "2";
                exit();
            }
            $list_type = $_POST['list_type'];
            $des = $_POST['des'];
            try {
                if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
                    $fileName = $_FILES['file']['name'];
                    $fileTmp = $_FILES['file']['tmp_name'];
                    $fileType = $_FILES['file']['type'];
                    $allowed = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
                    if (in_array($fileType, $allowed)) {
                        $insert = $h->insert('campaign_list')->values([
                            'firm_id' => $loginUserId,
                            'name' => $name,
                            'list_type' => $list_type,
                            'des' => $des,
                        ])->run();
                        $spreadsheet = IOFactory::load($fileTmp);
                        $sheet = $spreadsheet->getActiveSheet();
                        $rows = $sheet->toArray();
                        $isFirstRow = true;
                        foreach ($rows as $row) {
                            if ($isFirstRow) {
                                $isFirstRow = false;
                                continue;
                            }
                            $srno = $row[0];
                            if ($list_type == 'email'){
                                $contact = $row[1];
                            }else{
                                $contact = $row[2];
                            }
                            if (empty($contact)) {
                                continue;
                            }
                            $insert_detail = $h->insert('campaign_list_detail')->values([
                                'firm_id' => $loginUserId,
                                'campaign_list_id' => $insert,
                                'contact' => $contact,
                            ])->run();
                        }
                    } else {
                        echo "3";
                        exit();
                    }
                } else {
                    echo "3";
                    exit();
                }
                echo "1";
                exit();
            } catch (PDOException $e) {
                echo "0";
                exit();
            }
        }
    }else{
        $seo = array(
            'title' => 'Campaign List',
            'description' => 'CRM',
            'keywords' => 'Admin Panel'
        );

        echo $twig->render('user/campaign/list.twig', ['seo' => $seo]);
    }

endif;
if($route == '/user/email/bulk_email'):
        $seo = array(
            'title' => 'Campaign List',
            'description' => 'CRM',
            'keywords' => 'Admin Panel'
        );
    $ListDetails = $h->table('campaign_list')->select()->where('firm_id', '=', $loginUserId)->fetchAll();
    $world_time_zonesDetails = $h->table('world_time_zones')->select()->fetchAll();
    $templatesDetails = $h->table('email_template')->select()->where('firm_id', '=', $loginUserId)->fetchAll();
    $campaign_lists = $h->table('campaign_list')->select()->where('firm_id', '=', $loginUserId)->where('list_type', 'email')->fetchAll();
    echo $twig->render('user/campaign/bulk_email.twig', ['seo' => $seo,'ListDetails' => $ListDetails,'world_time_zonesDetails' => $world_time_zonesDetails,'templatesDetails' => $templatesDetails,'campaign_lists' => $campaign_lists]);
endif;
if($route == '/user/sms/bulk_sms'):
        $seo = array(
            'title' => 'Campaign List',
            'description' => 'CRM',
            'keywords' => 'Admin Panel'
        );
    $ListDetails = $h->table('campaign_list')->select()->where('firm_id', '=', $loginUserId)->fetchAll();
    $world_time_zonesDetails = $h->table('world_time_zones')->select()->fetchAll();
    $templatesDetails = $h->table('email_template')->select()->where('firm_id', '=', $loginUserId)->fetchAll();
    $campaign_lists = $h->table('campaign_list')->select()->where('firm_id', '=', $loginUserId)->where('list_type', 'phone')->fetchAll();
    echo $twig->render('user/campaign/bulk_sms.twig', ['seo' => $seo,'ListDetails' => $ListDetails,'world_time_zonesDetails' => $world_time_zonesDetails,'templatesDetails' => $templatesDetails,'campaign_lists' => $campaign_lists]);
endif;
    if($route == '/user/campaign/list_details/$id'):

        $seo = array(
            'title' => 'Campaign List Details',
            'description' => 'CRM',
            'keywords' => 'Admin Panel'
        );
    unset($_SESSION['campaign_list_id']);
        $_SESSION['campaign_list_id'] = $id;
        $listDetails = $h->table('campaign_list')->select()->where('id', '=', $id)->fetchAll();
        echo $twig->render('user/campaign/list_detail.twig', ['seo' => $seo, 'listDetails' => $listDetails]);
endif;
if($route == '/user/campaign/start_campaign'):
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!empty($_POST['list'])){
            if (!empty($_POST['list']) && !empty($_POST['name'])&& !empty($_POST['date']) && !empty($_POST['timezone'])&& !empty($_POST['campaign_type'])) {
                $list_id = $_POST['list'];

                $date = $_POST['date'];
                $name = $_POST['name'];
                $timezone = $_POST['timezone'];
                $campaign_type = $_POST['campaign_type'];
            }else{
                echo "3";
                exit();
            }
            if ($campaign_type == 'email'){
                if (!empty($_POST['template_type'])){
                    $template_type = $_POST['template_type'];
                }else{
                    echo "3";
                    exit();
                }

if($template_type == 'your_email_template'){
    $body = '';
    if (!empty($_POST['template']) && !empty($_POST['subject'])){
        $template = $_POST['template'];
        $subject = $_POST['subject'];
    }else{
        echo "3";
        exit();
    }

}else{
    if (!empty($_POST['subject']) && !empty($_POST['body'])){
        $subject = $_POST['subject'];
        $body = $_POST['body'];
        $template = '';
    }else{
        echo "3";
        exit();
    }
}
            }else{
                $template_type = '';
                if (!empty($_POST['subject']) && !empty($_POST['body'])){
                    $subject = $_POST['subject'];
                    $body = $_POST['body'];
                    $template = '';
                }else{
                    echo "3";
                    exit();
                }
            }

            $campaign = $h->insert('campaign')->values([
                'name' => $name,
                'firm_id' => $loginUserId,
                'list_id' => $list_id,
                'campaign_type' => $campaign_type,
                'subject' => $subject,
                'body' => $body,
                'template_type' => $template_type,
                'template_id' => $template,
                'timezone' => $timezone,
                'date' => $date,
            ])->run();
            echo "1";
            exit();
        }else{
            echo "2";
            exit();
        }
    }else{
        $seo = array(
            'title' => 'Campaign Email Queue',
            'description' => 'CRM',
            'keywords' => 'Admin Panel'
        );
        $ListDetails = $h->table('campaign_list')->select()->where('firm_id', '=', $loginUserId)->fetchAll();
        $world_time_zonesDetails = $h->table('world_time_zones')->select()->fetchAll();
        $templatesDetails = $h->table('email_template')->select()->where('firm_id', '=', $loginUserId)->fetchAll();
        echo $twig->render('user/campaign/campaign.twig', ['seo' => $seo,'ListDetails' => $ListDetails,'world_time_zonesDetails' => $world_time_zonesDetails,'templatesDetails' => $templatesDetails]);

    }

endif;
if($route == '/user/get_campaign_list'):
    $list_type = $_POST['list_type'];
    $campaign_lists = $h->table('campaign_list')->select()->where('firm_id', '=', $loginUserId)->where('list_type', $list_type)->fetchAll();


    $output = '';
    if (!empty($campaign_lists)){
        foreach ($campaign_lists as $campaign_list) {

            $output .= '<option value="" selected >Choose List</option><option value="'.$campaign_list['id'].'">'.$campaign_list['name'].'</option>  ';
        }
    }else{
        $output .= '<option value="" selected >Choose List</option>';
    }

    echo $output;
    exit();
        endif;
if($route == '/user/campaign/list_detail/add'):
    $list_id = $_POST['list_id'];
    $contact = $_POST['contact'];
    $campaign_list_detail = $h->insert('campaign_list_detail')->values([
        'firm_id' => $loginUserId,
        'campaign_list_id' => $list_id,
        'contact' => $contact,
    ])->run();
    echo '1';
    exit();
        endif;