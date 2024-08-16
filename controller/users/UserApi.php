<?php
require("config/env.php");
header('Content-Type: application/json');
//FETCH ALL CATEGORIES
if($_GET['page_name']=="view_sections"){
    $srNo = 0;
    $sections= $h->table('sections')->select()->orderBy('id', 'desc')->fetchAll();
    if (!empty($sections)) {
        foreach ($sections as $section) {
            // Determine user status
            if ($section['status'] == "active") {
                $statusView = "<span class='badge badge-light-success'>Active</span>";
                $userStatus = "<a href='javascript:;' onclick='userStatus(".$section['id'].", 0)' class='badge badge-light-success text-start me-2 action-edit' ><i class='fa-solid fa-unlock'></i></a>";
            } else if ($section['status'] == "block") {
                $statusView = "<span class='badge badge-light-danger'>Inactive</span>";
                $userStatus = "<a href='javascript:;' onclick='userStatus(".$section['id'].", 1)' class='badge badge-light-danger text-start me-2 action-edit' ><i class='fa-solid fa-lock'></i></a>";
            } else {
                $statusView = "<span class='badge badge-light-danger'>Inactive</span>";
                $userStatus = "<a href='javascript:;' onclick='userStatus(".$section['id'].", 1)' class='badge badge-light-danger text-start me-2 action-edit' ><i class='fa-solid fa-lock'></i></a>";
            }

            // $plus = array("plusView" => "<a class='control' tabindex='0' style=""></a>");
            $action = array('action' =>  $userStatus.'
            <a role="button" href="/user/interviews/questions/update/'.$section["id"].'" class="badge badge-light-info text-start me-2 action-edit" ><i class="fa-solid fa-edit"></i></a>
       
              ');

            $status = array("statusView" => $statusView);
            $srNo++;
            $ids = array("ids" => "$srNo");
            $check_arr[] = array_merge($ids, $section, $status, $action);
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
if($_GET['page_name']=="view_interview_List"){
    $srNo = 0;
    $slug=$_GET['slug'];
    $templates_slug= $h->table('templates')->select()->where('status', '=', 'active')->where('slug', '=', $slug)->orderBy('id', 'desc')->fetchAll();
    $templates=$h->table('template_request') ->select('users.*','template_request.status as template_request_status','template_request.id as template_request_id','template_request.*') ->leftJoin('users')->on('users.id', 'template_request.user_id') ->where('template_request.template_id','=',$templates_slug[0]['id'])->orderBy('template_request.id', 'desc')->fetchAll();

    if (!empty($templates)) {
        foreach ($templates as $template) {
            // Determine  status
            if ($template['template_request_status'] == "completed") {
                $statusView = "<span class='badge badge-light-success'>Completed</span>";
                $action = array('action' => '
           <a role="button" href="/user/template/display-data/'.$template["user_id"].'/'.$templates_slug[0]['id'].'" class="badge badge-light-info text-start me-2 action-edit" ><i class="fa-solid fa-eye"></i></a>
            <a href="javascript:;" class="badge badge-light-danger text-start me-2 action-edit" onclick="deleteUser('.$template["template_request_id"].')" ><i class="fa-regular fa-circle-xmark"></i></a>
   
              ');
            } else if ($template['template_request_status'] == "pending") {
                $statusView = "<span class='badge badge-light-danger'>Pending</span>";
                $action = array('action' => '
           <button type="button" disabled class="badge badge-light-danger text-start me-2 action-edit" title="data is not available"><i class="fa-solid fa-eye"></i></button>
            <a href="javascript:;" class="badge badge-light-danger text-start me-2 action-edit" title="" onclick="deleteUser('.$template["template_request_id"].')" ><i class="fa-regular fa-circle-xmark"></i></a>
   
              ');
            } else {
                $statusView = "<span class='badge badge-light-danger'>Pending</span>";
                $action = array('action' => '
           <a role="button" disabled="" class="badge badge-light-info text-start me-2 action-edit" ><i class="fa-solid fa-eye"></i></a>
            <a href="javascript:;" class="badge badge-light-danger text-start me-2 action-edit" onclick="deleteUser('.$template["template_request_id"].')" ><i class="fa-regular fa-circle-xmark"></i></a>
   
              ');
            }
            // $plus = array("plusView" => "<a class='control' tabindex='0' style=""></a>");

            $srNo++;
            $ids = array("ids" => "$srNo");
            $userName = array("userName" => $template['fname']." ".$template['lname']);
            $status = array("statusView" => "$statusView");
            $check_arr[] = array_merge($ids,$userName,$status, $template, $action);
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
if($_GET['page_name']=="view_Templates"){
    $srNo = 0;
    $templates= $h->table('templates')->select()->where('status', '=', 'active')->orderBy('id', 'desc')->fetchAll();
    if (!empty($templates)) {
        foreach ($templates as $template) {
            // Determine user status
            if ($template['status'] == "active") {
                $statusView = "<span class='badge badge-light-success'>Active</span>";
                $userStatus = "<a href='javascript:;' onclick='userStatus(".$template['id'].", 0)' class='badge badge-light-success text-start me-2 action-edit' ><i class='fa-solid fa-unlock'></i></a>";
            } else if ($template['status'] == "block") {
                $statusView = "<span class='badge badge-light-danger'>Inactive</span>";
                $userStatus = "<a href='javascript:;' onclick='userStatus(".$template['id'].", 1)' class='badge badge-light-danger text-start me-2 action-edit' ><i class='fa-solid fa-lock'></i></a>";
            } else {
                $statusView = "<span class='badge badge-light-danger'>Inactive</span>";
                $userStatus = "<a href='javascript:;' onclick='userStatus(".$template['id'].", 1)' class='badge badge-light-danger text-start me-2 action-edit' ><i class='fa-solid fa-lock'></i></a>";
            }

            // $plus = array("plusView" => "<a class='control' tabindex='0' style=""></a>");
            $action = array('action' => '
            <a role="button" data-id="'.$template["id"].'" data-bs-toggle="modal" data-bs-target="#inputFormModal" class="edit badge badge-light-info text-start me-2 action-edit" ><i class="fa-solid fa-user-plus"></i></a>
           <a role="button" href="/user/template/interview-list/'.$template["slug"].'" class="badge badge-light-info text-start me-2 action-edit" ><i class="fa-solid fa-eye"></i></a>
           
              ');
            if (!empty($template['description']) && $template['description'] != ''){
                $words = explode(' ', $template['description']);
                $description = implode(' ', array_slice($words, 0, 10));
                $des = array('desView' =>'<p>' . $description . '... <a href="#" class="see-more text-danger" data-description="' . htmlspecialchars($template['description'], ENT_QUOTES, 'UTF-8') . '">Read More</a></p>');

            }else{
                $des = array('desView' =>'---');
            }
            $srNo++;
            $ids = array("ids" => "$srNo");
            $check_arr[] = array_merge($ids,$des, $template, $action);
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
if($_GET['page_name']=="view_Templates_request"){
    $srNo = 0;
//    $template_request= $h->table('template_request')->select(['templates.','template_request.', 'template_request.status as template_request_status'])->innerJoin('templates')->on(['templates.id', 'template_request.template_id'])->where('template_request.user_id', $loginUserId)
    $template_request=$h->table('template_request') ->select('templates.*','template_request.status as template_request_status','template_request.*') ->leftJoin('templates')->on('templates.id', 'template_request.template_id') ->where('template_request.user_id','=',$loginUserId)->orderBy('template_request.id', 'desc')->fetchAll();

     if (!empty($template_request)) {
        foreach ($template_request as $template) {

            // Determine user status
            if ($template['template_request_status'] == "completed") {
                $statusView = "<span class='badge badge-light-success'>Completed</span>";
                $action = array('action' => '            <a role="button" disabled href="#" class="badge badge-light-info text-start me-2 action-edit" ><i class="fa-solid fa-eye"></i></a>
');
            } else if ($template['template_request_status'] == "pending") {
                $statusView = "<span class='badge badge-light-danger'>Pending</span>";
                $action = array('action' => '
            <a role="button" href="/user/template/view/'.$template["slug"].'" class="badge badge-light-info text-start me-2 action-edit" ><i class="fa-solid fa-eye"></i></a>
              ');
            } else {
                $statusView = "<span class='badge badge-light-danger'>Pending</span>";
                $action = array('action' => '
            <a role="button" href="/user/template/view/'.$template["slug"].'" class="badge badge-light-info text-start me-2 action-edit" ><i class="fa-solid fa-eye"></i></a>
              ');
            }



            $des= array("desView" => substr($template['description'], 0, 100));
            $srNo++;
            $ids = array("ids" => "$srNo");
            $status = array("statusView" => "$statusView");
            $check_arr[] = array_merge($ids,$des,$status, $template, $action);
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
if($_GET['page_name']=="view_clients"){
    $srNo = 0;
    $users = $h->table('users')->select()->where('firm_id', '=', $loginUserId)->where('type', '=', 'client')->orderBy('id', 'desc')->fetchAll();
    if (!empty($users)) {
        foreach ($users as $user) {
            // Determine user status
            if ($user['status'] == "active") {
                $statusView = "<span class='badge badge-light-success'>Active</span>";
                $userStatus = "<div class='menu-item px-3'><a  onclick='userStatus(".$user['id'].", 0)' class='menu-link px-3'>Lock</a></div>";
            } else if ($user['status'] == "block") {
                $statusView = "<span class='badge badge-light-danger'>Inactive</span>";
                $userStatus = "<div class='menu-item px-3'><a  onclick='userStatus(".$user['id'].", 1)' class='menu-link px-3'>UnLock</a></div>";
            } else {
                $statusView = "<span class='badge badge-light-danger'>Inactive</span>";
                $userStatus = "<div class='menu-item px-3'><a  onclick='userStatus(".$user['id'].", 1)' class='menu-link px-3'>UnLock</a></div>";
            }
            $action = array('action' => '<a href="#" class="btn btn-sm btn-light btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Actions
														<span class="svg-icon svg-icon-5 m-0">
															<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
																<path d="M11.4343 12.7344L7.25 8.55005C6.83579 8.13583 6.16421 8.13584 5.75 8.55005C5.33579 8.96426 5.33579 9.63583 5.75 10.05L11.2929 15.5929C11.6834 15.9835 12.3166 15.9835 12.7071 15.5929L18.25 10.05C18.6642 9.63584 18.6642 8.96426 18.25 8.55005C17.8358 8.13584 17.1642 8.13584 16.75 8.55005L12.5657 12.7344C12.2533 13.0468 11.7467 13.0468 11.4343 12.7344Z" fill="currentColor"></path>
															</svg>
														</span>
													</a>
											
														<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-125px py-4" data-kt-menu="true" style="">
														      '.$userStatus.'
															<div class="menu-item px-3">
																<a role="button" data-id="'.$user["id"].'" data-bs-toggle="modal" data-bs-target="#editExampleModal"  class="menu-link px-3" >Edit</a>
															</div>
															<div class="menu-item px-3">
																<a href="javascript:;"  onclick="deleteUser('.$user["id"].')" class="menu-link px-3" data-kt-customer-table-filter="delete_row">Delete</a>
															</div>
															<!--end::Menu item-->
														</div>');


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
if($_GET['page_name']=="view_clients_unassigned"){
    $srNo = 0;
    $saaud='1';
    $users = $h->table('users')->select()->where('firm_id', '=', $loginUserId)->where('type', '=', 'client')->orderBy('id', 'desc')->fetchAll();
    if (!empty($users)) {
        foreach ($users as $user) {
            $check= $h->table('template_request')->select()->where('user_id', '=', $user['id'])->count();
                if($check < 1){
                    $saaud='0';
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
                    $action = array('action' =>  $userStatus.'
                    <a role="button" data-id="'.$user["id"].'" data-bs-toggle="modal" data-bs-target="#editExampleModal" class="edit badge badge-light-info text-start me-2 action-edit" ><i class="fa-solid fa-pen-to-square"></i></a>
                   <a href="javascript:;" class="badge badge-light-danger text-start me-2 action-edit" onclick="deleteUser('.$user["id"].')" ><i class="fa-regular fa-circle-xmark"></i></a>
           
                      ');


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
        }
        if($saaud =='1'){
        $result = array(
            "sEcho" => 1,
            "iTotalRecords" => 0,
            "iTotalDisplayRecords" => 0,
            "aaData" => array()
        );
        }else{
            $result = array(
                "sEcho" => 1,
                "iTotalRecords" => count($check_arr),
                "iTotalDisplayRecords" => count($check_arr),
                "aaData" => $check_arr
            );
        }
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
if($_GET['page_name']=="view_clients_inProgress"){
    $srNo = 0;
    $saaud ='1';
    $users = $h->table('users')->select()->where('firm_id', '=', $loginUserId)->where('type', '=', 'client')->orderBy('id', 'desc')->fetchAll();
    if (!empty($users)) {
        foreach ($users as $user) {
            $check= $h->table('template_request')->select()->where('user_id', '=', $user['id'])->where('status', '=','pending')->fetchAll();
            if(!empty($check)){
                $saaud ='0';
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
            $action = array('action' =>  $userStatus.'
            <a role="button" data-id="'.$user["id"].'" data-bs-toggle="modal" data-bs-target="#editExampleModal" class="edit badge badge-light-info text-start me-2 action-edit" ><i class="fa-solid fa-pen-to-square"></i></a>
           <a href="javascript:;" class="badge badge-light-danger text-start me-2 action-edit" onclick="deleteUser('.$user["id"].')" ><i class="fa-regular fa-circle-xmark"></i></a>
   
              ');


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
        }
        if($saaud =='1'){
            $result = array(
                "sEcho" => 1,
                "iTotalRecords" => 0,
                "iTotalDisplayRecords" => 0,
                "aaData" => array()
            );
        }else{
            $result = array(
                "sEcho" => 1,
                "iTotalRecords" => count($check_arr),
                "iTotalDisplayRecords" => count($check_arr),
                "aaData" => $check_arr
            );
        }
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
if($_GET['page_name']=="view_clients_completed"){
    $srNo = 0;
    $saaud ='1';
    $users = $h->table('users')->select()->where('firm_id', '=', $loginUserId)->where('type', '=', 'client')->orderBy('id', 'desc')->fetchAll();
    if (!empty($users)) {
        foreach ($users as $user) {
            $check= $h->table('template_request')->select()->where('user_id', '=', $user['id'])->where('status', '=','completed')->fetchAll();
            if(!empty($check)){
                $saaud ='0';
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
            $action = array('action' =>  $userStatus.'
            <a role="button" data-id="'.$user["id"].'" data-bs-toggle="modal" data-bs-target="#editExampleModal" class="edit badge badge-light-info text-start me-2 action-edit" ><i class="fa-solid fa-pen-to-square"></i></a>
           <a href="javascript:;" class="badge badge-light-danger text-start me-2 action-edit" onclick="deleteUser('.$user["id"].')" ><i class="fa-regular fa-circle-xmark"></i></a>
   
              ');


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
        }
        if($saaud =='1'){
            $result = array(
                "sEcho" => 1,
                "iTotalRecords" => 0,
                "iTotalDisplayRecords" => 0,
                "aaData" => array()
            );
        }else{
            $result = array(
                "sEcho" => 1,
                "iTotalRecords" => count($check_arr),
                "iTotalDisplayRecords" => count($check_arr),
                "aaData" => $check_arr
            );
        }
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
if($_GET['page_name']=="view_members"){
    $srNo = 0;
    $users = $h->table('users')->select()->where('firm_id', '=', $loginUserId)->where('type', '=', 'member')->orderBy('id', 'desc')->fetchAll();
    if (!empty($users)) {
        foreach ($users as $user) {
            // Determine user status
            if ($user['status'] == "active") {
                $statusView = "<span class='badge badge-light-success'>Active</span>";
                $userStatus = "<a href='javascript:;' onclick='userStatus(".$user['id'].", 0)' class='badge badge-light-success text-start me-2 action-edit' ><i class='fa-solid fa-unlock'></i></a>";
            } else if ($user['status'] == "block") {
                $statusView = "<span class='badge badge-light-danger'>Inactive</span>";
                $userStatus = "<a href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='badge badge-light-danger text-start me-2 action-edit'><i class='fa-solid fa-lock'></i></a>";
            } else {
                $statusView = "<span class='badge badge-light-danger'>Inactive</span>";
                $userStatus = "<a href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='badge badge-light-danger text-start me-2 action-edit'><i class='fa-solid fa-lock'></i></a>";
            }
            // $plus = array("plusView" => "<a class='control' tabindex='0' style=""></a>");
            $action = array('action' =>  $userStatus.'
            <a role="button" data-id="'.$user["id"].'" data-bs-toggle="modal" data-bs-target="#editExampleModal" class="edit badge badge-light-info text-start me-2 action-edit" ><i class="fa-solid fa-pen-to-square"></i></a>
           <a href="javascript:;" class="badge badge-light-danger text-start me-2 action-edit" onclick="deleteUser('.$user["id"].')" ><i class="fa-regular fa-circle-xmark"></i></a>
   
              ');


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
if($_GET['page_name']=="view_invoices"){
    $srNo = 0;
    if($loginUserType == 'firm'){
        $invoices = $h->table('invoice')->select()->where('firm_id', '=', $loginUserId)->orderBy('id', 'desc')->fetchAll();
    }else {
        $invoices = $h->table('invoice')->select()->where('client_id', '=', $loginUserId)->orderBy('id', 'desc')->fetchAll();
    }
    if (!empty($invoices)) {
        foreach ($invoices as $invoice) {
            // Determine user status
            if ($invoice['status'] == "paid") {
                $statusView = "<span class='badge badge-light-success'>Paid</span>";
            } else if ($invoice['status'] == "unpaid") {
                $statusView = "<span class='badge badge-light-danger'>Unpaid</span>";
            } else {
                $statusView = "<span class='badge badge-light-danger'>Unpaid</span>";
            }
            if ($loginUserType == 'firm'){
                $action = array('action' =>'<a href="/user/invoice/update/'.$invoice["id"].'" class="badge badge-light-info text-start me-2 action-edit" ><i class="fa-solid fa-pen-to-square"></i></a>
           <a href="javascript:;" class="badge badge-light-danger text-start me-2 action-edit" onclick="deleteUser('.$invoice["id"].')" ><i class="fa-regular fa-circle-xmark"></i></a>
              ');
            }else{
                $action = array('action' =>'---');
            }

            $clientInfo = $h->table('users')->select()->where('id', '=', $invoice['client_id'])->fetchAll();
if ($clientInfo[0]["profile_image"] != '' && $clientInfo[0]["profile_image"] != 'null'){
$profile_image = $clientInfo[0]["profile_image"];
}else{
    $profile_image = "avatar.png";
}
            $userView = array(
                "userView" => '  <div class="d-flex">
                                                    <div class="usr-img-frame me-2 rounded-circle">
                                                    
                                                        <img alt="avatar" class="img-fluid rounded-circle" style="height: 40px;" src="'.$env["APP_URL"].'uploads/profile/'.$profile_image.'">
                                                    </div>
                                                    <p class="align-self-center mb-0 user-name">'. $clientInfo[0]["fname"].' '.$clientInfo[0]["lname"].'</p>
                                                </div>'
            );
            $status = array("statusView" => $statusView);
            $date = new DateTime($invoice["due_date"]);
            $newDate = $date->format('d M');
            $DueDate = array("DueDate" => '<span class="inv-date"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg> '.$newDate.' </span>');
            $ClientEmail = array("ClientEmail" => '<span class="inv-email"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-mail"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg> '.$invoice["client_email"].'</span>');
            $FinalTotal = array("FinalTotal" => "$".$invoice["final_total"]);
            $srNo++;
            $ids = array("ids" =>'<a href="/user/invoice/view/'.$invoice["id"].'" style="color: #ED141F;cursor: pointer;font-size: 16px;text-align: left;" >'.$invoice['invoice_number'].'</a>');
            $check_arr[] = array_merge($ids, $invoice,$userView,$DueDate,$ClientEmail,$FinalTotal, $status, $action);
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
if($_GET['page_name']=="view_document_hub"){
    $srNo = 0;
    $document_hubs = $h->table('document_hub')->select()->where('firm_id', '=', $loginUserId)->orderBy('id', 'desc')->fetchAll();
    if (!empty($document_hubs)) {
        foreach ($document_hubs as $document_hub) {
            // Determine user status
            if ($document_hub['status'] == "yes") {
                $statusView = "<span class='badge badge-light-success'>Uploaded</span>";
            } else if ($document_hub['status'] == "no") {
                $statusView = "<span class='badge badge-light-danger'>Not Uploaded Yet</span>";
            } else {
                $statusView = "<span class='badge badge-light-danger'>Not Uploaded Yet</span>";
            }
            $action = array('action' =>'<a href="javascript:;" class="badge badge-light-danger text-start me-2 action-edit" onclick="deleteUser('.$document_hub["id"].')" ><i class="fa-regular fa-circle-xmark"></i></a>');
            if (!empty($document_hub['client_des']) && $document_hub['client_des'] != ''){
                $words = explode(' ', $document_hub['client_des']);
                $des = implode(' ', array_slice($words, 0, 10));
                $ClientDes = array('ClientDes' =>'<p>' . $des . '... <a href="#" class="see-more text-danger" data-description="' . htmlspecialchars($document_hub['client_des'], ENT_QUOTES, 'UTF-8') . '">Read More</a></p>');

            }else{
                $ClientDes = array('ClientDes' =>'---');
            }

            $status = array("statusView" => $statusView);
if (!empty($document_hub['client_id'])){
    $usersInfo = $h->table('users')->select()->where('id', '=', $document_hub['client_id'])->fetchAll();
    $userView = array(
        "userView" => '<div class="d-flex justify-content-start align-items-center user-name">
    <div class="d-flex flex-column">
        <span class="emp_name text-truncate text-heading fw-medium">' . $usersInfo[0]['fname'] .' '.  $usersInfo[0]['lname'] . '</span>
        <small class="emp_post text-truncate">'.  $usersInfo[0]['email'] . '</small>
    </div>
</div>'
    );
}else{
    $userView = array(
        "userView" => '---'
    );
}


            $srNo++;
            $ids = array("ids" => "$srNo");
            $check_arr[] = array_merge($ids, $document_hub,$userView,$ClientDes, $status, $action);
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
if($_GET['page_name']=="view_firm_documents"){
    $srNo = 0;
    $firm_upload_files = $h->table('firm_upload_file')->select()->where('firm_id', '=', $loginUserId)->orderBy('id', 'desc')->fetchAll();
    if (!empty($firm_upload_files)) {
        foreach ($firm_upload_files as $firm_upload_file) {
            if (!empty($firm_upload_file['description']) && $firm_upload_file['description'] != ''){
                $words = explode(' ', $firm_upload_file['description']);
                $des = implode(' ', array_slice($words, 0, 10));
                $Des = array('Des' =>'<p>' . $des . '... <a href="#" class="see-more text-danger" data-description="' . htmlspecialchars($firm_upload_file['description'], ENT_QUOTES, 'UTF-8') . '">Read More</a></p>');
            }else{
                $Des = array('Des' =>'---');
            }

            $action = array('action' =>'<a href="javascript:;" class="badge badge-light-danger text-start me-2 action-edit" onclick="deleteUser('.$firm_upload_file["id"].')" ><i class="fa-regular fa-circle-xmark"></i></a><a href="'.$env['APP_URL'].$firm_upload_file["file"].'" download="'.$env['APP_URL'].$firm_upload_file["file"].'" class="badge badge-light-info text-start me-2"><i class="fa-solid fa-download"></i></a>');
            $FileName = array('FileName' =>'<div class="d-inline-flex"><svg style="color: red;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg><p style="margin-top: 3px;
    margin-left: 11px;">'.$firm_upload_file["file_name"].'<p></div>');
            $srNo++;
            $ids = array("ids" => "$srNo");
            $check_arr[] = array_merge($ids, $firm_upload_file,$FileName,$Des, $action);
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
if($_GET['page_name']=="view_document_hub_client"){
    $srNo = 0;
    $document_hubs = $h->table('document_hub')->select()->where('client_id', '=', $loginUserId)->orderBy('id', 'desc')->fetchAll();
    if (!empty($document_hubs)) {
        foreach ($document_hubs as $document_hub) {
            // Determine user status
            if (!empty($document_hub['document_id'])){
                $document_ids = htmlspecialchars($document_hub['document_id'], ENT_QUOTES, 'UTF-8');
                $downloadFiles = '<a type="button" class="edit btn btn-info  me-4"  onclick="download_files(\'' . $document_ids . '\')" >Download File</a>';
            }else{
                $downloadFiles = '';
            }
            if ($document_hub['status'] == "yes") {
                $statusView = "<span class='badge badge-light-success'>Uploaded</span>";
                $action = array('action' =>$downloadFiles.'Your File Has Been Uploaded');
            } else if ($document_hub['status'] == "no") {
                $statusView = "<span class='badge badge-light-danger'>Not Uploaded Yet</span>";
                $action = array('action' =>$downloadFiles.'<a type="button" class="edit btn btn-primary  me-4" data-id="'. $document_hub['id'].'" role="button" data-bs-toggle="modal" data-bs-target="#editExampleModal" >Upload File</a>');
            } else {
                $statusView = "<span class='badge badge-light-danger'>Not Uploaded Yet</span>";
                $action = array('action' =>$downloadFiles.'<a type="button" class="edit btn btn-primary  me-4" data-id="'. $document_hub['id'].'" role="button" data-bs-toggle="modal" data-bs-target="#editExampleModal" >Upload File</a>');
            }
            if (!empty($document_hub['firm_des']) && $document_hub['firm_des'] != ''){
                $words = explode(' ', $document_hub['firm_des']);
                $des = implode(' ', array_slice($words, 0, 10));
                $firmDes = array('firmDes' =>'<p>' . $des . '... <a href="#" class="see-more text-danger" data-description="' . htmlspecialchars($document_hub['firm_des'], ENT_QUOTES, 'UTF-8') . '">Read More</a></p>');

            }else{
                $firmDes = array('firmDes' =>'---');
            }
            $status = array("statusView" => $statusView);
            if (!empty($document_hub['firm_id'])){
                $usersInfo = $h->table('users')->select()->where('id', '=', $document_hub['firm_id'])->fetchAll();
                $userView = array(
                    "userView" => '<div class="d-flex justify-content-start align-items-center user-name">
    <div class="d-flex flex-column">
        <span class="emp_name text-truncate text-heading fw-medium">' . $usersInfo[0]['fname'] .' '.  $usersInfo[0]['lname'] . '</span>
        <small class="emp_post text-truncate">'.  $usersInfo[0]['email'] . '</small>
    </div>
</div>' );
            }else{
                $userView = array(
                    "userView" => '---'
                );
            }


            $srNo++;
            $ids = array("ids" => "$srNo");
            $check_arr[] = array_merge($ids, $document_hub,$userView, $status, $action,$firmDes);
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
if($_GET['page_name']=="profileStatusUpdate") {
//    if (!is_csrf_v_script()) {
//        exit();
//    }
    if(isset($_GET['id'])) {
        $id=$_GET['id'];
        $table_name=$_GET['table_name'];
        $status_table=$_GET['status_table'];
        $h->table($table_name)->update(['status' => 'secondary'])->where('status', '=', 'primary')->run();
        try {
            $statusUpdate = $h->table($table_name)
                ->update([$status_table=>'primary'])
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
if ($_GET['page_name'] == "deleteFromProfile") {

    // if (!is_csrf_v_script()) {
    //     exit();
    // }
    if (isset($_GET['table_name'])) {
        $table_name = $_GET['table_name'];
        $id = $_GET['id'];
        $table_nameData = $h->table($table_name)->select()->where('id', '=', $id)->fetchAll();
        if ($table_nameData[0]['status'] == 'primary') {
            $secondaryRecords = $h->table($table_name)->select()->where('status', '=', 'secondary')->fetchAll();
            if (!empty($secondaryRecords)) {
                $randomIndex = array_rand($secondaryRecords);
                $secondaryId = $secondaryRecords[$randomIndex]['id'];
                $h->table($table_name)->update(['status' => 'primary'])->where('id', '=', $secondaryId)->run();
            }
        }
        try {
            $h->table($table_name)->delete()->where('id', $id)->run();
            echo "1";
            exit;
        } catch (PDOException $e) {
            echo 0;
            exit;
        }
    }
}
