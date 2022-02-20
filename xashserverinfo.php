<?php

/*
xashserverinfo.php
by Drum3X
*/

//class
class Xashserver {
    protected $ip;
    protected $port;
    
    //get variable function
    public function __construct(string $ip, int $port) {
        $this->ip = $ip;
        $this->port = $port;
        $this->sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP) or die("connection error\n");        
    }
    
    //connect function
    function connect() {
        try {
            socket_connect($this->sock, $this->ip, $this->port);
            echo "connection succesfuly\n\n";
        } catch (Exception $err) {
            echo "connection error\n\n$err";
        }
    }    
    
    //package sender function
    function sendpackage(string $package) {
        try {
            socket_send($this->sock, $package, strlen($package), MSG_EOR);
            socket_recv($this->sock, $result, 10000, MSG_WAITALL);
            return $result;
        } catch (Exception $err){
            echo "package send error\n\n$err";
        }        
    }
}

//argv
$ip = $argv[1];
$port = $argv[2];

//argv controller
if ($ip == null || $port == null) {
    echo "\nuse: php xashserverinfo.php <ip> <port>";
    goto none;
}

//terminal cleaner
system("clear");

//prepare script
$xashserver = new Xashserver($ip, $port);
$xashserver->connect();
sleep(4);

//status command
$statusresult = $xashserver->sendpackage("\xff\xff\xff\xff\status");
$statusresult = preg_split("/\n/", $statusresult); 

//get players info
$players = [];
for ($i = 2; $i < (count($statusresult) - 1); $i++) {
    $player = explode(" ", $statusresult[$i]);
    array_push($players, $player[2]);
}

//netinfo command
$netinforesult = $xashserver->sendpackage("\xff\xff\xff\xffnetinfo 48 0 4");
$netinforesult = explode("\\", $netinforesult);

//xash colors
$colors = [
    "^0", 
    "^1",
    "^2",
    "^3",
    "^4",
    "^5",
    "^6",
    "^7",
    "^8",
];

//results
$result = [
    "hostname" => str_replace($colors, "", $netinforesult[2]),
    "gamedir" => $netinforesult[4],
    "playercount" => $netinforesult[6]. "/".$netinforesult[8],
    "map" => $netinforesult[10],
    "players" => str_replace($colors, "", join(", ", $players))
];

echo <<<END
    hostname: {$result["hostname"]} 
    gamedir: {$result["gamedir"]} 
    player count: {$result["playercount"]}
    map: {$result["map"]}
    players: {$result["players"]}
    END;

none:
