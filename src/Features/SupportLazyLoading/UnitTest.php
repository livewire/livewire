<?php

namespace Livewire\Features\SupportLazyLoading;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Defer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use Livewire\Component;
use Livewire\Exceptions\MethodNotFoundException;
use Livewire\Livewire;
use Livewire\Mechanisms\HandleComponents\CorruptComponentPayloadException;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;
use PHPUnit\Framework\Attributes\DataProvider;

class UnitTest extends \Tests\TestCase
{
    public function test_typeerror_in_lazy_mount_is_still_reported()
    {
        config()->set('app.debug', false);
        SupportLazyLoading::$disableWhileTesting = false;

        $component = Livewire::test(new #[Lazy] class extends Component {
            public function mount() { strlen([]); }
            public function placeholder() { return '<div>Loading...</div>'; }
            public function render() { return '<div>Loaded</div>'; }
        });

        preg_match("/__lazyLoad\('([^']+)'\)/", html_entity_decode($component->html()), $matches);

        $reported = [];

        app(ExceptionHandler::class)->reportable(function (\Throwable $e) use (&$reported) {
            $reported[] = $e;

            return false;
        });

        $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [[
                'snapshot' => json_encode($component->snapshot),
                'updates' => [],
                'calls' => [['method' => '__lazyLoad', 'params' => [$matches[1]]]],
            ]]])
            ->assertStatus(500);

        $this->assertCount(1, $reported);
        $this->assertInstanceOf(\TypeError::class, $reported[0]);
    }

    #[DataProvider('invalidLazyLoadParams')]
    public function test_invalid_lazy_load_encoding_is_not_reported($params)
    {
        config()->set('app.debug', false);
        SupportLazyLoading::$disableWhileTesting = false;

        $component = Livewire::test(BasicLazyComponent::class);
        $reported = [];

        app(ExceptionHandler::class)->reportable(function (\Throwable $e) use (&$reported) {
            $reported[] = $e;

            return false;
        });

        $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [[
                'snapshot' => json_encode($component->snapshot),
                'updates' => [],
                'calls' => [['method' => '__lazyLoad', 'params' => $params]],
            ]]])
            ->assertStatus(419);

        $this->assertEmpty($reported);
    }

    public static function invalidLazyLoadParams()
    {
        return [
            'missing' => [[]],
            'null' => [[null]],
            'integer' => [[42]],
            'array' => [[[]]],
            'invalid base64' => [['|']],
        ];
    }

    public function test_invalid_base64_characters_are_not_discarded_from_a_lazy_snapshot()
    {
        config()->set('app.debug', true);
        SupportLazyLoading::$disableWhileTesting = false;

        $component = Livewire::test(BasicLazyComponent::class);
        preg_match("/__lazyLoad\('([^']+)'\)/", html_entity_decode($component->html()), $matches);

        $this->expectException(CorruptComponentPayloadException::class);

        $component->call('__lazyLoad', $matches[1].'|');
    }

    #[DataProvider('malformedSnapshots')]
    public function test_malformed_lazy_snapshot_is_not_reported($snapshot)
    {
        config()->set('app.debug', false);
        SupportLazyLoading::$disableWhileTesting = false;

        $component = Livewire::test(BasicLazyComponent::class);
        $reported = [];

        app(ExceptionHandler::class)->reportable(function (\Throwable $e) use (&$reported) {
            $reported[] = $e;

            return false;
        });

        $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [[
                'snapshot' => json_encode($component->snapshot),
                'updates' => [],
                'calls' => [['method' => '__lazyLoad', 'params' => [base64_encode($snapshot)]]],
            ]]])
            ->assertNotFound();

        $this->assertEmpty($reported);
    }

    public static function malformedSnapshots()
    {
        return [
            'invalid JSON' => ['{'],
            'null' => ['null'],
            'scalar' => ['42'],
            'empty array' => ['[]'],
            'empty object' => ['{}'],
            'missing checksum' => ['{"data":[],"memo":{"id":"abc","name":"foo"}}'],
            'non-string checksum' => ['{"data":[],"memo":{"id":"abc","name":"foo"},"checksum":[]}'],
            'non-string name' => ['{"data":[],"memo":{"id":"abc","name":[]},"checksum":"hash"}'],
        ];
    }

    public function test_malformed_lazy_snapshot_has_a_useful_error_in_debug_mode()
    {
        config()->set('app.debug', true);
        SupportLazyLoading::$disableWhileTesting = false;

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Livewire snapshot structure');

        Livewire::test(BasicLazyComponent::class)->call('__lazyLoad', base64_encode('{}'));
    }

    public function test_can_lazy_load_component_with_custom_layout()
    {
        Livewire::component('page', PageWithCustomLayout::class);
        Route::get('/one', PageWithCustomLayout::class)->middleware('web');

        Livewire::component('page', PageWithCustomLayoutOnView::class);
        Route::get('/two', PageWithCustomLayoutOnView::class)->middleware('web');

        Livewire::component('page', PageWithCustomLayoutAttributeOnMethod::class);
        Route::get('/three', PageWithCustomLayoutAttributeOnMethod::class)->middleware('web');

        $this->get('/one')->assertSee('This is a custom layout');
        $this->get('/two')->assertSee('This is a custom layout');
        $this->get('/three')->assertSee('This is a custom layout');
    }

    public function test_can_disable_lazy_loading_during_unit_tests()
    {
        Livewire::component('lazy-component', BasicLazyComponent::class);

        Livewire::withoutLazyLoading()->test(new class extends Component {
            public function render()
            {
                return <<<'HTML'
                    <div>
                        <livewire:lazy-component />
                    </div>
                HTML;
            }
        })
        ->assertDontSee('Loading...')
        ->assertSee('Hello world!');
    }

    public function test_can_disable_lazy_loading_with_lazy_false_parameter()
    {
        Livewire::test(BasicLazyComponent::class, ['lazy' => false])
            ->assertDontSee('Loading...')
            ->assertSee('Hello world!');
    }

    public function test_can_disable_deferred_loading_with_defer_false_parameter()
    {
        Livewire::test(BasicDeferComponent::class, ['defer' => false])
            ->assertDontSee('Loading...')
            ->assertSee('Hello world!');
    }

    public function test_lazy_false_does_not_affect_defer_attribute()
    {
        Livewire::test(BasicDeferComponent::class, ['lazy' => false])
            ->assertSee('Loading...')
            ->assertDontSee('Hello world!');
    }

    public function test_defer_false_does_not_affect_lazy_attribute()
    {
        Livewire::test(BasicLazyComponent::class, ['defer' => false])
            ->assertSee('Loading...')
            ->assertDontSee('Hello world!');
    }

    public function test_a_lazy_component_loads_with_its_own_mount_params()
    {
        SupportLazyLoading::$disableWhileTesting = false;

        Livewire::component('lazy-alpha', LazyAlpha::class);

        $html = html_entity_decode(Livewire::mount('lazy-alpha', ['level' => 5]));
        preg_match("/__lazyLoad\('([^']+)'\)/", $html, $matches);

        $this->assertNotEmpty($matches[1] ?? null);

        Livewire::test('lazy-alpha')
            ->call('__lazyLoad', $matches[1])
            ->assertSee('level:5');
    }

    public function test_a_repeat_lazy_load_call_is_ignored_after_the_component_has_loaded()
    {
        SupportLazyLoading::$disableWhileTesting = false;

        Livewire::component('lazy-alpha', LazyAlpha::class);

        $component = Livewire::test('lazy-alpha', ['level' => 5]);

        preg_match("/__lazyLoad\('([^']+)'\)/", html_entity_decode($component->html()), $matches);

        $component
            ->call('__lazyLoad', $matches[1])
            ->assertSee('level:5')
            ->call('__lazyLoad', $matches[1])
            ->assertSee('level:5')
            ->assertSee('mounts:1')
            ->call('__lazyLoad', $matches[1])
            ->assertSee('level:5')
            ->assertSee('mounts:1');
    }

    public function test_a_lazy_load_call_on_a_non_lazy_component_is_not_claimed()
    {
        $this->expectException(MethodNotFoundException::class);

        Livewire::test(new class extends Component {
            public function render() {
                return '<div></div>';
            }
        })->call('__lazyLoad', 'invalid');
    }

    public function test_a_mount_params_container_is_scoped_to_its_own_component()
    {
        SupportLazyLoading::$disableWhileTesting = false;

        Livewire::component('lazy-alpha', LazyAlpha::class);
        Livewire::component('lazy-beta', LazyBeta::class);

        $html = html_entity_decode(Livewire::mount('lazy-alpha', ['level' => 1]));
        preg_match("/__lazyLoad\('([^']+)'\)/", $html, $matches);

        $this->assertNotEmpty($matches[1] ?? null);

        $this->expectException(CorruptComponentPayloadException::class);

        Livewire::test('lazy-beta')->call('__lazyLoad', $matches[1]);
    }
}

