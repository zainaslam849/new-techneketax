<?php
require("config/env.php");

if($route == '/admin/site_settings'):

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //LOGIN
        if(isset($_POST['name'])):
                $name = $_POST['name'];
                $logo_image=upload('logo', 'uploads/settings/');
                $favicon_image=upload('favicon', 'uploads/settings/');
                $site_setting = $h->table('site_setting')->select()->where('id', '=','1')->fetchAll();
                if ($logo_image == 'null'){
                    $logo_image = $site_setting[0]['logo'];
                }
                if ($favicon_image == 'null'){
                    $favicon_image = $site_setting[0]['favicon'];
                }
                try {
                    $update = $h->update('site_setting')->values([
                        'name' => $name,
                        'logo' => $logo_image,
                        'favicon' => $favicon_image
                    ])->where('id','=','1')->run();
                    echo "1";
                    exit();
                } catch (PDOException $e) {
                    echo "0";
                    exit();                }
        endif;
        exit();

    }else{
        $seo = array(
            'title' => 'Site Settings | Techneketax',
            'description' => 'Enter your username or email address to log in.',
            'keywords' => 'Site Settings, Settings'
        );
        $SiteInfo=$h->table('site_setting')->select()->where('id', '=', '1')->fetchAll();
        $siteinfo = array(
            'name' => $SiteInfo[0]['name'],
            'logo' => $SiteInfo[0]['logo'],
            'favicon' => $SiteInfo[0]['favicon']
        );
        echo $twig->render('admin/settings/site_setting.twig', ['seo'=>$seo,'siteinfo' => $siteinfo, 'csrf'=>set_csrf()]);
    }
endif;