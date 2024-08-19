<?php
/////////////////////////
///////ZOTEC FRAMEWORK
//////admin@zotecsoft.com
////////////////////////
require 'vendor/autoload.php';
use PHRETS\Configuration;
use PHRETS\Session;
use PHRETS\Models\Search\Results;
use Intervention\Image\ImageManagerStatic as Image;
use Tracy\Debugger;
//use GuzzleHttp\Client;
use Twilio\Rest\Client;
use Carbon\Carbon;
//Debugger::enable();
session_start();



function get($route, $path_to_include, $page_name=NULL){
    if( $_SERVER['REQUEST_METHOD'] == 'GET' ){

        route($route, $path_to_include, $page_name);

    }
}
function post($route, $path_to_include,$page_name=NULL){
    if( $_SERVER['REQUEST_METHOD'] == 'POST' ){ route($route, $path_to_include,$page_name ); }
}
function put($route, $path_to_include){
    if( $_SERVER['REQUEST_METHOD'] == 'PUT' ){ route($route, $path_to_include); }
}
function patch($route, $path_to_include){
    if( $_SERVER['REQUEST_METHOD'] == 'PATCH' ){ route($route, $path_to_include); }
}
function delete($route, $path_to_include){
    if( $_SERVER['REQUEST_METHOD'] == 'DELETE' ){ route($route, $path_to_include); }
}
function any($route, $path_to_include){ route($route, $path_to_include); }
function route($route, $path_to_include, $page_name=NULL){
    $PAGE_NAME=$page_name;
    $ROOT = $_SERVER['DOCUMENT_ROOT'];
    if($route == "/404"){
        include_once("$ROOT/$path_to_include");
        exit();
    }
    $request_url = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
    $request_url = rtrim($request_url, '/');
    $request_url = strtok($request_url, '?');
    $route_parts = explode('/', $route);
    $request_url_parts = explode('/', $request_url);
    array_shift($route_parts);
    array_shift($request_url_parts);
    if( @$route_parts[0] == '' && count($request_url_parts) == 0 ){
        include_once("$ROOT/$path_to_include");
        exit();
    }
    if( count($route_parts) != count($request_url_parts) ){ return; }
    $parameters = [];
    for( $__i__ = 0; $__i__ < count($route_parts); $__i__++ ){
        $route_part = $route_parts[$__i__];
        if( preg_match("/^[$]/", $route_part) ){
            $route_part = ltrim($route_part, '$');
            array_push($parameters, $request_url_parts[$__i__]);
            $$route_part=$request_url_parts[$__i__];
        }
        else if( $route_parts[$__i__] != $request_url_parts[$__i__] ){
            return;
        }
    }
    include_once("$ROOT/$path_to_include");
    exit();
}
function out($text){echo htmlspecialchars($text);}
function set_csrf(){
    $csrf_token = bin2hex(random_bytes(25));
    $_SESSION['csrf'] = $csrf_token;
    return $csrf_token;
}
function is_csrf_GET_script(){
    if( ! isset($_SESSION['csrf']) || ! isset($_GET['csrf'])){ return false; }
    if( $_SESSION['csrf'] != $_GET['csrf']){ return false; }
    return true;
}
function is_csrf_valid(){
    if( ! isset($_SESSION['csrf']) || ! isset($_POST['csrf'])){ return false; }
    if( $_SESSION['csrf'] != $_POST['csrf']){ return false; }
    return true;
}

