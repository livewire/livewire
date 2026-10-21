<?php

namespace Livewire\Features\SupportAuthorization;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Authorize;
use Livewire\Attributes\Json;
use Livewire\Attributes\On;
use Livewire\Livewire;
use Sushi\Sushi;
use Tests\TestCase;
use Tests\TestComponent;

class AuthorizeParameterBindingUnitTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Gate::policy(ReproPost::class, ReproPostPolicy::class);

        ReproComponent::$touched = [];
    }

    public function test_out_of_order_integer_keys_on_a_call_are_rejected()
    {
        // User 1 owns post 1 only. Authorize would see key 0 (post 1) while the method got post 2.
        Livewire::actingAs(ReproUser::find(1))
            ->test(ReproComponent::class)
            ->update(calls: [['method' => 'deletePost', 'params' => [1 => 2, 0 => 1], 'path' => '']])
            ->assertNotFound();

        $this->assertSame([], ReproComponent::$touched);
    }

    public function test_out_of_order_integer_keys_on_an_event_listener_are_rejected()
    {
        Livewire::actingAs(ReproUser::find(1))
            ->test(ReproComponent::class)
            ->update(calls: [['method' => '__dispatch', 'params' => ['delete-post', [1 => 2, 0 => 1]], 'path' => '']])
            ->assertNotFound();

        $this->assertSame([], ReproComponent::$touched);
    }

    public function test_ordered_params_still_authorize_and_call_the_same_model()
    {
        Livewire::actingAs(ReproUser::find(1))
            ->test(ReproComponent::class)
            ->call('deletePost', 1)
            ->assertOk()
            ->call('deletePost', 2)
            ->assertForbidden();

        $this->assertSame([1], ReproComponent::$touched);
    }

    public function test_json_method_is_authorized_before_it_runs()
    {
        Livewire::actingAs(ReproUser::find(1))
            ->test(ReproComponent::class)
            ->update(calls: [['method' => 'jsonDeleteOthersPost', 'params' => [], 'path' => '']])
            ->assertForbidden();

        $this->assertSame([], ReproComponent::$touched);
    }
}

class ReproComponent extends TestComponent
{
    public static array $touched = [];

    public ReproPost $othersPost;

    public function mount()
    {
        $this->othersPost = ReproPost::find(2);
    }

    #[On('delete-post')]
    #[Authorize('delete', 'post')]
    public function deletePost(ReproPost $post)
    {
        static::$touched[] = (int) $post->id;
    }

    #[Json]
    #[Authorize('delete', 'othersPost')]
    public function jsonDeleteOthersPost()
    {
        static::$touched[] = 'json';
    }
}

class ReproUser extends AuthUser
{
    use Sushi;

    protected $rows = [
        ['id' => 1, 'name' => 'One', 'email' => 'one@repro.test', 'password' => ''],
        ['id' => 2, 'name' => 'Two', 'email' => 'two@repro.test', 'password' => ''],
    ];
}

class ReproPost extends Model
{
    use Sushi;

    protected $rows = [
        ['id' => 1, 'user_id' => 1],
        ['id' => 2, 'user_id' => 2],
    ];
}

class ReproPostPolicy
{
    public function delete(ReproUser $user, ReproPost $post): bool
    {
        return (int) $post->user_id === (int) $user->id;
    }
}
