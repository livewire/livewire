<?php

namespace Livewire\Features\SupportVirtualProperties;

use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Virtual;
use Livewire\Form;
use Livewire\Livewire;
use Tests\TestComponent;

class FormObjectUnitTest extends \Tests\TestCase
{
    function test_a_virtual_form_object_is_booted()
    {
        Livewire::test(VirtualFormComponent::class)
            ->assertSetStrict('form.bootCount', 1);
    }

    function test_a_virtual_form_object_is_booted_exactly_once_per_request()
    {
        // The snapshot carries bootCount, so it can't tell one boot from two —
        // count them out of band. Hydration reuses the materialized instance,
        // and a second boot would mean a second set of registered attributes...
        VirtualForm::$boots = 0;

        Livewire::test(VirtualFormComponent::class);
        $this->assertSame(1, VirtualForm::$boots);

        VirtualForm::$boots = 0;

        Livewire::test(VirtualFormComponent::class)->set('form.title', 'Something');
        $this->assertSame(2, VirtualForm::$boots); // one for the mount, one for the update
    }

    function test_a_virtual_form_object_honors_validate_attributes()
    {
        Livewire::test(VirtualFormComponent::class)
            ->call('save')
            ->assertHasErrors(['form.title' => 'required']);
    }

    function test_a_virtual_form_object_validates_on_update()
    {
        Livewire::test(VirtualFormComponent::class)
            ->set('form.title', '')
            ->assertHasErrors(['form.title' => 'required'])
            ->set('form.title', 'Filled in')
            ->assertHasNoErrors();
    }

    function test_a_virtual_form_object_reads_url_attributes_from_the_query_string()
    {
        Livewire::withQueryParams(['title' => 'from-url'])
            ->test(VirtualFormComponent::class)
            ->assertSetStrict('form.title', 'from-url');
    }

    function test_a_virtual_form_object_pushes_the_same_query_string_effect_as_a_declared_one()
    {
        $virtual = Livewire::withQueryParams([])
            ->test(VirtualFormComponent::class)
            ->effects;

        $declared = Livewire::withQueryParams([])
            ->test(new class extends TestComponent {
                public VirtualForm $form;
            })
            ->effects;

        $this->assertSame(['form.title'], array_keys($virtual['url']));
        $this->assertSame($declared['url'], $virtual['url']);
    }

    function test_a_virtual_form_object_round_trips_its_state()
    {
        Livewire::test(VirtualFormComponent::class)
            ->set('form.title', 'Kept')
            ->call('$refresh')
            ->assertSetStrict('form.title', 'Kept');
    }

    function test_a_virtual_form_object_keeps_configuration_that_does_not_serialize()
    {
        // The method — not the snapshot — is the source of the closure, so it
        // has to survive a round trip the way a declared form's boot would...
        Livewire::test(VirtualFormComponent::class)
            ->set('form.title', 'shout')
            ->call('applyDecorator')
            ->assertSetStrict('form.title', 'SHOUT');
    }

    function test_a_virtual_form_object_runs_its_updated_hooks()
    {
        Livewire::test(VirtualFormComponent::class)
            ->set('form.title', 'Hooked')
            ->assertSetStrict('form.updatedPaths', ['title']);
    }

    function test_a_consolidated_update_to_a_virtual_form_is_decomposed_into_property_updates()
    {
        // The JS diff sends the whole form as one update when every property
        // changed. Each one still has to go through the normal update path...
        Livewire::test(VirtualFormComponent::class)
            ->set('form', ['title' => 'Whole', 'status' => 'draft'])
            ->assertSetStrict('form.title', 'Whole')
            ->assertSetStrict('form.updatedPaths', ['title', 'status']);
    }

    function test_a_virtual_form_object_can_be_reset()
    {
        Livewire::test(VirtualFormComponent::class)
            ->set('form.title', 'Filled in')
            ->call('startOver')
            ->assertSetStrict('form.title', '')
            ->call('save')
            ->assertHasErrors(['form.title' => 'required']);
    }

    function test_replacing_a_virtual_form_object_detaches_the_old_instance()
    {
        // Rebuilding the instance re-registers the form's attributes. If the
        // abandoned one stays registered too, it keeps validating its own
        // stale data and reports errors for a form nobody can see...
        Livewire::test(ResettingVirtualFormComponent::class)
            ->set('form.title', 'Filled in')
            ->assertHasNoErrors()
            ->assertSetStrict('form.title', 'Filled in');
    }

    function test_a_virtual_form_object_can_be_an_anonymous_class()
    {
        // The payoff: a bespoke one-off form has no name to typehint, so it
        // can't be expressed as a declared property at all...
        Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function form(): Form
            {
                return new class ($this, 'form') extends Form {
                    #[Validate('required')]
                    public $name = '';
                };
            }

            public function save() { $this->form->validate(); }
        })
            ->call('save')
            ->assertHasErrors(['form.name' => 'required'])
            ->set('form.name', 'Caleb')
            ->call('save')
            ->assertHasNoErrors();
    }
}

class VirtualFormComponent extends TestComponent
{
    public function save()
    {
        $this->form->validate();
    }

    public function startOver()
    {
        $this->reset('form');
    }

    public function applyDecorator()
    {
        $this->form->decorate();
    }

    #[Virtual]
    public function form(): VirtualForm
    {
        return new VirtualForm($this, 'form', decorate: fn ($value) => strtoupper($value));
    }
}

class ResettingVirtualFormComponent extends VirtualFormComponent
{
    public function boot()
    {
        // Empty the instance out, then throw it away for a fresh one...
        $this->form->title = '';

        $this->reset('form');
    }
}

class VirtualForm extends Form
{
    #[Validate('required')]
    #[Url]
    public $title = '';

    public $status = '';

    public $bootCount = 0;

    public $updatedPaths = [];

    // Boots counted out of band, where the snapshot can't paper over them...
    public static $boots = 0;

    // Protected, so it never crosses the wire — the virtual method rebuilds
    // it from code on every request...
    protected $decorator;

    public function __construct($component, $propertyName, $decorate = null)
    {
        parent::__construct($component, $propertyName);

        $this->decorator = $decorate;
    }

    public function decorate()
    {
        $this->title = ($this->decorator)($this->title);
    }

    public function boot()
    {
        $this->bootCount++;

        static::$boots++;
    }

    public function updated($path)
    {
        $this->updatedPaths[] = $path;
    }
}
