<?php
    require __DIR__.'/../../../APLib/core.php';
    class Server
    {
        public static $rooms    = array();
        public static $messages = array();
        public static $users    = array();
        public static function addRoom($name){
            $newRoom = array(
                'name'  => $name,
                'users' => array()
            );
            array_push(static::$rooms, $newRoom);
        }
        public static function removeRoom($name){
            $newRooms = array();
            for ($i=0; $i < $room; $i++) {
                if(static::$rooms[$i]['name'] != $name) array_push($newRooms, static::$rooms[$i]);
            }
            static::$rooms = $newRooms;
        }
        public static function findRoom($name){
            for ($i=0; $i < $room; $i++) {
                if(static::$rooms[$i]['name'] == $name){
                    return static::$rooms[$i];
                }
            }
        }
        public static function subscribeToRoom($name, $user){
            for ($i=0; $i < $room; $i++) {
                if(static::$rooms[$i]['name'] == $name){
                    array_push(static::$rooms[$i]['users'], $user);
                    return;
                }
            }
        }
        public static function unsubscribeFromRoom($name, $user){
            for ($i=0; $i < $room; $i++) {
                if(static::$rooms[$i]['name'] == $name){
                    $newUsers = array();
                    for ($ii=0; $ii < static::$rooms[$i]['users']; $ii++) {
                        if(static::$rooms[$i]['users'][$ii] != $user) array_push($newUsers, static::$rooms[$i]['users'][$ii]);
                    }
                    static::$rooms[$i]['users'] = $newUsers;
                    return;
                }
            }
        }
        public static function sendMessage($from, $msg, $to){
            if(isset(static::$users[$to])){
                $conn = static::$users[$to];
                $conn->send(json_encode(array('command' => 'chat', 'type' => 'new', 'sender' => $from, 'message' => $msg)));
            }else{
                foreach (static::$users as $user => $conn) {
                    if($user != $from) $conn->send(json_encode(array('command' => 'chat', 'type' => 'new', 'sender' => $from, 'message' => $msg)));
                }
            }
        }
        public static function addUser($user, $conn){
            static::$users[$user] = $conn;
            $list = array();
            foreach (static::$users as $user => $conn) {
                array_push($list, $user);
            }
            static::broadcastEvent(array('command' => 'chat', 'type' => 'list', 'list' => $list));
        }
        public static function removeUser($user){
            unset(static::$users[$user]);
            $list = array();
            foreach (static::$users as $user => $conn) {
                array_push($list, $user);
            }
            static::broadcastEvent(array('command' => 'chat', 'type' => 'list', 'list' => $list));
        }
        public static function findUser($conn){
            foreach (static::$users as $user => $conn1) {
                if($conn == $conn1) return $user;
            }
        }
        public static function broadcastEvent($event){
            foreach (static::$users as $user => $conn) {
                $conn->send(json_encode($event));
            }
        }
    }
    \APLib\WebSockets::init(
        function($conn, $message)
        {
            try {
                $command = json_decode($message, true);
                switch ($command['command']) {
                    case 'user':
                        \APLib\WebSockets\Connections::set($conn, 'user', $command['user']);
                        if(\APLib\WebSockets\Channels::subscribed('users', $command['user']))
                        {
                            $conn->send(json_encode(array('command' => 'error', 'message' => 'Name ('.$command['user'].') is already in use')));
                            $conn->close();
                            return;
                        }
                        \APLib\WebSockets\Channels::subscribe('users', $command['user'], $conn);
                        $list = array();
                        foreach (\APLib\WebSockets\Channels::subscribers('users') as $user => $conn) array_push($list, $user);
                        \APLib\WebSockets\Connections::broadcast(json_encode(array('command' => 'chat', 'type' => 'list', 'list' => $list)), null, array($conn));
                        break;
                    case 'server':
                        switch ($command['type']) {
                            case 'info':
                                $info = array('command' => 'server', 'type' => 'info', 'info' => 'Test');
                                $conn->send(json_encode($info));
                                break;
                            case 'time':
                                $time = array('command' => 'server', 'type' => 'time', 'time' => 'Test');
                                $conn->send(json_encode($time));
                                break;
                        }
                        break;
                    case 'chat':
                        switch ($command['type']) {
                            case 'send':
                                if(isset($command['to']))
                                {
                                    \APLib\WebSockets\Channels::subscriber('users', $command['to'])->send(json_encode(array('command' => 'chat', 'type' => 'new', 'sender' => \APLib\WebSockets\Connections::get($conn, 'user'), 'message' => $command['message'])));
                                } else {
                                    \APLib\WebSockets\Channels::broadcast('users', json_encode(array('command' => 'chat', 'type' => 'new', 'sender' => \APLib\WebSockets\Connections::get($conn, 'user'), 'message' => $command['message'])), null, array($conn));
                                }
                                break;
                            case 'list':
                                $list = array();
                                foreach (\APLib\WebSockets\Channels::subscribers('users') as $user => $conn1) array_push($list, $user);
                                $conn->send(json_encode(array('command' => 'chat', 'type' => 'list', 'list' => $list)));
                                break;
                        }
                        break;
                    case 'channel':
                        switch ($command['type']) {
                            case 'subscribe':
                                $channels = \APLib\WebSockets\Connections::get($conn, 'channels');
                                if($channels === undefined) array();
                                array_push($channels, $command['name']);
                                \APLib\WebSockets\Connections::set($conn, 'channels', $channels);
                                \APLib\WebSockets\Channels::subscribe($command['name'], \APLib\WebSockets\Connections::get($conn, 'user'), $conn);
                                break;
                            case 'unsubscribe':
                                $channels    = \APLib\WebSockets\Connections::get($conn, 'channels');
                                if($channels === undefined) array();
                                $newChannels = array();
                                foreach ($channels as $channel) if($channel == $command['name']) array_push($newChannels, $channel);
                                \APLib\WebSockets\Connections::set($conn, 'channels', $newChannels);
                                \APLib\WebSockets\Channels::unsubscribe($command['name'], \APLib\WebSockets\Connections::get($conn, 'user'));
                                break;
                        }
                        break;
                }
            } catch (\Exception $e) {
                $conn->close();
            }
        },
        array(
            'close' => function($connection) {
                \APLib\WebSockets\Channels::unsubscribe('users', \APLib\WebSockets\Connections::get($connection, 'user'));
                $list = array();
                foreach (\APLib\WebSockets\Channels::subscribers('users') as $user => $conn) array_push($list, $user);
                \APLib\WebSockets\Connections::broadcast(json_encode(array('command' => 'chat', 'type' => 'list', 'list' => $list)), null, array($connection));
            }
        )
    );
    \APLib\WebSockets\Channels::create('users');
    \APLib\WebSockets\Server::init(8080);
    \APLib\WebSockets\Server::run();
?>