#[Lazy]
class LazyAlpha extends Component {
    public $level = 0;
    public $mounts = 0;

    public function mount($level = 0) {
        $this->level = $level;
        $this->mounts++;
    }

    public function placeholder() {
        return '<div>Loading...</div>';
    }

    public function render() {
        return '<div>level:'.$this->level.' mounts:'.$this->mounts.'</div>';
    }
}

#[Lazy]
class LazyBeta extends Component {
    public function placeholder() {
        return '<div>Loading...</div>';
    }

    public function render() {
        return '<div>beta</div>';
    }
}

#[Lazy]
class BasicLazyComponent extends Component {
    public function placeholder() {
        return '<div>Loading...</div>';
    }

    public function render()
    {
        return '<div>Hello world!</div>';
    }
}

#[Defer]
class BasicDeferComponent extends Component {
    public function placeholder() {
        return '<div>Loading...</div>';
    }

    public function render()
    {
        return '<div>Hello world!</div>';
    }
}

#[Layout('components.layouts.custom'), Lazy]
class PageWithCustomLayout extends Component {
    public function placeholder() {
        return '<div>Loading...</div>';
    }
}

#[Lazy]
class PageWithCustomLayoutAttributeOnMethod extends Component {
    #[Layout('components.layouts.custom')]
    public function placeholder() {
        return '<div>Loading...</div>';
    }
}

#[Lazy]
class PageWithCustomLayoutOnView extends Component {
    public function placeholder() {
        return view('show-name', ['name' => 'foo'])->layout('components.layouts.custom');
    }
}
