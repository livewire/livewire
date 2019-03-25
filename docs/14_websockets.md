# WebSockets

By default, Livewire uses standard Ajax requests to communicate with the server. This has the benefit of being simple to setup and easily scalable, with the downside of being slower than other alternatives like WebSockets.

For those willing to take on the technical challenges involved with running a WebSocket server, Livewire offers a WebSocket driver.

## Use WebSocket JavaScript Driver
To configure the JS portion of Livewire to connect using WebSockets, pass in the following configuration.

**(If you are injecting the JavaScript)**

```php
    ...
    {!! Livewire::scripts(['driver' => 'websocket']) !!}
    </body>
</html>
```

**(If you are importing the JavaScript from NPM)**
```js
Livewire.start({ driver: 'websocket' })
```

## Run WebSocket Server
Running Livewire's WebSocket server is as simple as running one artisan commmand:

```bash
> php artisan livewire:start
```

Livewire is now listening for connections from clients.

Now, when you load your app in the browser, you should see a new connection appear in the console running the `livewire:start` command.

## Warning: this feature is under development
Currently, Livewire uses RatchetPHP to run the WebSocket server. It is not configured to custom domains, and not configured for https. In the near future, we will be implementing a more robust, and scalable system, using `laravel-websockets`.
