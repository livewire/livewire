<?php

namespace Livewire\Features\SupportDeferredLoading;

use Sushi\Sushi;
use Livewire\Livewire;
use Livewire\Component;
use Tests\BrowserTestCase;
use Livewire\Attributes\Computed;
use Illuminate\Foundation\Auth\User as AuthUser;

class BrowserTest extends BrowserTestCase
{
    /** @test */
    public function can_defer_loading_of_a_collection_of_models_in_blade()
    {
        Livewire::visit(new class extends Component {
            public bool $load = false;

            public function loadUsers(): void
            {
                $this->load = true;
                unset($this->users);
            }

            #[Computed]
            public function users(): \Illuminate\Support\Collection
            {
                return $this->load
                    ? User::query()->limit(2)->get()
                    : collect();
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <button dusk="load" wire:click="loadUsers">Load Users</button>
                        <ul>
                            @foreach ($this->users as $user)
                                <li wire:key="{{ $user->id }}">{{ $user->name }}</li>
                            @endforeach
                        </ul>
                    </div>
                BLADE;
            }
        })
            ->assertDontSee('First User')
            ->assertDontSee('Second User')
            ->waitForLivewire()->click('@load')
            ->assertSee('First User')
            ->assertSee('Second User');
    }

    /** @test */
    public function can_defer_loading_of_a_collection_of_models_into_alpine_state()
    {
        Livewire::visit(new class extends Component {
            public bool $load = false;

            public function loadUsers(): void
            {
                $this->load = true;
                unset($this->users);
            }

            #[Computed]
            public function users(): \Illuminate\Support\Collection
            {
                return $this->load
                    ? User::query()->inRandomOrder()->limit(2)->get()
                    : collect();
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <button dusk="load" wire:click="loadUsers">Load Users</button>
                        <div x-data="{ users: @js($this->users) }">
                            <ul>
                                <template x-for="user in users" :key="user.id">
                                    <li x-text="user.name"></li>
                                </template>
                            </ul>
                        </div>
                    </div>
                BLADE;
            }
        })
            ->assertDontSee('First User')
            ->assertDontSee('Second User')
            ->waitForLivewire()->click('@load')
            ->assertSee('First User')
            ->assertSee('Second User');
    }
}

class User extends AuthUser
{
    use Sushi;

    protected $rows = [
        [
            'id' => 1,
            'name' => 'First User',
            'email' => 'first@laravel-livewire.com',
            'password' => '',
        ],
        [
            'id' => 2,
            'name' => 'Second User',
            'email' => 'second@laravel-livewire.com',
            'password' => '',
        ],
    ];
}
