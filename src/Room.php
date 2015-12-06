<?php
namespace ZerglingGo\ImDJ_Server;

use Thread;
use ZerglingGo\ImDJ_Server\Client;

define("SEC", 1000000);

require 'vendor/autoload.php';

class Room extends Thread {

    private $roomId;
    private $videoId;
    private $clients;

    public function __construct($roomId) {
        $this->roomId = $roomId;
        echo "Room Created (".$roomId.")\n";
    }

    public function run() {
        $this->synchronized(function () {
            $nowTime = $this->nowTime = 5; //getDuration($this->vid);
            while(true) {
                if($nowTime >= 0) {
                    $this->wait(1 * SEC);
                    $this->nowTime = $nowTime--;
                    echo $this->roomId." ".$this->nowTime."\n";
                    //parent::send()
                } else {
                    break;
                }
            }
        });
    }

    public function joinRoom(Client $client) {
        $clientId = $client->getId();
        $this->clients[$clientId]["client"] = $client;
        echo "Client ".$clientId." joined the ".$this->roomId."\n";
    }

    public function leftRoom(Client $client) {
        $clientId = $client->getId;
        unset($this->clients[$clientId]);
        echo "Client ".$clientId." left the ".$this->roomId."\n";
    }
}