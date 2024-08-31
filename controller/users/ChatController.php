<?php
require("config/env.php");
use Carbon\Carbon;
//chat
if($route == '/user/chat'){
    $seo = array(
        'title' => 'Messaging',
        'description' => 'CRM',
        'keywords' => 'Admin Panel'
    );

    if ($loginUserType == 'firm') {
        $usersList = $h->table('users')
            ->select('users.id', 'users.fname', 'users.lname', 'users.email', 'users.type', 'users.profile_image')
            ->where('users.firm_id', '=', $loginUserId)
            ->fetchAll();
    } else if ($loginUserType == 'client') {
        $userFirmId = $h->table('users')
            ->select('firm_id')
            ->where('users.id', '=', $loginUserId)
            ->fetchAll();
        $usersList = $h->table('users')
            ->select('users.id', 'users.fname', 'users.lname', 'users.email', 'users.type', 'users.profile_image')
            ->where('users.type','firm')
            ->Where('users.id', '=', $userFirmId[0]['firm_id'])
            ->fetchAll();
    }
    $groups = $h->table('group_members')
        ->select('groups.id AS group_id', 'groups.name AS group_name', 'groups.created_by')
        ->leftJoin('groups')->on('groups.id', 'group_members.group_id')
        ->where(function($query) use ($loginUserId) {
            $query->where('group_members.user_id', '=', $loginUserId)
                ->orWhere('groups.created_by', '=', $loginUserId);
        })
        ->fetchAll();

    foreach ($groups as &$group) {
        $groupMembers = $h->table('group_members')
            ->select('users.id', 'users.fname', 'users.lname', 'users.email', 'users.profile_image')
            ->leftJoin('users')->on('users.id', 'group_members.user_id')
            ->where('group_members.group_id', '=', $group['group_id'])
            ->fetchAll();

        $group['members'] = $groupMembers;
    }
    $groupIdsAdded = [];
    foreach ($groups as $group) {
        if (!in_array($group['group_id'], $groupIdsAdded)) {
            $usersList[] = [
                'id' => $group['group_id'],
                'fname' => $group['group_name'],
                'lname' => 'Group',
                'email' => null,
                'type' => 'group',
                'profile_image' => null,
                'members' => $group['members']
            ];
            $groupIdsAdded[] = $group['group_id'];
        }
    }

    echo $twig->render('user/chat/chat.twig', ['seo' => $seo, 'chatUsers'=>$usersList]);
}
if ($route === '/user/chat/$user_id') {

    if ($loginUserType == 'firm') {
        $usersList = $h->table('users')
            ->select('users.id', 'users.fname', 'users.lname', 'users.email', 'users.type', 'users.profile_image')
            ->where('users.firm_id', '=', $loginUserId)
            ->fetchAll();
    } else if ($loginUserType == 'client') {
        $userFirmId = $h->table('users')
            ->select('firm_id')
            ->where('users.id', '=', $loginUserId)
            ->fetchAll();
        $usersList = $h->table('users')
            ->select('users.id', 'users.fname', 'users.lname', 'users.email', 'users.type', 'users.profile_image')
            ->where('users.type','firm')
            ->Where('users.id', '=', $userFirmId[0]['firm_id'])
            ->fetchAll();
    }
    $groups = $h->table('group_members')
        ->select('groups.id AS group_id', 'groups.name AS group_name', 'groups.created_by')
        ->leftJoin('groups')->on('groups.id', 'group_members.group_id')
        ->where(function($query) use ($loginUserId) {
            $query->where('group_members.user_id', '=', $loginUserId)
                ->orWhere('groups.created_by', '=', $loginUserId);
        })
        ->fetchAll();
    foreach ($groups as &$group) {
        $groupMembers = $h->table('group_members')
            ->select('users.id', 'users.fname', 'users.lname', 'users.email', 'users.profile_image')
            ->leftJoin('users')->on('users.id', 'group_members.user_id')
            ->where('group_members.group_id', '=', $group['group_id'])
            ->fetchAll();

        $group['members'] = $groupMembers;
    }
    $groupIdsAdded = [];
    foreach ($groups as $group) {
        if (!in_array($group['group_id'], $groupIdsAdded)) {
            $usersList[] = [
                'id' => $group['group_id'],
                'fname' => $group['group_name'],
                'lname' => 'Group',
                'email' => null,
                'type' => 'group',
                'profile_image' => null,
                'members' => $group['members']
            ];
            $groupIdsAdded[] = $group['group_id'];
        }
    }
    $chatWithUserInfo = $h->table('users')
        ->select('users.id', 'users.fname', 'users.lname', 'users.email', 'users.type', 'users.profile_image')
        ->where('users.id', '=', $user_id)
        ->fetchAll();
    $chatWithUserInfo = $chatWithUserInfo[0];

    $seo = array(
        'title' => 'Chat with ' . $chatWithUserInfo['fname'] . " " . $chatWithUserInfo['lname'],
        'description' => 'CRM',
        'keywords' => 'Admin Panel'
    );

    echo $twig->render('user/chat/user_chat.twig', [
        'seo' => $seo,
        'chatUsers' => $usersList,
        'chatWithUserInfo' => $chatWithUserInfo,
        'user_id' => $user_id
    ]);
}




