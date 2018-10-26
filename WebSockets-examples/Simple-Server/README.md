# A Simple Server
This example is as simple as typing one line command:
```
php server.php
```
### What now?
Now you can connect to it just like you would connect to any other WebSockets server.
Here's a `JavaScript` connect code example:
```
var ws = new WebSocket('ws://localhost:8080');
ws.onopen = function(){
    console.log('Connected');
}
ws.onclose = function(){
    console.log('Disconnected');
}
ws.onerror = function(){
    console.log('Error');
    this.close();
}
ws.onmessage = function(event){
    console.log('Message: '+event.data);
}
```
Run the above code in the browser's console (or Scratchpad) to connect to the server.
To test sending messages, open the browser's console and type:
```
ws.send('Your message');
```
And that would do it.
