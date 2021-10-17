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
            ->assertSet('memo', 'boothydrate');
    }

    /** @test */
    public function boot_method_can_be_added_to_trait()
    {
        Livewire::test(ComponentWithBootTrait::class)
            ->assertSet('memo', 'boottraitinitializemount')
            ->call('$refresh')
            ->assertSet('memo', 'boottraitinitializehydrate');
    }

    /** @test */
    public function boot_method_supports_dependency_injection()
    {
        Livewire::test(ComponentWithBootMethodDI::class)
            ->assertSet('memo', 'boottrait')
            ->call('$refresh')
            ->assertSet('memo', 'boottrait');
    }
}

class ComponentWithBootMethod extends Component
{
    // Use protected property to record all memo's
    // as hydrating memo wipes out changes from boot
    protected $_memo = '';
    public $memo = '';

    public function boot()
    {
        $this->_memo .= 'boot';
    }

    public function mount()
    {
        $this->_memo .= 'mount';
    }

    public function hydrate()
    {
        $this->_memo .= 'hydrate';
    }

    public function render()
    {
        $this->memo = $this->_memo;

        return view('null-view');
    }
}

class ComponentWithBootTrait extends Component
{
    use BootMethodTrait;

    // Use protected property to record all memo's
    // as hydrating memo wipes out changes from boot
    protected $_memo = '';
    public $memo = '';

    public function boot()
    {
        $this->_memo .= 'boot';
    }

    public function mount()
    {
        $this->_memo .= 'mount';
    }

    public function hydrate()
    {
        $this->_memo .= 'hydrate';
    }

    public function render()
    {
        $this->memo = $this->_memo;
        return view('null-view');
    }
}

trait BootMethodTrait
{
    public function bootBootMethodTrait()
    {
        $this->_memo .= 'trait';
    }

    public function initializeBootMethodTrait()
    {
        $this->_memo .= 'initialize';
    }
}

trait BootMethodTraitWithDI
{
    public function bootBootMethodTraitWithDI(Stringable $string)
    {
        $this->_memo .= $string->append('trait');
    }
}

class ComponentWithBootMethodDI extends Component
{
    use BootMethodTraitWithDI;

    // Use protected property to record all memo's
    // as hydrating memo wipes out changes from boot
    protected $_memo = '';
    public $memo = '';

    public function boot(Stringable $string)
    {
        $this->_memo .= $string->append('boot');
    }

    public function render()
    {
        $this->memo = $this->_memo;
        return view('null-view');
    }
}
