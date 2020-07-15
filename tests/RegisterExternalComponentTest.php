<?php

namespace Tests;

use Illuminate\Filesystem\Filesystem;
use Livewire\Component;
use Livewire\Livewire;
use Livewire\LivewireComponentsFinder;

class RegisterExternalComponentTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->files = new Filesystem;
        $this->files->makeDirectory(app_path('Http/Livewire'));
    }

    /** @test */
    public function can_register_external_component() {
        $finder = app()->make(LivewireComponentsFinder::class);

        $finder->registerExternal('external', ExternalComponent\Hello::class);

        $this->assertNotNull($finder->find('external'));
    }
}

namespace ExternalComponent;

use Livewire\Component;

class Hello extends Component
{
    //
}
