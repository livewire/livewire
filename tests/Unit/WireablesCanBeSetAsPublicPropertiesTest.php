<?php

namespace Tests\Unit;

use Illuminate\Support\Str;
use Livewire\Livewire;

class WireablesCanBeSetAsPublicPropertiesTest extends TestCase
{
    /** @test */
    public function a_wireable_can_be_set_as_a_public_property_and_validates()
    {
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            $this->markTestSkipped('Typed Property Initialization not supported prior to PHP 7.4');
        }

        require_once __DIR__.'/WireablesCanBeSetAsPublicPropertiesStubs.php';

        $wireable = new WireableClass($message = Str::random(), $embeddedMessage = Str::random());

        Livewire::test(ComponentWithWireablePublicProperty::class, ['wireable' => $wireable])
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('$refresh')
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('runValidation')
            ->assertHasNoErrors(['wireable.message', 'wireable.embeddedWireable.message'])
            ->call('removeWireable')
            ->assertDontSee($message)
            ->assertDontSee($embeddedMessage);
    }

    /** @test */
    public function a_wireable_can_be_set_as_a_public_property_and_validates_only()
    {
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            $this->markTestSkipped('Typed Property Initialization not supported prior to PHP 7.4');
        }

        require_once __DIR__.'/WireablesCanBeSetAsPublicPropertiesStubs.php';

        $wireable = new WireableClass($message = Str::random(), $embeddedMessage = Str::random());

        Livewire::test(ComponentWithWireablePublicProperty::class, ['wireable' => $wireable])
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('$refresh')
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('runValidateOnly', 'wireable.message')
            ->assertHasNoErrors('wireable.message')
            ->call('runValidateOnly', 'wireable.embeddedWireable.message')
            ->assertHasNoErrors('wireable.embeddedWireable.message')
            ->call('removeWireable')
            ->assertDontSee($message)
            ->assertDontSee($embeddedMessage);
    }

    /** @test */
    public function a_wireable_can_be_set_as_a_public_property_and_has_single_validation_error()
    {
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            $this->markTestSkipped('Typed Property Initialization not supported prior to PHP 7.4');
        }

        require_once __DIR__.'/WireablesCanBeSetAsPublicPropertiesStubs.php';

        $wireable = new WireableClass($message = '', $embeddedMessage = Str::random());

        Livewire::test(ComponentWithWireablePublicProperty::class, ['wireable' => $wireable])
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('$refresh')
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('runValidation')
            ->assertHasErrors(['wireable.message' => 'required'])
            ->assertHasNoErrors('wireable.embeddedWireable.message')
            ->call('removeWireable')
            ->assertDontSee($embeddedMessage);
    }

    /** @test */
    public function a_wireable_can_be_set_as_a_public_property_and_has_single_validation_error_on_validates_only()
    {
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            $this->markTestSkipped('Typed Property Initialization not supported prior to PHP 7.4');
        }

        require_once __DIR__.'/WireablesCanBeSetAsPublicPropertiesStubs.php';

        $wireable = new WireableClass($message = '', $embeddedMessage = Str::random());

        Livewire::test(ComponentWithWireablePublicProperty::class, ['wireable' => $wireable])
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('$refresh')
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('runValidateOnly', 'wireable.message')
            ->assertHasErrors(['wireable.message' => 'required'])
            ->assertHasNoErrors('wireable.embeddedWireable.message')
            ->call('removeWireable')
            ->assertDontSee($embeddedMessage);
    }

    /** @test */
    public function a_wireable_can_be_set_as_a_public_property_and_has_embedded_validation_error()
    {
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            $this->markTestSkipped('Typed Property Initialization not supported prior to PHP 7.4');
        }

        require_once __DIR__.'/WireablesCanBeSetAsPublicPropertiesStubs.php';

        $wireable = new WireableClass($message = Str::random(), $embeddedMessage = '');

        Livewire::test(ComponentWithWireablePublicProperty::class, ['wireable' => $wireable])
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('$refresh')
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('runValidation')
            ->assertHasErrors(['wireable.embeddedWireable.message' => 'required'])
            ->assertHasNoErrors('wireable.message')
            ->call('removeWireable')
            ->assertDontSee($message);
    }

    /** @test */
    public function a_wireable_can_be_set_as_a_public_property_and_has_embedded_validation_error_on_validate_only()
    {
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            $this->markTestSkipped('Typed Property Initialization not supported prior to PHP 7.4');
        }

        require_once __DIR__.'/WireablesCanBeSetAsPublicPropertiesStubs.php';

        $wireable = new WireableClass($message = Str::random(), $embeddedMessage = '');

        Livewire::test(ComponentWithWireablePublicProperty::class, ['wireable' => $wireable])
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('$refresh')
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('runValidateOnly', 'wireable.embeddedWireable.message')
            ->assertHasErrors(['wireable.embeddedWireable.message' => 'required'])
            ->assertHasNoErrors('wireable.message')
            ->call('removeWireable')
            ->assertDontSee($message);
    }

    /** @test */
    public function a_wireable_can_be_set_as_a_public_property_and_has_single_and_embedded_validation_errors()
    {
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            $this->markTestSkipped('Typed Property Initialization not supported prior to PHP 7.4');
        }

        require_once __DIR__.'/WireablesCanBeSetAsPublicPropertiesStubs.php';

        $wireable = new WireableClass($message = '', $embeddedMessage = '');

        Livewire::test(ComponentWithWireablePublicProperty::class, ['wireable' => $wireable])
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('$refresh')
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('runValidation')
            ->assertHasErrors(['wireable.message' => 'required', 'wireable.embeddedWireable.message' => 'required'])
            ->call('removeWireable');
    }

    /** @test */
    public function a_wireable_can_be_set_as_a_public_property_and_has_single_and_embedded_validation_errors_on_validate_only()
    {
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            $this->markTestSkipped('Typed Property Initialization not supported prior to PHP 7.4');
        }

        require_once __DIR__.'/WireablesCanBeSetAsPublicPropertiesStubs.php';

        $wireable = new WireableClass($message = '', $embeddedMessage = '');

        Livewire::test(ComponentWithWireablePublicProperty::class, ['wireable' => $wireable])
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('$refresh')
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('runValidateOnly', 'wireable.message')
            ->assertHasErrors(['wireable.message' => 'required'])
            ->call('$refresh')
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('runValidateOnly', 'wireable.embeddedWireable.message')
            ->assertHasErrors(['wireable.embeddedWireable.message' => 'required'])
            ->call('removeWireable');
    }
}
