# Lifecycle Hooks

Each Livewire component undergoes a lifecycle (created, updating, updated). Lifecycle hooks allow you to run code at any part of the component's lifecyle, or before specific actions are handled.

Hooks | Description
--- | ---
created | Runs immediately after the Livewire component is instantiated
updating | Runs before any update to the Livewire component
updatingEmail | Runs before a property called `$email` is updated
updated | Runs after any update to the Livewire component
updatedEmail | Runs after a property called `$email` is updated

**Example**
```php
class HelloWorld extends LivewireComponent
{
    public $email;

    public function created()
    {
        //
    }

    public function updating()
    {
        //
    }

    public function updatingEmail($value)
    {
        Validator::make(['email' => $value], ['email' => 'required'])->validate();
    }

    public function updatedEmail($value)
    {
        //
    }

    public function updated()
    {
        //
    }
}
```
