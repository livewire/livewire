<?php

namespace Livewire\Tests;

use Livewire\Component;
use Livewire\Livewire;

class ComponentWontConflictWithPredefinedClasses extends \Tests\TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // We need to set Livewire's class namespace to the current namespace, because
        // Livewire's mechanism will only conflict with PHP's predefined classes
        // when the relative path to the class matches exactly to any of
        // the predefined classes (for example 'directory').
        config()->set('livewire.class_namespace', 'Livewire\\Tests');
    }

    public function test_wont_conflict_on_initial_request()
    {
        $component = Livewire::test(Directory::class);

        $component->assertSee('Count: 1');
    }

    public function test_wont_conflict_on_subsequent_requests()
    {
        $component = Livewire::test(Directory::class);

        $component->call('increment');
        $component->assertSee('Count: 2');
    }
}

class Directory extends Component
{
    public int $count = 1;

    public function increment()
    {
        $this->count++;
    }

    public function render()
    {
        return <<<'HTML'
            <div>
                Count: {{ $count }}
            </div>
        HTML;
    }
}
