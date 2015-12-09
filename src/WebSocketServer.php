<?php
namespace ZerglingGo\ImDJ_Server;

use Thread;
use ZerglingGo\ImDJ_Server\Room;
use ZerglingGo\ImDJ_Server\Client;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

require 'vendor/autoload.php';

class WebSocketServer implements WampServerInterface {
    public $rooms;
    public $clients;
    public $heartbeat;
    public $connections;

    public function __construct() {
        $this->rooms = array();
        $this->clients = array();
        $this->connections = array();

        $this->rooms[] = new Room(uniqid("R#"));
    }

    protected function roomBroadcast($topic, $msg, ConnectionInterface $exclude = null) {
        foreach ($this->rooms[$topic] as $client) {
            if ($client !== $exclude) {
                $client->event($topic, $msg);
            }
        }
    }

    protected function getClientByConnection(ConnectionInterface $conn) {
        foreach ($this->clients as $clientId => $client) {
            if ($conn === $client->getConnection()) {
                return $this->clients[$clientId];
            }
        }
        return false;
    }

    public function onOpen(ConnectionInterface $conn) {
        $clientId = uniqid("C#");
        
        $this->clients[$clientId] = new Client($conn, $this, $clientId);
        $this->connections[$clientId] = $conn;
        echo "Client Connected (".$clientId.")\n";
    }

    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
        $client = $this->getClientByConnection($conn);
        switch ($topic) {
            case 'createRoom':
                $roomName = $this->escape($params[0]); // R#1a2b3c~~

                if (empty($roomName)) {
                    return $conn->callError($id, "Empty room name");
                }

                if (array_key_exists($this->rooms, $roomName)) {
                    return $conn->callError($id, array('message' => "Already exists room"));
                } else {
                    $this->rooms[] = new Room(uniqid("R#"));
                    return->$conn->callResult($id, array('message' => "Created Successfully"));
                }

                break;
        }
    }

    public function onSubscribe(ConnectionInterface $conn, $topic) {
        $client = $this->getClientByConnection($conn);
        echo "subscribe: ".$client->getId()." {$topic}\n";
        $conn->callError("test");
    }

    public function onUnSubscribe(ConnectionInterface $conn, $topic) {
        $client = $this->getClientByConnection($conn);
        echo "unsubscribe: ".$client->getId()." {$topic}\n";
    }

    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude = array(), array $eligible = array()) {
        $client = $this->getClientByConnection($conn);
        echo "publish: ".$client->getId()." {$topic} {$event}\n";
    }

    public function onClose(ConnectionInterface $conn) {
        $client = $this->getClientByConnection($conn);
        $clientId = $client->getId();
        $room = $client->getRoom();
        if ($room) {
            $room->leftRoom($clientId);
        }
        unset($this->clients[$clientId]);
        unset($this->connections[$clientId]);
        echo "Client Disconnected (".$clientId.")\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $this->stdout("An error has occurred: {$e->getMessage()} at {$e->getFile()}:{$e->getLine()}");
        $conn->close();
    }

    protected function escape($string) {
        return htmlspecialchars($string);
    }

    public function stdout($message) {
        echo $message."\n";
    }
}