<?php

namespace Livewire\Features\SupportDebounce;

use Livewire\Livewire;
use Tests\TestCase;
use Tests\TestComponent;

class UnitTest extends TestCase
{
    function test_can_push_debounce_effect_for_property_and_method()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce(250)]
            public $search = '';

            #[BaseDebounce(250)]
            public function save() {}
        });

        $this->assertSame(250, $component->effects['debounce']['search']);
        $this->assertSame(250, $component->effects['debounce']['save']);
    }

    function test_debounce_effect_fallback_to_defaults_when_no_time_given()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce]
            public $search = '';

            #[BaseDebounce]
            public function save() {}
        });

        $this->assertSame(150, $component->effects['debounce']['search']);
        $this->assertSame(250, $component->effects['debounce']['save']);
    }

    function test_debounce_effect_normalizes_ms_string()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce('300ms')]
            public $search = '';

            #[BaseDebounce('300ms')]
            public function save() {}
        });

        $this->assertSame(300, $component->effects['debounce']['search']);
        $this->assertSame(300, $component->effects['debounce']['save']);
    }

    function test_debounce_effect_normalizes_seconds_string()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce('0.5s')]
            public $search = '';

            #[BaseDebounce('0.5s')]
            public function save() {}
        });

        $this->assertSame(500, $component->effects['debounce']['search']);
        $this->assertSame(500, $component->effects['debounce']['save']);
    }

    function test_debounce_effect_falls_back_to_defaults_for_non_numeric_string()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce('fast')]
            public $search = '';

            #[BaseDebounce('slow')]
            public function save() {}
        });

        $this->assertSame(150, $component->effects['debounce']['search']);
        $this->assertSame(250, $component->effects['debounce']['save']);
    }

    function test_debounce_effect_only_includes_annotated_properties()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce(200)]
            public $search = '';

            public $other = '';

            #[BaseDebounce(200)]
            public function save() {}

            public function edit() {}
        });

        $this->assertArrayHasKey('search', $component->effects['debounce']);
        $this->assertSame(200, $component->effects['debounce']['search']);
        $this->assertArrayNotHasKey('other', $component->effects['debounce']);

        $this->assertArrayHasKey('save', $component->effects['debounce']);
        $this->assertArrayNotHasKey('edit', $component->effects['debounce']);
        $this->assertSame(200, $component->effects['debounce']['save']);
    }

    function test_multiple_properties_and_methods_can_have_different_debounce_times()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce(200)]
            public $search = '';

            #[BaseDebounce(500)]
            public $filter = '';

            #[BaseDebounce(200)]
            public function save() {}

            #[BaseDebounce(500)]
            public function edit() {}
        });

        $this->assertSame(200, $component->effects['debounce']['search']);
        $this->assertSame(500, $component->effects['debounce']['filter']);

        $this->assertSame(200, $component->effects['debounce']['save']);
        $this->assertSame(500, $component->effects['debounce']['edit']);
    }

    function test_debounce_effect_is_not_re_pushed_on_subsequent_request()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce(250)]
            public $search = '';

            #[BaseDebounce(250)]
            public function save() {}

            public function updateSearch($value)
            {
                $this->search = $value;
            }
        });

        $this->assertSame(250, $component->effects['debounce']['search']);
        $this->assertSame(250, $component->effects['debounce']['save']);

        $component->call('updateSearch', 'foo');

        // Mount-only dehydrate (same contract as BaseUrl): effect must not reappear
        $this->assertArrayNotHasKey('search', $component->effects['debounce'] ?? []);
        $this->assertArrayNotHasKey('save', $component->effects['debounce'] ?? []);
    }

    function test_debounce_effect_allows_duration_below_default()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce(50)]
            public $search = '';

            #[BaseDebounce(50)]
            public function save() {}
        });

        $this->assertSame(50, $component->effects['debounce']['search']);
        $this->assertSame(50, $component->effects['debounce']['save']);
    }

    function test_debounce_effect_allows_zero_duration()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce(0)]
            public $search = '';

            #[BaseDebounce(0)]
            public function save() {}
        });

        $this->assertSame(0, $component->effects['debounce']['search']);
        $this->assertSame(0, $component->effects['debounce']['save']);
    }

    function test_debounce_effect_clamps_negative_int_to_zero()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce(-100)]
            public $search = '';

            #[BaseDebounce(-100)]
            public function save() {}
        });

        $this->assertSame(0, $component->effects['debounce']['search']);
        $this->assertSame(0, $component->effects['debounce']['save']);
    }

    function test_debounce_effect_allows_zero_ms_string()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce('0ms')]
            public $search = '';

            #[BaseDebounce('0ms')]
            public function save() {}
        });

        $this->assertSame(0, $component->effects['debounce']['search']);
        $this->assertSame(0, $component->effects['debounce']['save']);
    }

    function test_debounce_effect_allows_sub_default_ms_string()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce('50ms')]
            public $search = '';

            #[BaseDebounce('50ms')]
            public function save() {}
        });

        $this->assertSame(50, $component->effects['debounce']['search']);
        $this->assertSame(50, $component->effects['debounce']['save']);
    }

    function test_debounce_effect_normalizes_whole_seconds_string()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce('2s')]
            public $search = '';

            #[BaseDebounce('2s')]
            public function save() {}
        });

        $this->assertSame(2000, $component->effects['debounce']['search']);
        $this->assertSame(2000, $component->effects['debounce']['save']);
    }

    function test_debounce_effect_normalizes_bare_numeric_string_as_ms()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce('250')]
            public $search = '';

            #[BaseDebounce('250')]
            public function save() {}
        });

        $this->assertSame(250, $component->effects['debounce']['search']);
        $this->assertSame(250, $component->effects['debounce']['save']);
    }

    function test_debounce_effect_falls_back_to_true_for_empty_string()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce('')]
            public $search = '';

            #[BaseDebounce('')]
            public function save() {}
        });

        $this->assertSame(150, $component->effects['debounce']['search']);
        $this->assertSame(250, $component->effects['debounce']['save']);
    }

    function test_debounce_effect_falls_back_to_true_for_ms_without_number()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce('ms')]
            public $search = '';

            #[BaseDebounce('ms')]
            public function save() {}
        });

        $this->assertSame(150, $component->effects['debounce']['search']);
        $this->assertSame(250, $component->effects['debounce']['save']);
    }

    function test_debounce_effect_is_case_insensitive_for_units()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce('300MS')]
            public $search = '';

            #[BaseDebounce('300MS')]
            public function save() {}
        });

        $this->assertSame(300, $component->effects['debounce']['search']);
        $this->assertSame(300, $component->effects['debounce']['save']);
    }

    function test_debounce_effect_trims_whitespace_in_string_duration()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce('  400ms  ')]
            public $search = '';

            #[BaseDebounce('  400ms  ')]
            public function save() {}
        });

        $this->assertSame(400, $component->effects['debounce']['search']);
        $this->assertSame(400, $component->effects['debounce']['save']);
    }

    function test_debounce_effect_on_form_object_uses_dotted_property_and_method_name()
    {
        $component = Livewire::test(new class extends TestComponent {
            public SearchFrom $form;
        });

        // getName() for form properties is "form.search" — same key JS looks up
        $this->assertSame(2000, $component->effects['debounce']['form.search']);
        $this->assertArrayNotHasKey('search', $component->effects['debounce']);

        // getName() for form methods is "form.save" — same key JS looks up
        $this->assertSame(2000, $component->effects['debounce']['form.save']);
        $this->assertArrayNotHasKey('save', $component->effects['debounce']);
    }

    function test_property_and_method_can_have_independent_debounce_times()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseDebounce(200)]
            public $search = '';

            #[BaseDebounce(500)]
            public function save()
            {
                //
            }
        });

        $this->assertSame(200, $component->effects['debounce']['search']);
        $this->assertSame(500, $component->effects['debounce']['save']);
    }
}

class SearchFrom extends \Livewire\Form
{
    #[BaseDebounce(2000)]
    public string $search = '';

    #[BaseDebounce(2000)]
    public function save() {}
}