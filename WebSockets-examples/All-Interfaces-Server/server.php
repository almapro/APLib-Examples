<?php
    require __DIR__.'/../../../APLib/core.php';
    \APLib\WebSockets::init(
        function($conn, $message)
        {
            echo "A client with the IP address '{$conn->remoteAddress}' sent: {$message}\r\n";
        }
    );
    // Nothing new, just the bind address
    \APLib\WebSockets\Server::init(
        8080,     // Bind port
        '0.0.0.0' // Bind Address
    );
    \APLib\WebSockets\Server::run();
?>
