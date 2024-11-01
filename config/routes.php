<?php
/////////////////////////
///////ZOTEC FRAMEWORK
//////admin@zotecsoft.com
////////////////////////
require_once("./config/main.php");
//PUBLIC Pages
get('/', 'controller/public/HomeController.php');

get('/admin', 'controller/auth/AuthController.php');
post('/admin', 'controller/auth/AuthController.php');

get('/login', 'controller/auth/AuthController.php');
post('/login', 'controller/auth/AuthController.php');
get('/login/$path', 'controller/auth/AuthController.php');
post('/login/$path', 'controller/auth/AuthController.php');
get('/register', 'controller/auth/AuthController.php');
post('/register', 'controller/auth/AuthController.php');

get('/join/$firm_id/$associates_id/$email/$invite', 'controller/auth/AuthController.php');
post('/join/$firm_id/$associates_id/$email/$invite', 'controller/auth/AuthController.php');
get('/forget-password', 'controller/auth/AuthController.php');
post('/forget-password', 'controller/auth/AuthController.php');
get('/invoice/view/$invoice_id', 'controller/users/InvoiceController.php');
post('/stripe/pay-invoice', 'controller/users/InvoiceController.php');
// Jitsi Related Routes
get('/meet/$room_id', 'controller/users/MeetingController.php');

if(@$_SESSION['loginemail'] != "" && @$_SESSION['loginpassword'] != ""):
    get('/2fa/login', 'controller/auth/AuthController.php');
    post('/2fa/login', 'controller/auth/AuthController.php');
endif;
if(@$_SESSION['reset'] != ""):
    get('/reset/login', 'controller/auth/AuthController.php');
    post('/reset/login', 'controller/auth/AuthController.php');
endif;

