# Apache2 mod_proxy_wstunnel server implementation
This example applies with all other WebSockets examples.

Before starting the server, you need to do some steps first:
  1. Run the following command (works only on Linux):
  ```
  a2enmod proxy_wstunnel
  ```
  2. Add the following line to your apache2.conf file:
  ```
  ProxyPass "/websockets" "ws://localhost:8080"
  ```
  3. Restart Apache2 server (only Linux):
  ```
  systemctl restart apache2
  ```

After successfully completing the steps above, run `server.php`:
```
php server.php
```

### What now?
Now you can connect to it just like you would connect to any other WebSockets example.
As mentioned above, this example applies to all other WebSockets examples. All you need to do is changing the port to match the one your server is running on.
