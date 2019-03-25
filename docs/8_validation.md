# Form Validation

Consider the following Livewire component:

```php
class ContactForm extends LivewireComponent
{
    public $email;

    public function saveContact()
    {
        Contact::create(['email' => $this->email]);
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
```

```php
<div>
    Email: <input wire:model="email">

    <button wire:click="saveContact">Save Contact</button>
</div>
```

## Defining validation rules

You can define validation rules using the `$validates` class property in your component. All the standard Laravel validation rules are supported.

```php
class ContactForm extends LivewireComponent
{
    public $email;

    public $validates = [
        'email' => 'required|email',
    ];

    [...]
}
```

## Running the validator

To run this validator against your component's data, you can use a built-in function called `$this->validate()`.

```php
class ContactForm extends LivewireComponent
{
    public $email;

    public $validates = [
        'email' => 'required|email',
    ];

    public function saveContact()
    {
        Contact::create($this->validate())
    }
}
```

`$this->validate()` will run the rules provided by `$this->validates` and return an array of validated data, keyed by names.

You can optionally specify only specific data you want validated. For example:

```php
$this->validates = [
    'name' => 'required|min:6',
    'title' => 'required|min:8',
    'dob' => 'required',
];

// Will ignore "dob" validation.
$this->validate(['name', 'title']);
```

## Accessing validation errors

Dealing with Livewire validation errors should feel exactly like dealing with normal Laravel validation errors. Livewire provides the standard `$errors` object to every view, and you can access all the errors like you normall would.

```php
<div>
    Email: <input wire:model="email">

    @if($errors->has('email'))
        <span>{{ $errors->first('email') }}</span>
    @endif

    <button wire:click="saveContact">Save Contact</button>
</div>
```

## Custom validators

If you wish to use your own validation system in Livewire, that isn't a problem. Livewire will catch `ValidationException`s and provide the errors to the view just like using the stock `$this->validates()` method.

For example:

```php
class ContactForm extends LivewireComponent
{
    public $email;

    public function saveContact()
    {
        $validated = Validator::make(['email' => $this->email], [
            'email' => 'required|email',
        ])->validate();

        Contact::create($validated);
    }
}
```

Displaying errors in the view is the same as before.

```php
<div>
    Email: <input wire:model="email">

    @if($errors->has('email'))
        <span>{{ $errors->first('email') }}</span>
    @endif

    <button wire:click="saveContact">Save Contact</button>
</div>
```
