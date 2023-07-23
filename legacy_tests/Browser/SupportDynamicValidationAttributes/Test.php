<?php

namespace LegacyTests\Browser\SupportDynamicValidationAttributes;

use Livewire\Livewire;
use LegacyTests\Browser\TestCase;

class Test extends TestCase
{
    /**
     * @requires PHP >= 8.1
     */
    public function test()
    {
        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, Component::class)
                ->waitForLivewire()->click('@dynamicForm')
                ->assertSeeIn('@output.dynamic.form.name', 'Field Title is must.')
                ->assertSeeIn('@output.dynamic.form.body', 'Description')

                ->waitForLivewire()->click('@defaultForm')
                ->assertSeeIn('@output.default.form.name', 'The Title field is must.')
                ->assertSeeIn('@output.default.form.body', 'The Description field is required.');
        });
    }
}