if($route == '/chat/users'){
   echo getChatUsers($loginUserId);
}
if ($route == '/chat/messages/$userId') {
    function getUserChat($sender_id, $receiver_id = null, $group_id = null) {
        global $h; // Access Cycle ORM database handler
        global $env;
        try {
            // Get the underlying driver (e.g., MySQL driver)
            $driver = $h->getDriver();

            // Base SQL query
            $sql = "
            SELECT
                chat.message,
                chat.created_at,
                sender.fname AS sender_fname,
                sender.lname AS sender_lname,
                sender.profile_image AS sender_profile_image,
                receiver.fname AS receiver_fname,
                receiver.lname AS receiver_lname,
                receiver.profile_image AS receiver_profile_image,
                chat.sender_id,
                chat.receiver_id,
                chat.group_id
            FROM
                chat
            JOIN
                users AS sender ON chat.sender_id = sender.id
            LEFT JOIN
                users AS receiver ON chat.receiver_id = receiver.id
            WHERE
        ";

            $params = [];

            // Adjust the query depending on whether it's an individual or group chat
            if ($group_id) {
                $sql .= " chat.group_id = ? ";
                $params[] = $group_id;
            } else {
                $sql .= " (chat.sender_id = ? AND chat.receiver_id = ?) OR (chat.sender_id = ? AND chat.receiver_id = ?) ";
                $params[] = $sender_id;
                $params[] = $receiver_id;
                $params[] = $receiver_id;
                $params[] = $sender_id;
            }

            $sql .= " ORDER BY chat.created_at ASC";

            // Execute the query with parameters
            $result = $driver->query($sql, $params);

            // Fetch all results as associative arrays
            $usersList = $result->fetchAll();

            $messages = [];
            foreach ($usersList as $message) {
                $senderClass = ($message['sender_id'] == $sender_id) ? 'me' : 'you';

                // Check if sender profile image is null, then generate the avatar URL
                $senderProfileImage = $message['sender_profile_image']
                    ? $env['APP_URL']."uploads/profile/".$message['sender_profile_image']
                    : "https://avatar.iran.liara.run/username?username=" . urlencode($message['sender_fname'] . ' ' . $message['sender_lname']);

                // Check if receiver profile image is null, then generate the avatar URL
                $receiverProfileImage = $message['receiver_profile_image']
                    ? $env['APP_URL']."uploads/profile/".$message['receiver_profile_image']
                    : "https://avatar.iran.liara.run/username?username=" . urlencode($message['receiver_fname'] . ' ' . $message['receiver_lname']);

                $messages[] = [
                    'sender' => $senderClass,
                    'message' => htmlspecialchars($message['message']),
                    'timestamp' => $message['created_at'],
                    'senderName' => ($senderClass == 'me') ? 'You' : $message['sender_fname'] . ' ' . $message['sender_lname'],
                    'senderProfileImage' => $senderProfileImage,
                    'receiverProfileImage' => $receiverProfileImage
                ];
            }

            // Return the JSON-encoded array of messages
            return json_encode([
                'messages' => $messages
            ]);

        } catch (\Cycle\Database\Exception\DriverException $e) {
            // Catch and handle database driver errors
            return json_encode([
                'error' => 'Driver Exception: ' . $e->getMessage()
            ]);
        } catch (\Exception $e) {
            // Catch any other exceptions
            return json_encode([
                'error' => 'An unexpected error occurred: ' . $e->getMessage()
            ]);
        }
    }

// Output JSON response
    header('Content-Type: application/json');
    echo getUserChat($loginUserId, $userId);
}
if($route == '/call/ring/$userId'){

    $user = $h->table('users')
        ->select('id','fname','lname', 'email', 'type')
        ->where('id', '=', $userId)
        ->fetchAll();
    $fullname= $user[0]['fname'].' '.$user[0]['lname'];
    echo $twig->render('user/chat/audio-call_ringing.twig', ['fullname'=>$fullname, 'userId'=>$userId]);
}
if($route == '/video-call/ring/$userId'){

    $user = $h->table('users')
        ->select('id','fname','lname', 'email', 'type', 'profile_image')
        ->where('id', '=', $userId)
        ->fetchAll();
    $fullname= $user[0]['fname'].' '.$user[0]['lname'];
    $caller_pickup_id= $user[0]['id'];
    $caller_start_id= $loginUserId;

    if(!empty($user[0]['profile_image'])){
        $profile_image= $env['APP_URL'].'uploads/profile/'.$user[0]['profile_image'];
    }else{
        $profile_image= "https://avatar.iran.liara.run/username?username=".$fullname;
    }

    $call = $h->table('calls')
        ->select()
        ->where('caller_start_id', '=', $caller_start_id)
        ->where('reciever_pickup_id','=',$caller_pickup_id)
        ->where('status','!=', 'cancel')
        ->where('status','!=', 'hangup');


    if($call->count() > 0){
        //UPdate scnerio
        $previousData=$call->fetchAll();
        foreach ($previousData as $data){
            $updateCall = $h->update('calls')
                ->values([
                    'status' => 'cancel',
                ])->where('id','=',$data['id'])->run();
        }
    }

        $channel_id=random_strings(14);
        $insert = $h->insert('calls')
            ->values([
                'caller_start_id' => $caller_start_id,
                'reciever_pickup_id' => $caller_pickup_id,
                'type' => 'video',
                'channel_id' => $channel_id,
                'status' => 'ringing',
            ])->run();

    echo $twig->render('user/chat/video-call_ringing.twig', ['fullname'=>$fullname, 'pickup_id'=>$userId, 'channel_id'=>$channel_id, 'profile_image'=>$profile_image]);
}
if($route == '/call/status'){
    if(isset($_POST['channel_id'])){
        $call = $h->table('calls')
            ->select()
            ->where('channel_id','=',$_POST['channel_id'] );
        header('Content-Type: application/json');
        if($call->count() > 0){
            $data=$call->fetchAll();
            echo json_encode(array('status'=>$data[0]['status'], 'call_link'=>'/video-call/'.$data[0]['channel_id']));
        }else{
            echo json_encode(array('status'=>$data[0]['status']));
        }
    }
}
if($route == '/video-call/$room_id'){
    if(!empty($room_id)){

        $updateCall = $h->update('calls')
            ->values([
                'status' => 'pickup',
            ])->where('channel_id','=',$room_id)->run();

        $call = $h->table('calls')
            ->select()
            ->where('channel_id','=',$room_id)
            ->where('status','=', 'pickup');
        $data=$call->fetchAll();



        if(!empty($userInfo[0]['profile_image'])){
            $profile_image= $env['APP_URL'].'uploads/profile/'.$userInfo[0]['profile_image'];
        }else{
            $profile_image= "https://avatar.iran.liara.run/username?username=".$loginUserName;
        }



        echo $twig->render('user/chat/video-call.twig',[
            'room_id'=>$room_id,
            'pickup_id'=>$data[0]['reciever_pickup_id'],
            'caller_id'=>$data[0]['caller_start_id'],
            'profile_image'=>$profile_image
        ]);
    }
}
if($route == '/call/check'){
        $call = $h->table('calls')
            ->select('calls.*','calls.status as call_status','users.*')
            ->leftJoin('users')->on('users.id', 'calls.caller_start_id')
            ->where('calls.reciever_pickup_id','=',$loginUserId)
            ->where('calls.status','=', 'ringing');
        if($call->count() > 0){
            $data=$call->fetchAll();
            $data=$data[0];
            echo json_encode(array('status'=>$data['call_status'],
                'call_link'=>'/video-call/'.$data['channel_id'],
                'channel_id'=>$data['channel_id'],
                'caller_name'=>$data['fname'].' '.$data['lname']
                ));
        }else
            echo json_encode(array('status'=>NULL));

}
if($route == '/call/hangup/$room_id'){
    $updateCall = $h->update('calls')
        ->values([
            'status' => 'hangup',
        ])->where('channel_id','=',$room_id)->run();
    echo json_encode(array('status'=>true));
}

