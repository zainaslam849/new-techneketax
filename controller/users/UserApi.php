<?php
require("config/env.php");
header('Content-Type: application/json');
use Carbon\Carbon;
//FETCH ALL CATEGORIES
if($loginUserType == "member"){
    if($_GET['page_name']=="view_client_associates"){
        $srNo = 0;
        $users = $h->table('users')->select()->where('associates_id', '=', $loginUserId)->where('type', '=', 'client')->where('work_status', '!=', 'unassigned')->orderBy('id', 'desc')->fetchAll();
        if (!empty($users)) {

            foreach ($users as $user) {
                // Determine user status
                if ($user['work_status'] == "assigned") {
                    $statusView = "<span class='badge badge-light-success'>Assigned</span>";
                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 0)' class='menu-link px-3'>Completed</a></div>
                  <div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='menu-link px-3'>In Progress</a></div>";
                } else if ($user['work_status'] == "inprogress") {
                    $statusView = "<span class='badge badge-light-info'>In Progress</span>";
                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 0)' class='menu-link px-3'>Completed</a></div>
                 ";
                }  else if ($user['work_status'] == "completed") {
                    $statusView = "<span class='badge badge-light-success'>Completed</span>";
                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='menu-link px-3'>In Progress</a></div>";
                } else {
                        $statusView = "<span class='badge badge-light-success'>Completed</span>";
                        $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='menu-link px-3'>In Progress</a></div>";
                }
                $action = array('action' => '<a role="button" onclick="manage('.$user['id'].')" class="edit btn-sm btn btn-light-info text-start me-2 action-edit" >Manage</a>
<button type="button" onclick="openAction('.$user['id'].')" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-flip="top-end">

                            <span class="svg-icon svg-icon-5 m-0">
                               <i class="bi bi-three-dots-vertical"></i>
                            </span>
                        </button>
                        <div id="menu-'.$user['id'].'" class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-125px py-4" data-kt-menu="true">
                            	'.$userStatus.'
                        </div>');


                $status = array("statusView" => $statusView.'');
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
//    if($_GET['page_name']=="view_clients_unassigned_associates"){
//        $srNo = 0;
//        $users = $h->table('users')->select()->where('associates_id', '=', $loginUserId)->where('type', '=', 'client')->where('work_status', '=', 'unassigned')->orderBy('id', 'desc')->fetchAll();
//        if (!empty($users)) {
//            foreach ($users as $user) {
//                // Determine user status
//                if ($user['work_status'] == "unassigned") {
//                    $statusView = "<span class='badge badge-light-warning'>Un Assigned</span>";
//                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 0)' class='menu-link px-3'>Completed</a></div>
//                  <div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='menu-link px-3'>In Progress</a></div>";
//                } else if ($user['work_status'] == "inprogress") {
//                    $statusView = "<span class='badge badge-light-info'>In Progress</span>";
//                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 0)' class='menu-link px-3'>Completed</a></div>
//                 <div class='menu-item px-3'><a href='javascript:;' onclick='userStatus(".$user['id'].", 2)' class='menu-link px-3'>Un Assigned</a></div>";
//                }  else if ($user['work_status'] == "completed") {
//                    $statusView = "<span class='badge badge-light-success'>Completed</span>";
//
//                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='menu-link px-3'>In Progress</a></div>
//                   <div class='menu-item px-3'><a href='javascript:;' onclick='userStatus(".$user['id'].", 2)' class='menu-link px-3'>Un Assigned</a></div>";
//                } else {
//                    $statusView = "<span class='badge badge-light-warning'>Un Assigned</span>";
//                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 0)' class='menu-link px-3'>Completed</a></div>
//                   <div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='menu-link px-3'>In Progress</a></div>";
//                }
//                // $plus = array("plusView" => "<a class='control' tabindex='0' style=""></a>");
//                $action = array('action' =>  ' <a role="button" onclick="manage('.$user['id'].')" class="edit btn-sm btn btn-light-info text-start me-2 action-edit" >Manage</a>
// 	                                                    <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Actions
//                                                        <span class="svg-icon svg-icon-5 m-0">
//															<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
//																<path d="M11.4343 12.7344L7.25 8.55005C6.83579 8.13583 6.16421 8.13584 5.75 8.55005C5.33579 8.96426 5.33579 9.63583 5.75 10.05L11.2929 15.5929C11.6834 15.9835 12.3166 15.9835 12.7071 15.5929L18.25 10.05C18.6642 9.63584 18.6642 8.96426 18.25 8.55005C17.8358 8.13584 17.1642 8.13584 16.75 8.55005L12.5657 12.7344C12.2533 13.0468 11.7467 13.0468 11.4343 12.7344Z" fill="currentColor"></path>
//															</svg>
//														</span>
//														</a>
//														<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-125px py-4" data-kt-menu="true" style="">
//															'.$userStatus.'
//														</div>');
//
//
//                $status = array("statusView" => $statusView);
//                if ($user["profile_image"] != '' && $user["profile_image"] != 'null'){
//                    $profile_image = $user["profile_image"];
//                }else{
//                    $profile_image = "avatar.png";
//                }
//                $userView = array(
//                    "userView" => '<div class="d-flex align-items-center"> <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
//															<a href="#">
//																<div class="symbol-label">
//																	<img src="'.$env["APP_URL"].'uploads/profile/'.$profile_image.'" alt="Emma Smith" class="w-100">
//																</div>
//															</a>
//														</div> <div class="d-flex flex-column">
//															<a href="#" class="text-gray-800 text-hover-primary mb-1">' . $user['fname'] .' '.  $user['lname'] . '</a>
//															<span>'.  $user['email'] . '</span>
//														</div></div>'
//                );
//                $srNo++;
//                $ids = array("ids" => "$srNo");
//                $check_arr[] = array_merge($ids, $user,$userView, $status, $action);
//            }
//
//            $result = array(
//                "sEcho" => 1,
//                "iTotalRecords" => count($check_arr),
//                "iTotalDisplayRecords" => count($check_arr),
//                "aaData" => $check_arr
//            );
//            echo json_encode($result);
//        } else {
//            $result = array(
//                "sEcho" => 1,
//                "iTotalRecords" => 0,
//                "iTotalDisplayRecords" => 0,
//                "aaData" => array()
//            );
//            echo json_encode($result);
//        }
//
//    }
    if($_GET['page_name']=="view_clients_inProgress_associates"){
        $srNo = 0;
        $users = $h->table('users')->select()->where('associates_id', '=', $loginUserId)->where('type', '=', 'client')->where('work_status', '=', 'inProgress')->orderBy('id', 'desc')->fetchAll();
        if (!empty($users)) {
            foreach ($users as $user) {
                // Determine user status
                if ($user['work_status'] == "inprogress") {
                    $statusView = "<span class='badge badge-light-info'>In Progress</span>";
                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 0)' class='menu-link px-3'>Completed</a></div>
                 ";
                }  else if ($user['work_status'] == "completed") {
                    $statusView = "<span class='badge badge-light-success'>Completed</span>";
                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='menu-link px-3'>In Progress</a></div>";
                } else {
                    $statusView = "<span class='badge badge-light-success'>Completed</span>";
                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='menu-link px-3'>In Progress</a></div>";
                }

                // $plus = array("plusView" => "<a class='control' tabindex='0' style=""></a>");
                $action = array('action' =>  ' <a role="button" onclick="manage('.$user['id'].')" class="edit btn-sm btn btn-light-info text-start me-2 action-edit" >Manage</a>
<button type="button" onclick="openAction1('.$user['id'].')" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-flip="top-end">

                            <span class="svg-icon svg-icon-5 m-0">
                               <i class="bi bi-three-dots-vertical"></i>
                            </span>
                        </button>
														<div id="menu1-'.$user['id'].'" class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-125px py-4" data-kt-menu="true" style="">
															'.$userStatus.'
														</div>');


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
    if($_GET['page_name']=="view_clients_completed_associates"){
        $srNo = 0;
        $users = $h->table('users')->select()->where('associates_id', '=', $loginUserId)->where('type', '=', 'client')->where('work_status', '=', 'completed')->orderBy('id', 'desc')->fetchAll();
        if (!empty($users)) {
            foreach ($users as $user) {
                // Determine user status
                if ($user['work_status'] == "inprogress") {
                    $statusView = "<span class='badge badge-light-info'>In Progress</span>";
                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 0)' class='menu-link px-3'>Completed</a></div>
                 ";
                }  else if ($user['work_status'] == "completed") {
                    $statusView = "<span class='badge badge-light-success'>Completed</span>";
                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='menu-link px-3'>In Progress</a></div>";
                } else {
                    $statusView = "<span class='badge badge-light-success'>Completed</span>";
                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='menu-link px-3'>In Progress</a></div>";
                }

                // $plus = array("plusView" => "<a class='control' tabindex='0' style=""></a>");
                $action = array('action' => ' <a role="button" onclick="manage('.$user['id'].')" class="edit btn-sm btn btn-light-info text-start me-2 action-edit" >Manage</a>
<button type="button" onclick="openAction2('.$user['id'].')" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-flip="top-end">

                            <span class="svg-icon svg-icon-5 m-0">
                               <i class="bi bi-three-dots-vertical"></i>
                            </span>
                        </button>
														<div id="menu2-'.$user['id'].'" class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-125px py-4" data-kt-menu="true" style="">
															'.$userStatus.'
														</div>');


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
}
if($loginUserType == "firm") {
    if ($_GET['page_name'] == "view_clients_dashboard") {
        $srNo = 0;
        $users = $h->table('users')->select()->where('firm_id', '=', $loginUserId)->where('type', '=', 'client')->orderBy('id', 'desc')->fetchAll();
        if (!empty($users)) {
            foreach ($users as $user) {
                if ($user['work_status'] == "unassigned") {
                    $statusView = "<span class='badge badge-light-warning'>Un Assigned</span>";
                    $userStatus = "";
                } else if ($user['work_status'] == "assigned") {
                    $statusView = "<span class='badge badge-light-success'>Assigned</span>";
                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 0)' class='menu-link px-3'>Completed</a></div>
                <div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='menu-link px-3'>In Progress</a></div>";
                }else if ($user['work_status'] == "inprogress") {
                    $statusView = "<span class='badge badge-light-info'>In Progress</span>";
                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 0)' class='menu-link px-3'>Completed</a></div>
                 <div class='menu-item px-3'><a href='javascript:;' onclick='userStatus(".$user['id'].", 2)' class='menu-link px-3'>Assigned</a></div>";
                }  else if ($user['work_status'] == "completed") {
                    $statusView = "<span class='badge badge-light-success'>Completed</span>";
                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='menu-link px-3'>In Progress</a></div>
                   <div class='menu-item px-3'><a href='javascript:;' onclick='userStatus(".$user['id'].", 2)' class='menu-link px-3'>Assigned</a></div>";
                } else {
                    $statusView = "<span class='badge badge-light-warning'>Un Assigned</span>";
                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 0)' class='menu-link px-3'>Completed</a></div>
                   <div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='menu-link px-3'>In Progress</a></div>";
                }

                // $plus = array("plusView" => "<a class='control' tabindex='0' style=""></a>");
                $action = array('action' =>'
                   <a href="javascript:;" class="btn-sm btn btn-light-danger text-start me-2 action-edit" onclick="deleteUser(' . $user["id"] . ')" ><i style="font-size: 16px;" class="fa-regular fa-trash-can"></i></a>
           <button type="button" onclick="openAction('.$user['id'].')" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-flip="top-end">

                            <span class="svg-icon svg-icon-5 m-0">
                               <i class="bi bi-three-dots-vertical"></i>
                            </span>
                        </button>
                        <div id="menu-'.$user['id'].'" class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-125px py-4" data-kt-menu="true">
                            	<div class="menu-item px-3"><a  role="button" data-id="' . $user["id"] . '" data-bs-toggle="modal" data-bs-target="#editExampleModal"  class="edit menu-link px-3">Edit</a></div>
                            	'.$userStatus.'
                            
                        </div>');


                $status = array("statusView" => $statusView);
                if ($user["profile_image"] != '' && $user["profile_image"] != 'null') {
                    $profile_image = $user["profile_image"];
                } else {
                    $profile_image = "avatar.png";
                }
                $userView = array(
                    "userView" => '<div class="d-flex align-items-center"> <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
															<a href="#">
																<div class="symbol-label">
																	<img src="' . $env["APP_URL"] . 'uploads/profile/' . $profile_image . '" alt="Emma Smith" class="w-100">
																</div>
															</a>
														</div> <div class="d-flex flex-column">
															<a href="#" class="text-gray-800 text-hover-primary mb-1">' . $user['fname'] . ' ' . $user['lname'] . '</a>
															<span>' . $user['email'] . '</span>
														</div></div>'
                );
                if (!empty($user["associates_id"])){
                    $associate = $h->table('users')->select()->where('id', '=', $user["associates_id"])->fetchAll();
                    if (@$associate[0]["profile_image"] != '' && @$associate[0]["profile_image"] != 'null') {
                        $associate_profile_image = @$associate[0]["profile_image"];
                    } else {
                        $associate_profile_image = "avatar.png";
                    }
                    $associateView = array("associateView" => '<div class="d-flex align-items-center"> <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
															<a href="#">
																<div class="symbol-label">
																	<img src="' . $env["APP_URL"] . 'uploads/profile/' . $associate_profile_image . '" alt="Emma Smith" class="w-100">
																</div>
															</a>
														</div> <div class="d-flex flex-column">
															<a href="#" class="text-gray-800 text-hover-primary mb-1">' . @$associate[0]['fname'] . ' ' . @$associate[0]['lname'] . '</a>
															<span>' . @$associate[0]['email'] . '</span>
														</div></div>'
                    );
                }else{
                    $associate_profile_image = "avatar.png";
                    $associateView = array("associateView" => '<div class="d-flex align-items-center"> <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
															<a href="#">
																<div class="symbol-label">
																	<img src="' . $env["APP_URL"] . 'uploads/profile/' . $associate_profile_image . '" alt="Emma Smith" class="w-100">
																</div>
															</a>
														</div> <div class="d-flex flex-column">
															<a href="#" class="text-gray-800 text-hover-primary mb-1">N/A</a>
															<span>N/A</span>
														</div></div>'
                    );
                }

                $srNo++;
                $ids = array("ids" => "$srNo");
                $check_arr[] = array_merge($ids, $user, $userView,$associateView, $status, $action);
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
    if ($_GET['page_name'] == "view_clients_assigned") {
        $srNo = 0;
        $users = $h->table('users')->select()->where('firm_id', '=', $loginUserId)->where('type', '=', 'client')->where('work_status', '=', 'assigned')->orderBy('id', 'desc')->fetchAll();
        if (!empty($users)) {
            foreach ($users as $user) {
                if ($user['work_status'] == "unassigned") {
                    $statusView = "<span class='badge badge-light-warning'>Un Assigned</span>";
                    $userStatus = "";
                } else if ($user['work_status'] == "assigned") {
                    $statusView = "<span class='badge badge-light-success'>Assigned</span>";
                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 0)' class='menu-link px-3'>Completed</a></div>
                <div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='menu-link px-3'>In Progress</a></div>";
                }else if ($user['work_status'] == "inprogress") {
                    $statusView = "<span class='badge badge-light-info'>In Progress</span>";
                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 0)' class='menu-link px-3'>Completed</a></div>
                 <div class='menu-item px-3'><a href='javascript:;' onclick='userStatus(".$user['id'].", 2)' class='menu-link px-3'>Assigned</a></div>";
                }  else if ($user['work_status'] == "completed") {
                    $statusView = "<span class='badge badge-light-success'>Completed</span>";
                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='menu-link px-3'>In Progress</a></div>
                   <div class='menu-item px-3'><a href='javascript:;' onclick='userStatus(".$user['id'].", 2)' class='menu-link px-3'>Assigned</a></div>";
                } else {
                    $statusView = "<span class='badge badge-light-warning'>Un Assigned</span>";
                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 0)' class='menu-link px-3'>Completed</a></div>
                   <div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='menu-link px-3'>In Progress</a></div>";
                }

                // $plus = array("plusView" => "<a class='control' tabindex='0' style=""></a>");
                $action = array('action' =>'
                   <a href="javascript:;" class="btn-sm btn btn-light-danger text-start me-2 action-edit" onclick="deleteUser(' . $user["id"] . ')" ><i style="font-size: 16px;" class="fa-regular fa-trash-can"></i></a>
           <button type="button" onclick="openAction1('.$user['id'].')" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-flip="top-end">

                            <span class="svg-icon svg-icon-5 m-0">
                               <i class="bi bi-three-dots-vertical"></i>
                            </span>
                        </button>
                        <div id="menu1-'.$user['id'].'" class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-125px py-4" data-kt-menu="true">
                            	<div class="menu-item px-3"><a  role="button" data-id="' . $user["id"] . '" data-bs-toggle="modal" data-bs-target="#editExampleModal"  class="edit menu-link px-3">Edit</a></div>
                            	'.$userStatus.'
                            
                        </div>');


                $status = array("statusView" => $statusView);
                if ($user["profile_image"] != '' && $user["profile_image"] != 'null') {
                    $profile_image = $user["profile_image"];
                } else {
                    $profile_image = "avatar.png";
                }
                $userView = array(
                    "userView" => '<div class="d-flex align-items-center"> <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
															<a href="#">
																<div class="symbol-label">
																	<img src="' . $env["APP_URL"] . 'uploads/profile/' . $profile_image . '" alt="Emma Smith" class="w-100">
																</div>
															</a>
														</div> <div class="d-flex flex-column">
															<a href="#" class="text-gray-800 text-hover-primary mb-1">' . $user['fname'] . ' ' . $user['lname'] . '</a>
															<span>' . $user['email'] . '</span>
														</div></div>'
                );
                if (!empty($user["associates_id"])){
                    $associate = $h->table('users')->select()->where('id', '=', $user["associates_id"])->fetchAll();
                    if (@$associate[0]["profile_image"] != '' && @$associate[0]["profile_image"] != 'null') {
                        $associate_profile_image = @$associate[0]["profile_image"];
                    } else {
                        $associate_profile_image = "avatar.png";
                    }
                    $associateView = array("associateView" => '<div class="d-flex align-items-center"> <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
															<a href="#">
																<div class="symbol-label">
																	<img src="' . $env["APP_URL"] . 'uploads/profile/' . $associate_profile_image . '" alt="Emma Smith" class="w-100">
																</div>
															</a>
														</div> <div class="d-flex flex-column">
															<a href="#" class="text-gray-800 text-hover-primary mb-1">' . @$associate[0]['fname'] . ' ' . @$associate[0]['lname'] . '</a>
															<span>' . @$associate[0]['email'] . '</span>
														</div></div>'
                    );
                }else{
                    $associate_profile_image = "avatar.png";
                    $associateView = array("associateView" => '<div class="d-flex align-items-center"> <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
															<a href="#">
																<div class="symbol-label">
																	<img src="' . $env["APP_URL"] . 'uploads/profile/' . $associate_profile_image . '" alt="Emma Smith" class="w-100">
																</div>
															</a>
														</div> <div class="d-flex flex-column">
															<a href="#" class="text-gray-800 text-hover-primary mb-1">N/A</a>
															<span>N/A</span>
														</div></div>'
                    );
                }
                $srNo++;
                $ids = array("ids" => "$srNo");
                $check_arr[] = array_merge($ids, $user, $userView,$associateView, $status, $action);
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
    if ($_GET['page_name'] == "view_clients_unassigned") {
        $srNo = 0;
        $users = $h->table('users')->select()->where('firm_id', '=', $loginUserId)->where('type', '=', 'client')->where('work_status', '=', 'unassigned')->orderBy('id', 'desc')->fetchAll();
        if (!empty($users)) {
            foreach ($users as $user) {
                if ($user['work_status'] == "unassigned") {
                    $statusView = "<span class='badge badge-light-warning'>Un Assigned</span>";
                    $userStatus = "";
                } else if ($user['work_status'] == "assigned") {
                    $statusView = "<span class='badge badge-light-success'>Assigned</span>";
                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 0)' class='menu-link px-3'>Completed</a></div>
                <div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='menu-link px-3'>In Progress</a></div>";
                }else if ($user['work_status'] == "inprogress") {
                    $statusView = "<span class='badge badge-light-info'>In Progress</span>";
                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 0)' class='menu-link px-3'>Completed</a></div>
                 <div class='menu-item px-3'><a href='javascript:;' onclick='userStatus(".$user['id'].", 2)' class='menu-link px-3'>Assigned</a></div>";
                }  else if ($user['work_status'] == "completed") {
                    $statusView = "<span class='badge badge-light-success'>Completed</span>";
                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='menu-link px-3'>In Progress</a></div>
                   <div class='menu-item px-3'><a href='javascript:;' onclick='userStatus(".$user['id'].", 2)' class='menu-link px-3'>Assigned</a></div>";
                } else {
                    $statusView = "<span class='badge badge-light-warning'>Un Assigned</span>";
                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 0)' class='menu-link px-3'>Completed</a></div>
                   <div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='menu-link px-3'>In Progress</a></div>";
                }

                // $plus = array("plusView" => "<a class='control' tabindex='0' style=""></a>");
                $action = array('action' =>'
                   <a href="javascript:;" class="btn-sm btn btn-light-danger text-start me-2 action-edit" onclick="deleteUser(' . $user["id"] . ')" ><i style="font-size: 16px;" class="fa-regular fa-trash-can"></i></a>
           <button type="button" onclick="openAction2('.$user['id'].')" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-flip="top-end">

                            <span class="svg-icon svg-icon-5 m-0">
                               <i class="bi bi-three-dots-vertical"></i>
                            </span>
                        </button>
                        <div id="menu2-'.$user['id'].'" class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-125px py-4" data-kt-menu="true">
                            	<div class="menu-item px-3"><a  role="button" data-id="' . $user["id"] . '" data-bs-toggle="modal" data-bs-target="#editExampleModal"  class="edit menu-link px-3">Edit</a></div>
                            	'.$userStatus.'
                            
                        </div>');


                $status = array("statusView" => $statusView);
                if ($user["profile_image"] != '' && $user["profile_image"] != 'null') {
                    $profile_image = $user["profile_image"];
                } else {
                    $profile_image = "avatar.png";
                }
                $userView = array(
                    "userView" => '<div class="d-flex align-items-center"> <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
															<a href="#">
																<div class="symbol-label">
																	<img src="' . $env["APP_URL"] . 'uploads/profile/' . $profile_image . '" alt="Emma Smith" class="w-100">
																</div>
															</a>
														</div> <div class="d-flex flex-column">
															<a href="#" class="text-gray-800 text-hover-primary mb-1">' . $user['fname'] . ' ' . $user['lname'] . '</a>
															<span>' . $user['email'] . '</span>
														</div></div>'
                );
                if (!empty($user["associates_id"])){
                    $associate = $h->table('users')->select()->where('id', '=', $user["associates_id"])->fetchAll();
                    if (@$associate[0]["profile_image"] != '' && @$associate[0]["profile_image"] != 'null') {
                        $associate_profile_image = @$associate[0]["profile_image"];
                    } else {
                        $associate_profile_image = "avatar.png";
                    }
                    $associateView = array("associateView" => '<div class="d-flex align-items-center"> <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
															<a href="#">
																<div class="symbol-label">
																	<img src="' . $env["APP_URL"] . 'uploads/profile/' . $associate_profile_image . '" alt="Emma Smith" class="w-100">
																</div>
															</a>
														</div> <div class="d-flex flex-column">
															<a href="#" class="text-gray-800 text-hover-primary mb-1">' . @$associate[0]['fname'] . ' ' . @$associate[0]['lname'] . '</a>
															<span>' . @$associate[0]['email'] . '</span>
														</div></div>'
                    );
                }else{
                    $associate_profile_image = "avatar.png";
                    $associateView = array("associateView" => '<div class="d-flex align-items-center"> <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
															<a href="#">
																<div class="symbol-label">
																	<img src="' . $env["APP_URL"] . 'uploads/profile/' . $associate_profile_image . '" alt="Emma Smith" class="w-100">
																</div>
															</a>
														</div> <div class="d-flex flex-column">
															<a href="#" class="text-gray-800 text-hover-primary mb-1">N/A</a>
															<span>N/A</span>
														</div></div>'
                    );
                }
                $srNo++;
                $ids = array("ids" => "$srNo");
                $check_arr[] = array_merge($ids, $user, $userView,$associateView, $status, $action);
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
    if ($_GET['page_name'] == "view_clients_inProgress") {
        $srNo = 0;
        $users = $h->table('users')->select()->where('firm_id', '=', $loginUserId)->where('type', '=', 'client')->where('work_status', '=', 'inprogress')->orderBy('id', 'desc')->fetchAll();
        if (!empty($users)) {
            foreach ($users as $user) {
                if ($user['work_status'] == "unassigned") {
                    $statusView = "<span class='badge badge-light-warning'>Un Assigned</span>";
                    $userStatus = "";
                } else if ($user['work_status'] == "assigned") {
                    $statusView = "<span class='badge badge-light-success'>Assigned</span>";
                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 0)' class='menu-link px-3'>Completed</a></div>
                <div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='menu-link px-3'>In Progress</a></div>";
                }else if ($user['work_status'] == "inprogress") {
                    $statusView = "<span class='badge badge-light-info'>In Progress</span>";
                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 0)' class='menu-link px-3'>Completed</a></div>
                 <div class='menu-item px-3'><a href='javascript:;' onclick='userStatus(".$user['id'].", 2)' class='menu-link px-3'>Assigned</a></div>";
                }  else if ($user['work_status'] == "completed") {
                    $statusView = "<span class='badge badge-light-success'>Completed</span>";
                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='menu-link px-3'>In Progress</a></div>
                   <div class='menu-item px-3'><a href='javascript:;' onclick='userStatus(".$user['id'].", 2)' class='menu-link px-3'>Assigned</a></div>";
                } else {
                    $statusView = "<span class='badge badge-light-warning'>Un Assigned</span>";
                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 0)' class='menu-link px-3'>Completed</a></div>
                   <div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='menu-link px-3'>In Progress</a></div>";
                }

                // $plus = array("plusView" => "<a class='control' tabindex='0' style=""></a>");
                $action = array('action' =>'
                   <a href="javascript:;" class="btn-sm btn btn-light-danger text-start me-2 action-edit" onclick="deleteUser(' . $user["id"] . ')" ><i style="font-size: 16px;" class="fa-regular fa-trash-can"></i></a>
           <button type="button" onclick="openAction3('.$user['id'].')" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-flip="top-end">

                            <span class="svg-icon svg-icon-5 m-0">
                               <i class="bi bi-three-dots-vertical"></i>
                            </span>
                        </button>
                        <div id="menu3-'.$user['id'].'" class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-125px py-4" data-kt-menu="true">
                            	<div class="menu-item px-3"><a  role="button" data-id="' . $user["id"] . '" data-bs-toggle="modal" data-bs-target="#editExampleModal"  class="edit menu-link px-3">Edit</a></div>
                            	'.$userStatus.'
                            
                        </div>');


                $status = array("statusView" => $statusView);
                if ($user["profile_image"] != '' && $user["profile_image"] != 'null') {
                    $profile_image = $user["profile_image"];
                } else {
                    $profile_image = "avatar.png";
                }
                $userView = array(
                    "userView" => '<div class="d-flex align-items-center"> <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
															<a href="#">
																<div class="symbol-label">
																	<img src="' . $env["APP_URL"] . 'uploads/profile/' . $profile_image . '" alt="Emma Smith" class="w-100">
																</div>
															</a>
														</div> <div class="d-flex flex-column">
															<a href="#" class="text-gray-800 text-hover-primary mb-1">' . $user['fname'] . ' ' . $user['lname'] . '</a>
															<span>' . $user['email'] . '</span>
														</div></div>'
                );
                if (!empty($user["associates_id"])){
                    $associate = $h->table('users')->select()->where('id', '=', $user["associates_id"])->fetchAll();
                    if (@$associate[0]["profile_image"] != '' && @$associate[0]["profile_image"] != 'null') {
                        $associate_profile_image = @$associate[0]["profile_image"];
                    } else {
                        $associate_profile_image = "avatar.png";
                    }
                    $associateView = array("associateView" => '<div class="d-flex align-items-center"> <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
															<a href="#">
																<div class="symbol-label">
																	<img src="' . $env["APP_URL"] . 'uploads/profile/' . $associate_profile_image . '" alt="Emma Smith" class="w-100">
																</div>
															</a>
														</div> <div class="d-flex flex-column">
															<a href="#" class="text-gray-800 text-hover-primary mb-1">' . @$associate[0]['fname'] . ' ' . @$associate[0]['lname'] . '</a>
															<span>' . @$associate[0]['email'] . '</span>
														</div></div>'
                    );
                }else{
                    $associate_profile_image = "avatar.png";
                    $associateView = array("associateView" => '<div class="d-flex align-items-center"> <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
															<a href="#">
																<div class="symbol-label">
																	<img src="' . $env["APP_URL"] . 'uploads/profile/' . $associate_profile_image . '" alt="Emma Smith" class="w-100">
																</div>
															</a>
														</div> <div class="d-flex flex-column">
															<a href="#" class="text-gray-800 text-hover-primary mb-1">N/A</a>
															<span>N/A</span>
														</div></div>'
                    );
                }
                $srNo++;
                $ids = array("ids" => "$srNo");
                $check_arr[] = array_merge($ids, $user, $userView,$associateView, $status, $action);
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
    if ($_GET['page_name'] == "view_clients_completed") {
        $srNo = 0;
        $users = $h->table('users')->select()->where('firm_id', '=', $loginUserId)->where('type', '=', 'client')->where('work_status', '=', 'completed')->orderBy('id', 'desc')->fetchAll();
        if (!empty($users)) {
            foreach ($users as $user) {
                if ($user['work_status'] == "unassigned") {
                    $statusView = "<span class='badge badge-light-warning'>Un Assigned</span>";
                    $userStatus = "";
                } else if ($user['work_status'] == "assigned") {
                    $statusView = "<span class='badge badge-light-success'>Assigned</span>";
                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 0)' class='menu-link px-3'>Completed</a></div>
                <div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='menu-link px-3'>In Progress</a></div>";
                }else if ($user['work_status'] == "inprogress") {
                    $statusView = "<span class='badge badge-light-info'>In Progress</span>";
                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 0)' class='menu-link px-3'>Completed</a></div>
                 <div class='menu-item px-3'><a href='javascript:;' onclick='userStatus(".$user['id'].", 2)' class='menu-link px-3'>Assigned</a></div>";
                }  else if ($user['work_status'] == "completed") {
                    $statusView = "<span class='badge badge-light-success'>Completed</span>";
                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='menu-link px-3'>In Progress</a></div>
                   <div class='menu-item px-3'><a href='javascript:;' onclick='userStatus(".$user['id'].", 2)' class='menu-link px-3'>Assigned</a></div>";
                } else {
                    $statusView = "<span class='badge badge-light-warning'>Un Assigned</span>";
                    $userStatus = "<div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 0)' class='menu-link px-3'>Completed</a></div>
                   <div class='menu-item px-3'><a  href='javascript:;' onclick='userStatus(".$user['id'].", 1)' class='menu-link px-3'>In Progress</a></div>";
                }

                // $plus = array("plusView" => "<a class='control' tabindex='0' style=""></a>");
                $action = array('action' =>'
                   <a href="javascript:;" class="btn-sm btn btn-light-danger text-start me-2 action-edit" onclick="deleteUser(' . $user["id"] . ')" ><i style="font-size: 16px;" class="fa-regular fa-trash-can"></i></a>
           <button type="button" onclick="openAction4('.$user['id'].')" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-flip="top-end">

                            <span class="svg-icon svg-icon-5 m-0">
                               <i class="bi bi-three-dots-vertical"></i>
                            </span>
                        </button>
                        <div id="menu4-'.$user['id'].'" class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-125px py-4" data-kt-menu="true">
                            	<div class="menu-item px-3"><a  role="button" data-id="' . $user["id"] . '" data-bs-toggle="modal" data-bs-target="#editExampleModal"  class="edit menu-link px-3">Edit</a></div>
                            	'.$userStatus.'
                            
                        </div>');


                $status = array("statusView" => $statusView);
                if ($user["profile_image"] != '' && $user["profile_image"] != 'null') {
                    $profile_image = $user["profile_image"];
                } else {
                    $profile_image = "avatar.png";
                }
                $userView = array(
                    "userView" => '<div class="d-flex align-items-center"> <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
															<a href="#">
																<div class="symbol-label">
																	<img src="' . $env["APP_URL"] . 'uploads/profile/' . $profile_image . '" alt="Emma Smith" class="w-100">
																</div>
															</a>
														</div> <div class="d-flex flex-column">
															<a href="#" class="text-gray-800 text-hover-primary mb-1">' . $user['fname'] . ' ' . $user['lname'] . '</a>
															<span>' . $user['email'] . '</span>
														</div></div>'
                );
                if (!empty($user["associates_id"])){
                    $associate = $h->table('users')->select()->where('id', '=', $user["associates_id"])->fetchAll();
                    if (@$associate[0]["profile_image"] != '' && @$associate[0]["profile_image"] != 'null') {
                        $associate_profile_image = @$associate[0]["profile_image"];
                    } else {
                        $associate_profile_image = "avatar.png";
                    }
                    $associateView = array("associateView" => '<div class="d-flex align-items-center"> <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
															<a href="#">
																<div class="symbol-label">
																	<img src="' . $env["APP_URL"] . 'uploads/profile/' . $associate_profile_image . '" alt="Emma Smith" class="w-100">
																</div>
															</a>
														</div> <div class="d-flex flex-column">
															<a href="#" class="text-gray-800 text-hover-primary mb-1">' . @$associate[0]['fname'] . ' ' . @$associate[0]['lname'] . '</a>
															<span>' . @$associate[0]['email'] . '</span>
														</div></div>'
                    );
                }else{
                    $associate_profile_image = "avatar.png";
                    $associateView = array("associateView" => '<div class="d-flex align-items-center"> <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
															<a href="#">
																<div class="symbol-label">
																	<img src="' . $env["APP_URL"] . 'uploads/profile/' . $associate_profile_image . '" alt="Emma Smith" class="w-100">
																</div>
															</a>
														</div> <div class="d-flex flex-column">
															<a href="#" class="text-gray-800 text-hover-primary mb-1">N/A</a>
															<span>N/A</span>
														</div></div>'
                    );
                }
                $srNo++;
                $ids = array("ids" => "$srNo");
                $check_arr[] = array_merge($ids, $user, $userView,$associateView, $status, $action);
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
    if($_GET['page_name']=="view_campaign_list"){
        $srNo = 0;
        $campaign_lists = $h->table('campaign_list')->select()->where('firm_id', '=', $loginUserId)->orderBy('id', 'desc')->fetchAll();
        if (!empty($campaign_lists)) {
            foreach ($campaign_lists as $campaign_list) {
                // Determine user status
                if ($campaign_list['list_type'] == "email") {
                    $userType = "<span class='badge badge-light-success'>Email</span>";
                } else if ($campaign_list['list_type'] == "phone") {
                    $userType = "<span class='badge badge-light-info'>Telephone No</span>";
                } else {
                    $userType = "<span class='badge badge-light-success'>Email</span>";}

                $action = array('action' => '
                    <a href="/user/campaign/list_details/'.$campaign_list["id"].'"  class="btn-sm btn btn-light-info text-start me-2 action-edit" ><i style="font-size: 16px;" class="fa-solid fa-eye"></i></a>
                   <a href="javascript:;" class="btn-sm btn btn-light-danger text-start me-2 action-edit" onclick="deleteUser('.$campaign_list["id"].')" ><i style="font-size: 16px;" class="fa-regular fa-trash-can"></i></a>
           
                      ');

                $user_type = array("userType" => $userType);
                $srNo++;
                $ids = array("ids" => "$srNo");
                $check_arr[] = array_merge($ids, $campaign_list, $user_type, $action);
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
    if($_GET['page_name']=="view_campaign_list_details"){
        $srNo = 0;
        if (!empty($_SESSION['campaign_list_id'])){
            $campaign_list_details = $h->table('campaign_list_detail')->select()->where('firm_id', '=', $loginUserId)->where('campaign_list_id', '=', $_SESSION['campaign_list_id'])->orderBy('id', 'desc')->fetchAll();
            if (!empty($campaign_list_details)) {
                foreach ($campaign_list_details as $campaign_list_detail) {
                    $campaign_lists = $h->table('campaign_list')->select()->where('id', '=', $campaign_list_detail["campaign_list_id"])->fetchAll();
                    if ($campaign_lists[0]['list_type'] == "email"){
                        $userContact = array("userContact" => '<span class="inv-email" style="font-size: 15px;"><svg style="width: 17px; margin-right: 7px;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-mail"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg> '.$campaign_list_detail["contact"].'</span>');
                    }elseif($campaign_lists[0]['list_type'] == "phone"){
                        $userContact = array("userContact" => '<span class="inv-email" style="font-size: 15px;"><svg style="width: 17px; margin-right: 7px;" viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>'.$campaign_list_detail["contact"].'</span>');
                    }

                    $action = array('action' => '
                   <a href="javascript:;" class="btn-sm btn btn-light-danger text-start me-2 action-edit" onclick="deleteUser('.$campaign_list_detail["id"].')" ><i style="font-size: 16px;" class="fa-regular fa-trash-can"></i></a>
                      ');
                    $srNo++;
                    $ids = array("ids" => "$srNo");
                    $check_arr[] = array_merge($ids, $campaign_list_detail,$userContact, $action);
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
        }else {
            $result = array(
                "sEcho" => 1,
                "iTotalRecords" => 0,
                "iTotalDisplayRecords" => 0,
                "aaData" => array()
            );
            echo json_encode($result);
        }

    }
    if($_GET['page_name']=="view_campaign"){
        $srNo = 0;
       $campaigns = $h->table('campaign')->select()->where('firm_id', '=', $loginUserId)->orderBy('id', 'desc')->fetchAll();
        if (!empty($campaigns)) {
            foreach ($campaigns as $campaign) {
                // Determine user status
                if ($campaign['status'] == "start") {
                    $statusView = "<span class='badge badge-light-success'>Started</span>";
                    $userStatus = "<a href='javascript:;' onclick='userStatus(".$campaign['id'].", 0)' class='btn btn-light-warning btn-sm text-start me-2 action-edit' ><i style='font-size: 16px;' class='fa-solid fa-pause'></i></a>";

                } else if ($campaign['status'] == "pause") {
                    $statusView = "<span class='badge badge-light-warning'>Paused</span>";
                    $userStatus = "<a href='javascript:;' onclick='userStatus(".$campaign['id'].", 1)' class='btn-sm btn btn-light-success text-start me-2 action-edit' ><i style='font-size: 16px;' class='fa-solid fa-play'></i></a>";

                }else if ($campaign['status'] == "pending") {
                    $statusView = "<span class='badge badge-light-info'>Not Start Yet</span>";
                    $userStatus='';
                }else if ($campaign['status'] == "ended") {
                    $statusView = "<span class='badge badge-light-danger'>Ended</span>";
                    $userStatus='';
                } else {
                    $statusView = "<span class='badge badge-light-info'>Not Start Yet</span>";
                    $userStatus='';
                }
                if ($campaign['campaign_type'] == "email") {
                    $listView = "<span class='badge badge-light-success'>Email</span>";
                }else{
                    $listView = "<span class='badge badge-light-info'>Phone</span>";
                }
                $listViews = array("listView" => $listView);
                $action = array('action' => $userStatus.'
                   <a href="javascript:;" class="btn-sm btn btn-light-danger text-start me-2 action-edit" onclick="deleteUser('.$campaign["id"].')" ><i style="font-size: 16px;" class="fa-regular fa-trash-can"></i></a>
           
                      ');
                $dateTime = new DateTime($campaign["date"]);
                $formattedDate = $dateTime->format(' l, j F Y g:i A');
                $date = array(
                    "DateView" => '<span class="inv-date" >
            <svg style="color:red" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg> ' . $formattedDate . ' 
        </span>'
                );
                $status = array("statusView" => $statusView);
                $srNo++;
                $ids = array("ids" => "$srNo");
                $check_arr[] = array_merge($ids, $campaign,$date,$listViews, $status, $action);
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
        if($_GET['page_name']=="view_email_template"){
            $srNo = 0;
            $email_templates = $h->table('email_template')->select()->where('firm_id', '=', $loginUserId)->orderBy('id', 'desc')->fetchAll();
            if (!empty($email_templates)) {
                foreach ($email_templates as $email_template) {
                    // Determine user status
                    if ($email_template['status'] == "active") {
                        $statusView = "<span class='badge badge-light-success'>Active</span>";
                        $userStatus = "<a href='javascript:;' onclick='userStatus(".$email_template['id'].", 0)' class='btn btn-light-danger btn-sm text-start me-2 action-edit' ><i style='font-size: 16px;' class='fa-solid fa-lock'></i></a>";

                    } else if ($email_template['status'] == "block") {
                        $statusView = "<span class='badge badge-light-danger'>Block</span>";
                        $userStatus = "<a href='javascript:;' onclick='userStatus(".$email_template['id'].", 1)' class='btn-sm btn btn-light-success text-start me-2 action-edit' ><i style='font-size: 16px;' class='fa-solid fa-unlock'></i></a>";

                    } else {
                        $statusView = "<span class='badge badge-light-info'>Not Start Yet</span>";
                        $userStatus='';
                    }
                    $action = array('action' => $userStatus.'
                    <a href="/user/email-template/edit/'.$email_template["id"].'" class="btn-sm btn btn-light-info text-start me-2" ><i style="font-size: 16px;" class="fa-solid fa-pen-to-square"></i></a>
                      <a role="button" data-id="'.$email_template["id"].'" data-bs-toggle="modal" data-bs-target="#viewExampleModal" class="view btn-sm btn btn-light-warning text-start me-2 action-edit" ><i style="font-size: 16px;" class="fa-solid fa-eye fa-fw"></i></a>
                   <a href="javascript:;" class="btn-sm btn btn-light-danger text-start me-2 action-edit" onclick="deleteUser('.$email_template["id"].')" ><i style="font-size: 16px;" class="fa-regular fa-trash-can"></i></a>');
                    $status = array("statusView" => $statusView);
                    $srNo++;
                    $ids = array("ids" => "$srNo");
                    $check_arr[] = array_merge($ids,$email_template, $status, $action);
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
}
if ($_GET['page_name'] == "view_inbox") {
    $inboxEmails = fetchEmailsFromFolder('INBOX');
    if (!empty($inboxEmails)) {
        $srNo = 0;
        $check_arr = [];

        foreach ($inboxEmails as $email) {
            $srNo++;
            $email_id = $email['id'];
            $userName = $email['fromName'];
            $subject = $email['subject'];
            $dateTime = Carbon::parse($email['date']);
            $formattedDate = $dateTime->diffForHumans();
            $action = "<a href='javascript:;' data-id='".$email_id."' class='btn btn-sm btn-icon btn-light btn-active-light-primary action-edit' data-bs-toggle='tooltip' data-bs-placement='top' title='Send To Trash' data-bs-original-title='Delete'>
                 
                                        <span class='svg-icon svg-icon-2'>
															<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none'>
																<path d='M5 9C5 8.44772 5.44772 8 6 8H18C18.5523 8 19 8.44772 19 9V18C19 19.6569 17.6569 21 16 21H8C6.34315 21 5 19.6569 5 18V9Z' fill='currentColor'></path>
																<path opacity='0.5' d='M5 5C5 4.44772 5.44772 4 6 4H18C18.5523 4 19 4.44772 19 5V5C19 5.55228 18.5523 6 18 6H6C5.44772 6 5 5.55228 5 5V5Z' fill='currentColor'></path>
																<path opacity='0.5' d='M9 4C9 3.44772 9.44772 3 10 3H14C14.5523 3 15 3.44772 15 4V4H9V4Z' fill='currentColor'></path>
															</svg>
														</span></a>";

            $emailData = [
                "select" => "<div class='form-check form-check-sm form-check-custom form-check-solid me-3 mt-3'>
                                        <input class='form-check-input' type='checkbox' name='bulk_action'  value='".$email_id."'>
                </div>",
                "from" => "<a href='/user/email/email_reply/INBOX/".$email_id."' class='d-flex align-items-center text-dark'>
                                <div class='symbol symbol-35px me-3'>
                                    <div class='symbol-label'>
                                        <img src='https://avatar.iran.liara.run/username?username={$userName}' alt='default' style='width: 100%;'>
                                    </div>
                                </div>
                                <span class='fw-bold'>{$userName}</span>
                            </a>",
                "subject" => "
    <div class='text-dark mb-1'>
        <!--begin::Heading-->
        <a href='/user/email/email_reply/INBOX/".$email_id."' class='text-dark'>
            <span class='fw-bolder'>" . str_replace('Fwd: ', '', $subject) . "</span>
        </a>
        <!--end::Heading-->
    </div>",
                "DateView" => "<span class='inv-date'>{$formattedDate}</span>",
                "actions" => $action
            ];

            $check_arr[] = $emailData;
        }

        $result = [
            "sEcho" => 1,
            "iTotalRecords" => count($check_arr),
            "iTotalDisplayRecords" => count($check_arr),
            "aaData" => $check_arr
        ];

        echo json_encode($result);
    } else {
        $result = [
            "sEcho" => 1,
            "iTotalRecords" => 0,
            "iTotalDisplayRecords" => 0,
            "aaData" => []
        ];

        echo json_encode($result);
    }
}
if ($_GET['page_name'] == "view_sent") {
    $inboxEmails = fetchEmailsFromFolder('INBOX.Sent');

    if (!empty($inboxEmails)) {
        $srNo = 0;
        $check_arr = [];

        foreach ($inboxEmails as $email) {
            $srNo++;
            $email_id = $email['id'];
            $userName = $email['toEmail'];
            $subject = $email['subject'];
            $dateTime = Carbon::parse($email['date']);
            $formattedDate = $dateTime->diffForHumans();
            $action = "<a href='javascript:;' data-id='".$email_id."' class='btn btn-sm btn-icon btn-light btn-active-light-primary action-edit' data-bs-toggle='tooltip' data-bs-placement='top' title='Send To Trash' data-bs-original-title='Delete'>
                 
                                        <span class='svg-icon svg-icon-2'>
															<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none'>
																<path d='M5 9C5 8.44772 5.44772 8 6 8H18C18.5523 8 19 8.44772 19 9V18C19 19.6569 17.6569 21 16 21H8C6.34315 21 5 19.6569 5 18V9Z' fill='currentColor'></path>
																<path opacity='0.5' d='M5 5C5 4.44772 5.44772 4 6 4H18C18.5523 4 19 4.44772 19 5V5C19 5.55228 18.5523 6 18 6H6C5.44772 6 5 5.55228 5 5V5Z' fill='currentColor'></path>
																<path opacity='0.5' d='M9 4C9 3.44772 9.44772 3 10 3H14C14.5523 3 15 3.44772 15 4V4H9V4Z' fill='currentColor'></path>
															</svg>
														</span></a>";

            $emailData = [
                "select" => "<div class='form-check form-check-sm form-check-custom form-check-solid me-3 mt-3'>
                                        <input class='form-check-input' type='checkbox' name='bulk_action'  value='".$email_id."'>
                </div>",
                "from" => "<a href='/user/email/email_reply/INBOX.Sent/".$email_id."' class='d-flex align-items-center text-dark'>
                                <div class='symbol symbol-35px me-3'>
                                    <div class='symbol-label'>
                                        <img src='https://avatar.iran.liara.run/username?username={$userName}' alt='default' style='width: 100%;'>
                                    </div>
                                </div>
                                <span class='fw-bold'>{$userName}</span>
                            </a>",
                "subject" => "
    <div class='text-dark mb-1'>
        <!--begin::Heading-->
        <a href='/user/email/email_reply/INBOX.Sent/".$email_id."' class='text-dark'>
            <span class='fw-bolder'>" . str_replace('Fwd: ', '', $subject) . "</span>
        </a>
        <!--end::Heading-->
    </div>",
                "DateView" => "<span class='inv-date'>{$formattedDate}</span>",
                "actions" => $action
            ];

            $check_arr[] = $emailData;
        }

        $result = [
            "sEcho" => 1,
            "iTotalRecords" => count($check_arr),
            "iTotalDisplayRecords" => count($check_arr),
            "aaData" => $check_arr
        ];

        echo json_encode($result);
    } else {
        $result = [
            "sEcho" => 1,
            "iTotalRecords" => 0,
            "iTotalDisplayRecords" => 0,
            "aaData" => []
        ];

        echo json_encode($result);
    }
}
if ($_GET['page_name'] == "view_trash") {
    $inboxEmails = fetchEmailsFromFolder('INBOX.Trash');

    if (!empty($inboxEmails)) {
        $srNo = 0;
        $check_arr = [];

        foreach ($inboxEmails as $email) {
            $srNo++;
            $email_id = $email['id'];
            if (!empty($email['fromName'])){
                $userName = $email['fromName'];
            }else{
                $userName = $email['toEmail'];
            }
            $subject = $email['subject'];
            $dateTime = Carbon::parse($email['date']);
            $formattedDate = $dateTime->diffForHumans();
            $action = "<a href='javascript:;' data-id='".$email_id."' class='btn btn-sm btn-icon btn-light btn-active-light-primary action-edit' data-bs-toggle='tooltip' data-bs-placement='top' title='Send To Trash' data-bs-original-title='Delete'>
                 
                                        <span class='svg-icon svg-icon-2'>
															<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none'>
																<path d='M5 9C5 8.44772 5.44772 8 6 8H18C18.5523 8 19 8.44772 19 9V18C19 19.6569 17.6569 21 16 21H8C6.34315 21 5 19.6569 5 18V9Z' fill='currentColor'></path>
																<path opacity='0.5' d='M5 5C5 4.44772 5.44772 4 6 4H18C18.5523 4 19 4.44772 19 5V5C19 5.55228 18.5523 6 18 6H6C5.44772 6 5 5.55228 5 5V5Z' fill='currentColor'></path>
																<path opacity='0.5' d='M9 4C9 3.44772 9.44772 3 10 3H14C14.5523 3 15 3.44772 15 4V4H9V4Z' fill='currentColor'></path>
															</svg>
														</span></a>";

            $emailData = [
                "select" => "<div class='form-check form-check-sm form-check-custom form-check-solid me-3 mt-3'>
                                        <input class='form-check-input' type='checkbox' name='bulk_action'  value='".$email_id."'>
                </div>",
                "from" => "<a href='/user/email/email_reply/INBOX.Trash/".$email_id."' class='d-flex align-items-center text-dark'>
                                <div class='symbol symbol-35px me-3'>
                                    <div class='symbol-label'>
                                        <img src='https://avatar.iran.liara.run/username?username={$userName}' alt='default' style='width: 100%;'>
                                    </div>
                                </div>
                                <span class='fw-bold'>{$userName}</span>
                            </a>",
                "subject" => "
    <div class='text-dark mb-1'>
        <!--begin::Heading-->
        <a href='/user/email/email_reply/INBOX.Trash/".$email_id."' class='text-dark'>
            <span class='fw-bolder'>" . str_replace('Fwd: ', '', $subject) . "</span>
        </a>
        <!--end::Heading-->
    </div>",
                "DateView" => "<span class='inv-date'>{$formattedDate}</span>",
                "actions" => $action
            ];

            $check_arr[] = $emailData;
        }

        $result = [
            "sEcho" => 1,
            "iTotalRecords" => count($check_arr),
            "iTotalDisplayRecords" => count($check_arr),
            "aaData" => $check_arr
        ];

        echo json_encode($result);
    } else {
        $result = [
            "sEcho" => 1,
            "iTotalRecords" => 0,
            "iTotalDisplayRecords" => 0,
            "aaData" => []
        ];

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
if($_GET['page_name']=="view_members"){
    $srNo = 0;
    $users = $h->table('users')->select()->where('firm_id', '=', $loginUserId)->where('type', '=', 'member')->orderBy('id', 'desc')->fetchAll();
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
if($_GET['page_name']=="view_manage_clients"){
    $srNo = 0;
    $users = $h->table('users')->select()->where('firm_id', '=', $loginUserId)->where('type', '=', 'client')->orderBy('id', 'desc')->fetchAll();
    if (!empty($users)) {
        foreach ($users as $user) {

            $action = array('action' =>'
                     <a role="button" data-id="'.$user["id"].'" data-bs-toggle="modal" data-bs-target="#editPerModal" class="edit btn-sm btn btn-light-warning text-start me-2 action-edit" ><i style="font-size: 16px;" class="fa-solid fa-user-tag"></i></a>           
                      ');
            if ($user["profile_image"] != '' && $user["profile_image"] != 'null'){
                $profile_image = $user["profile_image"];
            }else{
                $profile_image = "avatar.png";
            }
            $clientView = array(
                "clientView" => '<div class="d-flex align-items-center"> <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
															<a href="#">
																<div class="symbol-label">
																	<img src="'.$env["APP_URL"].'uploads/profile/'.$profile_image.'" alt="Emma Smith" class="w-100">
																</div>
															</a>
														</div> <div class="d-flex flex-column">
															<a href="#" class="text-gray-800 text-hover-primary mb-1">' . $user['fname'] .' '.  $user['lname'] . '</a>
															<span>'.  $user['email'] . '</span>
														</div></div>');
            $memberinfo = $h->table('users')->select()->where('id', '=', $user['associates_id'])->fetchAll();
            if (!empty($memberinfo)){
                if ($memberinfo[0]["profile_image"] != '' && $memberinfo[0]["profile_image"] != 'null'){
                    $profile_image_member = $memberinfo[0]["profile_image"];
                }else{
                    $profile_image_member = "avatar.png";
                }
                $memberView = array(
                    "memberView" => '<div class="d-flex align-items-center"> <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
															<a href="#">
																<div class="symbol-label">
																	<img src="'.$env["APP_URL"].'uploads/profile/'.$profile_image_member.'" alt="Emma Smith" class="w-100">
																</div>
															</a>
														</div> <div class="d-flex flex-column">
															<a href="#" class="text-gray-800 text-hover-primary mb-1">' . $memberinfo[0]["fname"] .' '.  $memberinfo[0]['lname'] . '</a>
															<span>'.  $memberinfo[0]['email'] . '</span>
														</div></div>');
            }else{
                $memberView =array(
                    "memberView" => 'Member Not Assigned');
            }
            $srNo++;
            $ids = array("ids" => "$srNo");
            $check_arr[] = array_merge($ids, $user,$clientView,$memberView, $action);
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
if($_GET['page_name']=="view_sections"){
    $srNo = 0;
    $sections= $h->table('sections')->select()->orderBy('id', 'desc')->fetchAll();
    if (!empty($sections)) {
        foreach ($sections as $section) {
            // Determine user status
            if ($section['status'] == "active") {
                $statusView = "<span class='badge badge-light-success'>Active</span>";
                $userStatus = "<a href='javascript:;' onclick='userStatus(".$section['id'].", 0)' class='btn btn-light-success btn-sm text-start me-2 action-edit' ><i style='font-size: 16px' class='fa-solid fa-unlock'></i></a>";
            } else if ($section['status'] == "block") {
                $statusView = "<span class='badge badge-light-danger'>Inactive</span>";
                $userStatus = "<a href='javascript:;' onclick='userStatus(".$section['id'].", 1)' class='btn-sm btn btn-light-danger text-start me-2 action-edit' ><i style='font-size: 16px' class='fa-solid fa-lock'></i></a>";
            } else {
                $statusView = "<span class='badge badge-light-danger'>Inactive</span>";
                $userStatus = "<a href='javascript:;' onclick='userStatus(".$section['id'].", 1)' class='btn-sm btn btn-light-danger text-start me-2 action-edit' ><i style='font-size: 16px' class='fa-solid fa-lock'></i></a>";
            }

            // $plus = array("plusView" => "<a class='control' tabindex='0' style=""></a>");
            $action = array('action' =>  $userStatus.'
            <a role="button" href="/user/interviews/questions/update/'.$section["id"].'" class="edit btn-sm btn btn-light-info text-start me-2 action-edit" ><i style="font-size: 16px" class="fa-solid fa-edit"></i></a>
       
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
           <a role="button" href="/user/template/display-data/'.$template["user_id"].'/'.$templates_slug[0]['id'].'" class="btn btn-light-info btn-sm text-start me-2 action-edit" ><i style="font-size: 16px;" class="fa-solid fa-eye"></i></a>
            <a href="javascript:;" class="btn-sm btn btn-light-danger text-start me-2 action-edit" onclick="deleteUser('.$template["template_request_id"].')" ><i style="font-size: 16px;" class="fa-regular fa-trash-can"></i></a>
   
              ');
            } else if ($template['template_request_status'] == "pending") {
                $statusView = "<span class='badge badge-light-danger'>Pending</span>";
                $action = array('action' => '
           <button type="button" disabled class="btn btn-light-info btn-sm text-start me-2 action-edit" title="data is not available"><i style="font-size: 16px;" class="fa-solid fa-eye"></i></button>
            <a href="javascript:;" class="btn-sm btn btn-light-danger text-start me-2 action-edit" title="" onclick="deleteUser('.$template["template_request_id"].')" ><i style="font-size: 16px;" class="fa-regular fa-trash-can"></i></a>
   
              ');
            } else {
                $statusView = "<span class='badge badge-light-danger'>Pending</span>";
                $action = array('action' => '
           <a role="button" disabled="" class="btn btn-light-info btn-sm text-start me-2 action-edit" ><i style="font-size: 16px;" class="fa-solid fa-eye"></i></a>
            <a href="javascript:;" class="btn-sm btn btn-light-danger text-start me-2 action-edit" onclick="deleteUser('.$template["template_request_id"].')" ><i style="font-size: 16px;" class="fa-regular fa-trash-can"></i></a>
   
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
                $userStatus = "<a href='javascript:;' onclick='userStatus(".$template['id'].", 0)' class='btn btn-light-success btn-sm text-start me-2 action-edit' ><i style='font-size: 16px;' class='fa-solid fa-unlock'></i></a>";
            } else if ($template['status'] == "block") {
                $statusView = "<span class='badge badge-light-danger'>Inactive</span>";
                $userStatus = "<a href='javascript:;' onclick='userStatus(".$template['id'].", 1)' class='btn-sm btn btn-light-danger text-start me-2 action-edit' ><i style='font-size: 16px;' class='fa-solid fa-lock'></i></a>";
            } else {
                $statusView = "<span class='badge badge-light-danger'>Inactive</span>";
                $userStatus = "<a href='javascript:;' onclick='userStatus(".$template['id'].", 1)' class='btn-sm btn btn-light-danger text-start me-2 action-edit' ><i style='font-size: 16px;' class='fa-solid fa-lock'></i></a>";
            }

            // $plus = array("plusView" => "<a class='control' tabindex='0' style=""></a>");
            $action = array('action' => '
            <a role="button" data-id="'.$template["id"].'" data-bs-toggle="modal" data-bs-target="#inputFormModal" class="edit btn-sm btn btn-light-success text-start me-2 action-edit" ><i style="font-size: 16px;" class="fa-solid fa-user-plus"></i></a>
           <a role="button" href="/user/template/interview-list/'.$template["slug"].'" class="btn-sm btn btn-light-info text-start me-2 action-edit" ><i style="font-size: 16px;" class="fa-solid fa-eye"></i></a>
           
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
                $action = array('action' => '<a role="button" disabled href="/user/template/display-data/'.$template["user_id"].'/'.$template['template_id'].'" class="btn-sm btn btn-info text-start me-2 action-edit" ><i style="font-size: 16px;" class="fa-solid fa-eye"></i></a>
');
            } else if ($template['template_request_status'] == "pending") {
                $statusView = "<span class='badge badge-light-danger'>Pending</span>";
                $action = array('action' => '
            <a role="button" href="/user/template/view/'.$template["slug"].'" class="btn-sm btn btn-light-info text-start me-2 action-edit" ><i style="font-size: 16px;" class="fa-solid fa-eye"></i></a>
              ');
            } else {
                $statusView = "<span class='badge badge-light-danger'>Pending</span>";
                $action = array('action' => '
            <a role="button" href="/user/template/view/'.$template["slug"].'" class="btn-sm btn btn-light-info text-start me-2 action-edit" ><i style="font-size: 16px;" class="fa-solid fa-eye"></i></a>
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
                $action = array('action' =>'<a href="/user/invoice/update/'.$invoice["id"].'" class="btn-sm btn btn-light-info text-start me-2 action-edit" ><i style="font-size: 16px" class="fa-solid fa-pen-to-square"></i></a>
           <a href="javascript:;" class="btn-sm btn btn-light-danger text-start me-2 action-edit" onclick="deleteUser('.$invoice["id"].')" ><i style="font-size: 16px;" class="fa-regular fa-trash-can"></i></a>
              ');
            }else{
                if ($invoice['status'] == "unpaid") {
                    $action = array('action' => ' <a href="javascript:;" class="btn btn-danger btn-sm text-start me-2 " data-bs-toggle="modal" data-bs-target="#kt_modal_1" onclick="getDataForStripe(' . $invoice["id"] . ')" >Pay Invoice</a>
              ');
                }else{
                    $action = array('action' => ' <a href="javascript:;" class="btn btn-info btn-sm text-start me-2 " >Paid</a>
              ');
                }
            }

            $clientInfo = $h->table('users')->select()->where('id', '=', $invoice['client_id'])->fetchAll();
if ($clientInfo[0]["profile_image"] != '' && $clientInfo[0]["profile_image"] != 'null'){
$profile_image = $clientInfo[0]["profile_image"];
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
															<a href="#" class="text-gray-800 text-hover-primary mb-1">'. $clientInfo[0]["fname"].' '.$clientInfo[0]["lname"].'</a>
															<span>'.$invoice["client_email"].'</span>
														</div></div>'
            );
            $status = array("statusView" => $statusView);

            $date = new DateTime($invoice["due_date"]);
            $newDate = $date->format('d M');
            $currentDate = new DateTime();

// Check if the current date is greater than the due date
            if ($currentDate > $date) {
                // If the due date is in the past, display it in red
                if($invoice["status"] == 'paid'){
                    $color='green';
                }else{
                    $color='red';
                }
                $DueDate = array(
                    "DueDate" => '<span class="inv-date" style="color:'.$color.'"  >
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg> ' . $newDate . ' 
        </span>'
                );
            } else {
                if($invoice["status"] == 'paid'){
                    $color='green';
                }else{
                    $color='black';
                }
                // If the due date is not in the past, display it in the default color
                $DueDate = array(
                    "DueDate" => '<span class="inv-date" style="color:'.$color.'">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg> ' . $newDate . ' 
        </span>'
                );
            }
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

            if ($document_hub['document_type'] == "interviews") {
                $statusDocView = "<span class='badge badge-light-success'>Interviews</span>";
                $template_request = $h->table('template_request')->select()->where('doc_hub_id', '=', $document_hub['id'])->fetchAll();
                if($template_request[0]['status'] == 'completed'){
                    $DocDetailsView = '<a href="/user/template/display-data/'.$document_hub['client_id'].'/'.$template_request[0]['template_id'].'" target="_blank" class="btn-sm btn btn-light-info text-start me-2 action-edit" ><i style="font-size: 16px;" class="fa-solid fa-eye"></i></a>';
                }else{
                    $DocDetailsView = '<a class="btn-sm btn btn-light-info text-start me-2 action-edit" ><i style="font-size: 16px;" class="fa-solid fa-eye"></i></a>';
                }
            } else if ($document_hub['document_type'] == "resources") {
                $statusDocView = "<span class='badge badge-light-info'>Resources</span>";
                $DocDetailsView = '<a href="/user/dochubdetails/'.$document_hub['id'].'" target="_blank" class="btn-sm btn btn-light-info text-start me-2 action-edit" ><i style="font-size: 16px;" class="fa-solid fa-eye"></i></a>';
            } else {
                $statusDocView = "<span class='badge badge-light-success'>Interviews</span>";
                $template_request = $h->table('template_request')->select()->where('doc_hub_id', '=', $document_hub['id'])->fetchAll();
                if($template_request[0]['status'] == 'completed'){
                    $DocDetailsView = '<a href="/user/template/display-data/'.$document_hub['client_id'].'/'.$template_request[0]['template_id'].'" target="_blank" class="btn-sm btn btn-light-info text-start me-2 action-edit" ><i style="font-size: 16px;" class="fa-solid fa-eye"></i></a>';
                }else{
                    $DocDetailsView = '<a class="btn-sm btn btn-light-info text-start me-2 action-edit" ><i style="font-size: 16px;" class="fa-solid fa-eye"></i></a>';
                }
            }
            $action = array('action' =>$DocDetailsView.'<a href="javascript:;" class="btn-sm btn btn-light-danger text-start me-2 action-edit" onclick="deleteUser('.$document_hub["id"].')" ><i style="font-size: 16px;" class="fa-regular fa-trash-can"></i></a>');
            if (!empty($document_hub['client_des']) && $document_hub['client_des'] != ''){
                $words = explode(' ', $document_hub['client_des']);
                $des = implode(' ', array_slice($words, 0, 10));
                $ClientDes = array('ClientDes' =>'<p>' . $des . '... <a href="#" class="see-more text-danger" data-description="' . htmlspecialchars($document_hub['client_des'], ENT_QUOTES, 'UTF-8') . '">Read More</a></p>');

            }else{
                $ClientDes = array('ClientDes' =>'---');
            }

            $status = array("statusView" => $statusView);
            $statusDoc = array("statusDocView" => $statusDocView);
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
            $check_arr[] = array_merge($ids, $document_hub,$userView,$ClientDes, $status,$statusDoc, $action);
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

            $action = array(
                'action' => '<a href="javascript:;" class="btn-sm btn btn-light-danger text-start me-2 action-edit" onclick="deleteUser(' . htmlspecialchars($firm_upload_file["id"], ENT_QUOTES, 'UTF-8') . ')"><i style="font-size: 16px;" class="fa-regular fa-trash-can"></i></a>' .
                    '<a href="#" onclick="downloadFile(\'' . htmlspecialchars($env['APP_URL'] . $firm_upload_file['file'], ENT_QUOTES, 'UTF-8') . '\')" class="btn-sm btn btn-light-info text-start me-2"><i style="font-size: 16px;" class="fa-solid fa-download"></i></a>'
            );
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
            if (!empty($document_hub['firm_des']) && $document_hub['firm_des'] != NULL){
                $words = explode(' ', $document_hub['firm_des']);
                $des = implode(' ', array_slice($words, 0, 40));
                $firmDes = '<p><a role="button" onclick="userStatus('.$document_hub['id'].')"  class=" text-muted"><span class="text-muted fw-bold fs-6">' . $des . '....</span>Read More</a></p>';
            }else{
                $firmDes = '<p>---</p>';
            }
            $UserFirmDetails = $h->table('users')->select()->where('id', '=', $document_hub['firm_id'])->orderBy('id', 'desc')->fetchAll();
            if($document_hub['status'] == 'yes'){

                $StatusView = '<span class="badge badge-light-success my-1">Uploaded</span>';
            }else{
                $StatusView = '<span class="badge badge-light-danger my-1">Not Uploaded</span>';
            }
            if ($document_hub['document_type'] == "interviews") {
                $statusDocView = "<span class='' style='margin-left: 5px; font-weight: bold'>Interview</span>";
            } else if ($document_hub['document_type'] == "resources") {
                $statusDocView = "<span class='' style='margin-left: 5px; font-weight: bold'>Resource Document</span>";
            } else {
                $statusDocView = "<span class='' style='margin-left: 5px; font-weight: bold'>Interview</span>";
            }
            if($document_hub['see_doc'] == '0'){
                $SeeDoc = '<span class="svg-icon svg-icon-2x me-5 ms-n1 mt-2 svg-icon-warning">
                                                              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
																	<path opacity="0.3" d="M19 22H5C4.4 22 4 21.6 4 21V3C4 2.4 4.4 2 5 2H14L20 8V21C20 21.6 19.6 22 19 22ZM16 13.5L12.5 13V10C12.5 9.4 12.6 9.5 12 9.5C11.4 9.5 11.5 9.4 11.5 10L11 13L8 13.5C7.4 13.5 7 13.4 7 14C7 14.6 7.4 14.5 8 14.5H11V18C11 18.6 11.4 19 12 19C12.6 19 12.5 18.6 12.5 18V14.5L16 14C16.6 14 17 14.6 17 14C17 13.4 16.6 13.5 16 13.5Z" fill="currentColor"></path>
																	<rect x="11" y="19" width="10" height="2" rx="1" transform="rotate(-90 11 19)" fill="currentColor"></rect>
																	<rect x="7" y="13" width="10" height="2" rx="1" fill="currentColor"></rect>
																	<path d="M15 8H20L14 2V7C14 7.6 14.4 8 15 8Z" fill="currentColor"></path>
																</svg></span>';
            }else{
                if($document_hub['status'] == 'yes'){
                    $SeeDoc = '<span class="svg-icon svg-icon-2x me-5 ms-n1 mt-2 svg-icon-success">
																<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
																	<path opacity="0.3" d="M19 22H5C4.4 22 4 21.6 4 21V3C4 2.4 4.4 2 5 2H14L20 8V21C20 21.6 19.6 22 19 22ZM11.7 17.7L16 14C16.4 13.6 16.4 12.9 16 12.5C15.6 12.1 15.4 12.6 15 13L11 16L9 15C8.6 14.6 8.4 14.1 8 14.5C7.6 14.9 8.1 15.6 8.5 16L10.3 17.7C10.5 17.9 10.8 18 11 18C11.2 18 11.5 17.9 11.7 17.7Z" fill="currentColor" />
																	<path d="M10.4343 15.4343L9.25 14.25C8.83579 13.8358 8.16421 13.8358 7.75 14.25C7.33579 14.6642 7.33579 15.3358 7.75 15.75L10.2929 18.2929C10.6834 18.6834 11.3166 18.6834 11.7071 18.2929L16.25 13.75C16.6642 13.3358 16.6642 12.6642 16.25 12.25C15.8358 11.8358 15.1642 11.8358 14.75 12.25L11.5657 15.4343C11.2533 15.7467 10.7467 15.7467 10.4343 15.4343Z" fill="currentColor" />
																	<path d="M15 8H20L14 2V7C14 7.6 14.4 8 15 8Z" fill="currentColor" />
																</svg>
															</span>';
                }else{
                    $SeeDoc = '<span class="svg-icon svg-icon-2x me-5 ms-n1 mt-2 svg-icon-danger">
                                                                   <svg  xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
																	<path opacity="0.3" d="M19 22H5C4.4 22 4 21.6 4 21V3C4 2.4 4.4 2 5 2H14L20 8V21C20 21.6 19.6 22 19 22ZM16 13.5L12.5 13V10C12.5 9.4 12.6 9.5 12 9.5C11.4 9.5 11.5 9.4 11.5 10L11 13L8 13.5C7.4 13.5 7 13.4 7 14C7 14.6 7.4 14.5 8 14.5H11V18C11 18.6 11.4 19 12 19C12.6 19 12.5 18.6 12.5 18V14.5L16 14C16.6 14 17 14.6 17 14C17 13.4 16.6 13.5 16 13.5Z" fill="currentColor"></path>
																	<rect x="11" y="19" width="10" height="2" rx="1" transform="rotate(-90 11 19)" fill="currentColor"></rect>
																	<rect x="7" y="13" width="10" height="2" rx="1" fill="currentColor"></rect>
																	<path d="M15 8H20L14 2V7C14 7.6 14.4 8 15 8Z" fill="currentColor"></path>
																</svg></span>';
                }

            }
            $viewDoc = '
           <div class="d-flex mb-10">
                                      '.$SeeDoc.'
                                        <div class="d-flex flex-column">
                                            <!--begin::Content-->
                                            <div class="d-flex align-items-center mb-2">
                                                <!--begin::Title-->
                                                <a role="button" onclick="userStatus('.$document_hub['id'].')" class="text-dark text-hover-primary fs-4 me-3 fw-bold"><span style="color: #ED141F;">'.$UserFirmDetails[0]['fname'].' '.$UserFirmDetails[0]['lname'].'</span> Request For document.</a>
                                             '.$StatusView.'
                                            </div>
                                             <div class="d-flex align-items-center mb-2">
                                              Document Type is '.$statusDocView.'
                                            </div>
                                            <!--end::Content-->
                                            <!--begin::Text-->
                                            '.$firmDes.'
                                         
                                            <!--end::Text-->
                                        </div>
                                        <!--end::Section-->
                                    </div>
         ';
            $ViewDocs =   array("viewDocs" => "$viewDoc");

            $srNo++;
            $ids = array("ids" => "$srNo");
            $check_arr[] = array_merge($ids, $document_hub,$ViewDocs);
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
if($_GET['page_name']=="DocStatusUpdate") {
    if(isset($_GET['id'])) {
        $id=$_GET['id'];
        $status=$_GET['status'];
        $table_name=$_GET['table_name'];
        $status_table=$_GET['status_table'];
        $document_hubInfo = $h->table('document_hub')->select()->where('id', '=', $id)->fetchAll();
        if ($document_hubInfo[0]['document_type'] == 'interviews'){
            $template = $h->table('templates')->select()->where('id', '=', $document_hubInfo[0]['document_id'])->fetchAll();
            $template_request = $h->table('template_request')->select()->where('doc_hub_id', '=', $id)->fetchAll();
            $templates_slug = $template[0]['slug'];
            $template_request_status = $template_request[0]['status'];
            $document_hub_status = $document_hubInfo[0]['status'];
            $document_type = $document_hubInfo[0]['document_type'];
            $client_id = $document_hubInfo[0]['client_id'];
            $template_id = $template_request[0]['template_id'];
        }else{
            $templates_slug = $id;
            $template_request_status= '';
            $document_hub_status= '';
            $document_type = '';
            $template_id = '';
            $client_id = $document_hubInfo[0]['client_id'];

        }
        try {
            $statusUpdate = $h->table($table_name)
                ->update([$status_table=>$status])
                ->where('id','=',$id)
                ->run();
            echo json_encode(array("statusCode" => "1", "id"=>$templates_slug,"template_request_status"=>$template_request_status,"document_hub_status"=>$document_hub_status, "document_type"=>$document_type, "client_id"=>$client_id, "template_id"=>$template_id));
            exit;
        }catch (PDOException $e) {
            echo 0;
            exit;
        }
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
