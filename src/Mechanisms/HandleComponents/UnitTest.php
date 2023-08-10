<?php

namespace Livewire\Mechanisms\HandleComponents;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Stringable;
use Livewire\Component;
use Livewire\Livewire;

class UnitTest extends \Tests\TestCase
{
    /** @test */
    public function it_restores_laravel_middleware_after_livewire_test()
    {
        // Run a basic Livewire test first to ensure Livewire has disabled
        // trim strings and convert empty strings to null middleware
        Livewire::test(BasicComponent::class)
            ->set('name', 'test')
            ->assertSet('name', 'test');

        // Then make a standard laravel test and ensure that the input has
        // had trim strings re-applied
        Route::post('laravel', function() {
            return 'laravel' . request()->input('name') . 'laravel';
        });

        $this->post('laravel', ['name' => '    aaa    '])
        ->assertSee('laravelaaalaravel');
    }

    /** @test */
    public function synthesized_property_types_are_preserved_after_update()
    {
        Livewire::test(new class extends Component {
            public $foo;
            public $isStringable;
            public function mount() { $this->foo = str('bar'); }
            public function checkStringable()
            {
                $this->isStringable = $this->foo instanceof Stringable;
            }
            public function render() { return '<div></div>'; }
        })
            ->assertSet('foo', 'bar')
            ->call('checkStringable')
            ->assertSet('isStringable', true)
            ->set('foo', 'baz')
            ->assertSet('foo', 'baz')
            ->call('checkStringable')
            ->assertSet('isStringable', true)
        ;
    }

    /** @test */
    public function uninitialized_integer_can_be_set_to_empty_string()
    {
        Livewire::test(new class extends Component {
            public int $count;

            public function render() {
                return <<<'HTML'
                    <div>
                        <h1 dusk="count">count: {{ $count }};</h1>
                    </div>
                HTML;
            }
        })
            ->assertSee('count: ;')
            ->set('count', 1)
            ->assertSee('count: 1;')
            ->set('count', '')
            ->assertSee('count: ;')
        ;
    }

    /** @test */
    public function livewire_request_data_doesnt_get_manipulated()
    {
        // This test is better done as a Laravel dusk test now.
        $this->markTestSkipped();

        // LivewireManager::$isLivewireRequestTestingOverride = true;

        // $this->refreshApplication();

        // $component = app(LivewireManager::class)->test(ComponentWithStringPropertiesStub::class);

        // $this->withHeader('X-Livewire', 'true')->post("/livewire/message/{$component->componentName}", [
        //     'updateQueue' => [],
        //     'name' => $component->componentName,
        //     'children' => $component->payload['children'],
        //     'data' => $component->payload['data'],
        //     'meta' => $component->payload['meta'],
        //     'id' => $component->payload['id'],
        //     'checksum' => $component->payload['checksum'],
        //     'locale' => $component->payload['locale'],
        //     'fromPrefetch' => [],
        // ])->assertJson(['data' => [
        //     'emptyString' => '',
        //     'oneSpace' => ' ',
        // ]]);

        // LivewireManager::$isLivewireRequestTestingOverride = null;

        // $this->refreshApplication();
    }

    /** @test */
    public function non_livewire_requests_do_get_manipulated()
    {
        // This test is better done as a Laravel dusk test now.
        $this->markTestSkipped();

        // $this->expectException(CorruptComponentPayloadException::class);

        // $component = app(LivewireManager::class)->test(ComponentWithStringPropertiesStub::class);

        // $this->withMiddleware()->post("/livewire/message/{$component->componentName}", [
        //     'updateQueue' => [],
        //     'name' => $component->componentName,
        //     'children' => $component->payload['children'],
        //     'data' => $component->payload['data'],
        //     'meta' => $component->payload['meta'],
        //     'id' => $component->payload['id'],
        //     'checksum' => $component->payload['checksum'],
        //     'locale' => $component->payload['locale'],
        //     'fromPrefetch' => [],
        // ])->assertJson(['data' => [
        //     'emptyString' => null,
        //     'oneSpace' => null,
        // ]]);
    }
}

class BasicComponent extends Component
{
    public $name;

    public function render()
    {
        return view('null-view');
    }
}

class ComponentWithStringPropertiesStub extends Component
{
    public $emptyString = '';
    public $oneSpace = ' ';

    public function render()
    {
        return app('view')->make('null-view');
    }
}