function commaSeperated($string){
    $prefix=$temp="";
    foreach ($string as $s){
        if(!empty($s || $s != NULL)):
        $temp.=$prefix.$s;
        $prefix=',';
        endif;
    }
    return $temp;
}
function commaSeperatedToArray($string){
    $str_arr = explode (",", trim($string));
    return $str_arr;
}
function checkLanguage($lang_arr, $value){
    $re="selected";
    foreach ($lang_arr as $lang){
        if($lang== $value){
            return $re;
        }
    }
}
function upload($f_name, $f_path){
    $target_dir = $f_path;
    $target_file = $target_dir .basename($_FILES[$f_name]['name']);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
// Check if image file is a actual image or fake image
    if(isset($_POST["submit"])) {
        $check = getimagesize($_FILES[$f_name]["tmp_name"]);
        if($check !== false) {
            return "File is an image - " . $check["mime"] . ".";
            $uploadOk = 1;
        } else {
            return "File is not an image.";
            $uploadOk = 0;
        }
    }
// Check if file already exists
//    if (file_exists($target_file)) {
//        return "Sorry, file already exists.";
//        $uploadOk = 0;
//    }
// Check file size
    if ($_FILES[$f_name]["size"] > 5000000000) {
        return "null";
        $uploadOk = 0;
    }
// Allow certain file formats
    if($imageFileType != "jpg" &&  $imageFileType != "JPG" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "webp"
        && $imageFileType != "svg" && $imageFileType != "pdf" && $imageFileType != "docx" && $imageFileType != "gif") {
        return "null";
        $uploadOk = 0;
    }
// Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        return "null";
// if everything is ok, try to upload file
    } else {

        $temp = explode(".", $_FILES[$f_name]['name']);
        $orignalName = pathinfo($_FILES[$f_name]['name'], PATHINFO_FILENAME);
        $newfilename = rand().round(microtime(true)) . '.' . end($temp);

        if (move_uploaded_file($_FILES[$f_name]["tmp_name"], $target_dir.$newfilename)) {
            return $newfilename;
        } else {
            return "null";
        }
    }
}
function uploadFirmDocumentFile($userFolder, $file) {
    $response = [
        'status' => 'error',
        'message' => '',
        'file_path' => '',
    ];

    // Define the allowed file types
    $allowedFileTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];

    // Check if the file type is allowed
    if (!in_array($file['type'], $allowedFileTypes)) {
        $response['message'] = 'Invalid file type. Only PDF and Word documents are allowed.';
        return $response;
    }

    // Define the target directory
    $targetDirectory = 'uploads/firm_document/' . $userFolder;

    // Check if the directory exists, if not create it
    if (!file_exists($targetDirectory)) {
        if (!mkdir($targetDirectory, 0777, true)) {
            $response['message'] = 'Failed to create user directory.';
            return $response;
        }
    }

    // Add a random string to the file name
    $randomString = random_strings(5);
    $fileName = pathinfo($file['name'], PATHINFO_FILENAME);
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFileName = $fileName . '_' . $randomString . '.' . $fileExtension;

    // Define the target file path
    $targetFile = $targetDirectory . '/' . $newFileName;

    // Attempt to move the uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        $response['status'] = 'success';
        $response['message'] = 'File uploaded successfully.';
        $response['file_path'] = $targetFile;
        $response['file_name'] = $newFileName;

    } else {
        $response['message'] = 'Failed to upload file.';
    }

    return $response;
}
function uploadFile($firmName, $userName, $file) {
    $response = array();
    $uploadDir = 'uploads/';
    $firmFolder = $uploadDir . $firmName;
    $userFolder = $firmFolder . '/' . $userName;
    try {
        // Check if the firm folder exists, if not create it
        if (!is_dir($firmFolder)) {
            if (!mkdir($firmFolder, 0777, true)) {
                throw new Exception('Failed to create firm folder');
            }
        }
        // Check if the user folder exists, if not create it
        if (!is_dir($userFolder)) {
            if (!mkdir($userFolder, 0777, true)) {
                throw new Exception('Failed to create user folder');
            }
        }
        // Check if file was uploaded
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new Exception('Invalid file parameters');
        }
        // Check for upload errors
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new Exception('No file sent');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new Exception('Exceeded filesize limit');
            default:
                throw new Exception('Unknown errors');
        }
        // Define the allowed file types
        $allowedTypes = array(
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/zip'
        );
        // Check the file type
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Invalid file type');
        }
        // Define the file path with a random file name
        $randomFileName = uniqid() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $filePath = $userFolder . '/' . $randomFileName;

        // Move the uploaded file to the user folder
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception('Failed to move uploaded file');
        }
        $response['status'] = 'success';
        $response['message'] = 'File uploaded successfully';
        $response['file_path'] = $filePath;
    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = $e->getMessage();
    }
    return $response;
}
//Multi  Image Resize Upload
function uploadMultiResizeImage($f_name, $f_path,$f_thumbnail,$width,$height)
{
    $insertValuesSQL[]='';
    $targetDir = $f_path;
    $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
    $fileNames = array_filter($_FILES[$f_name]['name']);
    if (!empty($fileNames)) {
        foreach ($_FILES[$f_name]['name'] as $key => $val) {
            // File upload path
            $fileName = basename($_FILES[$f_name]['name'][$key]);
            $targetFilePath = $targetDir . $fileName;

            // Check whether file type is valid
            $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
            if (in_array($fileType, $allowTypes)) {
                $temp = explode(".", $_FILES[$f_name]['name'][$key]);
                $newfilename = round(microtime(true)).rand(100,10000).'.' . end($temp);
                // Upload file to server
                if (move_uploaded_file($_FILES[$f_name]["tmp_name"][$key], $targetDir . $newfilename)) {
                    $filepath=$targetDir.$newfilename;
                    $thumbnailPath = $f_thumbnail . $newfilename;
                    $img = Image::make($filepath);
                    $img->resize($width, $height, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                    $img->save($thumbnailPath);
                    // Image db insert sql
                    $insertValuesSQL[] .= $newfilename;
                }
            }
        }
        $imgz=trim(implode(',',$insertValuesSQL), ",");
        return $imgz;
    }
}
//Favicon Resize Upload
function uploadResizeFavicon($f_name, $f_path,$f_thumbnail,$width,$height){

    $target_dir = $f_path;
    $target_file = $target_dir . basename($_FILES[$f_name]['name']);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

// Check if image file is a actual image or fake image
    if(isset($_POST["submit"])) {
        $check = getimagesize($_FILES[$f_name]["tmp_name"]);
        if($check !== false) {
            return "File is an image - " . $check["mime"] . ".";
            $uploadOk = 1;
        } else {
            return "File is not an image.";
            $uploadOk = 0;
        }
    }
// Check if file already exists
    if (file_exists($target_file)) {
        return "Sorry, file already exists.";
        $uploadOk = 0;
    }
// Check file size
    if ($_FILES[$f_name]["size"] > 5000000000) {
        return "Sorry, your file is too large.";
        $uploadOk = 0;
    }
// Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "svg" && $imageFileType != "pdf" && $imageFileType != "docx") {
        return "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }
// Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        return "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
    } else {

        $temp = explode(".", $_FILES[$f_name]['name']);
        $newfilename = round(microtime(true)) . '.' . end($temp);

        if (move_uploaded_file($_FILES[$f_name]["tmp_name"], $target_dir.$newfilename)) {
            $filepath=$target_dir.$newfilename;
            $thumbnailPath = $f_thumbnail.$newfilename;
            $img = Image::make($filepath);
            $img->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->save($thumbnailPath);
            return $newfilename;
        } else {
            return "Sorry, there was an error uploading your file.";
        }
    }
}


