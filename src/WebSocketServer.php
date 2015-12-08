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
        $client = $this->getClientByConnection($from);
        echo "Client ".$client->getId()." onCall {$id} {$fn}\n";
        $from->send("hi");
    }

    public function onSubscribe(ConnectionInterface $conn, $topic) {
        $client = $this->getClientByConnection($conn);
        echo "subscribe: ".$client->getId()." {$topic}\n";
    }

    public function onUnSubscribe(ConnectionInterface $conn, $topic) {
        $client = $this->getClientByConnection($conn);
        echo "unsubscribe: ".$client->getId()." {$topic}\n";
    }

    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude = array(), array $eligible = array()) {
        echo "publish: {$topic} {$event}\n";
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

    public function stdout($message) {
        echo $message."\n";
    }
}