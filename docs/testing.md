
## Creating your first test

By appending the `--test` flag to the `make:livewire` command, you can generate a test file along with a component:

```shell
artisan make:livewire create-post --test
```

In addition to generating the component files themselves, the above command will generate the following test file `tests/Feature/Livewire/CreatePostTest.php`:

```php
<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\CreatePost;
use Livewire\Livewire;
use Tests\TestCase;

class CreatePostTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(CreatePost::class)
            ->assertStatus(200);
    }
}
```

You can always create these files by hand or use Livewire's testing utilities inside any other existing PHPUnit test in your Laravel application.

[You can read more about testing your Laravel applications here.](https://laravel.com/docs/10.x/testing)

## Testing a page contains a component

The most basic test you can write is asserting that a given endpoint in your application includes and successfully renders a given Livewire component.

Livewire provides an `assertHasLivewire` method that can be used from any Laravel test:

```php
<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\CreatePost;
use Tests\TestCase;

class CreatePostTest extends TestCase
{
    /** @test */
    public function component_exists_on_the_page()
    {
        $this->get('/post/create')
            ->assertHasLivewire(CreatePost::class);
    }
}
```

> [!tip] These are called smoke tests
> Smoke tests are broad tests that ensure no catastrophic problems in your application. Although it may seem like a test that isn't worth writing, pound for pound, these are some of the most valuable tests you can write as they require very little maintenance and provide you a base level of confidence that your app will render successfully with no major errors.

## Testing views

Livewire provides a simple yet powerful utility for asserting the existence of text in the component's rendered output: `assertSee`

Here's an example of using `assertSee` to ensure that all the posts in the database are displayed on the page:

```php
<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\ShowPosts;
use Livewire\Livewire;
use App\Models\Post;
use Tests\TestCase;

class ShowPostsTest extends TestCase
{
    /** @test */
    public function displays_posts()
    {
        Post::factory()->make(['title' => 'On bathing well']);
        Post::factory()->make(['title' => 'There\'s no time like bathtime']);

        Livewire::test(ShowPosts::class)
            ->assertSee('On bathing well')
            ->assertSee('There\'s no time like bathtime');
    }
}
```

### Asserting data from the view

In addition to asserting the output of a rendered view, sometimes it's helpful to alternatively test the data being passed into the view.

Here's the same test as above, this time testing view data rather than rendered output:

```php
<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\ShowPosts;
use Livewire\Livewire;
use App\Models\Post;
use Tests\TestCase;

class ShowPostsTest extends TestCase
{
    /** @test */
    public function displays_all_posts()
    {
        Post::factory()->make(['title' => 'On bathing well']);
        Post::factory()->make(['title' => 'The bathtub is my sanctuary']);

        Livewire::test(ShowPosts::class)
            ->assertViewHas('posts', function ($posts) {
                $this->assertEquals(2, count($posts));
            });
    }
}
```

As you can see, `->assertViewHas()` gives you lots of control over what assertions you want to make against the specified data. If you'd rather make a simple assertion, such as ensuring a piece of view data matches a given value, you can pass a value directly instead of a closure.

Assuming you have a component with a variable called `$postCount` being passed into the view, you can make assertions against its literal value like so:

```php
$this->assertViewHas('postCount', 3)
```

## Setting the authenticated user

Most web applications require users to log in before using them. Rather than manually authenticating a fake user at the beginning of your tests, Livewire provides an `actingAs` utility.

Here's an example of a test where multiple users have posts, yet the logged-in user should only see their own posts:

```php
<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\ShowPosts;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Post;
use Tests\TestCase;

class ShowPostsTest extends TestCase
{
    /** @test */
    public function user_only_sees_their_own_posts()
    {
        $user = User::factory()
            ->has(Post::factory()->count(3))
            ->create();

        $stranger = User::factory()
            ->has(Post::factory()->count(2))
            ->create();

        Livewire::actingAs($user)
            ->test(ShowPosts::class)
            ->assertViewHas('posts', function ($posts) {
                $this->assertEquals(3, count($posts));
            });
    }
}
```

## Testing properties

Livewire provides helpful utilities for setting and asserting properties within your components.

Component properties are typically updated in your application when users interact with form inputs containing `wire:model`. Because these tests don't type into an actual browser, Livewire allows you to set properties directly using the `->set()` method.

Here's an example of using `->set()` to update the `$title` property of a `CreatePosts` component:

```php
<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\CreatePost;
use Livewire\Livewire;
use Tests\TestCase;

class CreatePostTest extends TestCase
{
    /** @test */
    public function can_set_title()
    {
        Livewire::test(CreatePost::class)
            ->set('title', 'Confessions of a serial soaker')
            ->assertSet('title', 'Confessions of a serial soaker');
    }
}
```

The above example simulates a user typing into an input field with `wire:model="title"` on it.

### Initializing properties

Often, Livewire components receive data being passed in from a parent component or route parameters. Because Livewire components are tested in isolation, you can manually pass data into them using the second parameter of the `Livewire::test()` method like so:

```php
<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\UpdatePost;
use Livewire\Livewire;
use App\Models\Post;
use Tests\TestCase;

class UpdatePostTest extends TestCase
{
    /** @test */
    public function title_field_is_populated()
    {
        $post = Post::factory()->make(
            'title' => 'Top ten bath bombs'
        );

        Livewire::test(UpdatePost::class, ['post' => $post])
            ->assertSet('title', 'Top ten bath bombs');
    }
}
```

The underlying component being tested (`UpdatePost`) will receive `$post` through its `mount()` method.

Here's the source for `UpdatePost` to give you a clear picture:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Post;

class UpdatePost extends Component
{
	public Post $post;

    public $title = '';

	public function mount(Post $post)
	{
		$this->post = $post;

		$this->title = $post->title;
	}

	// ...
}
```

The `$post` model that was passed into the component from the test will now be received inside `mount()` and assigned as properties of the component.

### Setting URL parameters

If your Livewire component depends on specific query parameters in the URL of the page it's loaded on, you can use the `withUrlParams()` method to set them manually for your test.

Here is a basic `SearchPosts` component that uses [Livewire's URL feature](/docs/url) to store and track the current search query in the query string:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\With\Url;
use App\Models\Post;

class SearchPosts extends Component
{
    #[Url]
    public $search = '';

    public function render()
    {
        return view('livewire.search-posts', [
            'posts' => Post::search($this->search)->get(),
        ]);
    }
}
```

As you can see, the `$search` property above uses Livewire's `#[Url]` attribute to denote that its value should be stored in the URL.

Below is how you would simulate the scenario of loading this component on a page with specific query parameters in the URL:

```php
<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\SearchPosts;
use Livewire\Livewire;
use App\Models\Post;
use Tests\TestCase;

class SearchPostsTest extends TestCase
{
    /** @test */
    public function can_search_posts_via_url_query_string()
    {
        Post::factory()->create(['title' => 'Testing the first water-proof hair dryer']);
        Post::factory()->create(['title' => 'Rubber duckies that actually float']);

        Livewire::withUrlParams(['search' => 'hair'])
            ->test(SearchPosts::class)
            ->assertSee('Testing the first')
            ->assertDontSee('Rubber duckies');
    }
}
```

## Calling actions

Livewire actions are typically called from the frontend using something like `wire:click`.

Because Livewire component tests don't use an actual browser, you can instead trigger actions in your tests using the `->call()` method.

Here's an example of a `CreatePosts` component using the `call()` method to trigger the `save()` action:

```php
<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\CreatePost;
use Livewire\Livewire;
use App\Models\Post;
use Tests\TestCase;

class CreatePostTest extends TestCase
{
    /** @test */
    public function can_create_post()
    {
        $this->assertEquals(0, Post::count());

        Livewire::test(CreatePost::class)
            ->set('title', 'Wrinkly fingers? Try this one weird trick')
            ->set('content', '...')
            ->call('save');

        $this->assertEquals(1, Post::count());
    }
}
```

In the above test, we assert that calling `save()` creates a new post in the database.

You can also pass parameters to actions by passing additional parameters into the `->call()` method. For example:

```php
->call('deletePost', $postId);
```

### Validation

To test that a validation error has been thrown, you can use Livewire's `assertHasErrors`:

```php
<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\CreatePost;
use Livewire\Livewire;
use Tests\TestCase;

class CreatePostTest extends TestCase
{
    /** @test */
    public function title_field_is_required()
    {
        Livewire::test(CreatePost::class)
            ->set('title', '')
            ->call('save')
            ->assertHasErrors('title');
    }
}
```

If you want to test that a specific validation rule has failed, you can pass an array of rules as a second argument to `assertHasErrors`:

```php
$this->assertHasErrors('title', ['required']);
```

### Authorization

Authorizing actions relying on untrusted input in your Livewire components is essential. [Read more about authorization in Livewire here](/docs/properties#authorizing-the-input).

Livewire provides an `assertUnauthorized()` testing method to ensure that an authorization check has failed and a harmful action has been prevented:

```php
<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\UpdatePost;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Post;
use Tests\TestCase;

class UpdatePostTest extends TestCase
{
    /** @test */
    public function cant_update_another_users_post()
    {
        $user = User::factory()->create();
        $stranger = User::factory()->create();

        $post = Post::factory()->for($stranger)->create();

        Livewire::actingAs($user)
            ->test(UpdatePost::class, ['post' => $post])
            ->set('title', 'Living the lavender life')
            ->call('save')
            ->assertUnauthorized();
    }
}
```

If you prefer, you can also test for explicit status codes that an action in your component may have triggered using `assertStatus()`:

```php
->assertStatus(403);
```

### Redirects

You can test that a Livewire action performed a redirect by using the `assertRedirect()` method:

```php
<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\CreatePost;
use Livewire\Livewire;
use Tests\TestCase;

class CreatePostTest extends TestCase
{
    /** @test */
    public function redirected_to_all_posts_after_creating_a_post()
    {
        Livewire::test(CreatePost::class)
            ->set('title', 'Using a loofah doesn\'t make you aloof...ugh')
            ->set('content', '...')
            ->call('save')
            ->assertRedirect('/posts');
    }
}
```

As an added convenience, you can assert that the user was redirected to a specific page component, instead of a hard-coded URL.

```php
->assertRedirect(CreatePost::class);
```

### Events

To assert that an event was dispatched from within your component, you can use the `->assertDispatched()` method:

```php
<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\CreatePost;
use Livewire\Livewire;
use Tests\TestCase;

class CreatePostTest extends TestCase
{
    /** @test */
    public function creating_a_post_dispatches_event()
    {
        Livewire::test(CreatePost::class)
            ->set('title', 'Top 100 bubble bath brands')
            ->set('content', '...')
            ->call('save')
            ->assertDispatched('post-created');
    }
}
```

It is often helpful to test that two components can communicate with each other by dispatching and listening for events.

Below is an example of simulating a `CreatePost` component dispatching a `create-post` event and a `PostCountBadge` component listening for that event and updating its count.

To simulate an event dispatch and trigger a listener on a component, you can use the `->dispatch()` method:

```php
<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\PostCountBadge;
use App\Http\Livewire\CreatePost;
use Livewire\Livewire;
use Tests\TestCase;

class PostCountBadgeTest extends TestCase
{
    /** @test */
    public function post_count_is_updated_when_event_is_dispatched()
    {
        $badge = Livewire::test(PostCountBadge::class)
            ->assertSee("0");

        Livewire::test(CreatePost::class)
            ->set('title', 'Tear-free: the greatest lie ever told')
            ->set('content', '...')
            ->call('save')
            ->assertDispatched('post-created');

        $badge->dispatch('post-created')
            ->assertSee("1");
    }
}
```

## All available testing utilities

There are many more testing utilities that Livewire provides. Below is a comprehensive list of every testing method available to you, with a short description of how it's intended to be used:

### Setup methods
| Method                                                  | Description                                                                                                      |
|---------------------------------------------------------|------------------------------------------------------------------------------------------------------------------|
| `Livewire::test(CreatePost::class)`                      | Test the `CreatePost` component |
| `Livewire::test(UpdatePost::class, ['post' => $post])`                      | Test the `UpdatePost` component with the `post` parameter (To be received through the `mount()` method) |
| `Livewire::actingAs($user)`                      | Set the provided user as the session's logged in user |
| `Livewire::withQueryParams(['search' => '...'])`                      | Set the test's URL query parameter `search` to the provided value (ex. `?search=...`). Typically in the context of a property using Livewire's [`#[Url]` attribute](/docs/url) | 

### Interacting with components
| Method                                                  | Description                                                                                                      |
|---------------------------------------------------------|------------------------------------------------------------------------------------------------------------------|
| `set('title', '...')`                      | Set the `title` property to the provided value |
| `toggle('sortAsc')`                      | Toggle the `sortAsc` property between `true` and `false`  |
| `call('save')`                      | Call the `save` action/method |
| `call('remove', $post->id)`                      | Call the "remove" method, and pass the `$post->id` as the first parameter (Accepts subsequent parameters as well) |
| `refresh()`                      | Trigger a component re-render |
| `dispatch('post-created')`                      | Dispatches the `post-created` event onto the component  |
| `dispatch('post-created', $post->id)`                      | Dispatches the `post-created` event with `$post->id` as an additional parameter (`$event.detail` from AlpineJS) |

### Assertions
| Method                                                  | Description                                                                                                      |
|---------------------------------------------------------|------------------------------------------------------------------------------------------------------------------|
| `assertSet('title', '...')`                      | Assert that the `title` property is set to the provided value |
| `assertNotSet('title', '...')`                   | Assert that the `title` property is NOT set to the provided value |
| `assertCount('posts', 3)`                    | Assert that the `posts` property is an array-like value with `3` items in it |
| `assertSnapshotSet('date', '08/26/1990')`               | Assert that the `date` property's raw/dehydrated value (from JSON) is set to `08/26/1990`. Instead of asserting against the hydrated `DateTime` instance in the case of `date` |
| `assertSnapshotNotSet('date', '08/26/1990')`            | Assert that `date`'s raw/dehydrated value is NOT equal to the provided value |
| `assertSee($post->title)`                                    | Assert that the rendered HTML of the component contains the provided value |
| `assertDontSee($post->title)`                                | Assert that the rendered HTML does NOT contain the provided value |
| `assertSeeHtml('<div>...</div>')`          | Assert the provided string literal is contained in the rendered HTML without escaping the HTML characters (unlike `assertSee`, which DOES escape the provided characters by default)  |
| `assertDontSeeHtml('<div>...</div>')`                            | Assert the provided string is contained in the rendered HTML |
| `assertSeeInOrder(['...', '...'])`       | Assert that the provided strings appear in order in the rendered HTML output of the component |
| `assertSeeHtmlInOrder([$firstString, $secondString])`   | Assert that the provided HTML strings appear in order in the rendered output of the component |
| `assertDispatched('post-created')`              | Assert the given event has been dispatched by the component |
| `assertNotDispatched('post-created')`              | Assert the given event has NOT been dispatched by the component |
| `assertHasErrors('title')`                        | Assert that validation has failed for the `title` property |
| `assertHasErrors('title', ['required', 'min:6'])` | Assert that the provided validation rules failed for the `title` property |
| `assertHasNoErrors('title')`                      | Assert that there are no validation errors for the `title` property |
| `assertHasNoErrors('title', ['required', 'min:6'])`| Assert that the provided validation rules haven't failed for the `title` property |
| `assertRedirect()`                                      | Assert that a redirect has been triggered from within the component |
| `assertRedirect('/posts')`                                  | Assert the component triggered a redirect to the `/posts` endpoint |
| `assertRedirect(ShowPosts::class)`               | Assert that the component triggered a redirect to the `ShowPosts` component |
| `assertNoRedirect()`                                  | Assert that no redirect has been triggered |
| `assertViewHas('posts')`                           | Assert that the `render()` method has passed a `posts` item to the view data |
| `assertViewHas('postCount', 3)`           | Assert that a `postCount` variable has been passed to the view with a value of `3` |
| `assertViewHas('posts', function ($posts) { ... })` | Assert that `postCount` view data exists and that it passes any assertions declared in the provided callback |
| `assertViewIs('livewire.show-posts')`     | Assert that the component's render method returned the provided view name |
| `assertFileDownloaded()`             | Assert that a file download has been triggered |``
| `assertFileDownloaded($filename)`             | Assert that a file download matching the provided file name has been triggered |``
| `assertUnauthorized()`             | Assert that an authorization exception has been thrown within the component (status code: 401) |``
| `assertForbidden()`             | Assert that an error response was triggered with the status code: 403 |``
| `assertStatus(500)`             | Assert that the latest response matches the provided status code |``
