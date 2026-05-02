<?php

namespace Livewire\Features\SupportAuthorization;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Authorize;
use Livewire\Livewire;
use Sushi\Sushi;
use Tests\TestCase;
use Tests\TestComponent;

class UnitTest extends TestCase
{
    public function test_can_authorize_defined_basic_gates()
    {
        Gate::define('can-open-post', fn () : bool => true);
        Gate::define('cannot-open-post', fn () : bool => false);

        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                #[Authorize('can-open-post')]
                public function canOpenPost() : bool
                {
                    return true;
                }

                #[Authorize('cannot-open-post')]
                public function cannotOpenPost() : bool
                {
                    return true;
                }
            })
            ->call('canOpenPost')
            ->assertOk()
            ->call('cannotOpenPost')
            ->assertForbidden();
    }

    public function test_can_authorize_defined_gates_without_arguments()
    {
        Gate::define('can-open-post', function (AuthorizationUser $user) : bool
        {
            return (int) $user->id === 1;
        });

        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                #[Authorize('can-open-post')]
                public function canOpenPost() : bool
                {
                    return true;
                }
            })
            ->call('canOpenPost')
            ->assertOk();

        Livewire::actingAs(AuthorizationUser::find(2))
            ->test(new class extends TestComponent {
                #[Authorize('can-open-post')]
                public function canOpenPost() : bool
                {
                    return true;
                }
            })
            ->call('canOpenPost')
            ->assertForbidden();
    }

    public function test_can_authorize_defined_gates_with_model_argument()
    {
        Gate::define('can-open-post', function (AuthorizationUser $user, AuthorizationPost $post) : bool
        {
            return (int) $post->user_id === (int) $user->id;
        });

        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                public AuthorizationPost $post;

                public function mount() : void
                {
                    $this->post = AuthorizationPost::find(1);
                }

                #[Authorize('can-open-post', 'post')]
                public function canOpenPost() : bool
                {
                    return true;
                }
            })
            ->call('canOpenPost')
            ->assertOk();

        Livewire::actingAs(AuthorizationUser::find(2))
            ->test(new class extends TestComponent {
                public AuthorizationPost $post;

                public function mount() : void
                {
                    $this->post = AuthorizationPost::find(1);
                }

                #[Authorize('can-open-post', 'post')]
                public function canOpenPost() : bool
                {
                    return true;
                }
            })
            ->call('canOpenPost')
            ->assertForbidden();
    }

    public function test_can_authorize_policy_with_class()
    {
        Gate::policy(AuthorizationPost::class, AuthorizationPostPolicy::class);

        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                #[Authorize('create', AuthorizationPost::class)]
                public function createPost() : bool
                {
                    return true;
                }
            })
            ->call('createPost')
            ->assertOk();

        Livewire::actingAs(AuthorizationUser::find(2))
            ->test(new class extends TestComponent {
                #[Authorize('create', AuthorizationPost::class)]
                public function createPost() : bool
                {
                    return true;
                }
            })
            ->call('createPost')
            ->assertForbidden();
    }

    public function test_can_authorize_policy_with_model_argument()
    {
        Gate::policy(AuthorizationPost::class, AuthorizationPostPolicy::class);

        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                public AuthorizationPost $post;

                public function mount() : void
                {
                    $this->post = AuthorizationPost::find(1);
                }

                #[Authorize('edit', 'post')]
                public function editPost() : bool
                {
                    return true;
                }
            })
            ->call('editPost')
            ->assertOk();

        Livewire::actingAs(AuthorizationUser::find(2))
            ->test(new class extends TestComponent {
                public AuthorizationPost $post;

                public function mount() : void
                {
                    $this->post = AuthorizationPost::find(1);
                }

                #[Authorize('edit', 'post')]
                public function editPost() : bool
                {
                    return true;
                }
            })
            ->call('editPost')
            ->assertForbidden();
    }

    public function test_can_authorize_policy_with_model_id_argument()
    {
        Gate::policy(AuthorizationPost::class, AuthorizationPostPolicy::class);

        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                #[Authorize('edit', 'post')]
                public function editPost(AuthorizationPost $post) : bool
                {
                    return true;
                }
            })
            ->call('editPost', post: 1)
            ->assertOk();

        Livewire::actingAs(AuthorizationUser::find(2))
            ->test(new class extends TestComponent {
                #[Authorize('edit', 'post')]
                public function editPost(AuthorizationPost $post) : bool
                {
                    return true;
                }
            })
            ->call('editPost', post: 1)
            ->assertForbidden();
    }

    public function test_prioritizes_arguments_over_properties()
    {
        Gate::policy(AuthorizationPost::class, AuthorizationPostPolicy::class);

        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                public AuthorizationPost $post;

                public function mount() : void
                {
                    $this->post = AuthorizationPost::find(2);
                }

                #[Authorize('edit', 'post')]
                public function editPost(AuthorizationPost $post) : bool
                {
                    return true;
                }
            })
            ->call('editPost', post: 1)
            ->assertOk();

        Livewire::actingAs(AuthorizationUser::find(2))
            ->test(new class extends TestComponent {
                public AuthorizationPost $post;

                public function mount() : void
                {
                    $this->post = AuthorizationPost::find(1);
                }

                #[Authorize('edit', 'post')]
                public function editPost(AuthorizationPost $post) : bool
                {
                    return true;
                }
            })
            ->call('editPost', post: 1)
            ->assertForbidden();
    }

    public function test_can_authorize_multiple_policies()
    {
        Gate::policy(AuthorizationPost::class, AuthorizationPostPolicy::class);

        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                public AuthorizationPost $post;

                public function mount() : void
                {
                    $this->post = AuthorizationPost::find(1);
                }

                #[Authorize('create', AuthorizationPost::class)]
                #[Authorize('edit', 'post')]
                public function createPost() : bool
                {
                    return true;
                }
            })
            ->call('createPost')
            ->assertOk();

        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                public AuthorizationPost $post;

                public function mount() : void
                {
                    $this->post = AuthorizationPost::find(2);
                }

                #[Authorize('create', AuthorizationPost::class)]
                #[Authorize('edit', 'post')]
                public function createPost() : bool
                {
                    return true;
                }
            })
            ->call('createPost')
            ->assertForbidden();

        Livewire::actingAs(AuthorizationUser::find(2))
            ->test(new class extends TestComponent {
                public AuthorizationPost $post;

                public function mount() : void
                {
                    $this->post = AuthorizationPost::find(2);
                }

                #[Authorize('create', AuthorizationPost::class)]
                #[Authorize('edit', 'post')]
                public function createPost() : bool
                {
                    return true;
                }
            })
            ->call('createPost')
            ->assertForbidden();
    }

    public function test_it_does_not_perform_any_action_when_denied()
    {
        Gate::define('cannot-open-post', fn () => false);

        $this->assertFalse(Session::has('should-never-be-set'));

        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                #[Authorize('cannot-open-post')]
                public function cannotOpenPost() : void
                {
                    Session::put('should-never-be-set', true);
                }
            })
            ->call('cannotOpenPost')
            ->assertForbidden();

        $this->assertFalse(Session::has('should-never-be-set'));
    }

    public function test_authorize_is_enforced_on_event_listeners()
    {
        Gate::define('cannot-open-post', fn () => false);

        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                #[\Livewire\Attributes\On('some-event')]
                #[Authorize('cannot-open-post')]
                public function handleEvent() : void
                {
                    Session::put('should-never-be-set', true);
                }
            })
            ->dispatch('some-event')
            ->assertForbidden();

        $this->assertFalse(Session::has('should-never-be-set'));
    }

    public function test_authorize_allows_authorized_event_listeners()
    {
        Gate::define('can-open-post', fn () => true);

        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                #[\Livewire\Attributes\On('some-event')]
                #[Authorize('can-open-post')]
                public function handleEvent() : void
                {
                    Session::put('event-was-handled', true);
                }
            })
            ->dispatch('some-event')
            ->assertOk();

        $this->assertTrue(Session::has('event-was-handled'));
    }

    public function test_can_authorize_defined_gates_with_array_as_argument()
    {
        Gate::policy(AuthorizationPost::class, AuthorizationPostPolicy::class);

        Gate::define('create-comment', function (AuthorizationUser $user, string $commentClass, AuthorizationPost $post) {
            return (int) $user->id === 1 && $post->user_id === 1;
        });

        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                public AuthorizationPost $post;

                public function mount(): void
                {
                    $this->post = AuthorizationPost::find(1);
                }

                #[Authorize('create-comment', [AuthorizationComment::class, 'post'])]
                public function createComment(AuthorizationPost $post)
                {
                    return true;
                }
            })
            ->call('createComment', post: 1)
            ->assertOk();
    }

    public function test_can_authorize_defined_gates_with_array_using_component_property()
    {
        Gate::policy(AuthorizationPost::class, AuthorizationPostPolicy::class);

        Gate::define('create-comment', function (AuthorizationUser $user, string $commentClass, AuthorizationPost $post) {
            return (int) $user->id === 1 && $post->user_id === 1;
        });

        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                public AuthorizationPost $post;

                public function mount(): void
                {
                    $this->post = AuthorizationPost::find(1);
                }

                #[Authorize('create-comment', [AuthorizationComment::class, 'post'])]
                public function createComment()
                {
                    return true;
                }
            })
            ->call('createComment')
            ->assertOk();
    }

    public function test_can_authorize_policy_with_array_of_class_and_model()
    {
        Gate::policy(AuthorizationComment::class, AuthorizationCommentPolicy::class);

        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                #[Authorize('create', [AuthorizationComment::class, 'post'])]
                public function createComment(AuthorizationPost $post)
                {
                    return true;
                }
            })
            ->call('createComment', post: 1)
            ->assertOk();
    }

    public function test_can_authorize_policy_with_array_of_models()
    {
        Gate::policy(AuthorizationComment::class, AuthorizationCommentPolicy::class);

        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                #[Authorize('edit', ['comment', 'post'])]
                public function editComment(AuthorizationComment $comment, AuthorizationPost $post)
                {
                    return true;
                }
            })
            ->call('editComment', comment: 1, post: 1)
            ->assertOk();

        Livewire::actingAs(AuthorizationUser::find(2))
            ->test(new class extends TestComponent {
                #[Authorize('edit', ['comment', 'post'])]
                public function editComment(AuthorizationComment $comment, AuthorizationPost $post)
                {
                    return true;
                }
            })
            ->call('editComment', comment: 1, post: 1)
            ->assertForbidden();
    }
}

