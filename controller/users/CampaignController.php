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
                            $contact = $row[1];
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
            if (!empty($_POST['list']) && !empty($_POST['name'])&&!empty($_POST['subject'])&& !empty($_POST['body'])&& !empty($_POST['date']) && !empty($_POST['timezone'])) {
                $list_id = $_POST['list'];
                $subject = $_POST['subject'];
                $body = $_POST['body'];
                $date = $_POST['date'];
                $name = $_POST['name'];
                $timezone = $_POST['timezone'];
            }else{
                echo "3";
                exit();
            }
            $campaign = $h->insert('campaign')->values([
                'name' => $name,
                'firm_id' => $loginUserId,
                'list_id' => $list_id,
                'subject' => $subject,
                'body' => $body,
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
        echo $twig->render('user/campaign/campaign.twig', ['seo' => $seo,'ListDetails' => $ListDetails,'world_time_zonesDetails' => $world_time_zonesDetails]);
    }

endif;
