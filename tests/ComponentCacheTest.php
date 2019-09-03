<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;

class ComponentCacheTest extends TestCase
{
    /** @test */
    public function component_can_store_things_in_cache()
    {
        $component = app(LivewireManager::class)->test(ComponentWithCache::class);

        $component
            ->assertSet('foo', null)
            ->call('setValueFromCache')
            ->assertSet('foo', 'bar');
    }

    /** @test */
    public function protected_properties_are_dehydrated_into_the_cache_and_not_in_the_payload()
    {
        $component = app(LivewireManager::class)->test(ComponentWithCache::class);

        $component->call('setValueOfFiz', 'bluth');

        $this->assertNotContains('fiz', $component->data);
        $this->assertEquals('bluth', cache()->get("{$component->id}")['__protected_properties']['fiz']);
    }

    /** @test */
    public function protected_properties_are_rehydrated_from_the_cache()
    {
        $component = app(LivewireManager::class)->test(ComponentWithCache::class);

        $this->assertNotEquals('bluth', $component->fiz);

        cache()->put("{$component->id}", ['__protected_properties' => ['fiz' => 'bluth']]);

        $component->call('$refresh');

        $this->assertEquals('bluth', $component->fiz);
    }

    /** @test */
    public function components_cache_data_is_garbage_collected()
    {
        $oldComponent = app(LivewireManager::class)->test(ComponentWithCache::class);
        $component = app(LivewireManager::class)->test(ComponentWithCache::class);

        $this->assertNotNull(cache()->get($oldComponent->id));
        $this->assertNotNull(cache()->get($component->id));

        $component->gc = [$oldComponent->id];

        $component->call('$refresh');

        $this->assertNull(cache()->get($oldComponent->id));
        $this->assertNotNull(cache()->get($component->id));
        $this->assertCount(0, $component->gc);
    }
}

class ComponentWithCache extends Component
{
    public $foo;
    protected $fiz;

    public function mount()
    {
        $this->cache('foo', 'bar');

        $this->fiz = 'buz';
    }

    public function setValueOfFiz($value)
    {
        $this->fiz = $value;
    }

    public function setValueFromCache()
    {
        $this->foo = $this->cache('foo');
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