//Single  Image Resize Upload
function uploadResizeImage($f_name, $f_path,$f_thumbnail,$width,$height){

    $target_dir = $f_path;
    $target_file = $target_dir . basename($_FILES[$f_name]['name']);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

// Check if image file is a actual image or fake image
    if(isset($_POST["submit"])) {
        $check = getimagesize($_FILES[$f_name]["tmp_name"]);
        if($check !== false) {
            return array('error'=>true, 'message'=>"File is an image - " . $check["mime"] . ".");
            $uploadOk = 1;
        } else {
            return array('error'=>true, 'message'=>"File is not an image.");
            $uploadOk = 0;
        }
    }
// Check if file already exists
    if (file_exists($target_file)) {
        return array('error'=>true, 'message'=>"Sorry, file already exists.");
        $uploadOk = 0;
    }
// Check file size
    if ($_FILES[$f_name]["size"] > 5000000000) {
        return array('error'=>true, 'message'=>"Sorry, your file is too large.");
        $uploadOk = 0;
    }
// Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "svg" && $imageFileType != "pdf" && $imageFileType != "docx") {
        return array('error'=>true, 'message'=>"Sorry, only JPG, JPEG, PNG files are allowed.");

        $uploadOk = 0;
    }
// Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        return array('error'=>true, 'message'=>"Sorry, your file was not uploaded.");
// if everything is ok, try to upload file
    } else {

        $temp = explode(".", $_FILES[$f_name]['name']);
        $newfilename = round(microtime(true)) . '.' . end($temp);

        if (move_uploaded_file($_FILES[$f_name]["tmp_name"], $target_dir.$newfilename)) {
            $filepath=$target_dir.$newfilename;
            $thumbnailPath = $f_thumbnail.$newfilename;
            $img = Image::make($filepath);
            $img->resize($width, $height);//, function ($constraint) {
                //$constraint->aspectRatio();
                //$constraint->upsize();
            //});
            $img->save($thumbnailPath);
            return array('error'=>false, 'message'=>"Upload Successfully",'filename'=>$newfilename );
            //return $newfilename;
        } else {
            return array('error'=>true, 'message'=>"Sorry, there was an error uploading your file.");
        }
    }
}
function uploadDoc($f_name, $f_path){
    $target_dir = $f_path;
    $target_file = $target_dir . basename($_FILES[$f_name]['name']);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
// Check if image file is a actual image or fake image
    if(isset($_POST["submit"])) {
        $check = getimagesize($_FILES[$f_name]["tmp_name"]);
        if($check !== false) {
            return "File is an Zip - " . $check["mime"] . ".";
            $uploadOk = 1;
        } else {
            return "File is not an image.";
            $uploadOk = 0;
        }
    }
// Check if file already exists
    if (file_exists($target_file)) {
        return $arr=array("file_name"=>"not found", "status"=>"202", "error"=>"Sorry, file already exists.");
        $uploadOk = 0;
    }
// Check file size
    if ($_FILES[$f_name]["size"] > 5000000000) {
        return $arr=array("file_name"=>"not found", "status"=>"202", "error"=>"Sorry, your file is too large.");
        $uploadOk = 0;
    }
// Allow certain file formats
    if($imageFileType != "zip" && $imageFileType != "ZIP" && $imageFileType != "rar") {
        return $arr=array("file_name"=>"not found", "status"=>"202", "error"=>"Sorry, only zip, rar files are allowed.");
        $uploadOk = 0;
    }
// Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        return $arr=array("file_name"=>"not found", "status"=>"202", "error"=>"Sorry, your file was not uploaded.");

// if everything is ok, try to upload file
    } else {
        $temp = explode(".", $_FILES[$f_name]['name']);
        $newfilename = round(microtime(true)) . '.' . end($temp);

        if (move_uploaded_file($_FILES[$f_name]["tmp_name"], $target_dir.$newfilename)) {

            return $arr=array("file_name"=>$newfilename, "status"=>"200", "error"=>"File Successfully Submitted.");
        } else {
            return $arr=array("file_name"=>"not found", "status"=>"202", "error"=>"Sorry, there was an error uploading your file.");
        }
    }
}
function datetimeToDate($date){
    $datetime   = strtotime($date);
    return date('Y-m-d', $datetime);
}

