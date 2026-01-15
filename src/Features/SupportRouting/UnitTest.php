<?php

namespace Livewire\Features\SupportRouting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\Livewire;
use Sushi\Sushi;
use Tests\TestCase;

class UnitTest extends TestCase
{
    public function test_can_route_to_a_class_based_component_from_standard_route()
    {
        Route::get('/component-for-routing', ComponentForRouting::class);

        $this
            ->withoutExceptionHandling()
            ->get('/component-for-routing')
            ->assertSee('Component for routing');
    }

    public function test_can_use_livewire_macro_to_route_directory_to_class_based_components()
    {
        Route::livewire('/component-for-routing', ComponentForRouting::class);

        $this
            ->withoutExceptionHandling()
            ->get('/component-for-routing')
            ->assertSee('Component for routing');
    }

    public function test_can_use_livewire_macro_to_define_routes()
    {
        Livewire::component('component-for-routing', ComponentForRouting::class);

        Route::livewire('/component-for-routing', 'component-for-routing');

        $this
            ->withoutExceptionHandling()
            ->get('/component-for-routing')
            ->assertSee('Component for routing');
    }

    public function test_can_use_livewire_macro_with_anonymous_component_to_define_routes()
    {
        Route::livewire('/component-for-routing', new class extends Component {
            public function render()
            {
                return '<div>Component for routing</div>';
            }
        });

        $this
            ->withoutExceptionHandling()
            ->get('/component-for-routing')
            ->assertSee('Component for routing');
    }

    public function test_can_use_livewire_macro_with_auto_discvovered_single_file_component()
    {
        app('livewire.finder')->addLocation(viewPath: __DIR__ . '/fixtures');

        Route::livewire('/component-for-routing', 'sfc-counter');

        $this
            ->withoutExceptionHandling()
            ->get('/component-for-routing')
            ->assertSee('Count: 1');
    }

    public function test_route_parameters_are_passed_to_component()
    {
        Route::livewire('/route-with-params/{myId}', ComponentForRoutingWithParams::class);

        $this->get('/route-with-params/123')->assertSeeText('123');
    }

    public function test_can_use_authorization_middleware_with_route_livewire_macro_and_class_component()
    {
        Gate::policy(RoutingPost::class, RoutingPostPolicy::class);

        Route::livewire('/posts/{post}', ComponentWithPost::class)
            ->middleware(['web', 'can:view,post']);

        // User 1 owns Post 1 and should be allowed
        $this->actingAs(RoutingUser::find(1))
            ->get('/posts/1')
            ->assertOk()
            ->assertSee('Post: First');

        // User 2 does not own Post 1 and should be denied
        $this->actingAs(RoutingUser::find(2))
            ->get('/posts/1')
            ->assertForbidden();
    }

    public function test_can_use_authorization_middleware_with_route_livewire_macro_and_string_component()
    {
        Gate::policy(RoutingPost::class, RoutingPostPolicy::class);

        Livewire::component('component-with-post', ComponentWithPost::class);

        Route::livewire('/posts/{post}', 'component-with-post')
            ->middleware(['web', 'can:view,post']);

        // User 1 owns Post 1 and should be allowed
        $this->actingAs(RoutingUser::find(1))
            ->get('/posts/1')
            ->assertOk()
            ->assertSee('Post: First');

        // User 2 does not own Post 1 and should be denied
        $this->actingAs(RoutingUser::find(2))
            ->get('/posts/1')
            ->assertForbidden();
    }

    public function test_can_use_authorization_middleware_with_route_livewire_macro_and_anonymous_component()
    {
        Gate::policy(RoutingPost::class, RoutingPostPolicy::class);

        Route::livewire('/posts/{post}', new class extends Component {
            public RoutingPost $post;

            public function render()
            {
                return '<div>Post: {{ $post->title }}</div>';
            }
        })->middleware(['web', 'can:view,post']);

        // User 1 owns Post 1 and should be allowed
        $this->actingAs(RoutingUser::find(1))
            ->get('/posts/1')
            ->assertOk()
            ->assertSee('Post: First');

        // User 2 does not own Post 1 and should be denied
        $this->actingAs(RoutingUser::find(2))
            ->get('/posts/1')
            ->assertForbidden();
    }

