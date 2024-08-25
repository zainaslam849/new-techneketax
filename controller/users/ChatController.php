<?php
require("config/env.php");
use Carbon\Carbon;

if($route == '/user/chat'){
    $seo = array(
        'title' => 'Messaging',
        'description' => 'CRM',
        'keywords' => 'Admin Panel'
    );
//print_r(getChatU
//sers($loginUserId));
    if($loginUserType == 'firm'){
        $usersList = $h->table('users')
            ->select('id','fname','lname', 'email', 'type','profile_image')
            ->where('firm_id', '=', $loginUserId)
            ->fetchAll();
    }else if($loginUserType == 'client'){
        $userFirmId = $h->table('users')
            ->select('firm_id')
            ->where('id', '=', $loginUserId)
            ->fetchAll();
        $usersList = $h->table('users')
            ->select('id','fname','lname', 'email', 'type', 'profile_image')
            ->where('id', '=', $userFirmId[0]['firm_id'])
            ->fetchAll();
    }

    echo $twig->render('user/chat/chat.twig', ['seo' => $seo, 'chatUsers'=>$usersList]);
}
if($route === '/user/chat/$user_id'){

//print_r(getChatU
//sers($loginUserId));
    if($loginUserType == 'firm'){
        $usersList = $h->table('users')
            ->select('id','fname','lname', 'email', 'type', 'profile_image')
            ->where('firm_id', '=', $loginUserId)
            ->fetchAll();
    }else if($loginUserType == 'client'){
        $userFirmId = $h->table('users')
            ->select('firm_id')
            ->where('id', '=', $loginUserId)
            ->fetchAll();
        $usersList = $h->table('users')
            ->select('id','fname','lname', 'email', 'type', 'profile_image')
            ->where('id', '=', $userFirmId[0]['firm_id'])
            ->fetchAll();
    }

    $chatWithUserInfo = $h->table('users')
        ->select('id','fname','lname', 'email', 'type','profile_image')
        ->where('id', '=', $user_id)
        ->fetchAll();
    $chatWithUserInfo= $chatWithUserInfo[0];
    $seo = array(
        'title' => 'Chat with '.$chatWithUserInfo['fname'],
        'description' => 'CRM',
        'keywords' => 'Admin Panel'
    );
    echo $twig->render('user/chat/user_chat.twig', ['seo' => $seo, 'chatUsers'=>$usersList, 'chatWithUserInfo'=>$chatWithUserInfo, 'user_id'=>$user_id]);
}





if($route == '/chat/users'){
   echo getChatUsers($loginUserId);
}
if ($route == '/chat/messages/$userId') {
    function getUserChat($sender_id, $receiver_id) {
        global $h; // Access Cycle ORM database handler
        global $env;
        try {
            // Get the underlying driver (e.g., MySQL driver)
            $driver = $h->getDriver();

            // Prepare a raw SQL query
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
                chat.receiver_id
            FROM
                chat
            JOIN
                users AS sender ON chat.sender_id = sender.id
            JOIN
                users AS receiver ON chat.receiver_id = receiver.id
            WHERE
                (chat.sender_id = ? AND chat.receiver_id = ?)
            OR
                (chat.sender_id = ? AND chat.receiver_id = ?)
            ORDER BY
                chat.created_at ASC
        ";

            // Execute the query with parameters
            $result = $driver->query($sql, [$sender_id, $receiver_id, $receiver_id, $sender_id]);

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
    echo $twig->render('user/chat/audio-call_ringing.twig', ['fullname'=>$fullname]);
}

if($route == '/video-call/ring/$userId'){

    $user = $h->table('users')
        ->select('id','fname','lname', 'email', 'type')
        ->where('id', '=', $userId)
        ->fetchAll();
    $fullname= $user[0]['fname'].' '.$user[0]['lname'];
    $caller_pickup_id= $user[0]['id'];
    $caller_start_id= $loginUserId;

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

    echo $twig->render('user/chat/video-call_ringing.twig', ['fullname'=>$fullname, 'pickup_id'=>$userId, 'channel_id'=>$channel_id]);
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

        echo $twig->render('user/chat/video-call.twig',[
            'room_id'=>$room_id,
            'pickup_id'=>$data[0]['reciever_pickup_id'],
            'caller_id'=>$data[0]['caller_start_id']
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

