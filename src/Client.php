<?php
namespace ZerglingGo\ImDJ_Server;

use Thread;
use ZerglingGo\ImDJ_Server\Room;
use ZerglingGo\ImDJ_Server\WebSocketServer;
use Ratchet\ConnectionInterface;

require 'vendor/autoload.php';

class Client {
    private $room;
    private $parent;
    private $clientId;

    public function __construct($parent, $id) {
        $this->parent = $parent;
        $this->clientId = $id;
    }

    public function getId() {
        return $this->clientId;
    }

    public function getConnection() {
        return $this->parent->getConnectionById($this->clientId);
    }

    public function getRoom() {
        return $this->room;
    }

    public function setRoom($room) {
        $this->room = $room;
    }
}