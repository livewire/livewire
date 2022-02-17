<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Livewire;
use Livewire\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class DataBindingTest extends TestCase
{
    /** @test */
    public function update_component_data()
    {
        $component = Livewire::test(DataBindingStub::class);

        $component->updateProperty('foo', 'bar');

        $this->assertEquals('bar', $component->foo);
    }

    /** @test */
    public function update_nested_component_data_inside_array()
    {
        $component = Livewire::test(DataBindingStub::class);

        $component->updateProperty('foo', []);
        $component->updateProperty('foo.0', 'bar');
        $component->updateProperty('foo.bar', 'baz');

        $this->assertEquals(['bar', 'bar' => 'baz'], $component->foo);
    }

    /** @test */
    public function property_is_marked_as_dirty_if_changed_as_side_effect_of_an_action()
    {
        $component = Livewire::test(DataBindingStub::class);

        $component->updateProperty('foo', 'bar');

        $this->assertEquals('bar', $component->foo);
        $this->assertEmpty($component->payload['effects']['dirty'] ?? []);

        $component->runAction('changeFoo', 'baz');

        $this->assertEquals('baz', $component->foo);
        $this->assertContains('foo', $component->payload['effects']['dirty']);
    }

    /** @test */
    public function update_component_data_with_custom_files_structure()
    {
        $component = Livewire::test(DataBindingStub::class);

        $component->updateProperty([
            'files' => [
                'type_1' => [
                    TemporaryUploadedFile::fake()->image('sample.jpg', 521, 732),
                    TemporaryUploadedFile::fake()->image('sample2.png', 1001, 1702),
                    TemporaryUploadedFile::fake()->image('sample3.png', 1510, 1920),
                ],
                'type_2' => [],
            ],
        ]);

        $filesProperty = $component->files;

        $this->assertIsArray($filesProperty);

        $this->assertArrayHasKey('type_1', $filesProperty);
        $this->assertArrayHasKey('type_2', $filesProperty);
        $this->assertIsString( $filesProperty['type_1']);
        $this->assertStringStartsWith('livewire-files:[', $filesProperty['type_1']);
        $this->assertSame([], $filesProperty['type_2']);
    }

    /** @test */
    public function nested_property_is_marked_as_dirty_if_changed_as_side_effect_of_an_action()
    {
        $component = Livewire::test(DataBindingStub::class);

        $component->updateProperty('arrayProperty.1', 'baz');

        $this->assertEquals(['foo', 'baz'], $component->arrayProperty);
        $this->assertEmpty($component->payload['effects']['dirty'] ?? []);

        $component->runAction('changeArrayPropertyOne', 'bar');

        $this->assertEquals(['foo', 'bar'], $component->arrayProperty);
        $this->assertContains('arrayProperty.1', $component->payload['effects']['dirty']);
    }

    /** @test */
    public function nested_property_is_marked_as_dirty_if_removed_as_side_effect_of_an_action()
    {
        $component = Livewire::test(DataBindingStub::class);

        $component->runAction('removeArrayPropertyOne');

        $this->assertEquals(['foo'], $component->arrayProperty);
        $this->assertContains('arrayProperty.1', $component->payload['effects']['dirty']);
    }

    /** @test */
    public function property_is_marked_as_dirty_if_changed_as_side_effect_of_an_action_even_if_the_action_is_data_binding_for_that_specific_property()
    {
        $component = Livewire::test(DataBindingStub::class);

        $component->set('propertyWithHook', 'something');

        $this->assertEquals('something else', $component->propertyWithHook);
        $this->assertContains('propertyWithHook', $component->payload['effects']['dirty']);
    }
}

class DataBindingStub extends Component
{
    use WithFileUploads;

    public $foo;
    public $bar;
    public $files;
    public $propertyWithHook;
    public $arrayProperty = ['foo', 'bar'];

    public function updatedPropertyWithHook($value)
    {
        $this->propertyWithHook = 'something else';
    }

    public function changeFoo($value)
    {
        $this->foo = $value;
    }

    public function changeArrayPropertyOne($value)
    {
        $this->arrayProperty[1] = $value;
    }

    public function removeArrayPropertyOne()
    {
        unset($this->arrayProperty[1]);
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
