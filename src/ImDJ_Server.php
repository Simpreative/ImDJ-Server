<?php
namespace ZerglingGo\ImDJ_Server;

use ZerglingGo\ImDJ_Server\Room;
use ZerglingGo\ImDJ_Server\Client;
use ZerglingGo\ImDJ_Server\WebSocketServer;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

require 'vendor/autoload.php';

class ImDJ_Server {
    
    public function runServer() {
        $ImDJ_Server = IoServer::factory(new HttpServer(new WsServer(new WebSocketServer())), BIND_PORT);
        $ImDJ_Server->run();
    }
}