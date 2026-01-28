
Livewire components are simple to test. Because they are just Laravel classes under the hood, they can be tested using Laravel's existing testing tools. However, Livewire provides many additional utilities to make testing your components a breeze.

This documentation will guide you through testing Livewire components using **Pest** as the recommended testing framework, though you can also use PHPUnit if you prefer.

## Installing Pest

[Pest](https://pestphp.com/) is a delightful PHP testing framework with a focus on simplicity. It's the recommended way to test Livewire components in Livewire 4.

To install Pest in your Laravel application, first remove PHPUnit (if it's installed) and require Pest:

```shell
composer remove phpunit/phpunit
composer require pestphp/pest --dev --with-all-dependencies
```

Next, initialize Pest in your project:

```shell
./vendor/bin/pest --init
```

This will create a `tests/Pest.php` configuration file in your project.

For more detailed installation instructions, see the [Pest installation documentation](https://pestphp.com/docs/installation).

## Configuring Pest for view-based components

If you're writing tests alongside your view-based components (single-file or multi-file), you'll need to configure Pest to recognize these test files.

First, update your `tests/Pest.php` file to include the `resources/views` directory:

```php
pest()->extend(Tests\TestCase::class)
    // ...
    ->in('Feature', '../resources/views');
```

This tells Pest to use your `TestCase` base class for tests found in both the `tests/Feature` directory and anywhere within `resources/views`.

Next, update your `phpunit.xml` file to include a test suite for component tests:

```xml
<testsuite name="Components">
    <directory suffix=".test.php">resources/views</directory>
</testsuite>
```

Now Pest will recognize and run tests located next to your components when you run `./vendor/bin/pest`.

## Creating your first test

You can generate a test file alongside a component by appending the `--test` flag to the `make:livewire` command:

```shell
php artisan make:livewire post.create --test
```

For multi-file components, this will create a test file at `resources/views/components/post/create.test.php`:

```php
<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('post.create')
        ->assertStatus(200);
});
```

For class-based components, this creates a PHPUnit test file at `tests/Feature/Livewire/Post/CreateTest.php`. You can convert it to Pest syntax or keep using PHPUnit—both work great with Livewire.

### Testing a page contains a component

The simplest Livewire test you can write is asserting that a given endpoint includes and successfully renders a Livewire component.

```php
it('component exists on the page', function () {
    $this->get('/posts/create')
        ->assertSeeLivewire('post.create');
});
```

> [!tip] Smoke tests provide huge value
> Tests like these are called "smoke tests"—they ensure there are no catastrophic problems in your application. Although simple, these tests provide enormous value as they require very little maintenance and give you a base level of confidence that your pages render successfully.

## Browser testing

Pest v4 includes first-party browser testing support powered by Playwright. This allows you to test your Livewire components in a real browser, interacting with them just like a user would.

### Installing browser testing

First, install the Pest browser plugin:

```shell
composer require pestphp/pest-plugin-browser --dev
```

Next, install Playwright via npm:

```shell
npm install playwright@latest
npx playwright install
```

For complete browser testing documentation, see the [Pest browser testing guide](https://pestphp.com/docs/browser-testing).

### Writing browser tests

Instead of using `Livewire::test()`, you can use `Livewire::visit()` to test your component in a real browser:

```php
it('can create a new post', function () {
    Livewire::visit('post.create')
        ->type('[wire\:model="title"]', 'My first post')
        ->type('[wire\:model="content"]', 'This is the content')
        ->press('Save')
        ->assertSee('Post created successfully');
});
```

Browser tests are slower than unit tests but provide end-to-end confidence that your components work as expected in a real browser environment.

For a complete list of available browser testing assertions, see the [Pest browser testing assertions](https://pestphp.com/docs/browser-testing#content-available-assertions).

> [!info] When to use browser tests
> Use browser tests for critical user flows and complex interactions. For most component testing, the standard `Livewire::test()` approach is faster and sufficient.

## Testing views

Livewire provides `assertSee()` to verify that text appears in your component's rendered output:

```php
use App\Models\Post;

it('displays posts', function () {
    Post::factory()->create(['title' => 'My first post']);
    Post::factory()->create(['title' => 'My second post']);

    Livewire::test('show-posts')
        ->assertSee('My first post')
        ->assertSee('My second post');
});
```

### Asserting view data

Sometimes it's helpful to test the data being passed into the view rather than the rendered output:

```php
use App\Models\Post;

it('passes all posts to the view', function () {
    Post::factory()->count(3)->create();

    Livewire::test('show-posts')
        ->assertViewHas('posts', function ($posts) {
            return count($posts) === 3;
        });
});
```

For simple assertions, you can pass the expected value directly:

```php
Livewire::test('show-posts')
    ->assertViewHas('postCount', 3);
```

## Testing with authentication

Most applications require users to log in. Rather than manually authenticating at the beginning of each test, use the `actingAs()` method:

```php
use App\Models\User;
use App\Models\Post;

it('user only sees their own posts', function () {
    $user = User::factory()
        ->has(Post::factory()->count(3))
        ->create();

    $stranger = User::factory()
        ->has(Post::factory()->count(2))
        ->create();

    Livewire::actingAs($user)
        ->test('show-posts')
        ->assertViewHas('posts', function ($posts) {
            return count($posts) === 3;
        });
});
```

## Testing properties

Livewire provides utilities for setting and asserting component properties.

Use `set()` to update properties and `assertSet()` to verify their values:

```php
it('can set the title property', function () {
    Livewire::test('post.create')
        ->set('title', 'My amazing post')
        ->assertSet('title', 'My amazing post');
});
```

### Initializing properties

Components often receive data from parent components or route parameters. Pass this data as the second parameter to `Livewire::test()`:

```php
use App\Models\Post;

it('title field is populated when editing', function () {
    $post = Post::factory()->create([
        'title' => 'Existing post title',
    ]);

    Livewire::test('post.edit', ['post' => $post])
        ->assertSet('title', 'Existing post title');
});
```

### Setting URL parameters

If your component uses [Livewire's URL feature](/docs/4.x/url) to track state in query strings, use `withQueryParams()` to simulate URL parameters:

```php
use App\Models\Post;

it('can search posts via url query string', function () {
    Post::factory()->create(['title' => 'Laravel testing']);
    Post::factory()->create(['title' => 'Vue components']);

    Livewire::withQueryParams(['search' => 'Laravel'])
        ->test('search-posts')
        ->assertSee('Laravel testing')
        ->assertDontSee('Vue components');
});
```

### Setting cookies

Use `withCookie()` or `withCookies()` to set cookies for your tests:

```php
it('loads discount token from cookie', function () {
    Livewire::withCookies(['discountToken' => 'SUMMER2024'])
        ->test('cart')
        ->assertSet('discountToken', 'SUMMER2024');
});
```

## Calling actions

Use the `call()` method to trigger component actions in your tests:

```php
use App\Models\Post;

it('can create a post', function () {
    expect(Post::count())->toBe(0);

    Livewire::test('post.create')
        ->set('title', 'My new post')
        ->set('content', 'Post content here')
        ->call('save');

    expect(Post::count())->toBe(1);
});
```

> [!tip] Pest expectations
> The examples above use Pest's `expect()` syntax for assertions. For a complete list of available expectations, see the [Pest expectations documentation](https://pestphp.com/docs/expectations).

You can pass parameters to actions:

```php
Livewire::test('post.show')
    ->call('deletePost', $postId);
```

### Testing validation

Assert that validation errors have been thrown using `assertHasErrors()`:

```php
it('title field is required', function () {
    Livewire::test('post.create')
        ->set('title', '')
        ->call('save')
        ->assertHasErrors('title');
});
```

Test specific validation rules:

```php
it('title must be at least 3 characters', function () {
    Livewire::test('post.create')
        ->set('title', 'ab')
        ->call('save')
        ->assertHasErrors(['title' => ['min:3']]);
});
```

### Testing authorization

Ensure authorization checks work correctly using `assertUnauthorized()` and `assertForbidden()`:

```php
use App\Models\User;
use App\Models\Post;

it('cannot update another users post', function () {
    $user = User::factory()->create();
    $stranger = User::factory()->create();
    $post = Post::factory()->for($stranger)->create();

    Livewire::actingAs($user)
        ->test('post.edit', ['post' => $post])
        ->set('title', 'Hacked!')
        ->call('save')
        ->assertForbidden();
});
```

### Testing redirects

Assert that an action performed a redirect:

```php
it('redirects to posts index after creating', function () {
    Livewire::test('post.create')
        ->set('title', 'New post')
        ->set('content', 'Content here')
        ->call('save')
        ->assertRedirect('/posts');
});
```

You can also assert redirects to named routes or page components:

```php
->assertRedirect(route('posts.index'));
->assertRedirectToRoute('posts.index');
```

### Testing events

Assert that events were dispatched from your component:

```php
it('dispatches event when post is created', function () {
    Livewire::test('post.create')
        ->set('title', 'New post')
        ->call('save')
        ->assertDispatched('post-created');
});
```

Test event communication between components:

```php
it('updates post count when event is dispatched', function () {
    $badge = Livewire::test('post-count-badge')
        ->assertSee('0');

    Livewire::test('post.create')
        ->set('title', 'New post')
        ->call('save')
        ->assertDispatched('post-created');

    $badge->dispatch('post-created')
        ->assertSee('1');
});
```

Assert events were dispatched with specific parameters:

```php
it('dispatches notification when deleting post', function () {
    Livewire::test('post.show')
        ->call('delete', postId: 3)
        ->assertDispatched('notify', message: 'Post deleted');
});
```

For complex assertions, use a closure:

```php
it('dispatches event with correct data', function () {
    Livewire::test('post.show')
        ->call('delete', postId: 3)
        ->assertDispatched('notify', function ($event, $params) {
            return ($params['message'] ?? '') === 'Post deleted';
        });
});
```

## Using PHPUnit

While Pest is recommended, you can absolutely use PHPUnit to test Livewire components. All the same testing utilities work with PHPUnit's syntax.

Here's a PHPUnit example for comparison:

```php
<?php

namespace Tests\Feature\Livewire;

use Livewire\Livewire;
use App\Models\Post;
use Tests\TestCase;

class CreatePostTest extends TestCase
{
    public function test_can_create_post()
    {
        $this->assertEquals(0, Post::count());

        Livewire::test('post.create')
            ->set('title', 'My new post')
            ->set('content', 'Post content')
            ->call('save');

        $this->assertEquals(1, Post::count());
    }

    public function test_title_is_required()
    {
        Livewire::test('post.create')
            ->set('title', '')
            ->call('save')
            ->assertHasErrors('title');
    }
}
```

All features documented on this page work identically with PHPUnit—just use PHPUnit's assertion syntax instead of Pest's.

> [!tip] Consider trying Pest
> If you're interested in exploring Pest's more elegant syntax and features, check out [pestphp.com](https://pestphp.com/) to learn more.

## All available testing methods

Below is a comprehensive reference of every Livewire testing method available to you:

### Setup methods

| Method                                                  | Description                                                                                                      |
|---------------------------------------------------------|------------------------------------------------------------------------------------------------------------------|
| `Livewire::test('post.create')`                      | Test the `post.create` component |
| `Livewire::test(UpdatePost::class, ['post' => $post])`                      | Test the `UpdatePost` component with parameters passed to `mount()` |
| `Livewire::actingAs($user)`                      | Set the authenticated user for the test |
| `Livewire::withQueryParams(['search' => '...'])`                      | Set URL query parameters (ex. `?search=...`) |
| `Livewire::withCookie('name', 'value')`                      | Set a cookie for the test |
| `Livewire::withCookies(['color' => 'blue', 'name' => 'Taylor'])`                      | Set multiple cookies |
| `Livewire::withHeaders(['X-Header' => 'value'])`                      | Set custom headers |
| `Livewire::withoutLazyLoading()`                      | Disable lazy loading for all components in this test |

### Interacting with components

| Method                                                  | Description                                                                                                      |
|---------------------------------------------------------|------------------------------------------------------------------------------------------------------------------|
| `set('title', '...')`                      | Set the `title` property to the provided value |
| `set(['title' => '...', 'content' => '...'])`                      | Set multiple properties using an array |
| `toggle('sortAsc')`                      | Toggle a boolean property between `true` and `false`  |
| `call('save')`                      | Call the `save` action/method |
| `call('remove', $postId)`                      | Call a method with parameters |
| `refresh()`                      | Trigger a component re-render |
| `dispatch('post-created')`                      | Dispatch an event from the component  |
| `dispatch('post-created', postId: $post->id)`                      | Dispatch an event with parameters |

### Assertions

| Method                                                | Description                                                                                                                                                                          |
|-------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `assertSet('title', '...')`                           | Assert that a property equals the provided value                                                                                                                        |
| `assertNotSet('title', '...')`                        | Assert that a property does not equal the provided value                                                                                                                    |
| `assertCount('posts', 3)`                             | Assert that a property contains 3 items                                                                                                         |
| `assertSee('...')`                             | Assert that the rendered HTML contains the provided text                                                                                                           |
| `assertDontSee('...')`                         | Assert that the rendered HTML does not contain the provided text                                                                                                                    |
| `assertSeeHtml('<div>...</div>')`                     | Assert that raw HTML is present in the rendered output |
| `assertDontSeeHtml('<div>...</div>')`                 | Assert that raw HTML is not present in the rendered output                                                                                                                         |
| `assertSeeInOrder(['first', 'second'])`                    | Assert that strings appear in order in the rendered output                                                                                        |
| `assertDispatched('post-created')`                    | Assert that an event was dispatched                                                                                                                     |
| `assertNotDispatched('post-created')`                 | Assert that an event was not dispatched                                                                                                                 |
| `assertHasErrors('title')`                            | Assert that validation failed for a property                                                                                                                           |
| `assertHasErrors(['title' => ['required', 'min:6']])`   | Assert that specific validation rules failed                                                                                                            |
| `assertHasNoErrors('title')`                          | Assert that there are no validation errors for a property                                                                                                                  |
| `assertRedirect()`                                    | Assert that a redirect was triggered                                                                                                                  |
| `assertRedirect('/posts')`                            | Assert a redirect to a specific URL                                                                                                                   |
| `assertRedirectToRoute('posts.index')`       | Assert a redirect to a named route                                                                                                                    |
| `assertNoRedirect()`                                  | Assert that no redirect was triggered                                                                                                                                           |
| `assertViewHas('posts')`                              | Assert that data was passed to the view                                                                                                         |
| `assertViewHas('postCount', 3)`                       | Assert that view data has a specific value                                                                                                   |
| `assertViewHas('posts', function ($posts) { ... })`   | Assert view data passes custom validation                                                                         |
| `assertViewIs('livewire.show-posts')`                 | Assert that a specific view was rendered                                                                                                            |
| `assertFileDownloaded()`                              | Assert that a file download was triggered                                                                                                                                       |
| `assertFileDownloaded($filename)`                     | Assert that a specific file was downloaded                                                                                                       |
| `assertUnauthorized()`                                | Assert that an authorization exception was thrown (401)                                                                                       |
| `assertForbidden()`                                   | Assert that access was forbidden (403)                                                                                                                |
| `assertStatus(500)`                                   | Assert that a specific status code was returned                                                                                                                     |

## See also

- **[Actions](/docs/4.x/actions)** — Test component actions and interactions
- **[Forms](/docs/4.x/forms)** — Test form submissions and validation
- **[Events](/docs/4.x/events)** — Test event dispatching and listening
- **[Components](/docs/4.x/components)** — Create testable component structure
