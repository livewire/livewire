<?php

namespace Tests;

use Livewire\Exceptions\ComponentMismatchException;
use Livewire\Component;

class SubsequentComponentRequestsMustMatchOriginalComponentTest extends TestCase
{
    function test()
    {
        $this->expectException(ComponentMismatchException::class);

        app('livewire')->component('safe', SafeComponentStub::class);
        app('livewire')->component('unsafe', UnsafeComponentStub::class);
        $component = app('livewire')->test('safe');

        // Hijack the "safe" component, with "unsafe"
        $component->name = 'unsafe';

        // If the hijack was stopped, the expected exception will be thrown.
        // If it worked the, an execption will be thrown that will fail the test.
        $component->runAction('someMethod');
    }
}

class SafeComponentStub extends Component {
    public function someMethod()
    {
        return;
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}

class UnsafeComponentStub extends Component {
    public function someMethod()
    {
        throw new \Exception('Should not be able to acess me!');
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
