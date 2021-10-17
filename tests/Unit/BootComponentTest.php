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
            ->assertSet('memo', 'bootmountbooted')
            ->call('$refresh')
            ->assertSet('memo', 'boothydratebooted');
    }

    /** @test */
    public function boot_method_can_be_added_to_trait()
    {
        Livewire::test(ComponentWithBootTrait::class)
            ->assertSet('memo', 'boottraitboottraitinitializemountbootedtraitbooted')
            ->call('$refresh')
            ->assertSet('memo', 'boottraitboottraitinitializehydratebootedtraitbooted');
    }

    /** @test */
    public function boot_method_supports_dependency_injection()
    {
        Livewire::test(ComponentWithBootMethodDI::class)
            ->assertSet('memo', 'boottraitbootbootedtraitbooted')
            ->call('$refresh')
            ->assertSet('memo', 'boottraitbootbootedtraitbooted');
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

    public function booted()
    {
        $this->_memo .= 'booted';
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

    public function booted()
    {
        $this->_memo .= 'booted';
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
        $this->_memo .= 'traitboot';
    }

    public function initializeBootMethodTrait()
    {
        $this->_memo .= 'traitinitialize';
    }

    public function bootedBootMethodTrait()
    {
        $this->_memo .= 'traitbooted';
    }
}

trait BootMethodTraitWithDI
{
    public function bootBootMethodTraitWithDI(Stringable $string)
    {
        $this->_memo .= $string->append('traitboot');
    }

    public function bootedBootMethodTraitWithDI(Stringable $string)
    {
        $this->_memo .= $string->append('traitbooted');
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

    public function booted(Stringable $string)
    {
        $this->_memo .= $string->append('booted');
    }

    public function render()
    {
        $this->memo = $this->_memo;

        return view('null-view');
    }
}
