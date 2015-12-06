<?php
namespace ZerglingGo\ImDJ_Server;

use Thread;
use ZerglingGo\ImDJ_Server\Room;
use ZerglingGo\ImDJ_Server\Client;
use Ratchet\ComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

require 'vendor/autoload.php';

class WebSocketServer implements MessageComponentInterface {
    protected $rooms;
    protected $clients;
    protected $connections;

    public function __construct() {
        $this->rooms = array();
        $this->clients = array();
        $this->connections = array();

        $this->rooms[] = new Room(uniqid("R#"));
    }

    public function getConnectionById($clientId) {
        if (isset($this->connections[$clientId])) {
           return $this->connections[$clientId];
        } else {
            return false;
        }
    }

    public function getClientByConnection($conn) {
        foreach ($this->connections as $clientId => $connection) {
            if ($connection == $conn) {
                return $this->clients[$clientId];
            }
        }
        return false;
    }

    public function onOpen(ConnectionInterface $conn) {
        $clientId = uniqid("C#");
        $this->clients[$clientId] = new Client($this, $clientId);
        $this->connections[$clientId] = $conn;
        echo "Client Connected (".$clientId.")\n";
    }
    public function onMessage(ConnectionInterface $from, $msg) {
        $client = $this->getClientByConnection($from);
        echo "Client ".$client->getId()." received message: ".$msg."\n";
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