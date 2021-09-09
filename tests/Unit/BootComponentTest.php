<?php

namespace Tests\Unit;

use Illuminate\Support\Stringable;
use Livewire\Component;
use Livewire\Livewire;

class BootComponentTest extends TestCase
{
    /** @test */
    public function boot_method_is_called_on_mount_and_on_subsequent_updates()
    {
        Livewire::test(ComponentWithBootMethod::class)
            ->assertSet('memo', 'bootmount')
            ->call('$refresh')
            ->assertSet('memo', 'bootmountboothydrate');
    }

    /** @test */
    public function boot_method_can_be_added_to_trait()
    {
        Livewire::test(ComponentWithBootTrait::class)
            ->assertSet('memo', 'boottraitinitializemount')
            ->call('$refresh')
            ->assertSet('memo', 'boottraitinitializemountboottraitinitializehydrate');
    }

    /** @test */
    public function boot_method_supports_dependency_injection()
    {
        Livewire::test(ComponentWithBootMethodDI::class)
            ->assertSet('memo', 'boottrait')
            ->call('$refresh')
            ->assertSet('memo', 'boottraitboottrait');
    }
}

class ComponentWithBootMethod extends Component
{
    public $memo = '';

    public function mount()
    {
        $this->memo .= 'mount';
    }

    public function hydrate()
    {
        $this->memo .= 'hydrate';
    }

    public function boot()
    {
        $this->memo .= 'boot';
    }

    public function render()
    {
        return view('null-view');
    }
}

class ComponentWithBootTrait extends Component
{
    use BootMethodTrait;

    public $memo = '';

    public function mount()
    {
        $this->memo .= 'mount';
    }

    public function hydrate()
    {
        $this->memo .= 'hydrate';
    }

    public function boot()
    {
        $this->memo .= 'boot';
    }

    public function render()
    {
        return view('null-view');
    }
}

trait BootMethodTrait {
    public function bootBootMethodTrait()
    {
        $this->memo .= 'trait';
    }

    public function initializeBootMethodTrait()
    {
        $this->memo .= 'initialize';
    }
}

trait BootMethodTraitWithDI {
    public function bootBootMethodTraitWithDI(Stringable $string)
    {
        $this->memo .= $string->append('trait');
    }
}

class ComponentWithBootMethodDI extends Component
{
    use BootMethodTraitWithDI;

    public $memo = '';

    public function boot(Stringable $string)
    {
        $this->memo .= $string->append('boot');
    }

    public function render()
    {
        return view('null-view');
    }
}