function get_words($sentence, $count = 15) {
    preg_match("/(?:\w+(?:\W+|$)){0,$count}/", $sentence, $matches);
    return $matches[0].'....';
}
function getIPAddress() {
    //whether ip is from the share internet
    if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];

    }

    //whether ip is from the proxy
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
//whether ip is from the remote address
    else{
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}
//GET COUNTRY
function getClientCountry($ip) {
    //whether ip is from the share internet

    $iptolocation = 'http://api.hostip.info/country.php?ip='.$ip;

    return $creatorlocation = trim(file_get_contents($iptolocation));

}
//GET OS
$user_agent = $_SERVER['HTTP_USER_AGENT'];
function getOS() {

    global $user_agent;

    $os_platform  = "Unknown OS Platform";

    $os_array     = array(
        '/windows nt 10/i'      =>  'Windows',
        '/windows nt 6.3/i'     =>  'Windows',
        '/windows nt 6.2/i'     =>  'Windows',
        '/windows nt 6.1/i'     =>  'Windows',
        '/windows nt 6.0/i'     =>  'Windows',
        '/windows nt 5.2/i'     =>  'Windows',
        '/windows nt 5.1/i'     =>  'Windows',
        '/windows xp/i'         =>  'Windows',
        '/windows nt 5.0/i'     =>  'Windows',
        '/windows me/i'         =>  'Windows',
        '/win98/i'              =>  'Windows',
        '/win95/i'              =>  'Windows',
        '/win16/i'              =>  'Windows',
        '/macintosh|mac os x/i' =>  'Mac',
        '/mac_powerpc/i'        =>  'Mac',
        '/linux/i'              =>  'Linux',
        '/ubuntu/i'             =>  'Ubuntu',
        '/iphone/i'             =>  'iPhone',
        '/ipod/i'               =>  'iPod',
        '/ipad/i'               =>  'iPad',
        '/android/i'            =>  'Android',
        '/blackberry/i'         =>  'BlackBerry',
        '/webos/i'              =>  'Mobile'
    );

    foreach ($os_array as $regex => $value)
        if (preg_match($regex, $user_agent))
            $os_platform = $value;

    return $os_platform;
}
function getBrowser() {

    global $user_agent;

    $browser        = "Unknown Browser";

    $browser_array = array(
        '/msie/i'      => 'Internet Explorer',
        '/firefox/i'   => 'Firefox',
        '/safari/i'    => 'Safari',
        '/chrome/i'    => 'Chrome',
        '/edge/i'      => 'Edge',
        '/opera/i'     => 'Opera',
        '/netscape/i'  => 'Netscape',
        '/maxthon/i'   => 'Maxthon',
        '/konqueror/i' => 'Konqueror',
        '/mobile/i'    => 'Handheld Browser'
    );

    foreach ($browser_array as $regex => $value)
        if (preg_match($regex, $user_agent))
            $browser = $value;

    return $browser;
}
function ip_info($ip = NULL, $purpose = "location", $deep_detect = TRUE) {
    $output = NULL;
    if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
        $ip = $_SERVER["REMOTE_ADDR"];
        if ($deep_detect) {
            if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
    }
    $purpose    = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($purpose)));
    $support    = array("country", "countrycode", "state", "region", "city", "location", "address");
    $continents = array(
        "AF" => "Africa",
        "AN" => "Antarctica",
        "AS" => "Asia",
        "EU" => "Europe",
        "OC" => "Australia (Oceania)",
        "NA" => "North America",
        "SA" => "South America"
    );
    if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
        $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
        if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
            switch ($purpose) {
                case "location":
                    $output = array(
                        "city"           => @$ipdat->geoplugin_city,
                        "state"          => @$ipdat->geoplugin_regionName,
                        "country"        => @$ipdat->geoplugin_countryName,
                        "country_code"   => @$ipdat->geoplugin_countryCode,
                        "continent"      => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
                        "continent_code" => @$ipdat->geoplugin_continentCode
                    );
                    break;
                case "address":
                    $address = array($ipdat->geoplugin_countryName);
                    if (@strlen($ipdat->geoplugin_regionName) >= 1)
                        $address[] = $ipdat->geoplugin_regionName;
                    if (@strlen($ipdat->geoplugin_city) >= 1)
                        $address[] = $ipdat->geoplugin_city;
                    $output = implode(", ", array_reverse($address));
                    break;
                case "city":
                    $output = @$ipdat->geoplugin_city;
                    break;
                case "state":
                    $output = @$ipdat->geoplugin_regionName;
                    break;
                case "region":
                    $output = @$ipdat->geoplugin_regionName;
                    break;
                case "country":
                    $output = @$ipdat->geoplugin_countryName;
                    break;
                case "countrycode":
                    $output = @$ipdat->geoplugin_countryCode;
                    break;
            }
        }
    }
    return $output;
}
function random_strings($length_of_string)
{
    $str_result = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz';
    return substr(str_shuffle($str_result), 0, $length_of_string);
}
function TwoFA($email, $password, $table_name){
    global $h,$sql,$settings,$message,$env,$mail,$loginUserId;
    if(isset($email) && !empty($email) && isset($password) && !empty($password)){
//        if( ! is_csrf_v_script()){
//            http_response_code(202);
//            return json_encode(array("statusCode" => 202, "message"=>"Invalid CSRF Token. Please <a href='#' onclick='refresh_page()'> Refresh Page.</a>"));
//            exit();
//        }
        $stmt = $h->$table_name->select()->where('email', '=', $email)->fetchAll();
        if (!empty($stmt[0])) {
            if(password_verify($password, $stmt[0]['password'])){
                if($stmt[0]['status'] =='active'){
                    $verify_code=round(microtime(true));
                    $sql= $h->$table_name->update([
                        'verify_code' => $verify_code,
                    ])->where('email', '=', $email)->run();
                    unset($_SESSION['loginemail']);
                    unset($_SESSION['loginpassword']);
                    unset($_SESSION['loginpath']);
                    $_SESSION['loginemail'] = $email;
                    $_SESSION['loginpassword'] = $password;
                    //FORGET EMAIL
                        $AdminInfo = $h->table('users')->select()->where('type', '=', 'admin')->fetchAll();
                        @$company_name =  @$AdminInfo[0]['fname'].' '.@$AdminInfo[0]['lname'];
                        @$company_phone =  @$AdminInfo[0]['phone'];
                        @$company_email =  @$AdminInfo[0]['email'];
                        @$imgUrl = $env['APP_URL'].'assets/techneketax-black.png';



                    include "./views/email-template/login2fa.php";
                    mailSender($env['SENDER_EMAIL'],$email,'2FA For Login - '.$env['SITE_NAME'],$message,$mail);
                    http_response_code(200);
                    return json_encode(array("statusCode" => 200, "message"=>"2Fa For Login email has been send to your inbox." , "path"=>"/2fa/login"));
                }else{
                    http_response_code(202);
                    return json_encode(array("statusCode" => 202, "message"=>"Sorry! you are blocked!"));
                }
            }else{
                http_response_code(202);
                return json_encode(array("statusCode" => 202, "message"=>"Invalid Password"));
            }
        }else{
            http_response_code(202);
            return json_encode(array("statusCode" => 202, "message"=>"Invalid Email!"));
        }
    }

}
function userLogin($email, $password,$code, $table_name){
    global $h,$sql;
    if(isset($email) && !empty($email) && isset($password) && !empty($password)) {
//        if( ! is_csrf_v_script()){
//            http_response_code(202);
//            return json_encode(array("statusCode" => 202, "message"=>"Invalid CSRF Token. Please <a href='#' onclick='refresh_page()'> Refresh Page.</a>"));
//            exit();
//        }
        $stmt = $h->$table_name->select()->where('email', '=', $email)->fetchAll();
        if (!empty($stmt[0])) {
            if (password_verify($password, $stmt[0]['password'])) {
                if ($stmt[0]['status'] == 'active') {
                    if ($stmt[0]['verify_code'] == $code) {
                        $_SESSION[$table_name] = $stmt[0];
                        if ($stmt[0]['type'] == 'admin') {
                            http_response_code(200);
                            return json_encode(array("statusCode" => 200, "message" => "Successfully Login..", "path" => "/admin/dashboard"));

                            exit();
                        } else {
                            http_response_code(200);
                            return json_encode(array("statusCode" => 200, "message" => "Successfully Login..", "path" => '/user/dashboard'));
                            exit();
                        }
                    } else {
                        http_response_code(202);
                        return json_encode(array("statusCode" => 202, "message" => "Wrong Authentication Code"));
                    }
                } else {
                    http_response_code(202);
                    return json_encode(array("statusCode" => 202, "message" => "Sorry! you are blocked!"));
                }

            } else {
                http_response_code(202);
                return json_encode(array("statusCode" => 202, "message" => "Invalid Password"));
            }
        }else{
            http_response_code(202);
            return json_encode(array("statusCode" => 202, "message"=>"Invalid Email!"));
        }
    }
}
function Login($email, $password,$table_name){
    global $h,$sql;
    if(isset($email) && !empty($email) && isset($password) && !empty($password)) {
//        if( ! is_csrf_v_script()){
//            http_response_code(202);
//            return json_encode(array("statusCode" => 202, "message"=>"Invalid CSRF Token. Please <a href='#' onclick='refresh_page()'> Refresh Page.</a>"));
//            exit();
//        }
        $stmt = $h->$table_name->select()->where('email', '=', $email)->fetchAll();
        if (!empty($stmt[0])) {
            if (password_verify($password, $stmt[0]['password'])) {
                if ($stmt[0]['status'] == 'active') {
                    $_SESSION[$table_name] = $stmt[0];
                    if ($stmt[0]['type'] == 'admin') {
                        http_response_code(200);
                        return json_encode(array("statusCode" => 200, "message" => "Successfully Login..", "path" => "/admin/dashboard"));
                        exit();
                    } else {
                        http_response_code(200);
                        return json_encode(array("statusCode" => 200, "message" => "Successfully Login..", "path" => '/user/dashboard'));
                        exit();
                    }
                } else {
                    http_response_code(202);
                    return json_encode(array("statusCode" => 202, "message" => "Sorry! you are blocked!"));
                }

            } else {
                http_response_code(202);
                return json_encode(array("statusCode" => 202, "message" => "Invalid Password"));
            }
        }else{
            http_response_code(202);
            return json_encode(array("statusCode" => 202, "message"=>"Invalid Email!"));
        }
    }
}

