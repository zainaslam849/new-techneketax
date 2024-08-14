<?php
require("config/env.php");
$client = new \GuzzleHttp\Client();

function createUser($email){
    $response = $client->request('POST', 'https://api.onesignal.com/apps/86fcb8f1-7126-42e5-bb11-52e8ed8b4d42/users', [
        'body' => '{"subscriptions":[{"type":"Email","token":'.$email.',"enabled":true}]}',
        'headers' => [
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ],
    ]);
    return $response->getBody();
}

