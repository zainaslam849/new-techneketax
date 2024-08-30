<?php
require("config/env.php");
use Carbon\Carbon;

if ($route == '/user/group/$groupId' || $route === '/user/chat/$user_id') {
    // Fetch users based on the login type
    if ($loginUserType == 'firm') {
        $usersList = $h->table('users')
            ->select('users.id', 'users.fname', 'users.lname', 'users.email', 'users.type', 'users.profile_image')
            ->where('users.firm_id', '=', $loginUserId)
            ->fetchAll();
    } else if ($loginUserType == 'client') {
        $userFirmIdResult = $h->table('users')
            ->select('firm_id')
            ->where('users.id', '=', $loginUserId)
            ->fetchAll();

        $userFirmId = $userFirmIdResult[0]['firm_id'];

        $usersList = $h->table('users')
            ->select('users.id', 'users.fname', 'users.lname', 'users.email', 'users.type', 'users.profile_image')
            ->where('users.firm_id', '=', $userFirmId)
            ->fetchAll();
    }

    // Fetch groups the user is a member of
    $groups = $h->table('group_members')
        ->select('groups.id AS group_id', 'groups.name AS group_name', 'groups.created_by')
        ->leftJoin('groups')->on('groups.id', 'group_members.group_id')
        ->where(function($query) use ($loginUserId) {
            $query->where('group_members.user_id', '=', $loginUserId)
                ->orWhere('groups.created_by', '=', $loginUserId);
        })
        ->fetchAll();

    // Track added groups to avoid duplicates
    $addedGroupIds = [];

    // Add each group to the usersList only once
    foreach ($groups as $group) {
        if (!in_array($group['group_id'], $addedGroupIds)) {
            $groupMembers = $h->table('group_members')
                ->select('users.id', 'users.fname', 'users.lname', 'users.email', 'users.profile_image')
                ->leftJoin('users')->on('users.id', 'group_members.user_id')
                ->where('group_members.group_id', '=', $group['group_id'])
                ->fetchAll();

            $usersList[] = [
                'id' => $group['group_id'],
                'fname' => $group['group_name'],
                'lname' => 'Group',
                'email' => null,
                'type' => 'group',
                'profile_image' => null,
                'members' => $groupMembers
            ];

            // Mark this group as added
            $addedGroupIds[] = $group['group_id'];
        }
    }

    if ($route === '/user/chat/$user_id') {
        // Fetch the user information for personal chat
        $chatWithUserInfoResult = $h->table('users')
            ->select('users.id', 'users.fname', 'users.lname', 'users.email', 'users.type', 'users.profile_image')
            ->where('users.id', '=', $user_id)
            ->fetchAll();

        $chatWithUserInfo = $chatWithUserInfoResult[0];

        $seo = [
            'title' => 'Chat with ' . $chatWithUserInfo['fname'] . " " . $chatWithUserInfo['lname'],
            'description' => 'CRM',
            'keywords' => 'Admin Panel'
        ];

        echo $twig->render('user/chat/user_chat.twig', [
            'seo' => $seo,
            'chatUsers' => $usersList,
            'chatWithUserInfo' => $chatWithUserInfo,
            'user_id' => $user_id
        ]);
    } else if ($route == '/user/group/$groupId') {
        // Fetch the specific group details
        $groupResult = $h->table('groups')
            ->select('groups.id AS group_id', 'groups.name AS group_name', 'groups.created_by')
            ->where('groups.id', '=', $groupId)
            ->fetchAll();

        if (!empty($groupResult)) {
            $group = $groupResult[0];

            $groupMembersSQL = $h->table('group_members')
                ->select('users.id as user_id', 'users.fname', 'users.lname', 'users.email', 'users.profile_image', 'users.type')
                ->leftJoin('users')->on('users.id', 'group_members.user_id')
                ->where('group_members.group_id', '=', $group['group_id']);

            $groupMembers = $groupMembersSQL->fetchAll();
            $groupMembersCount = count($groupMembers);

            if ($groupMembersCount > 1) {
                $groupMembersCount -= 1;
            }else{
                $groupMembersCount=null;
            }

            $chatWithUserInfo = [
                'id' => $group['group_id'],
                'fname' => $group['group_name'],
                'lname' => 'Group',
                'email' => null,
                'type' => 'group',
                'profile_image' => null,
                'members' => $groupMembers
            ];

            $seo = [
                'title' => 'Group Chat - ' . $group['group_name'],
                'description' => 'CRM',
                'keywords' => 'Admin Panel'
            ];

            // Render the group chat page with the data
            echo $twig->render('user/chat/group_chat.twig', [
                'seo' => $seo,
                'chatUsers' => $usersList,  // Combined users and groups
                'chatWithUserInfo' => $chatWithUserInfo,
                'groupId' => $groupId,
                'groupMembers' => $groupMembers,  // Group members passed to Twig
                'groupMembersCount' => $groupMembersCount
            ]);
        } else {
            echo "Group not found.";
        }
    }
}






if($route == '/chat/messages/group/$groupId'){

    // Fetch group messages from the database
    $messages = $h->table('chat')
        ->select('chat.id', 'chat.message', 'chat.created_at', 'users.fname', 'users.lname', 'users.profile_image')
        ->leftJoin('users')->on('users.id', 'messages.sender_id')
        ->where('messages.group_id', '=', $groupId)
        ->orderBy('messages.timestamp', 'asc')
        ->fetchAll();

    echo json_encode(['messages' => $messages]);
    exit();
}

if($route == '/group/del/$groupId'){
    $h->table('groups')->delete()->where('id',$groupId)->run();
    header('Location: /user/chat');
}


if($route == '/group/delete/$group_id/$member_id'){
    $h->table('group_members')->delete()->where('group_id',$group_id)->where('user_id',$member_id)->run();
    echo json_encode(['status' => true]);
    exit;
}