function userRegister($first_name, $last_name, $email,$phone, $password, $account_type, $table_name){
    global $h;
    global $env,$message,$mail,$loginUserId;
    if(isset($email) && !empty($email) && isset($password) && !empty($password) && isset($first_name) && !empty($first_name) && isset($last_name) && !empty($last_name) && isset($phone) && !empty($phone)){
//        if( ! is_csrf_valid()){
//            http_response_code(202);
//            return json_encode(array("statusCode" => 202, "message"=>"Invalid CSRF Token. Please <a href='javascript:refresh_page()' onclick='refresh_page();return false;'> Refresh Page.</a>"));
//            exit();
//        }

        if (isset($password) && !empty($password)) {
            $uppercase = preg_match('@[A-Z]@', $password);
            $lowercase = preg_match('@[a-z]@', $password);
            $number = preg_match('@[0-9]@', $password);

            if (!$uppercase || !$lowercase || !$number || strlen($_POST['password']) < 8) {
                http_response_code(202);
                return json_encode(array("statusCode" => 202, "message"=>"A minimum 8 characters password contains a combination of uppercase and lowercase letter and number."));
                exit();
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            }
        } else {
            http_response_code(202);
            return json_encode(array("statusCode" => 202, "message"=>"Password is Required."));
            exit();
        }

        $userAvailable = $h->table($table_name)->select()->where('email', '=', $email);

        if($userAvailable->count() < 1){
            try{
                $insert = $h->insert($table_name)->values([
                    'fname'=> $first_name,
                    'lname'=> $last_name,
                    'email' => $email,
                    'phone'=> $phone,
                    'account_type'=> $account_type,
                    'password'=> $hashed_password,
                ])->run();
                    $AdminInfo = $h->table('users')->select()->where('type', '=', 'admin')->fetchAll();
                    @$company_name =  @$AdminInfo[0]['fname'].' '.@$AdminInfo[0]['lname'];
                    @$company_phone =  @$AdminInfo[0]['phone'];
                    @$company_email =  @$AdminInfo[0]['email'];
                    @$imgUrl = $env['APP_URL'].'assets/techneketax-black.png';


                    include "views/email-template/WelcomeRegister.php";
    mailSender($env['SENDER_EMAIL'],$email,'Welcome at - '.$env['SITE_NAME'],$message,$mail);
                return json_encode(array("statusCode" => 200, "message"=>"Successfully Registered."));

            }catch(PDOException $e) {
                return json_encode(array("statusCode" => 202, "message"=>"Server Side error try again!"));
                exit();
            }
        }else
            http_response_code(202);
        return json_encode(array("statusCode" => 202, "message"=>"Email Already Exist. Login to continue."));
    }
}

