<?php

namespace Tests;

use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\Livewire;

class ComponentCanAcceptValuesTest extends TestCase
{
    /** @test */
    public function can_pass_values_to_component()
    {
        app('livewire')->component('accepts-values', ComponentThatAcceptsAValue::class);
        Route::get('path', function () {
            return view('pass-values');
        });

        if (! Livewire::isLaravel7()) {
            $this->expectException(\Exception::class);
        }

        $this->withoutExceptionHandling()->get('/path')
            ->assertSeeText('tag-syntax-literal-empty: The value is ""', false)
            ->assertSeeText('tag-syntax-literal-non-empty: The value is "abc"', false)
            ->assertSeeText('tag-syntax-value-empty: The value is ""', false)
            ->assertSeeText('tag-syntax-value-non-empty: The value is "abc"', false)
            ->assertSeeText('old-syntax-empty: The value is ""', false)
            ->assertSeeText('old-syntax-non-empty: The value is "abc"', false);
    }
}

class ComponentThatAcceptsAValue extends Component
{
    public $value;

    public function mount($value)
    {
        $this->value = $value;
    }

    public function render()
    {
        return '<div>The value is "{{ $this->value }}"</div>';
    }
}
