<?php
    require __DIR__.'/../../../APLib/core.php';
    \APLib\WebSockets::init(
        function($conn, $message)
        {
            echo "A client with the IP address '{$conn->remoteAddress}' sent: {$message}\r\n";
        }
    );
    \APLib\WebSockets\Server::init(8080);
    \APLib\WebSockets\Server::run();
?>
