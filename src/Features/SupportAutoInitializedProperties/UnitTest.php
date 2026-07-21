<?php

namespace Livewire\Features\SupportAutoInitializedProperties;

use Livewire\Attributes\Validate;
use Livewire\Form;
use Livewire\Livewire;
use Livewire\Selection;
use PHPUnit\Framework\Assert;
use Tests\TestComponent;

/**
 * Typed public properties whose synthesizer knows how to initialize them
 * (an initialize() method on the synth) spring to life automatically:
 *
 *     public Selection $selection;   // no mount() assignment needed
 *
 * The machinery lives in the synth system — see
 * HandleSynths::initializeProperties().
 */
class UnitTest extends \Tests\TestCase
{
    function test_a_typed_selection_property_is_automatically_initialized()
    {
        Livewire::test(new class extends TestComponent {
            public Selection $selection;
        })
        ->assertSet('selection', fn ($selection) => $selection instanceof Selection && $selection->isEmpty())
        ;
    }

    function test_a_selection_subclass_is_initialized_as_the_subclass()
    {
        Livewire::test(new class extends TestComponent {
            public CustomSelection $selection;
        })
        ->assertSet('selection', fn ($selection) => $selection instanceof CustomSelection)
        ;
    }

    function test_a_mount_assignment_is_never_clobbered()
    {
        Livewire::test(new class extends TestComponent {
            public Selection $selection;

            public function mount()
            {
                $this->selection = new Selection(['9']);
            }
        })
        ->assertSet('selection', fn ($selection) => $selection->keys() === ['9'])
        ;
    }

    function test_a_typed_form_object_still_initializes_through_the_shared_scan()
    {
        Livewire::test(new class extends TestComponent {
            public AutoInitFormStub $form;
        })
        ->assertSetStrict('form.title', '')
        ->set('form.title', 'Some title')
        ->assertSetStrict('form.title', 'Some title')
        ;
    }

    function test_untyped_and_builtin_properties_are_untouched()
    {
        Livewire::test(new class extends TestComponent {
            public $plain;

            public string $string = 'default';

            public Selection $selection;
        })
        ->assertSetStrict('plain', null)
        ->assertSetStrict('string', 'default')
        ->assertSet('selection', fn ($selection) => $selection instanceof Selection)
        ;
    }
}

class CustomSelection extends Selection
{
    //
}

class AutoInitFormStub extends Form
{
    #[Validate('required')]
    public $title = '';
}
