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
                $userStatus = "<a href='javascript:;' onclick='userStatus(".$user['id'].", 0)' class='badge badge-light-success text-start me-2 action-edit' ><i class='fa-solid fa-unlock'></i></a>";
            } else if ($user['status'] == "block") {
                $statusView = "<span class='badge badge-light-danger'>Inactive</span>";
                $userStatus = "<a href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='badge badge-light-danger text-start me-2 action-edit' ><i class='fa-solid fa-lock'></i></a>";
            } else {
                $statusView = "<span class='badge badge-light-danger'>Inactive</span>";
                $userStatus = "<a href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='badge badge-light-danger text-start me-2 action-edit' ><i class='fa-solid fa-lock'></i></a>";
            }
            // $plus = array("plusView" => "<a class='control' tabindex='0' style=""></a>");
            $action = array('action' =>  $userStatus.'<a role="button" data-id="'.$user["id"].'" data-bs-toggle="modal" data-bs-target="#editExampleModal" class="edit badge badge-light-info text-start me-2 action-edit" ><i class="fa-solid fa-pen-to-square"></i></a>
            <a href="javascript:;" class="badge badge-light-danger text-start me-2 action-edit" onclick="deleteUser('.$user["id"].')" ><i class="fa-regular fa-circle-xmark"></i></a>');


            $status = array("statusView" => $statusView);

            $userView = array(
                "userView" => '
                          <div class="d-flex justify-content-start align-items-center user-name">
    <div class="d-flex flex-column">
        <span class="emp_name text-truncate text-heading fw-medium">' . $user['fname'] .' '.  $user['lname'] . '</span>
        <small class="emp_post text-truncate">'.  $user['email'] . '</small>
    </div>
</div>'
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
                    $userStatus = "<a href='javascript:;' onclick='userStatus(".$user['id'].", 0)' class='badge badge-light-success text-start me-2 action-edit' ><i class='fa-solid fa-unlock'></i></a>";
                } else if ($user['status'] == "block") {
                    $statusView = "<span class='badge badge-light-danger'>Inactive</span>";
                    $userStatus = "<a href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='badge badge-light-danger text-start me-2 action-edit' ><i class='fa-solid fa-lock'></i></a>";
                } else {
                    $statusView = "<span class='badge badge-light-danger'>Inactive</span>";
                    $userStatus = "<a href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='badge badge-light-danger text-start me-2 action-edit' ><i class='fa-solid fa-lock'></i></a>";
                }


                // $plus = array("plusView" => "<a class='control' tabindex='0' style=""></a>");
                $action = array('action' =>  $userStatus.'<a role="button" data-id="'.$user["id"].'" data-bs-toggle="modal" data-bs-target="#editExampleModal" class="edit badge badge-light-info text-start me-2 action-edit" ><i class="fa-solid fa-pen-to-square"></i></a>
            <a href="javascript:;" class="badge badge-light-danger text-start me-2 action-edit" onclick="deleteUser('.$user["id"].')" ><i class="fa-regular fa-circle-xmark"></i></a>');

                $FirmInfo = $h->table('users')->select()->where('id', '=', $user['firm_id'])->orderBy('id', 'desc')->fetchAll();
                $firmView = array(
                    "firmView" => '<div class="d-flex justify-content-start align-items-center user-name">
    <div class="d-flex flex-column">
        <span class="emp_name text-truncate text-heading fw-medium">'.$FirmInfo[0]['fname'].' '.$FirmInfo[0]['lname'].'</span>
        <small class="emp_post text-truncate">'.$FirmInfo[0]['email'].'</small>
    </div>
</div>'
                );
                $status = array("statusView" => $statusView);

                $userView = array(
                    "userView" => '
                          <div class="d-flex justify-content-start align-items-center user-name">
    <div class="d-flex flex-column">
        <span class="emp_name text-truncate text-heading fw-medium">' . $user['fname'] .' '.  $user['lname'] . '</span>
        <small class="emp_post text-truncate">'.  $user['email'] . '</small>
    </div>
</div>'
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
                // Determine user status
                if ($user['status'] == "active") {
                    $statusView = "<span class='badge badge-light-success'>Active</span>";
                    $userStatus = "<a href='javascript:;' onclick='userStatus(".$user['id'].", 0)' class='badge badge-light-success text-start me-2 action-edit' ><i class='fa-solid fa-unlock'></i></a>";
                } else if ($user['status'] == "block") {
                    $statusView = "<span class='badge badge-light-danger'>Inactive</span>";
                    $userStatus = "<a href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='badge badge-light-danger text-start me-2 action-edit' ><i class='fa-solid fa-lock'></i></a>";
                } else {
                    $statusView = "<span class='badge badge-light-danger'>Inactive</span>";
                    $userStatus = "<a href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='badge badge-light-danger text-start me-2 action-edit' ><i class='fa-solid fa-lock'></i></a>";
                }
                // $plus = array("plusView" => "<a class='control' tabindex='0' style=""></a>");
                $action = array('action' =>  $userStatus.'<a role="button" data-id="'.$user["id"].'" data-bs-toggle="modal" data-bs-target="#editExampleModal" class="edit badge badge-light-info text-start me-2 action-edit" ><i class="fa-solid fa-pen-to-square"></i></a>
            <a href="javascript:;" class="badge badge-light-danger text-start me-2 action-edit" onclick="deleteUser('.$user["id"].')" ><i class="fa-regular fa-circle-xmark"></i></a>');


                $status = array("statusView" => $statusView);
                $FirmInfo = $h->table('users')->select()->where('id', '=', $user['firm_id'])->orderBy('id', 'desc')->fetchAll();
                $firmView = array(
                    "firmView" => '<div class="d-flex justify-content-start align-items-center user-name">
  
    <div class="d-flex flex-column">
        <span class="emp_name text-truncate text-heading fw-medium">'.$FirmInfo[0]['fname'].' '.$FirmInfo[0]['lname'].'</span>
        <small class="emp_post text-truncate">'.$FirmInfo[0]['email'].'</small>
    </div>
</div>'
                );
                $userView = array(
                    "userView" => '
                          <div class="d-flex justify-content-start align-items-center user-name">
    <div class="d-flex flex-column">
        <span class="emp_name text-truncate text-heading fw-medium">' . $user['fname'] .' '.  $user['lname'] . '</span>
        <small class="emp_post text-truncate">'.  $user['email'] . '</small>
    </div>
</div>'
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
                $userStatus = "<a href='javascript:;' onclick='userStatus(".$plan['id'].", 0)' class='badge badge-light-success text-start me-2 action-edit' ><i class='fa-solid fa-unlock'></i></a>";
            } else if ($plan['status'] == "block") {
                $statusView = "<span class='badge badge-light-danger'>Inactive</span>";
                $userStatus = "<a href='javascript:;' onclick='userStatus(".$plan['id'].", 1)' class='badge badge-light-danger text-start me-2 action-edit' ><i class='fa-solid fa-lock'></i></a>";
            } else {
                $statusView = "<span class='badge badge-light-danger'>Inactive</span>";
                $userStatus = "<a href='javascript:;' onclick='userStatus(".$plan['id'].", 1)' class='badge badge-light-danger text-start me-2 action-edit' ><i class='fa-solid fa-lock'></i></a>";
            }
            // $plus = array("plusView" => "<a class='control' tabindex='0' style=""></a>");
            $action = array('action' =>  $userStatus.'
           <a href="javascript:;" class="badge badge-light-danger text-start me-2 action-edit" onclick="deleteUser('.$plan['id'].')" ><i class="fa-regular fa-circle-xmark"></i></a>
           <a role="button" data-id="'.$plan['id'].'" data-bs-toggle="modal" data-bs-target="#editExampleModal" class="edit badge badge-light-info text-start me-2 action-edit" ><i class="fa-solid fa-pen-to-square"></i></a>
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