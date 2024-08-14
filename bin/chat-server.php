<?php

require 'vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Cycle\Database;
use Cycle\Database\Config;

// Define environment variables directly
$env = [
    "DATABASE_HOST" => "localhost",
    "DATABASE_NAME" => "techneketax",
    "DATABASE_USERNAME" => "root",
    "DATABASE_PASSWORD" => "root",
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

// Define the WebSocket Chat server
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
        if (isset($data['sender_id']) && isset($data['receiver_id']) && isset($data['message'])) {
            // Debug output
            echo "Data received: " . print_r($data['sender_id']) . "\n";

            try {
                $sender_id=$data['sender_id'];
                $safe_message = htmlspecialchars($data['message'], ENT_QUOTES, 'UTF-8');

                $this->db->insert('chat')->values([
                    'sender_id' => $sender_id,
                    'reciever_id' => $data['receiver_id'],
                    'message' => $safe_message,
                ])->run();

                foreach ($this->clients as $client) {
                    if ($from !== $client) {
                        $client->send($msg);
                    }
                }
            } catch (\Exception $e) {
                echo "Database error: " . $e->getMessage() . "\n";
            }
        } else {
            echo "Invalid message format\n";
        }
    }


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
