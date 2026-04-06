<?php

namespace Livewire\Features\SupportReactiveProps;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Livewire\Component;
use Sushi\Sushi;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_can_pass_a_reactive_property_from_parent_to_child()
    {
        Livewire::visit([
            new class extends Component {
                public $count = 0;

                public function inc() { $this->count++; }

                public function dec() { $this->count--; }

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Parent count: <span dusk="parent.count">{{ $count }}</span>

                        <button wire:click="dec" dusk="parent.dec">dec</button>

                        <button wire:click="inc" dusk="parent.inc">inc</button>

                        <livewire:child :child-count="$count" />
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                #[BaseReactive]
                public $childCount;

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Child count: <span dusk="child.count">{{ $childCount }}</span>
                    </div>
                    HTML;
                }
            }
        ])
            ->assertSeeIn('@parent.count', 0)
            ->assertSeeIn('@child.count', 0)

            ->waitForLivewire()->click('@parent.inc')
            ->assertSeeIn('@parent.count', 1)
            ->assertSeeIn('@child.count', 1)

            ->waitForLivewire()->click('@parent.inc')
            ->assertSeeIn('@parent.count', 2)
            ->assertSeeIn('@child.count', 2)

            ->waitForLivewire()->click('@parent.dec')
            ->assertSeeIn('@parent.count', 1)
            ->assertSeeIn('@child.count', 1)

            ->waitForLivewire()->click('@parent.dec')
            ->assertSeeIn('@parent.count', 0)
            ->assertSeeIn('@child.count', 0)

            ->waitForLivewire()->click('@parent.dec')
            ->assertSeeIn('@parent.count', -1)
            ->assertSeeIn('@child.count', -1)

            ->waitForLivewire()->click('@parent.inc')
            ->assertSeeIn('@parent.count', 0)
            ->assertSeeIn('@child.count', 0);
    }

    public function test_can_pass_a_reactive_property_from_parent_to_nested_children()
    {
        Livewire::visit([
            new class extends Component {
                public $count = 0;

                public function inc() { $this->count++; }

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Parent count: <h1 dusk="parent.count">{{ $count }}</h1>

                        <button wire:click="inc" dusk="parent.inc">inc</button>

                        <livewire:child :$count />
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                #[BaseReactive]
                public $count;

                public function render() { return <<<'HTML'
                    <div>
                        <h2>Child count: <h2 dusk="child.count">{{ $count }}</h2>

                        <livewire:nestedchild :$count />
                    </div>
                    HTML;
                }
            },
            'nestedchild' => new class extends Component {
                #[BaseReactive]
                public $count;

                public function render() { return <<<'HTML'
                    <div>
                        <h3>Nested child count: <h3 dusk="nested-child.count">{{ $count }}</h3>
                    </div>
                    HTML;
                }
            }
        ])
            ->assertSeeIn('@parent.count', 0)
            ->assertSeeIn('@child.count', 0)
            ->assertSeeIn('@nested-child.count', 0)

            ->waitForLivewire()->click('@parent.inc')
            ->assertSeeIn('@parent.count', 1)
            ->assertSeeIn('@child.count', 1)
            ->assertSeeIn('@nested-child.count', 1)

            ->waitForLivewire()->click('@parent.inc')
            ->assertSeeIn('@parent.count', 2)
            ->assertSeeIn('@child.count', 2)
            ->assertSeeIn('@nested-child.count', 2);
    }

    public function test_can_throw_exception_cannot_mutate_reactive_prop()
    {
        Livewire::visit([
            new class extends Component {
                public $count = 0;

                public function inc() { $this->count++; }

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Parent count: <span dusk="parent.count">{{ $count }}</span>

                        <button wire:click="inc" dusk="parent.inc">inc</button>

                        <livewire:child :$count />
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                #[BaseReactive]
                public $count;

                public function inc() { $this->count++; }

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Child count: <span dusk="child.count">{{ $count }}</span>
                        <button wire:click="inc" dusk="child.inc">inc</button>
                    </div>
                    HTML;
                }
            }
        ])
        ->assertSeeIn('@parent.count', 0)
        ->assertSeeIn('@child.count', 0)
        ->waitForLivewire()->click('@parent.inc')
        ->assertSeeIn('@parent.count', 1)
        ->assertSeeIn('@child.count', 1)
        ->waitForLivewire()->click('@child.inc')
        ->waitFor('#livewire-error')
        ->click('#livewire-error')
        ->assertSeeIn('@parent.count', 1)
        ->assertSeeIn('@child.count', 1);
    }

    public function test_can_pass_a_reactive_property_from_parent_to_lazy_child()
    {
        Livewire::visit([
            new class extends Component {
                public $count = 0;

                public function inc() { $this->count++; }

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Parent count: <span dusk="parent.count">{{ $count }}</span>

                        <button wire:click="inc" dusk="parent.inc">inc</button>

                        <livewire:child :$count lazy />
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                #[BaseReactive]
                public $count;

                public function inc() { $this->count++; }

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Child count: <span dusk="child.count">{{ $count }}</span>
                        <button wire:click="inc" dusk="child.inc">inc</button>
                    </div>
                    HTML;
                }
            }
        ])
            ->assertSeeIn('@parent.count', 0)
            ->waitFor('@child.count')
            ->assertSeeIn('@child.count', 0)
        ;
    }

    public function test_reactive_property_triggers_updated_lifecycle_hook()
    {
        Livewire::visit([
            new class extends Component {
                public $count = 0;

                public function inc() { $this->count++; }

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Parent count: <span dusk="parent.count">{{ $count }}</span>

                        <button wire:click="inc" dusk="parent.inc">inc</button>

                        <livewire:child :$count />
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                #[BaseReactive]
                public $count;

                public $updatedCount = 0;

                public function updatedCount($value)
                {
                    // This lifecycle hook should fire when reactive prop changes
                    $this->updatedCount = $value * 10;
                }

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Child count: <span dusk="child.count">{{ $count }}</span>
                        <h1>Updated count (x10): <span dusk="child.updated-count">{{ $updatedCount }}</span>
                    </div>
                    HTML;
                }
            }
        ])
            ->assertSeeIn('@parent.count', 0)
            ->assertSeeIn('@child.count', 0)
            ->assertSeeIn('@child.updated-count', 0)

            ->waitForLivewire()->click('@parent.inc')
            ->assertSeeIn('@parent.count', 1)
            ->assertSeeIn('@child.count', 1)
            ->assertSeeIn('@child.updated-count', 10)

            ->waitForLivewire()->click('@parent.inc')
            ->assertSeeIn('@parent.count', 2)
            ->assertSeeIn('@child.count', 2)
            ->assertSeeIn('@child.updated-count', 20)
        ;
    }

    public function test_reactive_property_triggers_updating_lifecycle_hook()
    {
        Livewire::visit([
            new class extends Component {
                public $count = 0;

                public function inc() { $this->count++; }

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Parent count: <span dusk="parent.count">{{ $count }}</span>

                        <button wire:click="inc" dusk="parent.inc">inc</button>

                        <livewire:child :$count />
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                #[BaseReactive]
                public $count;

                public $oldCount = 0;

                public function updatingCount($value)
                {
                    // Capture the old value before update
                    $this->oldCount = $this->count;
                }

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Child count: <span dusk="child.count">{{ $count }}</span>
                        <h1>Old count: <span dusk="child.old-count">{{ $oldCount }}</span>
                    </div>
                    HTML;
                }
            }
        ])
            ->assertSeeIn('@parent.count', 0)
            ->assertSeeIn('@child.count', 0)
            ->assertSeeIn('@child.old-count', 0)

            ->waitForLivewire()->click('@parent.inc')
            ->assertSeeIn('@parent.count', 1)
            ->assertSeeIn('@child.count', 1)
            ->assertSeeIn('@child.old-count', 0)

            ->waitForLivewire()->click('@parent.inc')
            ->assertSeeIn('@parent.count', 2)
            ->assertSeeIn('@child.count', 2)
            ->assertSeeIn('@child.old-count', 1)
        ;
    }

    public function test_reactive_property_does_not_trigger_hooks_when_value_unchanged()
    {
        Livewire::visit([
            new class extends Component {
                public $count = 5;
                public $other = 0;

                public function incOther() { $this->other++; }

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Parent count: <span dusk="parent.count">{{ $count }}</span>
                        <h1>Parent other: <span dusk="parent.other">{{ $other }}</span>

                        <button wire:click="incOther" dusk="parent.inc-other">inc other</button>

                        <livewire:child :$count />
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                #[BaseReactive]
                public $count;

                public $hookCallCount = 0;

                public function updatedCount($value)
                {
                    // Count how many times hook is called
                    $this->hookCallCount++;
                }

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Child count: <span dusk="child.count">{{ $count }}</span>
                        <h1>Hook call count: <span dusk="child.hook-calls">{{ $hookCallCount }}</span>
                    </div>
                    HTML;
                }
            }
        ])
            ->assertSeeIn('@parent.count', 5)
            ->assertSeeIn('@child.count', 5)
            ->assertSeeIn('@child.hook-calls', 0)

            // Trigger parent update that doesn't change $count
            ->waitForLivewire()->click('@parent.inc-other')
            ->assertSeeIn('@parent.other', 1)
            ->assertSeeIn('@child.count', 5)
            // Hook should NOT have been called since value didn't change
            ->assertSeeIn('@child.hook-calls', 0)

            ->waitForLivewire()->click('@parent.inc-other')
            ->assertSeeIn('@parent.other', 2)
            ->assertSeeIn('@child.count', 5)
            ->assertSeeIn('@child.hook-calls', 0)
        ;
    }

    public function test_reactive_property_does_not_trigger_hooks_when_same_value_passed_multiple_times()
    {
        Livewire::visit([
            new class extends Component {
                public $property = 1;
                public $other = 0;

                public function incOther() { $this->other++; }

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Parent property: <span dusk="parent.property">{{ $property }}</span>
                        <h1>Parent other: <span dusk="parent.other">{{ $other }}</span>

                        <button wire:click="incOther" dusk="parent.inc-other">inc other</button>

                        <livewire:child :$property />
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                #[BaseReactive]
                public $property;

                public $hookCallCount = 0;

                public function updatedProperty($value)
                {
                    // Count how many times hook is called
                    $this->hookCallCount++;
                }

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Child property: <span dusk="child.property">{{ $property }}</span>
                        <h1>Hook call count: <span dusk="child.hook-calls">{{ $hookCallCount }}</span>
                    </div>
                    HTML;
                }
            }
        ])
            ->assertSeeIn('@parent.property', 1)
            ->assertSeeIn('@child.property', 1)
            ->assertSeeIn('@child.hook-calls', 0)

            // Parent re-renders but property stays 1 -> 1, hook should NOT fire
            ->waitForLivewire()->click('@parent.inc-other')
            ->assertSeeIn('@parent.other', 1)
            ->assertSeeIn('@child.property', 1)
            ->assertSeeIn('@child.hook-calls', 0)

            // Another re-render, property still 1 -> 1, hook should NOT fire
            ->waitForLivewire()->click('@parent.inc-other')
            ->assertSeeIn('@parent.other', 2)
            ->assertSeeIn('@child.property', 1)
            ->assertSeeIn('@child.hook-calls', 0)
        ;
    }

    public function test_reactive_property_is_available_during_booted_lifecycle_hook()
    {
        Livewire::visit([
            new class extends Component {
                public $count = 0;

                public function inc() { $this->count++; }

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Parent count: <span dusk="parent.count">{{ $count }}</span>

                        <button wire:click="inc" dusk="parent.inc">inc</button>

                        <livewire:child :$count />
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                #[BaseReactive]
                public $count;

                public $bootedCount = 0;

                public function booted()
                {
                    // This should see the updated reactive prop value
                    $this->bootedCount = $this->count;
                }

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Child count: <span dusk="child.count">{{ $count }}</span>
                        <h1>Booted count: <span dusk="child.booted-count">{{ $bootedCount }}</span>
                    </div>
                    HTML;
                }
            }
        ])
            ->assertSeeIn('@parent.count', 0)
            ->assertSeeIn('@child.count', 0)
            ->assertSeeIn('@child.booted-count', 0)

            ->waitForLivewire()->click('@parent.inc')
            ->assertSeeIn('@parent.count', 1)
            ->assertSeeIn('@child.count', 1)
            ->assertSeeIn('@child.booted-count', 1)

            ->waitForLivewire()->click('@parent.inc')
            ->assertSeeIn('@parent.count', 2)
            ->assertSeeIn('@child.count', 2)
            ->assertSeeIn('@child.booted-count', 2)
        ;
    }

    public function test_child_is_skipped_when_reactive_scalar_prop_unchanged()
    {
        Livewire::visit([
            new class extends Component {
                public $count = 0;
                public $name = 'Taylor';

                public function inc() { $this->count++; }
                public function changeName() { $this->name = 'Caleb'; }

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Parent count: <span dusk="parent.count">{{ $count }}</span>

                        <button wire:click="inc" dusk="parent.inc">inc</button>
                        <button wire:click="changeName" dusk="parent.change-name">change name</button>

                        <livewire:child :name="$name" />
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                #[BaseReactive]
                public $name;

                public $renderCount = 0;

                public function render() { $this->renderCount++; return <<<'HTML'
                    <div>
                        <h1>Child name: <span dusk="child.name">{{ $name }}</span>
                        <h1>Child renders: <span dusk="child.renders">{{ $renderCount }}</span>
                    </div>
                    HTML;
                }
            }
        ])
            ->assertSeeIn('@parent.count', 0)
            ->assertSeeIn('@child.name', 'Taylor')
            ->assertSeeIn('@child.renders', 1)

            // Parent action that doesn't change the reactive prop — child should be skipped
            ->waitForLivewire()->click('@parent.inc')
            ->assertSeeIn('@parent.count', 1)
            ->assertSeeIn('@child.name', 'Taylor')
            ->assertSeeIn('@child.renders', 1)

            // Another parent action that doesn't change the prop — still skipped
            ->waitForLivewire()->click('@parent.inc')
            ->assertSeeIn('@parent.count', 2)
            ->assertSeeIn('@child.renders', 1)

            // Now change the reactive prop — child must re-render
            ->waitForLivewire()->click('@parent.change-name')
            ->assertSeeIn('@child.name', 'Caleb')
            ->assertSeeIn('@child.renders', 2)
        ;
    }

    public function test_child_is_skipped_when_reactive_model_prop_is_not_modified()
    {
        Livewire::visit([
            new class extends Component {
                public $count = 0;
                public ReactivePropsBrowserTestPost $post;

                public function mount() { $this->post = ReactivePropsBrowserTestPost::find(1); }

                public function inc() { $this->count++; }

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Parent count: <span dusk="parent.count">{{ $count }}</span>

                        <button wire:click="inc" dusk="parent.inc">inc</button>

                        <livewire:child :post="$post" />
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                #[BaseReactive]
                public ReactivePropsBrowserTestPost $post;

                public $renderCount = 0;

                public function render() { $this->renderCount++; return <<<'HTML'
                    <div>
                        <h1>Post title: <span dusk="child.title">{{ $post->title }}</span>
                        <h1>Child renders: <span dusk="child.renders">{{ $renderCount }}</span>
                    </div>
                    HTML;
                }
            }
        ])
            ->assertSeeIn('@parent.count', 0)
            ->assertSeeIn('@child.title', 'Post #1')
            ->assertSeeIn('@child.renders', 1)

            // Parent action that doesn't touch the model — child should be skipped
            ->waitForLivewire()->click('@parent.inc')
            ->assertSeeIn('@parent.count', 1)
            ->assertSeeIn('@child.title', 'Post #1')
            ->assertSeeIn('@child.renders', 1)

            ->waitForLivewire()->click('@parent.inc')
            ->assertSeeIn('@parent.count', 2)
            ->assertSeeIn('@child.renders', 1)
        ;
    }

    public function test_child_is_not_skipped_when_parent_modifies_model_attributes()
    {
        Livewire::visit([
            new class extends Component {
                public ReactivePropsBrowserTestPost $post;

                public function mount() { $this->post = ReactivePropsBrowserTestPost::find(1); }

                public function modifyTitle() { $this->post->title = 'Modified'; }

                public function render() { return <<<'HTML'
                    <div>
                        <button wire:click="modifyTitle" dusk="parent.modify">modify</button>

                        <livewire:child :post="$post" />
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                #[BaseReactive]
                public ReactivePropsBrowserTestPost $post;

                public $renderCount = 0;

                public function render() { $this->renderCount++; return <<<'HTML'
                    <div>
                        <h1>Post title: <span dusk="child.title">{{ $post->title }}</span>
                        <h1>Child renders: <span dusk="child.renders">{{ $renderCount }}</span>
                    </div>
                    HTML;
                }
            }
        ])
            ->assertSeeIn('@child.title', 'Post #1')
            ->assertSeeIn('@child.renders', 1)

            // Parent modifies model attributes (without saving) — child must re-render
            ->waitForLivewire()->click('@parent.modify')
            ->assertSeeIn('@child.title', 'Modified')
            ->assertSeeIn('@child.renders', 2)
        ;
    }

    public function test_child_is_not_skipped_when_parent_saves_model()
    {
        Livewire::visit([
            new class extends Component {
                public ReactivePropsBrowserTestPost $post;

                public function mount() { $this->post = ReactivePropsBrowserTestPost::find(1); }

                public function saveNewTitle()
                {
                    $this->post->title = 'Saved';
                    $this->post->save();
                }

                public function render() { return <<<'HTML'
                    <div>
                        <button wire:click="saveNewTitle" dusk="parent.save">save</button>

                        <livewire:child :post="$post" />
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                #[BaseReactive]
                public ReactivePropsBrowserTestPost $post;

                public $renderCount = 0;

                public function render() { $this->renderCount++; return <<<'HTML'
                    <div>
                        <h1>Post title: <span dusk="child.title">{{ $post->title }}</span>
                        <h1>Child renders: <span dusk="child.renders">{{ $renderCount }}</span>
                    </div>
                    HTML;
                }
            }
        ])
            ->assertSeeIn('@child.title', 'Post #1')
            ->assertSeeIn('@child.renders', 1)

            // Parent saves the model. After save, isDirty()=false but wasChanged()=true.
            // The child must still re-render to reflect the new attributes.
            ->waitForLivewire()->click('@parent.save')
            ->assertSeeIn('@child.title', 'Saved')
            ->assertSeeIn('@child.renders', 2)
        ;
    }

    public function test_child_is_not_skipped_when_parent_assigns_a_different_model()
    {
        Livewire::visit([
            new class extends Component {
                public ReactivePropsBrowserTestPost $post;

                public function mount() { $this->post = ReactivePropsBrowserTestPost::find(1); }

                public function swap() { $this->post = ReactivePropsBrowserTestPost::find(2); }

                public function render() { return <<<'HTML'
                    <div>
                        <button wire:click="swap" dusk="parent.swap">swap</button>

                        <livewire:child :post="$post" />
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                #[BaseReactive]
                public ReactivePropsBrowserTestPost $post;

                public $renderCount = 0;

                public function render() { $this->renderCount++; return <<<'HTML'
                    <div>
                        <h1>Post title: <span dusk="child.title">{{ $post->title }}</span>
                        <h1>Child renders: <span dusk="child.renders">{{ $renderCount }}</span>
                    </div>
                    HTML;
                }
            }
        ])
            ->assertSeeIn('@child.title', 'Post #1')
            ->assertSeeIn('@child.renders', 1)

            // Parent swaps to a different model — child must re-render
            ->waitForLivewire()->click('@parent.swap')
            ->assertSeeIn('@child.title', 'Post #2')
            ->assertSeeIn('@child.renders', 2)
        ;
    }
}

class ReactivePropsBrowserTestPost extends Model
{
    use Sushi;

    protected $guarded = [];

    public function getRows() {
        return [
            ['id' => 1, 'title' => 'Post #1'],
            ['id' => 2, 'title' => 'Post #2'],
        ];
    }
}