//GROUP CHAT
if ($route == '/group/create') {
    $groupName = $_POST['name'];
    $memberIds = $_POST['member_ids'];
    $groupId = $h->insert('groups')
        ->values(['name' => $groupName, 'created_by' => $loginUserId])
        ->run();

    foreach ($memberIds as $memberId) {
        $h->insert('group_members')
            ->values(['group_id' => $groupId, 'user_id' => $memberId])
            ->run();
    }

    header('Location: /user/chat');
}
if ($route == '/chat/messages/group/$groupId') {
    function getGroupChatMessages($groupId) {
        global $h; // Access Cycle ORM database handler
        global $env;

        try {
            // Get the underlying driver (e.g., MySQL driver)
            $driver = $h->getDriver();

            // SQL query to fetch group chat messages with sender details
            $sql = "
            SELECT
                chat.message,
                chat.created_at,
                chat.sender_id,
                sender.fname AS sender_fname,
                sender.lname AS sender_lname,
                sender.profile_image AS sender_profile_image
            FROM
                chat
            JOIN
                users AS sender ON chat.sender_id = sender.id
            WHERE
                chat.group_id = ?
            ORDER BY
                chat.created_at ASC
            ";

            // Execute the query with the group ID as the parameter
            $result = $driver->query($sql, [$groupId]);

            // Fetch all results as associative arrays
            $messages = [];
            while ($message = $result->fetch()) {
                // Check if sender profile image is null, then generate the avatar URL
                $senderProfileImage = $message['sender_profile_image']
                    ? $env['APP_URL']."uploads/profile/".$message['sender_profile_image']
                    : "https://avatar.iran.liara.run/username?username=" . urlencode($message['sender_fname'] . ' ' . $message['sender_lname']);

                $messages[] = [
                    'sender' => 'you', // In group chat, 'you' can be adjusted as per requirement
                    'message' => htmlspecialchars($message['message']),
                    'timestamp' => $message['created_at'],
                    'senderName' => $message['sender_fname'] . ' ' . $message['sender_lname'],
                    'senderProfileImage' => $senderProfileImage
                ];
            }

            // Return the JSON-encoded array of messages
            return json_encode([
                'messages' => $messages
            ]);

        } catch (\Cycle\Database\Exception\DriverException $e) {
            // Catch and handle database driver errors
            return json_encode([
                'error' => 'Driver Exception: ' . $e->getMessage()
            ]);
        } catch (\Exception $e) {
            // Catch any other exceptions
            return json_encode([
                'error' => 'An unexpected error occurred: ' . $e->getMessage()
            ]);
        }
    }

    // Output JSON response
    header('Content-Type: application/json');
    echo getGroupChatMessages($groupId);
}

if ($route == '/chat/send-group-message') {
    $message = $_POST['message'];
    $groupId = $_POST['group_id'];

    $h->insert('chat')
        ->values([
            'sender_id' => $loginUserId,
            'group_id' => $groupId,
            'message' => $message
        ])
        ->run();
}
if ($route == '/chat/upload-file') {
    $groupId = $_POST['group_id'] ?? null;
    $receiverId = $_POST['receiver_id'] ?? null;
    $message = $_POST['message'];

    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['file']['tmp_name'];
        $fileName = $_FILES['file']['name'];
        $uploadFileDir = './uploads/chat_files/';
        $destPath = $uploadFileDir . $fileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $h->insert('chat')
                ->values([
                    'sender_id' => $loginUserId,
                    'group_id' => $groupId,
                    'receiver_id' => $receiverId,
                    'message' => $message,
                    'file_path' => $destPath
                ])
                ->run();
        }
    }

    header('Location: /user/chat');
}

