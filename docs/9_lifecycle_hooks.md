# Lifecycle Hooks

Each Livewire component undergoes a lifecycle (created, updated, destroyed). Lifecycle hooks allow you to run code at any part of the component's lifecyle.

Because Livewire is heavily inspired by VueJs, it borrows Vue's lifecycle hook naming conventions.

Hooks | Description
--- | ---
created | Runs as soon as the Livewire component is instantiated
mounted | Runs immediately after the Livewire component has rendered for the first time
beforeUpdate | Runs before actions such as: `wire:click="addTodo"`
updated | Runs after actions

**Example**
```php
class HelloWorld extends LivewireComponent
{
    public function created()
    {
        //
    }

    public function mounted()
    {
        //
    }

    public function beforeUpdate()
    {
        //
    }

    public function updated()
    {
        //
    }
}
```
