# Input Validation

Consider the following Livewire component:

<div title="Component"><div title="Component__class">

ContactForm
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
</div><div title="Component__view">

contact-form.blade.php
```html
<div>
    Email: <input wire:model.lazy="email">

    <button wire:click="saveContact">Save Contact</button>
</div>
```
</div></div>

We can add validation to this form almost exactly how you would in a controller. Take a look:

<div title="Component"><div title="Component__class">

ContactForm
<div char="fade">

```php
class ContactForm extends LivewireComponent
{
    public $email;

    public function saveContact()
    {
```
</div>

```php
        $validatedData = $this->validate([
            'email' => 'required|email',
        ]);

        // Execution doesn't reach here if validation fails.

        Contact::create($validatedData);
```
<div char="fade">

```php
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
```
</div></div><div title="Component__view">

contact-form.blade.php
<div char="fade">

```html
<div>
    Email: <input wire:model.lazy="email">

```
</div>

```html
    @if($errors->has('email'))
        <span>{{ $errors->first('email') }}</span>
    @endif
```
<div char="fade">

```html
    <button wire:click="saveContact">Save Contact</button>
</div>
```
</div></div></div>

> Note: Livewire exposes the same `$errors` object as Laravel, for more information, reference the [Laravel Docs](https://laravel.com/docs/5.8/validation#quick-displaying-the-validation-errors).

## Custom validators

If you wish to use your own validation system in Livewire, that isn't a problem. Livewire will catch `ValidationException`s and provide the errors to the view just like using `$this->validate()`.

For example:

<div title="Component"><div title="Component__class">

ContactForm
<div char="fade">

```php
class ContactForm extends LivewireComponent
{
    public $email;

    public function saveContact()
    {
```
</div>

```php
        $validatedData = Validator::make(
            ['email' => $this->email],
            ['email' => 'required|email'],
            ['required' => 'The :attribute field is required'],
        ])->validate();

        Contact::create($validatedData);
```
<div char="fade">

```php
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
```
</div></div><div title="Component__view">

contact-form.blade.php
<div char="fade">

```html
<div>
    Email: <input wire:model.lazy="email">

```
</div>

```html
    @if($errors->has('email'))
        <span>{{ $errors->first('email') }}</span>
    @endif
```
<div char="fade">

```html
    <button wire:click="saveContact">Save Contact</button>
</div>
```
</div></div></div>
