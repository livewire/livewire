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

    function test_debounce_effect_allows_duration_below_default()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce(50)]
            public $search = '';
        });

        $this->assertSame(50, $component->effects['debounce']['search']);
    }

    function test_debounce_effect_allows_zero_duration()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce(0)]
            public $search = '';
        });

        $this->assertSame(0, $component->effects['debounce']['search']);
    }

    function test_debounce_effect_clamps_negative_int_to_zero()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce(-100)]
            public $search = '';
        });

        $this->assertSame(0, $component->effects['debounce']['search']);
    }

    function test_debounce_effect_allows_zero_ms_string()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce('0ms')]
            public $search = '';
        });

        $this->assertSame(0, $component->effects['debounce']['search']);
    }

    function test_debounce_effect_allows_sub_default_ms_string()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce('50ms')]
            public $search = '';
        });

        $this->assertSame(50, $component->effects['debounce']['search']);
    }

    function test_debounce_effect_normalizes_whole_seconds_string()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce('2s')]
            public $search = '';
        });

        $this->assertSame(2000, $component->effects['debounce']['search']);
    }

    function test_debounce_effect_normalizes_bare_numeric_string_as_ms()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce('250')]
            public $search = '';
        });

        $this->assertSame(250, $component->effects['debounce']['search']);
    }

    function test_debounce_effect_falls_back_to_150_for_empty_string()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce('')]
            public $search = '';
        });

        $this->assertSame(150, $component->effects['debounce']['search']);
    }

    function test_debounce_effect_falls_back_to_150_for_ms_without_number()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce('ms')]
            public $search = '';
        });

        $this->assertSame(150, $component->effects['debounce']['search']);
    }

    function test_debounce_effect_is_case_insensitive_for_units()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce('300MS')]
            public $search = '';
        });

        $this->assertSame(300, $component->effects['debounce']['search']);
    }

    function test_debounce_effect_trims_whitespace_in_string_duration()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce('  400ms  ')]
            public $search = '';
        });

        $this->assertSame(400, $component->effects['debounce']['search']);
    }

    function test_debounce_effect_on_form_object_uses_dotted_property_name()
    {
        $component = Livewire::test(new class extends TestComponent {
            public SearchFrom $form;
        });

        // getName() for form properties is "form.q" — same key JS looks up
        $this->assertSame(2000, $component->effects['debounce']['form.q']);
        $this->assertArrayNotHasKey('q', $component->effects['debounce']);
    }
}

class SearchFrom extends \Livewire\Form
{
    #[BaseDebounce(2000)]
    public string $q = '';
}