class AuthorizationUser extends AuthUser
{
    use Sushi;

    protected $rows = [
        ['id' => 1, 'name' => 'First User', 'email' => 'first@example.com', 'password' => ''],
        ['id' => 2, 'name' => 'Second User', 'email' => 'second@example.com', 'password' => ''],
    ];
}

class AuthorizationPost extends Model
{
    use Sushi;

    protected $rows = [
        ['id' => 1, 'title' => 'First', 'user_id' => 1],
        ['id' => 2, 'title' => 'Second', 'user_id' => 2],
    ];
}

class AuthorizationComment extends Model
{
    use Sushi;

    protected $rows = [
        ['id' => 1, 'user_id' => 1, 'post_id' => 1, 'content' => 'Test comment'],
    ];
}

class AuthorizationPostPolicy
{
    public function create(AuthorizationUser $user) : bool
    {
        return (int) $user->id === 1;
    }

    public function edit(AuthorizationUser $user, AuthorizationPost $post) : bool
    {
        return (int) $post->user_id === (int) $user->id;
    }
}

class AuthorizationCommentPolicy
{
    public function create(AuthorizationUser $user, AuthorizationPost $post) : bool
    {
        return (int) $user->id === 1 && (int) $post->id === 1;
    }

    public function edit(AuthorizationUser $user, AuthorizationComment $comment, AuthorizationPost $post) : bool
    {
        return (int) $comment->post_id === (int) $post->id && (int) $comment->user_id === (int) $user->id;
    }
}