function setPassword($email, $password, $verify_code, $table_name){
    global $h;
//CHECK PASSWORD
    if (isset($password) && !empty($password)) {
        $uppercase = preg_match('@[A-Z]@', $password);
        $lowercase = preg_match('@[a-z]@', $password);
        $number = preg_match('@[0-9]@', $password);

        if (!$uppercase || !$lowercase || !$number || strlen($password) < 8) {
            http_response_code(202);
            return json_encode(array("statusCode" => 202, "message"=>"A minimum 8 characters password contains a combination of uppercase and lowercase letter and number."));
            exit();
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        }
    } else {
        http_response_code(202);
        return json_encode(array("statusCode" => 202, "message"=>"Password is Required."));
        exit();
    }
    $noRows = $h->$table_name->select()
        ->where('email', '=', $email)
        ->where('verify_code', '=', $verify_code);

    // $noRows = $CONN->query("SELECT COUNT(*) FROM $table_name WHERE `email`='$email' AND verify_code='$verify_code'")->fetchColumn();
    if($noRows->count() < 1){
        http_response_code(202);
        return json_encode(array("statusCode" => 202, "message"=>"Wrong Email or Verification Code."));
    }else{
        try{
            $updatePassSQL= $h->$table_name->update([
                'password' => $hashed_password,
            ])
                ->where('email', '=', $email)
                ->where('verify_code', '=', $verify_code)->run();

//            $updatePassSQL = "UPDATE $table_name SET `password`=? WHERE `email`=? AND `verify_code`=?";
//            $CONN->prepare($updatePassSQL)->execute([$hashed_password, $email, $verify_code]);

            //change Verify Code
            $verify_code=round(microtime(true));
            $h->$table_name->update([
                'verify_code' => $verify_code,
            ])
                ->where('email', '=', $email)
                ->run();

//            $changeVerifyCodeSQL = "UPDATE $table_name SET `verify_code`=? WHERE `email`=?";
//            $CONN->prepare($changeVerifyCodeSQL)->execute([$verify_code, $email]);

            http_response_code(200);
            return json_encode(array("statusCode" => 200, "message"=>"Password Successfully Changed"));
        }catch (PDOException $e){
            http_response_code(202);
            return json_encode(array("statusCode" => 202, "message"=>$e));
        }
    }
}
function forgetPassword($email, $table_name){
    global $h;
    $userAvailable = $h->$table_name->select()
        ->where('email', '=', $email);
    // $userAvailable = $CONN->query("SELECT COUNT(*) FROM $table_name WHERE `email`='$email'")->fetchColumn();
    if($userAvailable->count() < 1){
        http_response_code(202);
        return json_encode(array("statusCode" => 202, "message"=>"Email Not Found. Please SignUp to Continue."));
        exit();
    }else{
        try{
            forgetPasswordEmail($email, $table_name);

            http_response_code(200);
            return json_encode(array("statusCode" => 200, "message"=>"Forget password email has been send to your inbox."));
        }catch(PDOException $e){
            http_response_code(202);
            return json_encode(array("statusCode" => 202, "message"=>$e));
        }

    }
}
///MAIL SENDER
function mailSender($admin_email,$email,$subject,$message,$mail){
    global $env;
    //Recipients
    $mail->setFrom($admin_email, $env['SITE_NAME']);
    $mail->addAddress($email);               //Name is optional
    $mail->addReplyTo($admin_email);
    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = $subject;
    $mail->Body    = $message;

    $mail->send();
}
function forgetPasswordEmail($email, $table_name){
    global $h;
    global $env,$message,$mail;
    $verify_code=round(microtime(true));
    //SAVING VERIFICATION CODE
//    $sql = "UPDATE $table_name SET `verify_code`=? WHERE `email`=?";
//    $CONN->prepare($sql)->execute([$verify_code, $email]);
    $_SESSION['reset']= $email;
    $sql= $h->$table_name->update([
        'verify_code' => $verify_code,
    ])->where('email', '=', $email)->run();

    //FORGET EMAIL
    $AdminInfo = $h->table('users')->select()->where('type', '=', 'admin')->fetchAll();
    @$company_name =  @$AdminInfo[0]['fname'].' '.@$AdminInfo[0]['lname'];
    @$company_phone =  @$AdminInfo[0]['phone'];
    @$company_email =  @$AdminInfo[0]['email'];
    @$imgUrl = $env['APP_URL'].'assets/techneketax-black.png';
    include "views/email-template/forget-password.php";

    mailSender($env['SENDER_EMAIL'],$email,'Forget Password - '.$env['SITE_NAME'],$message,$mail);


}

