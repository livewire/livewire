# Lifecycle Hooks

Each Livewire component undergoes a lifecycle (created, updated, destroyed). Lifecycle hooks allow you to run your own code at an event of your choosing.

Because Livewire is heavily inspired by VueJs, it borrows Vue's lifecycle hook concept and naming convention.

Hooks | Description
--- | ---
created | Runs as soon as the Livewire component is instantiated
mounted | Runs immediately after the Livewire component has rendered for the first time
beforeUpdate | Runs before actions such as: `wire:click="addTodo"`
updated | Runs after actions

**Example**
```php
class Todos extends LivewireComponent
{
    public $todos;

    public function created()
    {
        $this->todos = Todo::all();
    }

    // ...
}
```
