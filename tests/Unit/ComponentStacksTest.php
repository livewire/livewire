<?php

namespace Tests\Unit;

use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Support\Facades\Route;

class ComponentStacksTest extends TestCase
{
    /** @test */
    public function component_stack_is_wrapped_with_specific_attributes()
    {
        Route::get('/component-with-push-directive', ComponentWithPushDirective::class);

        $response = $this->get('/component-with-push-directive');

        // Get component id from response.
        preg_match('/wire:id="([^"]+)"/', $response->getContent(), $match);

        // Strip-out initial-data and whitespace.
        $content = preg_replace('((wire:initial-data=\".+}")|\s)', '', $response->getContent());

        $this->assertEquals(
            '<divwire:id="' . $match[1] . '">foo</div>' .
            '<divwire:ignorewire:stack="page_bottom">' .
            '<divwire:stack-id="' . $match[1] . '">barbaz</div></div>',
            $content
        );
    }

    /** @test */
    public function component_stack_is_included_in_payload()
    {
        $component = Livewire::test(ComponentWithPushDirective::class)->call('$refresh');

        $this->assertStringContainsString(
            'barbaz',
            data_get($component->payload, "effects.stack.page_bottom.$component->id")
        );
    }

    /** @test */
    public function original_laravel_stack_is_still_working()
    {
        Route::view('/laravel-push-directive', 'laravel-push-directive');

        $response = $this->get('/laravel-push-directive');

        // Strip-out initial-data and whitespace.
        $content = preg_replace('(\s)', '', $response->getContent());

        // Assert that bar is out of wire:stack wrapper.
        $this->assertEquals(
            '<div>foo</div>bar<divwire:ignorewire:stack="page_bottom"></div>',
            $content
        );
    }
}

class ComponentWithPushDirective extends Component
{
    public function render()
    {
        return view('push-directive')->extends('layouts.app-with-stack')->section('content');
    }
}
