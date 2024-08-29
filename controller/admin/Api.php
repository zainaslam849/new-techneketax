<?php
require("config/env.php");
header('Content-Type: application/json');
//FETCH ALL CATEGORIES
if($route == '/admin/api/category'){
        $srNo=0;
        $categories=$h->table('categories')->select()->fetchAll();
        if(!empty($categories)){
            foreach ($categories as $category){

                $action= array("action"=>"
         <a href='/admin/listing/edit/".$category['id']."' class='btn btn-primary btn-sm'><i class='ti ti-pencil'></i></a>
         <button onclick=deleteUser('".$category['id']."') class='btn btn-danger btn-sm'><i class='ti ti-trash'></i></button>
         ");
                $srNo=$srNo+1;
                $ids= array("ids"=>"$srNo");
                $createdAT= array("createdAT"=>getRelativeTime($category['created_at'], 'UTC'));
                $check_arr[]=array_merge($ids,$category,$createdAT,  $action);
            }
            $result=array(
                "sEcho" => 1,
                "iTotalRecords" => count($check_arr),
                "iTotalDisplayRecords" => count($check_arr),
                "aaData"=>$check_arr);
            echo json_encode($result);
        }else{
            $result=array(
                "sEcho" => 1,
                "iTotalRecords" => 0,
                "iTotalDisplayRecords" => 0,
                "aaData"=>$categories);
            echo json_encode($result);
        }
    exit;
}
//if($route == '/admin/api/users') {
    if($_GET['page_name']=="view_firms"){
    $srNo = 0;
    $users = $h->table('users')->select()->where('type','=','firm')->orderBy('id', 'desc')->fetchAll();

    if (!empty($users)) {
        foreach ($users as $user) {
            // Determine user status
            if ($user['status'] == "active") {
                $statusView = "<span class='badge badge-light-success'>Active</span>";
                $userStatus = "<a href='javascript:;' onclick='userStatus(".$user['id'].", 0)' class='btn btn-light-success btn-sm text-start me-2 action-edit' ><i style='font-size: 16px;' class='fa-solid fa-unlock'></i></a>";
            } else if ($user['status'] == "block") {
                $statusView = "<span class='badge badge-light-danger'>Inactive</span>";
                $userStatus = "<a href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='btn-sm btn btn-light-danger text-start me-2 action-edit' ><i style='font-size: 16px;' class='fa-solid fa-lock'></i></a>";
            } else {
                $statusView = "<span class='badge badge-light-danger'>Inactive</span>";
                $userStatus = "<a href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='btn-sm btn btn-light-danger text-start me-2 action-edit' ><i style='font-size: 16px;' class='fa-solid fa-lock'></i></a>";
            }

            // $plus = array("plusView" => "<a class='control' tabindex='0' style=""></a>");
            $action = array('action' =>  $userStatus.'
                    <a role="button" data-id="'.$user["id"].'" data-bs-toggle="modal" data-bs-target="#editExampleModal" class="edit btn-sm btn btn-light-info text-start me-2 action-edit" ><i style="font-size: 16px;" class="fa-solid fa-pen-to-square"></i></a>
                   <a href="javascript:;" class="btn-sm btn btn-light-danger text-start me-2 action-edit" onclick="deleteUser('.$user["id"].')" ><i style="font-size: 16px;" class="fa-regular fa-trash-can"></i></a>
           
                      ');

            $status = array("statusView" => $statusView);

            if ($user["profile_image"] != '' && $user["profile_image"] != 'null'){
                $profile_image = $user["profile_image"];
            }else{
                $profile_image = "avatar.png";
            }
            $userView = array(
                "userView" => '<div class="d-flex align-items-center"> <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
															<a href="#">
																<div class="symbol-label">
																	<img src="'.$env["APP_URL"].'uploads/profile/'.$profile_image.'" alt="Emma Smith" class="w-100">
																</div>
															</a>
														</div> <div class="d-flex flex-column">
															<a href="#" class="text-gray-800 text-hover-primary mb-1">' . $user['fname'] .' '.  $user['lname'] . '</a>
															<span>'.  $user['email'] . '</span>
														</div></div>'
            );

            $srNo++;
            $ids = array("ids" => "$srNo");
            $check_arr[] = array_merge($ids, $user,$userView, $status, $action);
        }

        $result = array(
            "sEcho" => 1,
            "iTotalRecords" => count($check_arr),
            "iTotalDisplayRecords" => count($check_arr),
            "aaData" => $check_arr
        );
        echo json_encode($result);
    } else {
        $result = array(
            "sEcho" => 1,
            "iTotalRecords" => 0,
            "iTotalDisplayRecords" => 0,
            "aaData" => array()
        );
        echo json_encode($result);
    }

}
if($_GET['page_name']=="view_members")
{
    global $h;
    $srNo = 0;

    // Make sure $h is correctly instantiated
    if (method_exists($h, 'table'))
    {
        $users = $h->table('users')->select()->where('type', '=', 'member')->orderBy('id', 'desc')->fetchAll();

        // Determine user status
        if (!empty($users)) {
            foreach ($users as $user) {
                // Determine user status
                if ($user['status'] == "active") {
                    $statusView = "<span class='badge badge-light-success'>Active</span>";
                    $userStatus = "<a href='javascript:;' onclick='userStatus(".$user['id'].", 0)' class='btn btn-light-success btn-sm text-start me-2 action-edit' ><i style='font-size: 16px;' class='fa-solid fa-unlock'></i></a>";
                } else if ($user['status'] == "block") {
                    $statusView = "<span class='badge badge-light-danger'>Inactive</span>";
                    $userStatus = "<a href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='btn-sm btn btn-light-danger text-start me-2 action-edit' ><i style='font-size: 16px;' class='fa-solid fa-lock'></i></a>";
                } else {
                    $statusView = "<span class='badge badge-light-danger'>Inactive</span>";
                    $userStatus = "<a href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='btn-sm btn btn-light-danger text-start me-2 action-edit' ><i style='font-size: 16px;' class='fa-solid fa-lock'></i></a>";
                }

                // $plus = array("plusView" => "<a class='control' tabindex='0' style=""></a>");
                $action = array('action' =>  $userStatus.'
                    <a role="button" data-id="'.$user["id"].'" data-bs-toggle="modal" data-bs-target="#editExampleModal" class="edit btn-sm btn btn-light-info text-start me-2 action-edit" ><i style="font-size: 16px;" class="fa-solid fa-pen-to-square"></i></a>
                   <a href="javascript:;" class="btn-sm btn btn-light-danger text-start me-2 action-edit" onclick="deleteUser('.$user["id"].')" ><i style="font-size: 16px;" class="fa-regular fa-trash-can"></i></a>
           
                      ');

                $FirmInfo = $h->table('users')->select()->where('id', '=', $user['firm_id'])->orderBy('id', 'desc')->fetchAll();
                if ($FirmInfo[0]["profile_image"] != '' && $FirmInfo[0]["profile_image"] != 'null'){
                    $profile_image = $FirmInfo[0]["profile_image"];
                }else{
                    $profile_image = "avatar.png";
                }
                $firmView = array(
                    "firmView" => '<div class="d-flex align-items-center"> <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
															<a href="#">
																<div class="symbol-label">
																	<img src="'.$env["APP_URL"].'uploads/profile/'.$profile_image.'" alt="Emma Smith" class="w-100">
																</div>
															</a>
														</div> <div class="d-flex flex-column">
															<a href="#" class="text-gray-800 text-hover-primary mb-1">' . $FirmInfo[0]['fname'] .' '.  $FirmInfo[0]['lname'] . '</a>
															<span>'.  $FirmInfo[0]['email'] . '</span>
														</div></div>'
                );
                $status = array("statusView" => $statusView);

                if ($user["profile_image"] != '' && $user["profile_image"] != 'null'){
                    $profile_image = $user["profile_image"];
                }else{
                    $profile_image = "avatar.png";
                }
                $userView = array(
                    "userView" => '<div class="d-flex align-items-center"> <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
															<a href="#">
																<div class="symbol-label">
																	<img src="'.$env["APP_URL"].'uploads/profile/'.$profile_image.'" alt="Emma Smith" class="w-100">
																</div>
															</a>
														</div> <div class="d-flex flex-column">
															<a href="#" class="text-gray-800 text-hover-primary mb-1">' . $user['fname'] .' '.  $user['lname'] . '</a>
															<span>'.  $user['email'] . '</span>
														</div></div>'
                );


                $srNo++;
                $ids = array("ids" => "$srNo");
                $check_arr[] = array_merge($ids, $user,$userView, $status, $action,$firmView);
            }

            $result = array(
                "sEcho" => 1,
                "iTotalRecords" => count($check_arr),
                "iTotalDisplayRecords" => count($check_arr),
                "aaData" => $check_arr
            );
            echo json_encode($result);
        } else {
            $result = array(
                "sEcho" => 1,
                "iTotalRecords" => 0,
                "iTotalDisplayRecords" => 0,
                "aaData" => array()
            );
            echo json_encode($result);
        }
    }
    else {
        echo json_encode(array("error" => "Method table not found in the class of \$h."));

    }
}
if($_GET['page_name']=="view_clients") {
    global $h;
    $srNo = 0;

    // Make sure $h is correctly instantiated
    if (method_exists($h, 'table')) {
        $users = $h->table('users')->select()->where('type', '=', 'client')->orderBy('id', 'desc')->fetchAll();

        if (!empty($users)) {
            foreach ($users as $user) {
                if ($user['status'] == "active") {
                    $statusView = "<span class='badge badge-light-success'>Active</span>";
                    $userStatus = "<a href='javascript:;' onclick='userStatus(".$user['id'].", 0)' class='btn btn-light-success btn-sm text-start me-2 action-edit' ><i style='font-size: 16px;' class='fa-solid fa-unlock'></i></a>";
                } else if ($user['status'] == "block") {
                    $statusView = "<span class='badge badge-light-danger'>Inactive</span>";
                    $userStatus = "<a href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='btn-sm btn btn-light-danger text-start me-2 action-edit' ><i style='font-size: 16px;' class='fa-solid fa-lock'></i></a>";
                } else {
                    $statusView = "<span class='badge badge-light-danger'>Inactive</span>";
                    $userStatus = "<a href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='btn-sm btn btn-light-danger text-start me-2 action-edit' ><i style='font-size: 16px;' class='fa-solid fa-lock'></i></a>";
                }

                // $plus = array("plusView" => "<a class='control' tabindex='0' style=""></a>");
                $action = array('action' =>  $userStatus.'
                    <a role="button" data-id="'.$user["id"].'" data-bs-toggle="modal" data-bs-target="#editExampleModal" class="edit btn-sm btn btn-light-info text-start me-2 action-edit" ><i style="font-size: 16px;" class="fa-solid fa-pen-to-square"></i></a>
                   <a href="javascript:;" class="btn-sm btn btn-light-danger text-start me-2 action-edit" onclick="deleteUser('.$user["id"].')" ><i style="font-size: 16px;" class="fa-regular fa-trash-can"></i></a>
           
                      ');
                $status = array("statusView" => $statusView);
                $FirmInfo = $h->table('users')->select()->where('id', '=', $user['firm_id'])->orderBy('id', 'desc')->fetchAll();
                if ($FirmInfo[0]["profile_image"] != '' && $FirmInfo[0]["profile_image"] != 'null'){
                    $profile_image = $FirmInfo[0]["profile_image"];
                }else{
                    $profile_image = "avatar.png";
                }
                $firmView = array(
                    "firmView" => '<div class="d-flex align-items-center"> <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
															<a href="#">
																<div class="symbol-label">
																	<img src="'.$env["APP_URL"].'uploads/profile/'.$profile_image.'" alt="Emma Smith" class="w-100">
																</div>
															</a>
														</div> <div class="d-flex flex-column">
															<a href="#" class="text-gray-800 text-hover-primary mb-1">' . $FirmInfo[0]['fname'] .' '.  $FirmInfo[0]['lname'] . '</a>
															<span>'.  $FirmInfo[0]['email'] . '</span>
														</div></div>'
                );
                if ($user["profile_image"] != '' && $user["profile_image"] != 'null'){
                    $profile_image = $user["profile_image"];
                }else{
                    $profile_image = "avatar.png";
                }
                $userView = array(
                    "userView" => '<div class="d-flex align-items-center"> <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
															<a href="#">
																<div class="symbol-label">
																	<img src="'.$env["APP_URL"].'uploads/profile/'.$profile_image.'" alt="Emma Smith" class="w-100">
																</div>
															</a>
														</div> <div class="d-flex flex-column">
															<a href="#" class="text-gray-800 text-hover-primary mb-1">' . $user['fname'] .' '.  $user['lname'] . '</a>
															<span>'.  $user['email'] . '</span>
														</div></div>'
                );

                $srNo++;
                $ids = array("ids" => "$srNo");
                $check_arr[] = array_merge($ids, $user,$userView, $status, $action,$firmView);
            }

            $result = array(
                "sEcho" => 1,
                "iTotalRecords" => count($check_arr),
                "iTotalDisplayRecords" => count($check_arr),
                "aaData" => $check_arr
            );
            echo json_encode($result);
        } else {
            $result = array(
                "sEcho" => 1,
                "iTotalRecords" => 0,
                "iTotalDisplayRecords" => 0,
                "aaData" => array()
            );
            echo json_encode($result);
        }
    } else {
        echo json_encode(array("error" => "Method table not found in the class of \$h."));
    }
}

if($_GET['page_name']=="view_plans"){
    $srNo = 0;
    $plans = $h->table('plans')->select()->orderBy('id', 'desc')->fetchAll();

    if (!empty($plans)) {
        foreach ($plans as $plan) {
            // Determine user status
            if ($plan['status'] == "active") {
                $statusView = "<span class='badge badge-light-success'>Active</span>";
                $userStatus = "<a href='javascript:;' onclick='userStatus(".$plan['id'].", 0)' class='btn btn-light-success btn-sm text-start me-2 action-edit' ><i style='font-size: 16px;' class='fa-solid fa-unlock'></i></a>";
            } else if ($plan['status'] == "block") {
                $statusView = "<span class='badge badge-light-danger'>Inactive</span>";
                $userStatus = "<a href='javascript:;' onclick='userStatus(".$plan['id'].", 1)' class='btn-sm btn btn-light-danger text-start me-2 action-edit' ><i style='font-size: 16px;' class='fa-solid fa-lock'></i></a>";
            } else {
                $statusView = "<span class='badge badge-light-danger'>Inactive</span>";
                $userStatus = "<a href='javascript:;' onclick='userStatus(".$plan['id'].", 1)' class='btn-sm btn btn-light-danger text-start me-2 action-edit' ><i style='font-size: 16px;' class='fa-solid fa-lock'></i></a>";
            }
            // $plus = array("plusView" => "<a class='control' tabindex='0' style=""></a>");
            $action = array('action' =>  $userStatus.'
                       <a role="button" data-id="'.$plan['id'].'" data-bs-toggle="modal" data-bs-target="#editExampleModal" class="edit btn-sm btn btn-light-info text-start me-2 action-edit" ><i style="font-size: 16px;" class="fa-solid fa-pen-to-square"></i></a>
           <a href="javascript:;" class="btn-sm btn btn-light-danger text-start me-2 action-edit" onclick="deleteUser('.$plan['id'].')" ><i style="font-size: 16px;" class="fa-regular fa-trash-can"></i></a>
           ');


            $status = array("statusView" => $statusView);

            $monthlyPrice = array("monthlyPrice" =>'$'.$plan["monthly_price"]);
            $yearlyPrice = array("yearlyPrice" =>'$'.$plan["yearly_price"]);

            $srNo++;
            $ids = array("ids" => "$srNo");
            $check_arr[] = array_merge($ids, $plan,$monthlyPrice,$yearlyPrice, $status, $action);
        }

        $result = array(
            "sEcho" => 1,
            "iTotalRecords" => count($check_arr),
            "iTotalDisplayRecords" => count($check_arr),
            "aaData" => $check_arr
        );
        echo json_encode($result);
    } else {
        $result = array(
            "sEcho" => 1,
            "iTotalRecords" => 0,
            "iTotalDisplayRecords" => 0,
            "aaData" => array()
        );
        echo json_encode($result);
    }

}

if($_GET['page_name']=="userStatusUpdate") {
//    if (!is_csrf_v_script()) {
//        exit();
//    }
    if(isset($_GET['id'])) {
        $id=$_GET['id'];
        $status=$_GET['status'];
        $table_name=$_GET['table_name'];
        $status_table=$_GET['status_table'];

        try {
            $statusUpdate = $h->table($table_name)
                ->update([$status_table=>$status])
                ->where('id','=',$id)
                ->run();
            echo "1";
            exit;
        }catch (PDOException $e) {
            echo 0;
            exit;
        }
    }
}
if($_GET['page_name']=="delete") {
//    if (!is_csrf_v_script()) {
//        exit();
//    }
    if(isset($_GET['table_name'])) {
        $table_name=$_GET['table_name'];
        $id =$_GET['id'];
        try {
            $h->table($table_name)->delete()->where('id', $id)->run();
            echo "1";
            exit;
        }catch (PDOException $e) {
            echo 0;
            exit;
        }
    }

}