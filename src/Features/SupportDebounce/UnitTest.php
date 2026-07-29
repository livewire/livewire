<?php

namespace Livewire\Features\SupportDebounce;

use Livewire\Livewire;
use Tests\TestCase;
use Tests\TestComponent;

class UnitTest extends TestCase
{
    function test_can_push_debounce_effect_for_property()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce(250)]
            public $search = '';
        });

        $this->assertSame(250, $component->effects['debounce']['search']);
    }

    function test_debounce_effect_defaults_to_150_when_no_time_given()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce]
            public $search = '';
        });

        $this->assertSame(150, $component->effects['debounce']['search']);
    }

    function test_debounce_effect_normalizes_ms_string()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce('300ms')]
            public $search = '';
        });

        $this->assertSame(300, $component->effects['debounce']['search']);
    }

    function test_debounce_effect_normalizes_seconds_string()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce('0.5s')]
            public $search = '';
        });

        $this->assertSame(500, $component->effects['debounce']['search']);
    }

    function test_debounce_effect_falls_back_to_150_for_non_numeric_string()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce('fast')]
            public $search = '';
        });

        $this->assertSame(150, $component->effects['debounce']['search']);
    }

    function test_debounce_effect_only_includes_annotated_properties()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce(200)]
            public $search = '';

            public $other = '';
        });

        $this->assertArrayHasKey('search', $component->effects['debounce']);
        $this->assertArrayNotHasKey('other', $component->effects['debounce']);
    }

    function test_multiple_properties_can_have_different_debounce_times()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce(200)]
            public $search = '';

            #[BaseDebounce(500)]
            public $filter = '';
        });

        $this->assertSame(200, $component->effects['debounce']['search']);
        $this->assertSame(500, $component->effects['debounce']['filter']);
    }

    function test_debounce_effect_is_not_re_pushed_on_subsequent_request()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce(250)]
            public $search = '';

            public function updateSearch($value)
            {
                $this->search = $value;
            }
        });

        $this->assertSame(250, $component->effects['debounce']['search']);

        $component->call('updateSearch', 'foo');

        // Mount-only dehydrate (same contract as BaseUrl): effect must not reappear
        $this->assertArrayNotHasKey('search', $component->effects['debounce'] ?? []);
    }
}