<?php

namespace Tests;

use Illuminate\Support\Facades\View;
use Livewire\Livewire;

class LivewireUsesProperAppAndAssetsPathTest extends TestCase
{
    /** @test */
    public function livewire_js_calls_reference_relative_root()
    {
        $this->assertContains(
            '<script src="/livewire/livewire.js?',
            Livewire::assets()
        );

        $this->assertContains(
            'window.livewire_app_url = "";',
            Livewire::assets()
        );
    }

    /** @test */
    public function livewire_js_calls_reference_congigured_base_url()
    {
        $this->assertContains(
            '<script src="https://foo.com/assets/livewire/livewire.js?',
            Livewire::assets(['base_url' => 'https://foo.com/assets'])
        );

        $this->assertContains(
            'window.livewire_app_url = "https://foo.com/assets";',
            Livewire::assets(['base_url' => 'https://foo.com/assets'])
        );
    }

    /** @test */
    public function base_url_trailing_slashes_are_trimmed()
    {
        $this->assertContains(
            '<script src="https://foo.com/assets/livewire/livewire.js?',
            Livewire::assets(['base_url' => 'https://foo.com/assets/'])
        );

        $this->assertContains(
            'window.livewire_app_url = "https://foo.com/assets";',
            Livewire::assets(['base_url' => 'https://foo.com/assets/'])
        );
    }

    /** @test */
    public function base_url_passed_into_blade_assets_directive()
    {
        $output = View::make('assets-directive', [
            'options' => ['base_url' => 'https://foo.com/assets/'],
        ])->render();

        $this->assertContains(
            '<script src="https://foo.com/assets/livewire/livewire.js?',
            $output
        );

        $this->assertContains(
            'window.livewire_app_url = "https://foo.com/assets";',
            $output
        );
    }
}
