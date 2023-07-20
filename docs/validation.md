Livewire aims to make validating a user's input and giving them feedback as pleasant as possible. By building on top of Laravel's validation features, Livewire leverages your existing knowledge while also providing you with robust, additional features like real-time validation.

Here's an example `CreatePost` component that demonstrates the most basic validation workflow in Livewire:

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Post;

class CreatePost extends Component
{
	public $title = '';

    public $content = '';

    public function save()
    {
        $validated = $this->validate([ // [tl! highlight:3]
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

```blade
<form wire:submit="save">
	<input type="text" wire:model="title">
    <div>@error('title') {{ $message }} @enderror</div>

	<textarea wire:model="content"></textarea>
    <div>@error('content') {{ $message }} @enderror</div>

	<button type="submit">Save</button>
</form>
```

As you can see, Livewire provides a `validate()` method that you can call to validate your component's properties. It returns the validated set of data that you can then safely insert into the database.

On the frontend, you can use Laravel's existing Blade directives to show validation messages to your users.

For more information, see [Laravel's documentation on rendering validation errors in Blade](https://laravel.com/docs/blade#validation-errors).

## Rule attributes

If you prefer to co-locate your component's validation rules with the properties directly, you can use Livewire's `#[Rule]` attribute.

By associating validation rules with properties using `#[Rule]`, Livewire will automatically run the properties validation rules before each update. This frees you from needing to run `$this->validate()` manually:

```php
use Livewire\Attributes\Rule;
use Livewire\Component;
use App\Models\Post;

class CreatePost extends Component
{
    #[Rule('required|min:3')] // [tl! highlight]
	public $title = '';

    #[Rule('required|min:3')] // [tl! highlight]
    public $content = '';

    public function save()
    {
		Post::create([
            'title' => $this->title,
            'content' => $this->content,
		]);

		return redirect()->to('/posts');
    }

    // ...
}
```

If you prefer more control over when the properties are validated, you can pass a `onUpdate: false` parameter to the `#[Rule]` attribute. This will disabled any automatic validation and instead assume you want to manually validate the properties using the `$this->validated()` method:

```php
use Livewire\Attributes\Rule;
use Livewire\Component;
use App\Models\Post;

class CreatePost extends Component
{
    #[Rule('required|min:3', onUpdate: false)]
	public $title = '';

    #[Rule('required|min:3', onUpdate: false)]
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

When applying validation rules directly to a property using the `#[Rule]` attribute, Livewire assumes the validation key should be the name of the property itself. However, there are times when you may want to customize the validation key.

For example, you might want to provide separate validation rules for an array property and its children. In this case, instead of passing a validation rule as the first argument to the `#[Rule]` attribute, you can pass an array of key-value pairs instead:

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

Now, when a user updates `$todos`, or the `validate()` method is called, both of these validation rules will be applied.

## Form objects

As more properties and validation rules are added to a Livewire component, it can begin to feel too crowded. To alleviate this pain and also provide a helpful abstraction for code reuse, you can use Livewire's *Form Objects* to store your properties and validation rules.

Below is the same `CreatePost` example, but now the properties and rules have been extracted to a dedicated form object named `PostForm`:

```php
<?php

namespace App\Livewire\Forms;

use Livewire\Form;

class PostForm extends Form
{
    #[Rule('required|min:3')]
	public $title = '';

    #[Rule('required|min:3')]
    public $content = '';
}
```

The `PostForm` above can now be defined as a property on the `CreatePost` component:

```php
<?php

namespace App\Livewire;

use App\Livewire\Forms\PostForm;
use Livewire\Component;
use App\Models\Post;

class CreatePost extends Component
{
    public PostForm $form;

    public function save()
    {
		Post::create(
    		$this->form->all()
    	);

		return redirect()->to('/posts');
    }

    // ...
}
```

As you can see, instead of listing out each property individually, we can retrieve all the property values using the `->all()` method on the form object.

Also, when referencing the property names in the template, you must prepend `form.` to each instance:

```blade
<form wire:submit="save">
	<input type="text" wire:model="form.title">
    <div>@error('form.title') {{ $message }} @enderror</div>

	<textarea wire:model="form.content"></textarea>
    <div>@error('form.content') {{ $message }} @enderror</div>

	<button type="submit">Save</button>
</form>
```

When using form objects, `#[Rule]` attribute validation will be run every time a property is updated. However, if you disable this behavior by specifying `onUpdate: false` on the attribute, you can manually run a form object's validation using `$this->form->validate()`:

```php
public function save()
{
    Post::create(
        $this->form->validate()
    );

    return redirect()->to('/posts');
}
```

Form objects are a useful abstraction for most larger datasets and a variety of additional features that make them even more powerful. For more information, check out the comprehensive [form object documentation](/docs/forms#extracting-a-form-object).

## Real-time validation

Real-time validation is the term used for when you validate a user's input as they fill out a form rather than waiting for the form submission.

By using `#[Rule]` attributes directly on Livewire properties, any time a network request is sent to update a property's value on the server, the provided validation rules will be applied.

This means to provide a real-time validation experience for your users on a specific input, no extra backend work is required. The only thing that is required is using `wire:model.live` or `wire:model.blur` to instruct Livewire to trigger network requests as the fields are filled out.

In the below example, `wire:model.blur` has been added to the text input. Now, when a user types in the field and then tabs or clicks away from the field, a network request will be triggered with the updated value and the validation rules will run:

```blade
<form wire:submit="save">
    <input type="text" wire:model.blur="title">

    <!-- -->
</form>
```

## Customizing error messages

Out-of-the-box, Laravel provides sensible validation messages like "The title field is required." if the `$title` property has the `required` rule attached to it.

However, you may need to customize the language of these error messages to better suite your application and its users.

### Custom attribute names

Sometimes the property you are validating has a name that isn't suited for displaying to users. For example, if you have a database field in your app named `dob` that stands for "Date of birth", you would want to show your users "The date of birth field is required" instead of "The dob field is required".

Livewire allows you to specify an alternative name for a property using the `attribute: ` parameter:

```php
use Livewire\Attributes\Rule;

#[Rule('required', attribute: 'date of birth')]
public $dob = '';
```

Now, if the `required` validation rule fails, the error message will state "The date of birth field is required." instead of "The dob field is required.".

### Custom messages

If customizing the property name isn't enough, you can customize the entire validation message using the `message: ` parameter:

```php
use Livewire\Attributes\Rule;

#[Rule('required', message: 'Please fill out your date of birth.')]
public $dob = '';
```

If you have multiple rules to customize the message for, it is recommended that you use entirely separate `#[Rule]` attributes for each, like so:

```php
use Livewire\Attributes\Rule;

#[Rule('required', message: 'Please enter a title.')]
#[Rule('min:5', message: 'Your title is too short.')]
public $title = '';
```

If you want to use the `#[Rule]` attribute's array syntax instead, you can specify custom attributes and messages like so:

```php
use Livewire\Attributes\Rule;

#[Rule([
    'titles' => 'required',
    'titles.*' => 'required|min:5',
], message: [
    'required' => 'The :attribute is missing.',
    'titles.required' => 'The :attribute are missing.',
    'min' => 'The :attribute is too short.',
], attribute: [
    'titles.*' => 'title',
])]
public $titles = [];
```

## Manually controlling validation errors

Livewire's validation utilities should handle most common validation scenarios; however, there are times when you may want full control over the validation messages in your component.

Below are all the available methods for manipulating the validation errors in your Livewire component:

Method | Description
--- | ---
`$this->addError([key], [message])` | Manually add a validation message to the error bag
`$this->resetValidation([?key])` | Reset the validation errors for the provided key, or reset all errors if no key is supplied
`$this->getErrorBag()` | Retrieve the underlying Laravel error bag used in the Livewire component

## Accessing the validator instance

Sometimes you may want to access the Validator instance that Livewire uses internally in the `validate()` method. This is possible using the `withValidator` method. The closure you provide receives the fully constructed validator as an argument, allowing you to call any of its methods before the validation rules are actually evaluated.

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

    public function boot()
    {
        $this->withValidator(function ($validator) {
            $validator->after(function ($validator) {
                if (str($this->title)->startsWith('"')) {
                    $validator->errors()->add('title', 'Titles cannot start with quotations');
                }
            });
        });
    }

    public function save()
    {
		Post::create($this->all());

		return redirect()->to('/posts');
    }

    // ...
}
```

## Using custom validators

If you wish to use your own validation system in Livewire, that isn't a problem. Livewire will catch any `ValidationException` exceptions thrown inside of components and provide the errors to the view just as if you were using Livewire's own `validate()` method.

Below is an example of the `CreatePost` component, but instead of using Livewire's validation features, a completely custom validator is being created and applied to the component properties:

```php
use Illuminate\Validation\Validator;
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

Livewire provides useful testing utilities for validation scenarios, such as the `assertHasErrors()` method.

Below is a basic test case that ensures validation errors are thrown if no input is set for the `title` property:

```php
<?php

namespace Tests\Feature\Livewire;

use App\Livewire\CreatePost;
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

In addition to testing the presence of errors, `assertHasErrors` allows you to also narrow down the assertion to specific rules by passing the rules to assert against as the second argument to the method:

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

For more information on other testing utilities provided by Livewire, check out the [testing documentation](/docs/testing).
