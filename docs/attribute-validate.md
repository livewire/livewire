The `#[Validate]` attribute associates validation rules with component properties, enabling automatic real-time validation and clean rule declaration.

## Basic usage

Apply the `#[Validate]` attribute to properties that need validation:

```php
<?php // resources/views/components/post/⚡create.blade.php

use Livewire\Attributes\Validate;
use Livewire\Component;
use App\Models\Post;

new class extends Component {
    #[Validate('required|min:3')] // [tl! highlight]
    public $title = '';

    #[Validate('required|min:3')] // [tl! highlight]
    public $content = '';

    public function save()
    {
        $this->validate();

        Post::create([
            'title' => $this->title,
            'content' => $this->content,
        ]);

        return redirect('/posts');
    }
};
?>

<div>
    <input type="text" wire:model="title">
    @error('title') <span class="error">{{ $message }}</span> @enderror

    <textarea wire:model="content"></textarea>
    @error('content') <span class="error">{{ $message }}</span> @enderror

    <button wire:click="save">Save Post</button>
</div>
```

With `#[Validate]`, Livewire automatically validates properties on each update, providing instant feedback to users.

## How it works

When you add `#[Validate]` to a property:

1. **Automatic validation** - Property is validated every time it's updated
2. **Real-time feedback** - Users see validation errors immediately
3. **Manual validation** - You still call `$this->validate()` before saving to ensure all properties are validated

## Real-time validation

By default, `#[Validate]` validates properties as they're updated:

```php
<?php // resources/views/components/⚡registration.blade.php

use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {
    #[Validate('required|email|unique:users,email')]
    public $email = '';

    #[Validate('required|min:8')]
    public $password = '';
};
?>

<div>
    <input type="email" wire:model.blur="email">
    @error('email') <span>{{ $message }}</span> @enderror

    <input type="password" wire:model.blur="password">
    @error('password') <span>{{ $message }}</span> @enderror
</div>
```

As users fill out the form, they receive immediate validation feedback.

## Disabling auto-validation

To validate only when explicitly calling `$this->validate()`, use `onUpdate: false`:

```php
<?php // resources/views/components/post/⚡create.blade.php

use Livewire\Attributes\Validate;
use Livewire\Component;
use App\Models\Post;

new class extends Component {
    #[Validate('required|min:3', onUpdate: false)] // [tl! highlight]
    public $title = '';

    #[Validate('required|min:3', onUpdate: false)] // [tl! highlight]
    public $content = '';

    public function save()
    {
        $validated = $this->validate();
        Post::create($validated);
        return redirect('/posts');
    }
};
```

Now validation only runs when `save()` is called, not on every property update.

## Custom attribute names

Customize the field name in validation messages:

```php
#[Validate('required', as: 'date of birth')] // [tl! highlight]
public $dob;
```

Error message will be "The date of birth field is required" instead of "The dob field is required".

## Custom validation messages

Override default validation messages:

```php
#[Validate('required', message: 'Please provide a post title')] // [tl! highlight]
public $title;
```

For multiple rules, use multiple attributes:

```php
#[Validate('required', message: 'Please provide a post title')]
#[Validate('min:3', message: 'This title is too short')]
public $title;
```

## Array validation

Validate array properties and their children:

```php
<?php // resources/views/components/⚡task-list.blade.php

use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {
    #[Validate([
        'tasks' => 'required|array|min:1',
        'tasks.*' => 'required|string|min:3',
    ])]
    public $tasks = [];

    public function addTask()
    {
        $this->tasks[] = '';
    }
};
```

This validates both the array itself and each individual task.

## Limitations

> [!warning] Rule objects not supported
> PHP attributes can't use Laravel's Rule objects directly. For complex rules like `Rule::exists()`, use a `rules()` method instead:
>
> ```php
> protected function rules()
> {
>     return [
>         'email' => ['required', 'email', Rule::unique('users')->ignore($this->userId)],
>     ];
> }
> ```

## When to use

Use `#[Validate]` when:

* Building forms with real-time validation feedback
* Co-locating validation rules with property definitions
* Creating simple, readable validation logic
* Implementing inline validation for better UX

Use `rules()` method when:

* You need Laravel's Rule objects
* Rules depend on dynamic values
* You're working with complex conditional validation
* You prefer centralized rule definition

## Example: Contact form

Here's a complete contact form with validation:

```php
<?php // resources/views/pages/⚡contact.blade.php

use Livewire\Attributes\Validate;
use Livewire\Component;
use App\Mail\ContactMessage;
use Illuminate\Support\Facades\Mail;

new class extends Component {
    #[Validate('required|min:2', as: 'name')]
    public $name = '';

    #[Validate('required|email')]
    public $email = '';

    #[Validate('required')]
    public $subject = '';

    #[Validate('required|min:10', as: 'message')]
    public $message = '';

    public function submit()
    {
        $validated = $this->validate();

        Mail::to('support@example.com')->send(new ContactMessage($validated));

        session()->flash('success', 'Message sent successfully!');

        $this->reset();
    }
};
?>

<div>
    @if (session('success'))
        <div class="alert">{{ session('success') }}</div>
    @endif

    <form wire:submit="submit">
        <div>
            <input type="text" wire:model.blur="name" placeholder="Your name">
            @error('name') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div>
            <input type="email" wire:model.blur="email" placeholder="Your email">
            @error('email') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div>
            <input type="text" wire:model.blur="subject" placeholder="Subject">
            @error('subject') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div>
            <textarea wire:model.blur="message" placeholder="Your message"></textarea>
            @error('message') <span class="error">{{ $message }}</span> @enderror
        </div>

        <button type="submit">Send Message</button>
    </form>
</div>
```

Users get immediate feedback as they fill out the form, with friendly field names and helpful error messages.

## Learn more

For comprehensive documentation on validation, including form objects, custom rules, and testing, see the [Validation documentation](/docs/4.x/validation).

## Reference

```php
#[Validate(
    mixed $rule = null,
    ?string $attribute = null,
    ?string $as = null,
    mixed $message = null,
    bool $onUpdate = true,
    bool $translate = true,
)]
```

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$rule` | `mixed` | `null` | The validation rule(s) to apply |
| `$attribute` | `?string` | `null` | Custom attribute name for validation error messages |
| `$as` | `?string` | `null` | Friendly name to display in validation error messages |
| `$message` | `mixed` | `null` | Custom error message(s) for validation failures |
| `$onUpdate` | `bool` | `true` | Whether to run validation when the property is updated |
| `$translate` | `bool` | `true` | Whether to translate validation messages |
