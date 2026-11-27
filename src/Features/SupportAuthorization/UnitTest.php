<?php

namespace Livewire\Features\SupportAuthorization;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Authorize;
use Livewire\Features\SupportEvents\BaseOn;
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

    public function test_can_authorize_policy_with_positional_model_id_argument()
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
            ->call('editPost', 1)
            ->assertOk();

        Livewire::actingAs(AuthorizationUser::find(2))
            ->test(new class extends TestComponent {
                #[Authorize('edit', 'post')]
                public function editPost(AuthorizationPost $post) : bool
                {
                    return true;
                }
            })
            ->call('editPost', 1)
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

    public function test_can_authorize_policy_with_dependency_injection_and_positional_model_id_argument()
    {
        Gate::policy(AuthorizationPost::class, AuthorizationPostPolicy::class);

        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                #[Authorize('edit', 'post')]
                public function editPost(UrlGenerator $generator, AuthorizationPost $post) : bool
                {
                    return $generator->to('/some-url') !== '';
                }
            })
            ->call('editPost', 1)
            ->assertOk();

        Livewire::actingAs(AuthorizationUser::find(2))
            ->test(new class extends TestComponent {
                #[Authorize('edit', 'post')]
                public function editPost(UrlGenerator $generator, AuthorizationPost $post) : bool
                {
                    return $generator->to('/some-url') !== '';
                }
            })
            ->call('editPost', 1)
            ->assertForbidden();
    }

    public function test_can_authorize_policy_with_dependency_injection_and_named_model_id_argument()
    {
        Gate::policy(AuthorizationPost::class, AuthorizationPostPolicy::class);

        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                #[Authorize('edit', 'post')]
                public function editPost(UrlGenerator $generator, AuthorizationPost $post) : bool
                {
                    return $generator->to('/some-url') !== '';
                }
            })
            ->call('editPost', post: 1)
            ->assertOk();

        Livewire::actingAs(AuthorizationUser::find(2))
            ->test(new class extends TestComponent {
                #[Authorize('edit', 'post')]
                public function editPost(UrlGenerator $generator, AuthorizationPost $post) : bool
                {
                    return $generator->to('/some-url') !== '';
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

    public function test_authorize_allows_authorized_event_listeners_with_named_argument()
    {
        Gate::policy(AuthorizationPost::class, AuthorizationPostPolicy::class);

        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                #[\Livewire\Attributes\On('edit-post')]
                #[Authorize('edit', 'post')]
                public function editPost(AuthorizationPost $post) : bool
                {
                    return true;
                }
            })
            ->dispatch('edit-post', post: 1)
            ->assertOk();

        Livewire::actingAs(AuthorizationUser::find(2))
            ->test(new class extends TestComponent {
                #[\Livewire\Attributes\On('edit-post')]
                #[Authorize('edit', 'post')]
                public function editPost(AuthorizationPost $post) : bool
                {
                    return true;
                }
            })
            ->dispatch('edit-post', post: 1)
            ->assertForbidden();
    }

    public function test_can_authorize_defined_gates_with_array_as_argument()
    {
        Gate::define('create-comment', function (AuthorizationUser $user, string $commentClass, AuthorizationPost $post) {
            return (int) $user->id === 1 && $post->id === 1;
        });

        // AssertOk using class name and method argument
        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                #[Authorize('create-comment', [AuthorizationComment::class, 'post'])]
                public function createComment(AuthorizationPost $post)
                {
                    return true;
                }
            })
            ->call('createComment', post: 1)
            ->assertOk();

        // AssertOk using class name and component property
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

        // AssertOk using class name and method argument prioritized first
        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                public AuthorizationPost $post;

                public function mount(): void
                {
                    $this->post = AuthorizationPost::find(2);
                }

                #[Authorize('create-comment', [AuthorizationComment::class, 'post'])]
                public function createComment(AuthorizationPost $post)
                {
                    return true;
                }
            })
            ->call('createComment', post: 1)
            ->assertOk();

        // AssertForbidden using class name and method argument
        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                #[Authorize('create-comment', [AuthorizationComment::class, 'post'])]
                public function createComment(AuthorizationPost $post)
                {
                    return true;
                }
            })
            ->call('createComment', post: 2)
            ->assertForbidden();

        // AssertForbidden using class name and component property
        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                public AuthorizationPost $post;

                public function mount(): void
                {
                    $this->post = AuthorizationPost::find(2);
                }

                #[Authorize('create-comment', [AuthorizationComment::class, 'post'])]
                public function createComment()
                {
                    return true;
                }
            })
            ->call('createComment')
            ->assertForbidden();
        
        // AssertForbidden using class name and method argument prioritized first
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
            ->call('createComment', post: 2)
            ->assertForbidden();
    }

    public function test_can_authorize_policy_with_array_of_class_and_model()
    {
        Gate::policy(AuthorizationComment::class, AuthorizationCommentPolicy::class);

        // AssertOk using class name and method argument
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

        // AssertOk using class name and component property
        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                public AuthorizationPost $post;

                public function mount(): void
                {
                    $this->post = AuthorizationPost::find(1);
                }

                #[Authorize('create', [AuthorizationComment::class, 'post'])]
                public function createComment()
                {
                    return true;
                }
            })
            ->call('createComment')
            ->assertOk();

        // AssertOk using class name and method argument prioritized first
        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                public AuthorizationPost $post;

                public function mount(): void
                {
                    $this->post = AuthorizationPost::find(2);
                }

                #[Authorize('create', [AuthorizationComment::class, 'post'])]
                public function createComment(AuthorizationPost $post)
                {
                    return true;
                }
            })
            ->call('createComment', post: 1)
            ->assertOk();

        // AssertForbidden using class name and method argument
        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                #[Authorize('create', [AuthorizationComment::class, 'post'])]
                public function createComment(AuthorizationPost $post)
                {
                    return true;
                }
            })
            ->call('createComment', post: 2)
            ->assertForbidden();

        // AssertForbidden using class name and component property
        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                public AuthorizationPost $post;

                public function mount(): void
                {
                    $this->post = AuthorizationPost::find(2);
                }

                #[Authorize('create', [AuthorizationComment::class, 'post'])]
                public function createComment()
                {
                    return true;
                }
            })
            ->call('createComment')
            ->assertForbidden();
        
        // AssertForbidden using class name and method argument prioritized first
        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                public AuthorizationPost $post;

                public function mount(): void
                {
                    $this->post = AuthorizationPost::find(1);
                }

                #[Authorize('create', [AuthorizationComment::class, 'post'])]
                public function createComment(AuthorizationPost $post)
                {
                    return true;
                }
            })
            ->call('createComment', post: 2)
            ->assertForbidden();
    }

    public function test_can_authorize_policy_with_array_of_models()
    {
        Gate::policy(AuthorizationComment::class, AuthorizationCommentPolicy::class);

        // AssertOk using method arguments
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

        // AssertOk using component properties
        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                public AuthorizationComment $comment;

                public AuthorizationPost $post;

                public function mount(): void
                {
                    $this->comment = AuthorizationComment::find(1);
                    $this->post = AuthorizationPost::find(1);
                }

                #[Authorize('edit', ['comment', 'post'])]
                public function editComment()
                {
                    return true;
                }
            })
            ->call('editComment')
            ->assertOk();

        // AssertOk using method arguments prioritized first
        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                public AuthorizationComment $comment;

                public AuthorizationPost $post;

                public function mount(): void
                {
                    $this->comment = AuthorizationComment::find(1);
                    $this->post = AuthorizationPost::find(2);
                }

                #[Authorize('edit', ['comment', 'post'])]
                public function editComment(AuthorizationComment $comment, AuthorizationPost $post)
                {
                    return true;
                }
            })
            ->call('editComment', comment: 1, post: 1)
            ->assertOk();

        // AssertForbidden using method arguments
        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                #[Authorize('edit', ['comment', 'post'])]
                public function editComment(AuthorizationComment $comment, AuthorizationPost $post)
                {
                    return true;
                }
            })
            ->call('editComment', comment: 1, post: 2)
            ->assertForbidden();

        // AssertForbidden using component properties
        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                public AuthorizationComment $comment;

                public AuthorizationPost $post;

                public function mount(): void
                {
                    $this->comment = AuthorizationComment::find(1);
                    $this->post = AuthorizationPost::find(2);
                }

                #[Authorize('edit', ['comment', 'post'])]
                public function editComment()
                {
                    return true;
                }
            })
            ->call('editComment')
            ->assertForbidden();
        
        // AssertForbidden using method arguments prioritized first
        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                public AuthorizationComment $comment;

                public AuthorizationPost $post;

                public function mount(): void
                {
                    $this->comment = AuthorizationComment::find(1);
                    $this->post = AuthorizationPost::find(1);
                }

                #[Authorize('edit', ['comment', 'post'])]
                public function editComment(AuthorizationComment $comment, AuthorizationPost $post)
                {
                    return true;
                }
            })
            ->call('editComment', comment: 1, post: 2)
            ->assertForbidden();
    }

    public function test_can_authorize_using_defined_gate_with_only_class_name_as_argument()
    {
        Gate::define('create', function (AuthorizationUser $user) : bool
        {
            return (int) $user->id === 1;
        });

        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                #[Authorize(AuthorizationPost::class)]
                public function create() : bool
                {
                    return true;
                }
            })
            ->call('create')
            ->assertOk();
    }

    public function test_can_authorize_using_policy_with_only_class_name_and_no_arguments()
    {
        Gate::policy(AuthorizationPost::class, AuthorizationPostPolicy::class);

        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                #[Authorize(AuthorizationPost::class)]
                public function create() : bool
                {
                    return true;
                }
            })
            ->call('create')
            ->assertOk();
    }

    public function test_can_authorize_using_policy_resource_ability_mapping_with_only_class_name()
    {
        Gate::policy(AuthorizationPost::class, AuthorizationPostPolicy::class);

        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                #[Authorize(AuthorizationPost::class)]
                public function store() : bool
                {
                    return true;
                }
            })
            ->call('store')
            ->assertOk();
    }

    public function test_can_trigger_base_authorize_attribute_using_event()
    {
        Gate::policy(AuthorizationPost::class, AuthorizationPostPolicy::class);

        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                #[BaseAuthorize(AuthorizationPost::class)]
                #[BaseOn('some-event')]
                public function store() : bool
                {
                    return true;
                }
            })
            ->dispatch('some-event')
            ->assertOk();
    }

    public function test_base_authorize_attribute_blocks_unauthorized_user_via_event()
    {
        Gate::policy(AuthorizationPost::class, AuthorizationPostPolicy::class);

        Livewire::actingAs(AuthorizationUser::find(2))
            ->test(new class extends TestComponent {
                #[BaseAuthorize(AuthorizationPost::class)]
                #[BaseOn('some-event')]
                public function store() : bool
                {
                    return true;
                }
            })
            ->dispatch('some-event')
            ->assertForbidden();
    }

    public function test_authorize_attribute_trigger_component_exception_hook()
    {
        Gate::policy(AuthorizationPost::class, AuthorizationPostPolicy::class);

        Livewire::actingAs(AuthorizationUser::find(2))
            ->test(new class extends TestComponent {
                #[BaseAuthorize(AuthorizationPost::class)]
                public function store() : bool
                {
                    Session::put('action-ran', true);

                    return true;
                }

                public function exception($e, $stopPropagation)
                {
                    if ($e instanceof AuthorizationException) {
                        $stopPropagation();
    
                        Session::put('exception-hook-triggered', true);
                    }
                }
            })
            ->call('store')
            ->assertOk();

        $this->assertTrue(Session::has('exception-hook-triggered'));
        $this->assertFalse(Session::has('action-ran'));
    }

    public function test_listener_and_json_method_return_early_when_exception_hook_handles_authorize()
    {
        Gate::define('cannot-run', fn () : bool => false);

        $component = Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                public $listenerRan = false;

                public $jsonRan = false;

                #[BaseAuthorize('cannot-run')]
                #[BaseOn('some-event')]
                public function listener()
                {
                    $this->listenerRan = true;
                }

                #[\Livewire\Attributes\Json]
                #[BaseAuthorize('cannot-run')]
                public function json()
                {
                    Session::put('json-ran', true);
                }

                public function exception($e, $stopPropagation)
                {
                    if ($e instanceof AuthorizationException) $stopPropagation();
                }
            });

        $component->dispatch('some-event')->assertOk()->assertSet('listenerRan', false);
        $component->call('json')->assertOk();

        $this->assertFalse(Session::has('json-ran'));
    }

    public function test_json_method_returns_404_when_model_not_found()
    {
        $component = Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                public bool $called = false;

                #[\Livewire\Attributes\Json]
                #[Authorize('edit', 'post')]
                public function editPost(AuthorizationPost $post)
                {
                    $this->called = true;
                }

                public function exception($e, $stopPropagation)
                {
                    if ($e instanceof ModelNotFoundException) $stopPropagation();
                }
            })
            ->call('editPost', post: 3)
            ->assertSetStrict('called', false)
            ->assertOk();

        $this->assertStatusCode($component, 404);
    }

    public function test_json_method_returns_404_when_authorization_denied_as_not_found()
    {
        Gate::policy(AuthorizationPost::class, AuthorizationPostPolicy::class);

        $component = Livewire::actingAs(AuthorizationUser::find(2))
            ->test(new class extends TestComponent {
                public bool $called = false;

                #[\Livewire\Attributes\Json]
                #[Authorize('delete', 'post')]
                public function deletePost(AuthorizationPost $post)
                {
                    $this->called = true;
                }

                public function exception($e, $stopPropagation)
                {
                    if ($e instanceof AuthorizationException) $stopPropagation();
                }
            })
            ->call('deletePost', post: 1)
            ->assertSetStrict('called', false)
            ->assertOk();

        $this->assertStatusCode($component, 404);
    }

    public function test_json_method_returns_400_when_authorization_denied_with_custom_status()
    {
        Gate::policy(AuthorizationPost::class, AuthorizationPostPolicy::class);

        $component = Livewire::actingAs(AuthorizationUser::find(2))
            ->test(new class extends TestComponent {
                public bool $called = false;

                #[\Livewire\Attributes\Json]
                #[Authorize('restore', 'post')]
                public function restorePost(AuthorizationPost $post)
                {
                    $this->called = true;
                }

                public function exception($e, $stopPropagation)
                {
                    if ($e instanceof AuthorizationException) $stopPropagation();
                }
            })
            ->call('restorePost', post: 1)
            ->assertSetStrict('called', false)
            ->assertOk();

        $this->assertStatusCode($component, 400);
    }

    public function test_can_authorize_using_attribute_and_trait_method_at_the_same_time()
    {
        Gate::policy(AuthorizationPost::class, AuthorizationPostPolicy::class);

        Livewire::actingAs(AuthorizationUser::find(1))
            ->test(new class extends TestComponent {
                #[BaseAuthorize(AuthorizationPost::class)]
                public function store(AuthorizationPost $post)
                {
                    $this->authorize(AuthorizationPost::class);
                    $this->authorize('edit', $post);
                }

                public function exception($e, $stopPropagation)
                {
                    if ($e instanceof AuthorizationException) {
                        $stopPropagation();
    
                        Session::put('exception-hook-triggered', true);
                    }
                }
            })
            ->call('store', post: 1)
            ->assertOk();

        $this->assertFalse(Session::has('exception-hook-triggered'));
    }

    protected function assertStatusCode($component, $status)
    {
        $returnsMeta = $component->effects['returnsMeta'] ?? [];

        $this->assertArrayHasKey(0, $returnsMeta);
        $this->assertArrayHasKey('status', $returnsMeta[0]);
        $this->assertSame($status, $returnsMeta[0]['status']);
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

    public function delete(AuthorizationUser $user, AuthorizationPost $post) : Response
    {
        return (int) $post->user_id === (int) $user->id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function restore(AuthorizationUser $user, AuthorizationPost $post) : Response
    {
        return (int) $post->user_id === (int) $user->id
            ? Response::allow()
            : Response::denyWithStatus(400);
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
