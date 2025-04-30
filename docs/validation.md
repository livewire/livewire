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

## Validate attributes

If you prefer to co-locate your component's validation rules with the properties directly, you can use Livewire's `#[Validate]` attribute.

By associating validation rules with properties using `#[Validate]`, Livewire will automatically run the properties validation rules before each update. However, you should still run `$this->validate()` before persisting data to a database so that properties that haven't been updated are also validated.

```php
use Livewire\Attributes\Validate;
use Livewire\Component;
use App\Models\Post;

class CreatePost extends Component
{
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

		return redirect()->to('/posts');
    }

    // ...
}
```

> [!info] Validate attributes don't support Rule objects
> PHP Attributes are restricted to certain syntaxes like plain strings and arrays. If you find yourself wanting to use run-time syntaxes like Laravel's Rule objects (`Rule::exists(...)`) you should instead [define a `rules()` method](#defining-a-rules-method) in your component.
>
> Learn more in the documentation on [using Laravel Rule objects with Livewire](#using-laravel-rule-objects).

If you prefer more control over when the properties are validated, you can pass a `onUpdate: false` parameter to the `#[Validate]` attribute. This will disable any automatic validation and instead assume you want to manually validate the properties using the `$this->validate()` method:

```php
use Livewire\Attributes\Validate;
use Livewire\Component;
use App\Models\Post;

class CreatePost extends Component
{
    #[Validate('required|min:3', onUpdate: false)]
	public $title = '';

    #[Validate('required|min:3', onUpdate: false)]
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

### Custom attribute name

If you wish to customize the attribute name injected into the validation message, you may do so using the `as: ` parameter:

```php
use Livewire\Attributes\Validate;

#[Validate('required', as: 'date of birth')]
public $dob;
```

When validation fails in the above snippet, Laravel will use "date of birth" instead of "dob" as the name of the field in the validation message. The generated message will be "The date of birth field is required" instead of "The dob field is required".

### Custom validation message

To bypass Laravel's validation message and replace it with your own, you can use the `message: ` parameter in the `#[Validate]` attribute:

```php
use Livewire\Attributes\Validate;

#[Validate('required', message: 'Please provide a post title')]
public $title;
```

Now, when the validation fails for this property, the message will be "Please provide a post title" instead of "The title field is required".

If you wish to add different messages for different rules, you can simply provide multiple `#[Validate]` attributes:

```php
#[Validate('required', message: 'Please provide a post title')]
#[Validate('min:3', message: 'This title is too short')]
public $title;
```

### Opting out of localization

By default, Livewire rule messages and attributes are localized using Laravel's translate helper: `trans()`.

You can opt-out of localization by passing the `translate: false` parameter to the `#[Validate]` attribute:

```php
#[Validate('required', message: 'Please provide a post title', translate: false)]
public $title;
```

### Custom key

When applying validation rules directly to a property using the `#[Validate]` attribute, Livewire assumes the validation key should be the name of the property itself. However, there are times when you may want to customize the validation key.

For example, you might want to provide separate validation rules for an array property and its children. In this case, instead of passing a validation rule as the first argument to the `#[Validate]` attribute, you can pass an array of key-value pairs instead:

```php
#[Validate([
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

use Livewire\Attributes\Validate;
use Livewire\Form;

class PostForm extends Form
{
    #[Validate('required|min:3')]
	public $title = '';

    #[Validate('required|min:3')]
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

When using form objects, `#[Validate]` attribute validation will be run every time a property is updated. However, if you disable this behavior by specifying `onUpdate: false` on the attribute, you can manually run a form object's validation using `$this->form->validate()`:

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

By using `#[Validate]` attributes directly on Livewire properties, any time a network request is sent to update a property's value on the server, the provided validation rules will be applied.

This means to provide a real-time validation experience for your users on a specific input, no extra backend work is required. The only thing that is required is using `wire:model.live` or `wire:model.blur` to instruct Livewire to trigger network requests as the fields are filled out.

In the below example, `wire:model.blur` has been added to the text input. Now, when a user types in the field and then tabs or clicks away from the field, a network request will be triggered with the updated value and the validation rules will run:

```blade
<form wire:submit="save">
    <input type="text" wire:model.blur="title">

    <!-- -->
</form>
```

If you are using a `rules()` method to declare your validation rules for a property instead of the `#[Validate]` attribute, you can still include a #[Validate] attribute with no parameters to retain the real-time validating behavior:

```php
use Livewire\Attributes\Validate;
use Livewire\Component;
use App\Models\Post;

class CreatePost extends Component
{
    #[Validate] // [tl! highlight]
	public $title = '';

    public $content = '';

    protected function rules()
    {
        return [
            'title' => 'required|min:5',
            'content' => 'required|min:5',
        ];
    }

    public function save()
    {
        $validated = $this->validate();

		Post::create($validated);

		return redirect()->to('/posts');
    }
```

Now, in the above example, even though `#[Validate]` is empty, it will tell Livewire to run the fields validation provided by `rules()` everytime the property is updated.

## Customizing error messages

Out-of-the-box, Laravel provides sensible validation messages like "The title field is required." if the `$title` property has the `required` rule attached to it.

However, you may need to customize the language of these error messages to better suite your application and its users.

### Custom attribute names

Sometimes the property you are validating has a name that isn't suited for displaying to users. For example, if you have a database field in your app named `dob` that stands for "Date of birth", you would want to show your users "The date of birth field is required" instead of "The dob field is required".

Livewire allows you to specify an alternative name for a property using the `as: ` parameter:

```php
use Livewire\Attributes\Validate;

#[Validate('required', as: 'date of birth')]
public $dob = '';
```

Now, if the `required` validation rule fails, the error message will state "The date of birth field is required." instead of "The dob field is required.".

### Custom messages

If customizing the property name isn't enough, you can customize the entire validation message using the `message: ` parameter:

```php
use Livewire\Attributes\Validate;

#[Validate('required', message: 'Please fill out your date of birth.')]
public $dob = '';
```

If you have multiple rules to customize the message for, it is recommended that you use entirely separate `#[Validate]` attributes for each, like so:

```php
use Livewire\Attributes\Validate;

#[Validate('required', message: 'Please enter a title.')]
#[Validate('min:5', message: 'Your title is too short.')]
public $title = '';
```

If you want to use the `#[Validate]` attribute's array syntax instead, you can specify custom attributes and messages like so:

```php
use Livewire\Attributes\Validate;

#[Validate([
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

## Defining a `rules()` method

As an alternative to Livewire's `#[Validate]` attributes, you can define a method in your component called `rules()` and return a list of fields and corresponding validation rules. This can be helpful if you are trying to use run-time syntaxes that aren't supported in PHP Attributes, for example, Laravel rule objects like `Rule::password()`.

These rules will then be applied when you run `$this->validate()` inside the component. You also can define the `messages()` and `validationAttributes()` functions.

Here's an example:

```php
use Livewire\Component;
use App\Models\Post;
use Illuminate\Validation\Rule;

class CreatePost extends Component
{
	public $title = '';

    public $content = '';

    protected function rules() // [tl! highlight:6]
    {
        return [
            'title' => Rule::exists('posts', 'title'),
            'content' => 'required|min:3',
        ];
    }

    protected function messages() // [tl! highlight:6]
    {
        return [
            'content.required' => 'The :attribute are missing.',
            'content.min' => 'The :attribute is too short.',
        ];
    }

    protected function validationAttributes() // [tl! highlight:6]
    {
        return [
            'content' => 'description',
        ];
    }

    public function save()
    {
        $this->validate();

		Post::create([
            'title' => $this->title,
            'content' => $this->content,
		]);

		return redirect()->to('/posts');
    }

    // ...
}
```

> [!warning] The `rules()` method doesn't validate on data updates
> When defining rules via the `rules()` method, Livewire will ONLY use these validation rules to validate properties when you run `$this->validate()`. This is different than standard `#[Validate]` attributes which are applied every time a field is updated via something like `wire:model`. To apply these validation rules to a property every time it's updated, you can still use `#[Validate]` with no extra parameters.

> [!warning] Don't conflict with Livewire's mechanisms
> While using Livewire's validation utilities, your component should **not** have properties or methods named `rules`, `messages`, `validationAttributes` or `validationCustomValues`, unless you're customizing the validation process. Otherwise, those will conflict with Livewire's mechanisms.

## Using Laravel Rule objects

Laravel `Rule` objects are an extremely powerful way to add advanced validation behavior to your forms.

Here is an example of using Rule objects in conjunction with Livewire's `rules()` method to achieve more sophisticated validation:

```php
<?php

namespace App\Livewire;

use Illuminate\Validation\Rule;
use App\Models\Post;
use Livewire\Form;

class UpdatePost extends Form
{
    public ?Post $post;

    public $title = '';

    public $content = '';

    protected function rules()
    {
        return [
            'title' => [
                'required',
                Rule::unique('posts')->ignore($this->post), // [tl! highlight]
            ],
            'content' => 'required|min:5',
        ];
    }

    public function mount()
    {
        $this->title = $this->post->title;
        $this->content = $this->post->content;
    }

    public function update()
    {
        $this->validate(); // [tl! highlight]

        $this->post->update($this->all());

        $this->reset();
    }

    // ...
}
```

## Manually controlling validation errors

Livewire's validation utilities should handle the most common validation scenarios; however, there are times when you may want full control over the validation messages in your component.

Below are all the available methods for manipulating the validation errors in your Livewire component:

Method | Description
--- | ---
`$this->addError([key], [message])` | Manually add a validation message to the error bag
`$this->resetValidation([?key])` | Reset the validation errors for the provided key, or reset all errors if no key is supplied
`$this->getErrorBag()` | Retrieve the underlying Laravel error bag used in the Livewire component

> [!info] Using `$this->addError()` with Form Objects
> When manually adding errors using `$this->addError` inside of a form object the key will automatically be prefixed with the name of the property the form is assigned to in the parent component. For example, if in your Component you assign the form to a property called `$data`, key will become `data.key`.

## Accessing the validator instance

Sometimes you may want to access the Validator instance that Livewire uses internally in the `validate()` method. This is possible using the `withValidator` method. The closure you provide receives the fully constructed validator as an argument, allowing you to call any of its methods before the validation rules are actually evaluated.

Below is an example of intercepting Livewire's internal validator to manually check a condition and add an additional validation message:

```php
use Livewire\Attributes\Validate;
use Livewire\Component;
use App\Models\Post;

class CreatePost extends Component
{
    #[Validate('required|min:3')]
	public $title = '';

    #[Validate('required|min:3')]
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
use Illuminate\Support\Facades\Validator;
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
    public function test_cant_create_post_without_title()
    {
        Livewire::test(CreatePost::class)
            ->set('content', 'Sample content...')
            ->call('save')
            ->assertHasErrors('title');
    }
}
```

In addition to testing the presence of errors, `assertHasErrors` allows you to also narrow down the assertion to specific rules by passing the rules to assert against as the second argument to the method:

```php
public function test_cant_create_post_with_title_shorter_than_3_characters()
{
    Livewire::test(CreatePost::class)
        ->set('title', 'Sa')
        ->set('content', 'Sample content...')
        ->call('save')
        ->assertHasErrors(['title' => ['min:3']]);
}
```

You can also assert the presence of validation errors for multiple properties at the same time:

```php
public function test_cant_create_post_without_title_and_content()
{
    Livewire::test(CreatePost::class)
        ->call('save')
        ->assertHasErrors(['title', 'content']);
}
```

For more information on other testing utilities provided by Livewire, check out the [testing documentation](/docs/testing).

## Deprecated `[#Rule]` attribute

When Livewire v3 first launched, it used the term "Rule" instead of "Validate" for it's validation attributes (`#[Rule]`).

Because of naming conflicts with Laravel rule objects, this has since been changed to `#[Validate]`. Both are supported in Livewire v3, however it is recommended that you change all occurrences of `#[Rule]` with `#[Validate]` to stay current.