function define_once($name, $value){
    if (!defined($name)) define($name, $value);
}
function userDetails($loginUserId){
    global $CONN;
    $userDetails = $CONN->query("SELECT * FROM users WHERE id='$loginUserId'")->fetch();
    return $userDetails;
}
function slugify($str, $delimiter = '-')
{
    $slug = strtolower(trim(preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))), $delimiter));
    return $slug;
}



function sendNotification($heading,$your_message, $url,$player_ids){

    $apiKey = 'MzIwZDFkMmMtMGRmMy00NTJlLWI0ZmQtZmFhMWYyYWIzZjcy';
    $appId = '6a740caf-b162-4bd0-8bc9-6e5a30ec55f6';

    $notificationData = array(
        'app_id' => $appId,
//       'included_segments' => array('Total Subscriptions'),
        'include_subscription_ids' => array($player_ids),
        'contents' => array('en' => $your_message),
        'headings' => array('en' => $heading),
        'large_icon' => 'https://res.cloudinary.com/cyclone-coders/image/upload/v1696776542/DTF%20APP/notification-icon_xkuzi1.png',
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://onesignal.com/api/v1/notifications');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Basic ' . $apiKey,
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notificationData));
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;

}
function getRelativeTime($timestamp, $timezone = 'UTC') {
    // Create a DateTime object for the provided timestamp
    $dateTime = new DateTime($timestamp, new DateTimeZone($timezone));

    // Create a DateTime object for the current time in the specified timezone
    $currentTime = new DateTime('now', new DateTimeZone($timezone));

    // Calculate the difference between the current time and the provided timestamp
    $interval = $currentTime->diff($dateTime);

    // Determine the relative time string based on the interval
    if ($interval->y > 0) {
        // Years
        return $interval->y === 1 ? '1 year ago' : $interval->y . ' years ago';
    } elseif ($interval->m > 0) {
        // Months
        return $interval->m === 1 ? '1 month ago' : $interval->m . ' months ago';
    } elseif ($interval->d > 0) {
        // Days
        return $interval->d === 1 ? '1 day ago' : $interval->d . ' days ago';
    } elseif ($interval->h > 0) {
        // Hours
        return $interval->h === 1 ? '1 hour ago' : $interval->h . ' hours ago';
    } elseif ($interval->i > 0) {
        // Minutes
        return $interval->i === 1 ? '1 minute ago' : $interval->i . ' minutes ago';
    } else {
        // Seconds
        return $interval->s <= 1 ? '1 second ago' : $interval->s . ' seconds ago';
    }
}
function getStatusDescription($status) {
    switch ($status) {
        case "A":
            return "Active";
        case "P":
            return "Pending";
        case "S":
            return "Sold";
        case "X":
            return "Expired";
        case "C":
            return "Closed";
        default:
            return "Unknown";
    }
}

function saveJsonToFile($data, $filename) {
    $json = json_encode($data);
    if (file_put_contents($filename, $json)) {
        return true;
    } else {
        return false;
    }
}