//PUBLIC API
//get('/api/buy', 'controller/public/PublicApi.php');
//get('/api/latest-property', 'controller/public/PublicApi.php');
//get('/api/nav_cities', 'controller/public/PublicApi.php');
//get('/api/relatedListings', 'controller/public/PublicApi.php');
//get('/api/galleryImages', 'controller/public/PublicApi.php');
//get('/api/schedule-tour', 'controller/public/PublicApi.php');
//USER DASHBOARD
if(@$_SESSION['users']['type'] == 'firm' || @$_SESSION['users']['type'] == 'client' || @$_SESSION['users']['type'] == 'member'):
if (!empty(@$plan_id) && @$plan_id != '' && $current_date <= @$plan_end_date){
    if (in_array('Appointment_Scheduling', $permissionValues)) {
        if (!empty($_SESSION['member_id'])){
            if (in_array('Appointment_Scheduling', $permissionFirmValues)) {
                get('/user/appointment/cronjob', 'controller/users/AppointmentCronjobController.php');
                post('/user/appointment/cronjob', 'controller/users/AppointmentCronjobController.php');
                get('/user/appointments', 'controller/users/AppointmentsController.php');
                get('/user/get_appointment', 'controller/users/AppointmentsController.php');
                get('/user/get_users', 'controller/users/AppointmentsController.php');
                post('/user/add/appointments', 'controller/users/AppointmentsController.php');
                post('/user/update/appointments', 'controller/users/AppointmentsController.php');
                get('/user/add-to-calendar', 'controller/users/AppointmentsController.php');
            }
        }else{
            get('/user/appointment/cronjob', 'controller/users/AppointmentCronjobController.php');
            post('/user/appointment/cronjob', 'controller/users/AppointmentCronjobController.php');
            get('/user/appointments', 'controller/users/AppointmentsController.php');
            get('/user/get_appointment', 'controller/users/AppointmentsController.php');
            get('/user/get_users', 'controller/users/AppointmentsController.php');
            post('/user/add/appointments', 'controller/users/AppointmentsController.php');
            post('/user/update/appointments', 'controller/users/AppointmentsController.php');
            get('/user/add-to-calendar', 'controller/users/AppointmentsController.php');
        }
        }
    if (in_array('Secured_Document_Hub', $permissionValues)) {
        if (!empty($_SESSION['member_id'])){
            if (in_array('Secured_Document_Hub', $permissionFirmValues)) {
                if (@$_SESSION['users']['type'] == 'client'):
                    get('/client/document', 'controller/users/DocumentController.php');
                    post('/client/document/add', 'controller/users/DocumentController.php');
                    get('/client/dochubdetails/$id', 'controller/users/DocumentController.php');
                    post('/client/dochubdetails/$id', 'controller/users/DocumentController.php');
                endif;
                post('/client/download/document', 'controller/users/DocumentController.php');
                get('/user/document', 'controller/users/DocumentController.php');
                get('/user/dochubdetails/$id', 'controller/users/DocumentController.php');
                post('/user/dochubdetails/$id', 'controller/users/DocumentController.php');
                post('/user/request_for_document', 'controller/users/DocumentController.php');
                get('/user/upload/document/all', 'controller/users/DocumentController.php');
                post('/user/upload/document/all', 'controller/users/DocumentController.php');
            }
            }else{
            if (@$_SESSION['users']['type'] == 'client'):
                get('/client/document', 'controller/users/DocumentController.php');
                post('/client/document/add', 'controller/users/DocumentController.php');
                get('/client/dochubdetails/$id', 'controller/users/DocumentController.php');
                post('/client/dochubdetails/$id', 'controller/users/DocumentController.php');
            endif;
            post('/client/download/document', 'controller/users/DocumentController.php');
            get('/user/document', 'controller/users/DocumentController.php');
            get('/user/dochubdetails/$id', 'controller/users/DocumentController.php');
            post('/user/dochubdetails/$id', 'controller/users/DocumentController.php');
            post('/user/request_for_document', 'controller/users/DocumentController.php');
            get('/user/upload/document/all', 'controller/users/DocumentController.php');
            post('/user/upload/document/all', 'controller/users/DocumentController.php');
    }
}
    if (in_array('SMS_Text', $permissionValues)) {
        if (!empty($_SESSION['member_id'])){
            if (in_array('SMS_Text', $permissionFirmValues)) {
            get('/user/chat', 'controller/users/ChatController.php');
            get('/user/chat/$user_id', 'controller/users/ChatController.php');
            get('/user/group/$groupId', 'controller/users/GroupChatController.php');
            get('/group/del/$groupId', 'controller/users/GroupChatController.php');
            post('/group/delete/$group_id/$member_id', 'controller/users/GroupChatController.php');
            post('/group/add-members', 'controller/users/GroupChatController.php');
            get('/chat/messages/group/$groupId', 'controller/users/ChatController.php');
            get('/chat/users', 'controller/users/ChatController.php');
            get('/chat/messages/$userId', 'controller/users/ChatController.php');
            post('/chat/upload-file', 'controller/users/ChatController.php');
            post('/chat/send-group-message', 'controller/users/ChatController.php');
            post('/group/create', 'controller/users/ChatController.php');
            get('/call/ring/$userId', 'controller/users/ChatController.php');
            get('/video-call/ring/$userId', 'controller/users/ChatController.php');
            get('/video-call/$room_id', 'controller/users/ChatController.php');
            post('/call/check', 'controller/users/ChatController.php');
            post('/call/status', 'controller/users/ChatController.php');
            post('/call/hangup/$room_id', 'controller/users/ChatController.php');
    }
        }else{
            get('/user/chat', 'controller/users/ChatController.php');
            get('/user/chat/$user_id', 'controller/users/ChatController.php');
            get('/user/group/$groupId', 'controller/users/GroupChatController.php');
            get('/group/del/$groupId', 'controller/users/GroupChatController.php');
            post('/group/delete/$group_id/$member_id', 'controller/users/GroupChatController.php');
            post('/group/add-members', 'controller/users/GroupChatController.php');
            get('/chat/messages/group/$groupId', 'controller/users/ChatController.php');
            get('/chat/users', 'controller/users/ChatController.php');
            get('/chat/messages/$userId', 'controller/users/ChatController.php');
            post('/chat/upload-file', 'controller/users/ChatController.php');
            post('/chat/send-group-message', 'controller/users/ChatController.php');
            post('/group/create', 'controller/users/ChatController.php');
            get('/call/ring/$userId', 'controller/users/ChatController.php');
            get('/video-call/ring/$userId', 'controller/users/ChatController.php');
            get('/video-call/$room_id', 'controller/users/ChatController.php');
            post('/call/check', 'controller/users/ChatController.php');
            post('/call/status', 'controller/users/ChatController.php');
            post('/call/hangup/$room_id', 'controller/users/ChatController.php');
        }
        }
    if (in_array('File_Manager', $permissionValues)) {
        if (!empty($_SESSION['member_id'])) {
            if (in_array('File_Manager', $permissionFirmValues)) {
                get('/user/file', 'controller/users/FilemanagerController.php');
                get('/file', 'controller/users/FilemanagerController.php');
                get('/user/files', 'controller/users/FilemanagerController.php');
                post('/user/file/del', 'controller/users/FilemanagerController.php');
                post('/user/file/del-all', 'controller/users/FilemanagerController.php');
                post('/user/file/upload', 'controller/users/FilemanagerController.php');
                get('/file-manager', 'controller/users/FilemanagerController.php');
            }
        } else {
            get('/user/file', 'controller/users/FilemanagerController.php');
            get('/file', 'controller/users/FilemanagerController.php');
            get('/user/files', 'controller/users/FilemanagerController.php');
            post('/user/file/del', 'controller/users/FilemanagerController.php');
            post('/user/file/del-all', 'controller/users/FilemanagerController.php');
            post('/user/file/upload', 'controller/users/FilemanagerController.php');
            get('/file-manager', 'controller/users/FilemanagerController.php');
        }
    }
    if (in_array('Invoice_and_Payments', $permissionValues)) {
        if (!empty($_SESSION['member_id'])) {
            if (in_array('Invoice_and_Payments', $permissionFirmValues)) {
                get('/user/invoices', 'controller/users/InvoiceController.php');
                get('/user/invoice/add', 'controller/users/InvoiceController.php');
                post('/user/invoice/add', 'controller/users/InvoiceController.php');
                get('/user/invoice/update/$id', 'controller/users/InvoiceController.php');
                post('/user/invoice/update', 'controller/users/InvoiceController.php');
                post('/user/get_client_invoice', 'controller/users/InvoiceController.php');
                get('/user/invoice/view/$invoice_id', 'controller/users/InvoiceController.php');
            }
        } else {
            get('/user/invoices', 'controller/users/InvoiceController.php');
            get('/user/invoice/add', 'controller/users/InvoiceController.php');
            post('/user/invoice/add', 'controller/users/InvoiceController.php');
            get('/user/invoice/update/$id', 'controller/users/InvoiceController.php');
            post('/user/invoice/update', 'controller/users/InvoiceController.php');
            post('/user/get_client_invoice', 'controller/users/InvoiceController.php');
            get('/user/invoice/view/$invoice_id', 'controller/users/InvoiceController.php');
        }
    }
// interviews
  if (in_array('Funnels', $permissionValues)) {
      if (!empty($_SESSION['member_id'])) {
          if (in_array('Funnels', $permissionFirmValues)) {
              get('/user/template/all', 'controller/users/TemplatesController.php');
              get('/user/template/interview-list/$slug', 'controller/users/TemplatesController.php');

              get('/user/interviews/all', 'controller/users/InterviewsController.php');
              get('/user/interviews/questions', 'controller/users/InterviewsController.php');
              post('/user/interviews/questions', 'controller/users/InterviewsController.php');
              get('/user/interviews/questions/update/$sectionId', 'controller/users/InterviewsController.php');
              post('/user/interviews/questions/update/$sectionId', 'controller/users/InterviewsController.php');
// Templates
              get('/user/template/create', 'controller/users/TemplatesController.php');
              get('/user/template/create/$templateId', 'controller/users/TemplatesController.php');
              post('/user/template/create', 'controller/users/TemplatesController.php');
              post('/user/template/get', 'controller/users/TemplatesController.php');
              post('/user/templates/send_request', 'controller/users/TemplatesController.php');
              get('/user/template/view/$slug', 'controller/users/TemplatesController.php');
              post('/user/template/view', 'controller/users/TemplatesController.php');
              get('/user/template/display-data/$userId/$templateId', 'controller/users/TemplatesController.php');
              if (@$_SESSION['users']['type'] == 'client'):
                  get('/client/template/request', 'controller/users/TemplatesController.php');
              endif;
          }
      } else {
          get('/user/template/all', 'controller/users/TemplatesController.php');
          get('/user/template/interview-list/$slug', 'controller/users/TemplatesController.php');

          get('/user/interviews/all', 'controller/users/InterviewsController.php');
          get('/user/interviews/questions', 'controller/users/InterviewsController.php');
          post('/user/interviews/questions', 'controller/users/InterviewsController.php');
          get('/user/interviews/questions/update/$sectionId', 'controller/users/InterviewsController.php');
          post('/user/interviews/questions/update/$sectionId', 'controller/users/InterviewsController.php');
// Templates
          get('/user/template/create', 'controller/users/TemplatesController.php');
          get('/user/template/create/$templateId', 'controller/users/TemplatesController.php');
          post('/user/template/create', 'controller/users/TemplatesController.php');
          post('/user/template/get', 'controller/users/TemplatesController.php');
          post('/user/templates/send_request', 'controller/users/TemplatesController.php');
          get('/user/template/view/$slug', 'controller/users/TemplatesController.php');
          post('/user/template/view', 'controller/users/TemplatesController.php');
          get('/user/template/display-data/$userId/$templateId', 'controller/users/TemplatesController.php');
          if (@$_SESSION['users']['type'] == 'client'):
              get('/client/template/request', 'controller/users/TemplatesController.php');
          endif;
      }
  }
    if (in_array('Associate_Accounts', $permissionValues)) {
                get('/user/clients', 'controller/users/ClientsController.php');
                get('/user/members', 'controller/users/ClientsController.php');
                get('/user/manage/clients', 'controller/users/ClientsController.php');
                post('/user/send_invite', 'controller/users/ClientsController.php');
                post('/user/user_per', 'controller/users/ClientsController.php');
                post('/user/user_edit', 'controller/users/UsersController.php');
                post('/user/get_user', 'controller/users/UsersController.php');
                post('/user/add_user', 'controller/users/UsersController.php');

}
}
    post('/user/member/login_as_client', 'controller/auth/AuthController.php');
    post('/user/member/login_as_member', 'controller/auth/AuthController.php');
    get('/user/api', 'controller/users/UserApi.php');
    get('/api/users/email_inbox', 'controller/users/UserApi.php');
    get('/user/dashboard', 'controller/users/DashboardController.php');
    // plans
    if(@$_SESSION['users']['type'] == 'firm'):
        get('/user/plans', 'controller/users/PlansController.php');
        get('/user/plansCronjobs', 'controller/users/PlanCronjobsController.php');
        post('/user/plan/get_plan', 'controller/users/PlansController.php');
        get('/user/plans_details/$slug', 'controller/users/PlansController.php');
        post('/user/plan/checkout', 'controller/users/PlansController.php');

    endif;
    get('/user/campaign/list', 'controller/users/CampaignController.php');
    post('/user/campaign/list', 'controller/users/CampaignController.php');
    get('/user/campaign/list_details/$id', 'controller/users/CampaignController.php');
    get('/user/campaign/start_campaign', 'controller/users/CampaignController.php');
    post('/user/campaign/start_campaign', 'controller/users/CampaignController.php');
    post('/user/get_campaign_list', 'controller/users/CampaignController.php');
    post('/user/get_campaign_list_details', 'controller/users/CampaignController.php');
    post('/user/campaign/list_detail/add', 'controller/users/CampaignController.php');
    get('/user/campaign/CampaignCronjobController', 'controller/users/CampaignCronjobController.php');
    post('/user/campaign/CampaignCronjobController', 'controller/users/CampaignCronjobController.php');

    // email
    get('/user/email/inbox', 'controller/users/EmailController.php');
    get('/user/email/sent', 'controller/users/EmailController.php');
    get('/user/email/trash', 'controller/users/EmailController.php');
    get('/user/email/compose', 'controller/users/EmailController.php');
    post('/user/email/compose', 'controller/users/EmailController.php');
    post('/user/email/get_users', 'controller/users/EmailController.php');
    get('/user/email/upload_attachment', 'controller/users/EmailController.php');
    post('/user/email/upload_attachment', 'controller/users/EmailController.php');
    post('/user/email/sentToTrash', 'controller/users/EmailController.php');
    post('/user/email/deleteEmailFromTrash', 'controller/users/EmailController.php');
    post('/user/email/deleteEmailFromTrashBulk', 'controller/users/EmailController.php');
    post('/user/email/sentToTrashBulk', 'controller/users/EmailController.php');
    get('/user/email/email_reply/$folder/$email_id', 'controller/users/EmailController.php');
    get('/user/email/compose/$email', 'controller/users/EmailController.php');
    post('/user/email/attachment', 'controller/users/EmailController.php');
