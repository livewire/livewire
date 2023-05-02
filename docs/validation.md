Livewire aims to make the process of validating a user's input and giving them feedback as pleasant as possible. By building on-top of Laravel's validation features, Livewire leverages your existing knowledge, while also providing you with powerful, additional features like real-time validation.

Here's an example `CreatePost` component that demonstrates the most basic validation workflow in Livewire.

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Post;

class CreatePost extends Component
{
	public $title = '';

    public $content = '';

    public function save()
    {
        $validated = $this->validate([
			'title' => 'required|min:3',
			'content' => 'required|min:3',
        ]);

		Post::create($validated);

		return redirect()->to('/posts');
    }

    public function render()
    {
        return view('livewire.create-post');
    }
}
```

```html
<form wire:submit="save">
	<input type="text" wire:model="title">
    <div>@error('title') {{ $message }} @enderror</div>

	<textarea wire:model="content"></textarea>
    <div>@error('content') {{ $message }} @enderror</div>

	<button type="submit">Save</button>
</form>
```

As you can see, Livewire provides a `validate()` method that you can call to validate your component's properties. It returns the validated set of data that you can then use to insert into the database safely.

On the frontend, you can use Laravel's exising Blade directives for showing validation messages to your users.

For more information, see [Laravel's documentation on validation errors in Blade](https://laravel.com/docs/10.x/blade#validation-errors).

## Rule attributes

If you prefer to co-locate your component's validation rules with the properties directly, you can use Livewire's `#[Rule]` attribute.

The example below has the same behavior as above, however, 

Take a look at the example below, where because `#[Rule]` is being used now, you can call `validate()` directly without passing any parameters and it will use the component's `#[Rule]` attributes implicitly:

```php
use Livewire\Attributes\Rule;
use Livewire\Component;
use App\Models\Post;

class CreatePost extends Component
{
    #[Rule('required|min:3')]
	public $title = '';

    #[Rule('required|min:3')]
    public $content = '';

    public function save()
    {
        $validated = $this->validate();

		Post::create($validated);

		return redirect()->to('/posts');
    }

    // ...
}
```

### Custom key

When applying validation rules directly to a property using the `#[Rule]` attribute, Livewire assumes the validation key should be the name of the property itself. However, there are times where you might still want to customize the validation key.

For example, you might want to provide separate validation rules for both the property and its children. In this case, instead of passing a validation rule as the first parameter, you can pass an array of key-value pairs of validation rules:

```php
#[Rule([
    'todos' => 'required',
    'todos.*' => [
        'required',
        'min:3',
        new Uppercase,
    ],
])]
public $todos = [];
```

Now, when a user updates `todos` or the `validate()` method is called, both of these validation rules will be applied.

## Form objects

As more properties and validation rules are added to a Livewire component, it can feel too crowded.

To alleviate this pain and also provide a helpful abstraction for code re-use, you can use Livewire's *Form Objects* to store your properties and validation rules.

Below is the same `CreatePost` example, except this time the properties and rules have been extracted to a dedicated form object called `PostForm`:

```php
use Livewire\Form;

class PostForm extends Form
{
    #[Rule('required|min:3')]
	public $title = '';

    #[Rule('required|min:3')]
    public $content = '';   
}
```

The `PostForm` above can now be set directly as its own property on the `CreatePost` component:

```php
use Livewire\Component;
use App\Models\Post;

class CreatePost extends Component
{
    public PostForm $form;

    public function save()
    {
        $validated = $this->form->validate();

		Post::create($validated);

		return redirect()->to('/posts');
    }

    // ...
}
```

As you can see, instead of callling the `validate()` method on `$this`, when using form objects, you call `validate()` directly on the form object itself.

Now, when referencing the properties in the template, you must prepend `form.` to each instance:

```html
<form wire:submit="save">
	<input type="text" wire:model="form.title">
    <div>@error('form.title') {{ $message }} @enderror</div>

	<textarea wire:model="form.content"></textarea>
    <div>@error('form.content') {{ $message }} @enderror</div>

	<button type="submit">Save</button>
</form>
```

Form objects are a helpful abstraction for most larger datasets and have many more features to make them even more powerful.

For more information, checkout the [Form Object documentation](todo).

## Real-time validation

Real-time validation is the term used for when you validate a user's input as they fill out a form, rather than waiting for the form submission.

By using `#[Rule]` attributes directly on Livewire properties, any time a network request is sent to update a property's value on the server, the provided validation rules will be applied.

This means to provide a real-time validation experience for your users on a specific input, no extra backend work is required. The only thing that is required is using `wire:model.live` or `wire:model.lazy` to tell Livewire to trigger network requests as the fields are filled out.

In the below example, `wire:model.lazy` has been added to the text input. This means that when a user has tabbed or clicked away from this field--after typing into it--a network request will be triggered with the updated value and the validation rules will run:

