<?php

//Allow unlimited execution
set_time_limit(0);

// Set the IP to listen on
$address = 'localhost';

//Server name
$server_name = 'localhost';

define('RPL_WELCOME', '001');
define('ERR_NOSUCHNICK', '401');
define('ERR_NOSUCHCHANNEL', '403');
define('ERR_ERRONEUSNICKNAME', '432');
define('ERR_NICKNAMEINUSE', '433');
define('RPL_MOTDSTART', '375');
define('RPL_ENDOFMOTD', '376');
define('RPL_MOTD', '372');

$port = 6667;

$max_clients = 10;

// IRC channels in use
$channels = array();
// IRC nicks connected
$connected_nicks = array();


// Create a TCP Stream socket
$socket = socket_create(AF_INET, SOCK_STREAM, 0);

socket_set_nonblock($socket);

// Bind the socket to an address/port
socket_bind($socket, $address, $port) or die('Could not bind to address');

// Start listening for connections
socket_listen($socket);


if (!socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1)) {
    die('Unable to set option on socket: ' . socket_strerror(socket_last_error()) . PHP_EOL);
}


$client = array('0' => array('socket' => $socket));
echo "\n#########################\n";
echo "starting on port $port...\n";
echo "#########################\n";

//Fork, spoon, whatever
//Create five child processes
for ($pi = 1; $p <= 5; ++$p) {
    $pid = pcntl_fork();
    if (!$pid) {

        while (true) {

            $read[0] = $socket;

            for ($i = 1; $i < count($client) + 1; ++$i) {
                if ($client[$i] != NULL) {
                    $read[$i + 1] = $client[$i]['sock'];
                }
            }

            $ready = socket_select($read, $write = NULL, $except = NULL, $tv_sec = 5);

            //Check if socket_select() contains socket resources
            if ($ready < 1) {
                continue;
            }


                while(in_array($socket, $read)){  
                    
                for ($i = 1; $i < $max_clients + 1; ++$i) {

                    if (empty($client[$i]['sock'])) {

                        $client[$i]['sock'] = socket_accept($socket);
                        
                        //Get client IP
                        socket_getpeername($client[$i]['sock'], $ip);
                        $client[$i]['ipadd'] = $ip;
                        
                        //Make sure the connection is still open and supress the notices
                        while (!@feof($client[$i]['sock'])) {

                            $data = @socket_read($client[$i]['sock'], 1024);
                            
                            echo "\nNew client $i with " . $client[$i]['ipadd'] . " @ " . time() . "\n";

                            //Remove line endings
                            $buffer = rtrim($data);


                            $params = explode(' ', $buffer);
                            
                            //Include the meat of the server
                            require './meat.php';
                        }
                    }
                }
            }
        }

        usleep(10);
        echo "\n in child $p \n";
        exit;
    }
}