// profile
    get('/user/profile', 'controller/users/ProfileController.php');
    get('/user/firm-info', 'controller/users/ProfileController.php');
    post('/user/firm-info', 'controller/users/ProfileController.php');
    get('/user/profile/settings', 'controller/users/ProfileController.php');
    get('/user/profile/security', 'controller/users/ProfileController.php');
    get('/user/profile/paymentMethod', 'controller/users/ProfileController.php');
    get('/user/profile/billing', 'controller/users/ProfileController.php');
    post('/user/profile', 'controller/users/ProfileController.php');
    post('/user/fetch_profile', 'controller/users/ProfileController.php');
    post('/user/bank/profile', 'controller/users/ProfileController.php');
    post('/user/profile/password_change', 'controller/users/ProfileController.php');
    post('/user/add_billing_address', 'controller/users/ProfileController.php');
    post('/user/get_billing_address', 'controller/users/ProfileController.php');
    post('/user/update_billing_address', 'controller/users/ProfileController.php');
    post('/user/add_payment_method', 'controller/users/ProfileController.php');
    post('/user/get_payment_method', 'controller/users/ProfileController.php');
    post('/user/update_payment_method', 'controller/users/ProfileController.php');
    post('/user/profile/change_stripe_keys', 'controller/users/ProfileController.php');
    get('/user/email-template/add', 'controller/users/EmailTemplateController.php');
    post('/user/email-template/add', 'controller/users/EmailTemplateController.php');
    get('/user/email-template/edit/$id', 'controller/users/EmailTemplateController.php');
    post('/user/email-template/edit/$id', 'controller/users/EmailTemplateController.php');
    get('/user/email-template/all', 'controller/users/EmailTemplateController.php');
    post('/user/email-template/all', 'controller/users/EmailTemplateController.php');
    post('/user/get_email_template', 'controller/users/EmailTemplateController.php');
    get('/get-template', 'controller/users/EmailTemplateController.php');
// user



endif;
//END OF USER DASHBOARD


//ADMIN DASHBOARD
if(@$_SESSION['users']['type'] == 'admin'):
get('/admin/api', 'controller/admin/Api.php');
get('/admin/dashboard', 'controller/admin/DashboardController.php');
//users
get('/admin/add_user', 'controller/admin/UsersController.php');
post('/admin/add_user', 'controller/admin/UsersController.php');
get('/admin/users/firms', 'controller/admin/UsersController.php');
get('/admin/users/members', 'controller/admin/UsersController.php');
get('/admin/users/clients', 'controller/admin/UsersController.php');
post('/admin/get_user', 'controller/admin/UsersController.php');
post('/admin/user_edit', 'controller/admin/UsersController.php');
get('/admin/site_settings', 'controller/admin/SiteSettingsController.php');
post('/admin/site_settings', 'controller/admin/SiteSettingsController.php');


get('/admin/transactions', 'controller/admin/TransactionsController.php');
get('/admin/plans', 'controller/admin/PlanController.php');
get('/admin/add_plan', 'controller/admin/PlanController.php');
post('/admin/add_plan', 'controller/admin/PlanController.php');
post('/admin/get_plan', 'controller/admin/PlanController.php');
post('/admin/plan_edit', 'controller/admin/PlanController.php');

get('/admin/profile', 'controller/admin/ProfileController.php');
post('/admin/fetch_profile', 'controller/admin/ProfileController.php');
post('/admin/profile', 'controller/admin/ProfileController.php');
    get('/admin/profile/security', 'controller/admin/ProfileController.php');
    post('/admin/profile/password_change', 'controller/admin/ProfileController.php');
    get('/admin/profile/paymentMethod', 'controller/admin/ProfileController.php');
    post('/admin/profile/change_stripe_keys', 'controller/admin/ProfileController.php');


//get('/admin/json', 'controller/admin/JsonFileController.php');
//post('/admin/import', 'controller/admin/ListingsController.php');
//post('/admin/check-mlnum', 'controller/admin/ListingsController.php');
//get('/admin/listing/add', 'controller/admin/ListingsController.php');
//get('/admin/listings', 'controller/admin/ListingsController.php');
//get('/admin/listing/category', 'controller/admin/ListingsController.php');
//ADMIN DASHBOARD END

//ADMIN API
//get('/admin/api/category', 'controller/admin/PublicApi.php');

//END OF ADMIN API
endif;

get('/logout', 'controller/LogoutController.php');
//404 PAGE
any('/404','controller/public/ErrorController.php');
