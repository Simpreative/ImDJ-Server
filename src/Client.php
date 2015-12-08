<?php
namespace ZerglingGo\ImDJ_Server;

use ZerglingGo\ImDJ_Server\Room;
use ZerglingGo\ImDJ_Server\WebSocketServer;
use Ratchet\ConnectionInterface;

require 'vendor/autoload.php';

class Client {
    protected $conn;
    private $room;
    private $parent;
    private $clientId;

    public function __construct(ConnectionInterface $conn, $parent, $id) {
        $this->conn = $conn;
        $this->parent = $parent;
        $this->clientId = $id;
    }

    public function send($message) {
        $this->conn->send($message);
    }

    public function getId() {
        return $this->clientId;
    }

    public function getConnection() {
        return $this->conn;
    }

    public function getRoom() {
        return $this->room;
    }

    public function setRoom($room) {
        $this->room = $room;
    }
}