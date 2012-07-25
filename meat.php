<?php
/*
 * All the user commands.
 * 
 * This doesn't work at all.
 */

switch (strtolower($params[0])) {

    case 'user':
        $user_params = explode(' ', $buffer);

        $user = $user_params[1];
        $user_mode = $user_params[2];
        $user_realname = $user_params[3];
        $client_ident = "$user!$user@$server_name";
        break;

    case 'nick':
        $nick = $params[1];
        
        //Check if the nick is in use
        if (in_array($nick, $connected_nicks)) {

            $message = ERR_NICKNAMEINUSE . "NICK :$nick\r\n";
            socket_send($client[$i]['sock'], $message, strlen($message), MSG_EOF);
        } else {

            //Add nick the array of connected nicks
            array_push($connected_nicks, $nick);
            /*   if(preg_match('[^a-zA-Z0-9\-\[\]\'`^{}_]', $nick)){
             * 
             */

            //Nick is available, register and welcome!            
            $message = ":$address " . RPL_WELCOME . " $nick :welcome to the irc server\r\n";
            socket_send($client[$i]['sock'], $message, strlen($message), MSG_EOF);

            /*
             * MOTD
             */

            $message = ":$address " . RPL_MOTDSTART . ": motd start\r\n";
            socket_send($client[$i]['sock'], $message, strlen($message), MSG_EOF);


            $message = ":$address " . RPL_MOTD . ": motd middle\r\n";
            socket_send($client[$i]['sock'], $message, strlen($message), MSG_EOF);

            $message = ":$address " . RPL_ENDOFMOTD . ":End of /MOTD command\r\n";
            socket_send($client[$i]['sock'], $message, strlen($message), MSG_EOF);
        }
        break;
        
    case 'ping':
        //Send ping back to keep connection open
        $message = ":$address PONG :$address\r\n";
        socket_send($client[$i]['sock'], $message, strlen($message), MSG_EOF);
        break;

    case 'join':
        $channel_name = $params[1];

        $channel_clients = array();
        
        $$channel_clients = $channel_name;
        
        //echo "$channel_clients ${$channel_clients}";

        //Add nick to channel clients
        array_push($channel_clients, $nick);
        
        //Add to list of channels
        array_push($channels, $channel_name);

        //   foreach ($channel_clients as $channel_clients) {
        //   $message = ":$client_ident JOIN :$channel_name\r\n";
        //    socket_send($client[$i]['sock'], $message, strlen($message), MSG_EOF);
        //   foreach ($channel_clients as $channel_clients) {
        $message = ":$nick JOIN :$channel_name\r\n";
        socket_send($client[$i]['sock'], $message, strlen($message), MSG_EOF);
        //    $message = ":$server_name JOIN :$channel_name\r\n";
        //   socket_send($client[$i]['sock'], $message, strlen($message), MSG_EOF);
        // /  }
        //Set chanel mode
        $message = ":$server_name MODE $channel_name +ntr\r\n";
        socket_send($client[$i]['sock'], $message, strlen($message), MSG_EOF);


        //NAMES
        //This is a hack, it only lists the current user in the room
        $message = ":$server_name " . '353' . " $nick = $channel_name :$nick\r\n";
        socket_send($client[$i]['sock'], $message, strlen($message), MSG_EOF);


        //End NAMES
        $message = ":$server_name " . '366' . " $nick $channel_name :End of /NAMES list\r\n";
        socket_send($client[$i]['sock'], $message, strlen($message), MSG_EOF);

        //Set channel mode
        $message = ":$server_name" . '324' . " $nick $channel_name +ntr\r\n";
        socket_send($client[$i]['sock'], $message, strlen($message), MSG_EOF);

        //Send channel creation time
        $message = ":$server_name " . '329' . " $nick $channel_name " . time() . "\r\n";
        socket_send($client[$i]['sock'], $message, strlen($message), MSG_EOF);

        //Send channel creation time
        $message = ":$server_name PONG $server_name :" . time() . "\r\n";
        socket_send($client[$i]['sock'], $message, strlen($message), MSG_EOF);
        
        //Test message
        $message = ":$server_name PRIVMSG $channel_name :successful join?\r\n";
        socket_send($client[$i]['sock'], $message, strlen($message), MSG_EOF);

        break;

    case 'quit':
        $message = ":$client_ident QUIT : quit message\r\n";
        socket_send($client[$i]['sock'], $message, strlen($message), MSG_EOF);

        break;
    case 'privmsg':
        $msg = explode(':', $buffer);

        //Message all the clients in the channel
        foreach ($client as $channel_clients) {
            $message = ":$client_ident PRIVMSG $channel_name :$msg[1]\r\n";
            socket_send($client[$i]['sock'], $message, strlen($message), MSG_EOF);
        }
        break;
}