```html
<form wire:submit="save">
    <input type="text" wire:model.lazy="title">

    <!-- -->
</form>
```

### Real-time validation without #[Rule]

If you want to provide real-time validation on a form field but don't want to use Livewire's `#[Rule]` attribute, you can manually achieve this by combing existing Livewire features.

In the example below, an update lifecycle hook called `updatingTitle()` is being used to intercept an update to the `title` property. Inside of it, the `validate()` method is called to perform validation, however you'll notice a subset of the validation rules are passed in, ensuring that only validation for the `title` field is run:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Post;

class CreatePost extends Component
{
	public $title = '';

    public $content = '';

    public updatingTitle()
    {
        $this->validate(['title' => 'required|min:3']);
    }

    public function save()
    {
        $validated = $this->validate([
			'title' => $this->title,
			'content' => $this->content,
        ]);

		Post::create($validated);

		return redirect()->to('/posts');
    }

    // ...
}
```

## Manually controlling validation errors

For the most part, Livewire's validation utilities should handle most scenarios, however there are times where you want full control over the validation messages in your component.

Below are all the available methods for manipulating the validation errors in your Livewire component:
Method | Description
--- | ---
`$this->addError([key], [message])` | Manually add a validation message to the error bag
`$this->resetValidation([?key])` | Resets the validation errors for the provided key, or resets all errors if no key is supplied
`$this->getErrorBag()` | Retrieves the underlying Laravel error bag used in the Livewire component

## Accessing the validator instance

Sometimes you may want to access the Validator instance that Livewire uses internally in the `validate()` method. This is possible using the `withValidator` method. The closure you provide receives the fully constructed validator as an argument, allowing you to call any of its methods before the validation rules are actually evaluated.

Below is an example of intercepting Livewire's internal validator to manually check a condition and add an additional validation message:

```php
use Livewire\Attributes\Rule;
use Livewire\Component;
use App\Models\Post;

class CreatePost extends Component
{
    #[Rule('required|min:3')]
	public $title = '';

    #[Rule('required|min:3')]
    public $content = '';   

    public function save()
    {
        $validated = $this->withValidator(function ($validator) {
            $validator->after(function ($validator) {
                if (str($this->title)->startsWith('"')) {
                    $validator->errors()->add('title', 'Titles cannot start with quotations');
                }
            });
        })->validate();

		Post::create($validated);

		return redirect()->to('/posts');
    }

    // ...
}
```

## Using custom validators

If you wish to use your own validation system in Livewire, that isn't a problem. Livewire will catch any `ValidationException` exceptions thrown inside of it and provide the errors to the view just as if you were using the `validate()` method.

Below is an example of the `CreatePost` component, but instead of using Livewire's validation features, a completely custom validator is being created and applied to the component properties:

```php
use Livewire\Attributes\Rule;
use Livewire\Component;
use App\Models\Post;

class CreatePost extends Component
{
	public $title = '';

    public $content = '';   

    public function save()
    {
        $validated = Validator::make(
            // Data to validate...
            ['title' => $this->title, 'content' => $this->content],

            // Validation rules to apply...
            ['title' => 'required|min:3', 'content' => 'required|min:3'],

            // Custom validation messages...
            ['required' => 'The :attribute field is required'],
         )->validate();
        
		Post::create($validated);

		return redirect()->to('/posts');
    }

    // ...
}
```

## Testing validation

Livewire provides useful testing utilities for validation scenarios such as the `assertHasErrors()` method.

Below is a basic test case that ensures validation errors are thrown if no input is set for the `title` property:

```php
<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\CreatePost;
use Livewire\Livewire;
use Tests\TestCase;

class CreatePostTest extends TestCase
{
    /** @test */
    public function cant_create_post_without_title()
    {
        Livewire::test(CreatePost::class)
            ->call('save')
            ->set('content', 'Sample content...')
            ->assertHasErrors('title');
    }
}
```

The above test uses Livewire's `assertHasErrors()` method to ensure that validation errors were thrown for `title`.

In addition to testing the presence of errors, `assertHasErrors` allows you to also narrow down the assertion to specific rules by passing the rules to assert against as the second argument:

```php
/** @test */
public function cant_create_post_with_title_shorter_than_3_characters()
{
    Livewire::test(CreatePost::class)
        ->set('title', 'Sa')
        ->set('content', 'Sample content...')
        ->call('save')
        ->assertHasErrors('title', ['min:3']);
}
```

You can also assert the presence of validation errors for multiple properties at the same time:

```php
/** @test */
public function cant_create_post_without_title_and_content()
{
    Livewire::test(CreatePost::class)
        ->call('save')
        ->assertHasErrors(['title', 'content']);
}
```

For more information on other available testing utilities, visit the [testing documentation](/testing).