// Function to remove JSON file
function removeJsonFile($filename) {
    if (file_exists($filename)) {
        if (unlink($filename)) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}
// Function to update JSON file with no data
function updateJsonFile($filename, $json="{}") {
    if (file_put_contents($filename, $json)) {
        return true;
    } else {
        return false;
    }
}
function getFirstSixImagesWithLast($images) {
    $imageNames = $images['image_names'];
    $totalImages = count($imageNames);
    if ($totalImages <= 6) {
        return $imageNames;
    }
    $firstSixImages = array_slice($imageNames, 0, 6);
    $lastImage = $imageNames[$totalImages - 1];
    if (count($firstSixImages) === 6) {
        array_pop($firstSixImages);
    }

    // Combine the first six images and the last image into a new array
    $result = ['images' => $firstSixImages, 'last_image' => $lastImage, 'total_images' => $totalImages];

    return $result;
}
function apiRequestCounter(){
    global $h;
        $ApiCounter=$h->table('settings')->select('api_request_count')->fetchAll();
        $counter= $ApiCounter[0]['api_request_count']-1;
    $h->table('settings')->update([
        'last_api_request' => date('Y-m-d H:i:s'),
        'api_request_count' => $counter
    ])->where('id',1)->run();
}
function fetchSaveAndReturnUniqueImageNames($config, $mlsNumber, $savePath = 'uploads/listings/') {
    $rets = new Session($config);
    try {
        $connect = $rets->Login();
        $objects = $rets->GetObject('Property', 'Photo', $mlsNumber, '*', 1);

        // Create the directory if it doesn't exist
        if (!file_exists($savePath)) {
            mkdir($savePath, 0777, true);
        }

        $imageNames = []; // Array to store unique image names

        foreach ($objects as $object) {
            if ($object->isError()) {
                $error = $object->getError();
                return "Error: " . $error->getMessage();
            } else {
                $content = $object->getContent();
                $objectId = $object->getObjectId();

                // Generate a unique name using MLS number and object ID
                $uniqueName = $mlsNumber . '_' . $objectId . '.jpg'; // Or any other desired image format

                // Ensure uniqueness
                while (in_array($uniqueName, $imageNames)) {
                    // Append a unique identifier if needed
                    $uniqueName = $mlsNumber . '_' . $objectId . '_' . uniqid() . '.jpg';
                }

                // Save the image to file
                $saveFilename = $savePath . $uniqueName;
                file_put_contents($saveFilename, $content);

                // Add the unique name to the array
                $imageNames[] = $uniqueName;
            }
        }
        $rets->Logout();
        $rets->Disconnect();
        return json_encode(['image_names' => $imageNames]);
    } catch (\Exception $e) {
        return json_encode(['error' => $e->getMessage()]);
    }
}



function SentMessgeToMatterMost($message, $token, $channel_id){
    $api_url = 'https://team.zotecsoft.com';
    $data = array(
        'channel_id' => $channel_id,
        'message' => $message
    );
    $client = new Client([
        'base_uri' => $api_url,
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ]
    ]);
    try {
        $response = $client->post('/api/v4/posts', [
            'json' => $data
        ]);

    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }

}

function generateMessage($name, $email, $phone, $date, $message,$MLS, $ip, $agent) {
    $msgMM = "
    **REQ ID:".random_strings('5')."**
*Client Details*
- Full Name: $name
- Email: $email
- Mobile Number: $phone
- Date: $date
- IP: $ip
- User Agent: $agent
---------------------

*Message*
$message

---------------------
*LISTING DETAILS*
https://chaisbek.com/listing/$MLS";
    return $msgMM;
}

function calculateMonthlyPayment($loanAmount, $years, $annualInsurance, $interestRate, $annualTax, $monthlyHOA) {
    // Convert interest rate from percentage to decimal
    $monthlyInterestRate = $interestRate / 100 / 12;

    // Convert years to total number of payments
    $totalPayments = $years * 12;

    // Calculate Monthly P & I (Principal and Interest)
    $monthlyPAndI = ($loanAmount * $monthlyInterestRate * pow(1 + $monthlyInterestRate, $totalPayments)) / (pow(1 + $monthlyInterestRate, $totalPayments) - 1);

    // Calculate Monthly Tax
    $monthlyTax = $annualTax / 12;

    // Calculate Monthly Insurance
    $monthlyInsurance = $annualInsurance / 12;

    // Calculate Total Monthly Payment
    $totalMonthlyPayment = $monthlyPAndI + $monthlyTax + $monthlyInsurance + $monthlyHOA;

    return $totalMonthlyPayment;
}


function getChatUsers($firm_id){
    global $h;
    $chatUsersCount = $h->table('users')->select()->where('firm_id', '=', $firm_id);
    if($chatUsersCount->count() > 0) {
        $usersList = $h->table('users')->select('id','fname','lname', 'email', 'type')->where('firm_id', '=', $firm_id)->fetchAll();
        return json_encode($usersList);
    }else{
        echo "No User Available";
    }
}


function uploadTemplateFile($file, $uploadDir) {
    $targetDir = $uploadDir . '/';

    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Get file extension
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);

    // Generate random file name and check if it exists
    do {
        $randomName = bin2hex(random_bytes(8)) . '.' . $fileExtension;
        $targetFilePath = $targetDir . $randomName;
    } while (file_exists($targetFilePath));

    // Upload file
    if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
        return $targetFilePath; // Return the file path if upload is successful
    } else {
        return null; // Return null if upload fails
    }
}

function sendSMS($clientNumber, $message){
    global $twilio_number;
    global $account_sid;
    global $auth_token;


    $sid = $account_sid;
    $token = $auth_token;
    $twilio = new Client($sid, $token);

    $message = $twilio->messages
        ->create($clientNumber, // to
            array(
                "from" => $twilio_number,
                "body" => $message
            )
        );
    return $message;
}
?>