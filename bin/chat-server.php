<?php

require 'vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Cycle\Database;
use Cycle\Database\Config;

// Define environment variables directly
$env = [
    "APP_URL"=>"https://dev.techneketax.com/",
    "DATABASE_HOST"=>"localhost",
    "DATABASE_NAME"=>"dev_tecgneketax",
    "DATABASE_USERNAME"=>"dev_tecgneketax",
    "DATABASE_PASSWORD"=>"7L36?3mue",

//    "DATABASE_HOST" => "localhost",
//    "DATABASE_NAME" => "techneketax",
//    "DATABASE_USERNAME" => "root",
//    "DATABASE_PASSWORD" => "root",
    "ENV_TYPE" => "local"
];

// Setup Cycle ORM
$config = new Config\DatabaseConfig([
    'default' => 'default',
    'databases' => [
        'default' => ['connection' => 'mysql']
    ],
    'connections' => [
        'mysql' => new Config\MySQLDriverConfig(
            connection: new Config\MySQL\TcpConnectionConfig(
                database: $env['DATABASE_NAME'],
                host: $env['DATABASE_HOST'],
                port: 3306,
                user: $env['DATABASE_USERNAME'],
                password: $env['DATABASE_PASSWORD']
            ),
            queryCache: true
        ),
    ]
]);

$dbal = new Database\DatabaseManager($config);
$db = $dbal->database('default');

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $db;

    public function __construct($db) {
        $this->clients = new \SplObjectStorage;
        $this->db = $db;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }
    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);

        if (isset($data['sender_id']) && isset($data['message'])) {
            try {
                $safe_message = htmlspecialchars($data['message'], ENT_QUOTES, 'UTF-8');
                $timestamp = date('Y-m-d H:i:s');

                // Fetch the sender's profile image and name from the database
                $sender = $this->db->table('users')
                    ->select('fname', 'lname', 'profile_image')
                    ->where('id', '=', $data['sender_id'])
                    ->fetchAll();
                $sender=$sender[0];

                // Handle the profile image logic
                $senderProfileImage = $sender['profile_image']
                    ? "https://dev.techneketax.com/uploads/profile/".$sender['profile_image']
                    : "https://avatar.iran.liara.run/username?username=" . urlencode($sender['fname'] . ' ' . $sender['lname']);

                // Insert the message into the database
                $this->db->insert('chat')->values([
                    'sender_id' => $data['sender_id'],
                    'message' => $safe_message,
                    'group_id' => $data['group_id'] ?? null,
                    'receiver_id' => $data['receiver_id'] ?? null,
                    'created_at' => $timestamp,
                ])->run();

                // Broadcast the message to other clients
                foreach ($this->clients as $client) {
                    if ($client !== $from) {
                        $client->send(json_encode([
                            'sender_id' => $data['sender_id'],
                            'group_id' => $data['group_id'] ?? null,
                            'receiver_id' => $data['receiver_id'] ?? null,
                            'message' => $safe_message,
                            'senderName' => $sender['fname'] . ' ' . $sender['lname'],
                            'senderProfileImage' => $senderProfileImage,
                            'timestamp' => $timestamp
                        ]));
                    }
                }

                echo "Message from {$data['sender_id']} sent and saved: {$safe_message}\n";

            } catch (\Exception $e) {
                echo "Database error: " . $e->getMessage() . "\n";
            }
        } else {
            echo "Invalid message format\n";
        }
    }


//    public function onMessage(ConnectionInterface $from, $msg) {
//        $data = json_decode($msg, true);
//        if (isset($data['sender_id']) && isset($data['receiver_id']) && isset($data['message'])) {
//            try {
//                $sender_id = $data['sender_id'];
//                $safe_message = htmlspecialchars($data['message'], ENT_QUOTES, 'UTF-8');
//
//                // Insert message into database
//                $this->db->insert('chat')->values([
//                    'sender_id' => $sender_id,
//                    'receiver_id' => $data['receiver_id'],
//                    'message' => $safe_message,
//                ])->run();
//
//                // Send message to the recipient only
//                foreach ($this->clients as $client) {
//                    if ($from !== $client) {
//                        $client->send(json_encode([
//                            'sender_id' => $sender_id,
//                            'receiver_id' => $data['receiver_id'],
//                            'message' => $safe_message,
//                            'senderName' => $data['senderName'], // Add additional fields as necessary
//                            'senderProfileImage' => $data['senderProfileImage']
//                        ]));
//                    }
//                }
//
//                echo "Message sent from {$sender_id} to {$data['receiver_id']}: {$safe_message}\n";
//            } catch (\Exception $e) {
//                echo "Database error: " . $e->getMessage() . "\n";
//            }
//        } else {
//            echo "Invalid message format\n";
//        }
//    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

// Run WebSocket server
$server = \Ratchet\Server\IoServer::factory(
    new \Ratchet\Http\HttpServer(
        new \Ratchet\WebSocket\WsServer(
            new Chat($db)
        )
    ),
    8005
);

echo "WebSocket server started on port 8000...\n";
$server->run();
