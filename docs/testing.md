
## Creating your first test

```shell
artisan make:livewire create-post --test
```

```php
<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\CreatePost;
use Livewire\Livewire;
use Tests\TestCase;

class CreatePostTest extends TestCase
{
    /** @test */
    public function the_component_renders()
    {
        Livewire::test(CreatePost::class)
            ->assertStatus(200);
    }
}
```

## Testing existance of a component

```php
<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\CreatePost;
use Livewire\Livewire;
use Tests\TestCase;

class CreatePostTest extends TestCase
{
    /** @test */
    public function create_post_page_contains_component()
    {
        $this->get('/post/create')
            ->assertSeeLivewire(CreatePost::class);
    }
}
```


## Asserting properties

```php
<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\CreatePost;
use Livewire\Livewire;
use Tests\TestCase;

class CreatePostTest extends TestCase
{
    /** @test */
    public function initial_properties_are_set()
    {
        Livewire::test(CreatePost::class)
            ->assertSet('title', '')
            ->assertSet('content', '');
    }
}
```


### Setting URL parameters

```php
<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\SearchPosts;
use Livewire\Livewire;
use Tests\TestCase;

class SearchPostsTest extends TestCase
{
    /** @test */
    public function can_search_posts_via_url_query_string()
    {
        Livewire::withUrlParams(['search' => 'A dog went walking'])
            ->test(SearchPosts::class);
    }
}
```

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

### Passing in properties

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
    public function create_post_page_contains_component()
    {
        $post = Post::factory()->make();

        Livewire::test(UpdatePost::class, ['post' => $post])
            ->assertSet('title', '')
            ->assertSet('content', '');
    }
}
```

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;

class UpdatePost extends Component
{
	public Post $post;

	public function mount(Post $post)
	{
		$this->post = $post;
	}

	// ...
}
```

## Asserting seeing

```php
<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\CreatePost;
use Livewire\Livewire;
use Tests\TestCase;

class CreatePostTest extends TestCase
{
    /** @test */
    public function create_post_page_contains_component()
    {
        $post = Post::factory()->make(['title' => 'Test post']);

        Livewire::test(ShowPost::class, ['post' => $post])
            ->assertSee('Test post');
    }
}
```

## Setting properties

```php
<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\CreatePost;
use Livewire\Livewire;
use Tests\TestCase;

class CreatePostTest extends TestCase
{
    /** @test */
    public function create_post_page_contains_component()
    {
        Livewire::test(CreatePost::class)
            ->set('title', 'Foo')
            ->set('content', 'Bar');
    }
}
```

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;

class CreatePost extends Component
{
	public $title = '';

    public $content = '';

    public function save()
    {
        Post::create([
            'title' => $this->title,
            'content' => $this->content,
        ]);

        return $this->redirect(ShowPosts::class);
    }

    public function render()
    {
        return view(livewire.create-post);
    }
}
```

## Calling actions

```php
<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\CreatePost;
use Livewire\Livewire;
use Tests\TestCase;

class CreatePostTest extends TestCase
{
    /** @test */
    public function create_post_page_contains_component()
    {
        Livewire::test(CreatePost::class)
            ->set('title', 'Foo')
            ->set('content', 'Bar')
            ->call('save');
    }
}
```

## Testing validation

```php
<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\CreatePost;
use Livewire\Livewire;
use Tests\TestCase;

class CreatePostTest extends TestCase
{
    /** @test */
    public function create_post_page_contains_component()
    {
        Livewire::test(CreatePost::class)
            ->set('title', 'Foo')
            ->set('content', 'Bar')
            ->call('save')
            ->assertHasErrors('title');
    }
}
```

## Testing authorization

```php
<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\CreatePost;
use Livewire\Livewire;
use Tests\TestCase;

class CreatePostTest extends TestCase
{
    /** @test */
    public function create_post_page_contains_component()
    {
        Livewire::test(CreatePost::class)
            ->set('title', 'Foo')
            ->set('content', 'Bar')
            ->call('save')
            ->assertUnauthorized();
->assertStatus(403); // or...
    }
}
```

## Asserting redirect

```php
<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\CreatePost;
use Livewire\Livewire;
use Tests\TestCase;

class CreatePostTest extends TestCase
{
    /** @test */
    public function create_post_page_contains_component()
    {
        Livewire::test(CreatePost::class)
            ->set('title', 'Foo')
            ->set('content', 'Bar')
            ->call('save')
            ->assertRedirect('/create-post');
            ->assertRedirect(CreatePost::class); // or...
    }
}
```

## Dispatching events

```php
<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\CreatePost;
use Livewire\Livewire;
use Tests\TestCase;

class CreatePostTest extends TestCase
{
    /** @test */
    public function create_post_page_contains_component()
    {
        Livewire::test(CreatePost::class)
            ->set('title', 'Foo')
            ->set('content', 'Bar')
            ->call('save')
            ->assertDispatched('post-created');
    }
}
```

## Listening for events

```php
<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\CreatePost;
use Livewire\Livewire;
use Tests\TestCase;

class CreatePostTest extends TestCase
{
    /** @test */
    public function create_post_page_contains_component()
    {
        Livewire::test(CreatePostNotification::class)
            ->dispatch('post-created')
            ->assertSee('something');
    }
}
```

## Asserting data from the view

```php
<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\CreatePost;
use Livewire\Livewire;
use Tests\TestCase;

class CreatePostTest extends TestCase
{
    /** @test */
    public function create_post_page_contains_component()
    {
        $post = Post::create(['title' => 'foo']);

        Livewire::test(ShowPosts::class)
            ->assertViewHas('posts', function ($posts) {
                $this->assertEquals(Post::count(), $posts->count());
            });
    }
}
```


## All available testing utilties

// table of methods

// table of assertions