    public function test_can_use_authorization_middleware_with_route_livewire_macro_and_single_file_component()
    {
        Gate::policy(RoutingPost::class, RoutingPostPolicy::class);

        app('livewire.finder')->addLocation(viewPath: __DIR__ . '/fixtures');

        Route::livewire('/posts/{post}', 'sfc-post')
            ->middleware(['web', 'can:view,post']);

        // User 1 owns Post 1 and should be allowed
        $this->actingAs(RoutingUser::find(1))
            ->get('/posts/1')
            ->assertOk()
            ->assertSee('Post: First');

        // User 2 does not own Post 1 and should be denied
        $this->actingAs(RoutingUser::find(2))
            ->get('/posts/1')
            ->assertForbidden();
    }

    public function test_can_use_authorization_middleware_with_route_livewire_macro_and_namespaced_single_file_component()
    {
        Gate::policy(RoutingPost::class, RoutingPostPolicy::class);

        app('livewire.finder')->addNamespace('admin', viewPath: __DIR__ . '/fixtures');

        Route::livewire('/posts/{post}', 'admin::sfc-post')
            ->middleware(['web', 'can:view,post']);

        // User 1 owns Post 1 and should be allowed
        $this->actingAs(RoutingUser::find(1))
            ->get('/posts/1')
            ->assertOk()
            ->assertSee('Post: First');

        // User 2 does not own Post 1 and should be denied
        $this->actingAs(RoutingUser::find(2))
            ->get('/posts/1')
            ->assertForbidden();
    }

    public function test_can_use_closure_to_dynamically_select_component()
    {
        Route::livewire('/dashboard', function () {
            return ComponentForRouting::class;
        });

        $this
            ->withoutExceptionHandling()
            ->get('/dashboard')
            ->assertSee('Component for routing');
    }

    public function test_can_use_closure_with_condition_logic_to_select_component()
    {
        Route::livewire('/role-based', function () {
            return rand(0, 1) === 1 ? ComponentForRouting::class : ComponentForRoutingWithParams::class;
        });

        $response = $this->get('/role-based');

        $this->assertContains($response->getContent(), ['Component for routing', '<div>']);
    }

    public function test_can_use_closure_with_string_component_name()
    {
        Livewire::component('component-for-routing', ComponentForRouting::class);

        Route::livewire('/dashboard', function () {
            return 'component-for-routing';
        });

        $this
            ->withoutExceptionHandling()
            ->get('/dashboard')
            ->assertSee('Component for routing');
    }
}

class ComponentForRouting extends Component
{
    public function render()
    {
        return '<div>Component for routing</div>';
    }
}

class ComponentForRoutingWithParams extends Component
{
    public $myId;

    public function render()
    {
        return <<<'HTML'
        <div>
            {{ $myId }}
        </div>
        HTML;
    }
}

class ComponentWithPost extends Component
{
    public RoutingPost $post;

    public function render()
    {
        return '<div>Post: {{ $post->title }}</div>';
    }
}

class RoutingUser extends AuthUser
{
    use Sushi;

    protected $rows = [
        ['id' => 1, 'name' => 'First User', 'email' => 'first@example.com', 'password' => ''],
        ['id' => 2, 'name' => 'Second User', 'email' => 'second@example.com', 'password' => ''],
    ];
}

class RoutingPost extends Model
{
    use Sushi;

    protected $rows = [
        ['id' => 1, 'title' => 'First', 'user_id' => 1],
        ['id' => 2, 'title' => 'Second', 'user_id' => 2],
    ];
}

class RoutingPostPolicy
{
    public function view(RoutingUser $user, RoutingPost $post)
    {
        return (int) $post->user_id === (int) $user->id;
    }
}
