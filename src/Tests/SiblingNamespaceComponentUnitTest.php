<?php

namespace Livewire\Tests;

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Livewire\Tests\SiblingNamespace\Forms\ContactForm as InsideContactForm;
use Livewire\Tests\SiblingNamespaceForms\ContactForm as OutsideContactForm;

class SiblingNamespaceComponentUnitTest extends \Tests\TestCase
{
    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);

        // `Livewire\Tests\SiblingNamespaceForms` shares a character prefix with this, but is
        // a different namespace and is not a registered component location...
        $app['config']->set('livewire.class_namespace', 'Livewire\\Tests\\SiblingNamespace');
    }

    public function test_a_sibling_namespace_sharing_a_character_prefix_keeps_its_full_name()
    {
        $this->assertEquals(
            'livewire.tests.sibling-namespace-forms.contact-form',
            Livewire::new(OutsideContactForm::class)->getName()
        );
    }

    public function test_a_class_inside_the_namespace_still_gets_the_prefix_stripped()
    {
        $this->assertEquals('forms.contact-form', Livewire::new(InsideContactForm::class)->getName());
    }

    public function test_the_two_classes_do_not_collide_on_the_same_name()
    {
        $this->assertNotEquals(
            Livewire::new(InsideContactForm::class)->getName(),
            Livewire::new(OutsideContactForm::class)->getName()
        );
    }

    public function test_each_class_renders_itself_and_not_the_other()
    {
        Route::get('/inside', InsideContactForm::class);
        Route::get('/outside', OutsideContactForm::class);

        $this->get('/inside')
            ->assertOk()
            ->assertSee('inside the configured namespace')
            ->assertDontSee('outside the configured namespace');

        $this->get('/outside')
            ->assertOk()
            ->assertSee('outside the configured namespace')
            ->assertDontSee('inside the configured namespace');
    }
}
