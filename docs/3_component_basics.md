# Component Basics

## The render() method

There are two things to know:

1) It should return a plain-old Blade view
2) It runs every time the component updates

### Returning Blade
The `render()` method is expected to return a Blade view, therefore, you can compare it to writing a controller method. Here is an example:

<div title="Component">
<div title="Component__class">

ShowPosts.php
```php
class ShowPosts extends LivewireComponent
{
    public function render()
    {
        return view('livewire.show-posts', [
            'posts' => Post::all();
        ]);
    }
}
```
</div>
<div title="Component__view">

show-posts.blade.php
```html
<div>
    @foreach ($posts as $post)
        @include('includes.post', $post)
    @endforeach
</div>
```
</div>
</div>

<div title="Warning"><div title="Warning__content">

Although `render()` methods closely resemble controller methods, there are a few techniques you are used to using in controllers that aren't available in Livewire components.

Here are some common things you might forget ARE NOT possible in Livewire:

```php
public function render()
{
    return redirect()->to('/endpoint');
    // Or
    return back();
    // Or
    return ['some' => 'data'];
}
```
</div></div>

## Component Properties

Livewire components store and track state using class properties on the Component class. Here's what's important to know:

### Autmatically Available Inside View

Properties marked as `public` are automatically made available in the Blade view. For example:

<div title="Component">
<div title="Component__class">

HelloWorld.php
```php
class HelloWorld extends LivewireComponent
{
    public $message = 'Hello World';

    public function render()
    {
        // Notice we aren't passing "$message" into the view.
        return view('livewire.hello-world');
    }
}
```
</div>
<div title="Component__view">

hello-world.blade.php
```html
<div>
    <h1>{{ $message }}</h1>
    <!-- "Hello World" -->
</div>
```
</div>
</div>

### Initializing Properties

Let's say you wanted to make the 'Hello World' message more specific, and greet the currently logged in user. You might try setting the message to:

```php
public $message = 'Hello ' . auth()->user()->first_name;
```

Unfortunately, this is illegal in PHP. However, you can initialize properties at run-time using the `created` method/hook in Livewire. For example:

<div title="Component"><div title="Component__class">

HelloWorld.php
```php
class HelloWorld extends LivewireComponent
{
    public $message;

    public function created()
    {
        $this->message = 'Hello ' . auth()->user()->first_name;
    }

    public function render()
    {
        return view('livewire.hello-world');
    }
}
```
</div>
<div title="Component__view">

hello-world.blade.php
```html
<div>
    <h1>{{ $message }}</h1>
    <!-- "Hello Alex" -->
</div>
```
</div>
</div>

## Redirecting

You may want to redirect from inside a Livewire component to another route in your app. Livewire offers a simple `$this->redirect()` method to accomplish this:

<div title="Component"><div title="Component__class">

ContactForm.php
```php
class ContactForm extends LivewireComponent
{
    public $email;

    public function addContact()
    {
        Contact::create(['email' => $this->email]);

        $this->redirect('/contact-form-success');
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
```
</div>
<div title="Component__view">

contact-form.blade.php
```html
<div>
    Email: <input wire:model="email">

    <button wire:click="addContact">Submit</button>
</div>
```
</div>
</div>

Now, after the user clicks "Submit" and their contact is added to the database, they will be redirected to the success page (`/contact-form-success`).
