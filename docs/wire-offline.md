
In certain circumstances it can be helpful for your users to know if they are currently connected to the internet.

If for example, you have built a blogging platform on Livewire, you may want to notify your users in some way if they are offline so that they don't draft an entire blog post without the ability for Livewire to save it to the database.

Livewire make this trivial by providing the `wire:offline` directive. By attaching `wire:offline` to an element in your Livewire component, it will be hidden by default and only be displayed when Livewire detects the network connection has been interrupted and is unavailable. It will then disappear again when the network has regained connection.

For example:

```blade
<p class="alert alert-warning" wire:offline>
    Whoops, your device has lost connection. The web page you are viewing is offline.
</p>
```
