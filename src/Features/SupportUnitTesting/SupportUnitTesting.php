<?php

namespace Livewire\Features\SupportUnitTesting;

use function Livewire\store;
use function Livewire\on;
use Livewire\Mechanisms\UpdateComponents\Synthesizers\LivewireSynth;
use Livewire\Mechanisms\DataStore;
use Livewire\Mechanisms\ComponentRegistry;
use Livewire\ComponentHook;
use Livewire\Component;
use Illuminate\Validation\ValidationException;

class SupportUnitTesting extends ComponentHook
{
    static function provide()
    {
        if (! app()->environment('testing')) return;

        \Tests\DuskTestCase::onApplicationBoot();

        static::registerTestingMacros();
    }

    function dehydrate($context)
    {
        return function ($value) use ($context) {
            $target = $this->component;

            // $this->storeSet('testing.html', $context->effects['html'] ?? null);

            $errors = $target->getErrorBag();

            if (! $errors->isEmpty()) {
                $this->storeSet('testing.errors', $errors);
            }

            return $value;
        };
    }

    function render($view, $data)
    {
        return function ($html) use ($view) {
            $this->storeSet('testing.view', $view);
            $this->storeSet('testing.html', $html);
        };
    }

    function hydrate()
    {
        $this->storeSet('testing.validator', null);
    }

    function exception($e, $stopPropagation) {
        if (! $e instanceof ValidationException) return;

        $this->storeSet('testing.validator', $e->validator);
    }

    protected static function registerTestingMacros()
    {
        // Usage: $this->assertSeeLivewire('counter');
        \Illuminate\Testing\TestResponse::macro('assertSeeLivewire', function ($component) {
            if (is_subclass_of($component, Component::class)) {
                $component = app(ComponentRegistry::class)->getName($component);
            }

            $escapedComponentName = trim(htmlspecialchars(json_encode(['name' => $component])), '{}');

            \PHPUnit\Framework\Assert::assertStringContainsString(
                $escapedComponentName,
                $this->getContent(),
                'Cannot find Livewire component ['.$component.'] rendered on page.'
            );

            return $this;
        });

        // Usage: $this->assertDontSeeLivewire('counter');
        \Illuminate\Testing\TestResponse::macro('assertDontSeeLivewire', function ($component) {
            if (is_subclass_of($component, Component::class)) {
                $component = app(ComponentRegistry::class)->getName($component);
            }

            $escapedComponentName = trim(htmlspecialchars(json_encode(['name' => $component])), '{}');

            \PHPUnit\Framework\Assert::assertStringNotContainsString(
                $escapedComponentName,
                $this->getContent(),
                'Found Livewire component ['.$component.'] rendered on page.'
            );

            return $this;
        });

        if (class_exists(\Illuminate\Testing\TestView::class)) {
            \Illuminate\Testing\TestView::macro('assertSeeLivewire', function ($component) {
                if (is_subclass_of($component, Component::class)) {
                    $component = app(ComponentRegistry::class)->getName($component);
                }

                $escapedComponentName = trim(htmlspecialchars(json_encode(['name' => $component])), '{}');

                \PHPUnit\Framework\Assert::assertStringContainsString(
                    $escapedComponentName,
                    $this->rendered,
                    'Cannot find Livewire component ['.$component.'] rendered on page.'
                );

                return $this;
            });

            \Illuminate\Testing\TestView::macro('assertDontSeeLivewire', function ($component) {
                if (is_subclass_of($component, Component::class)) {
                    $component = app(ComponentRegistry::class)->getName($component);
                }

                $escapedComponentName = trim(htmlspecialchars(json_encode(['name' => $component])), '{}');

                \PHPUnit\Framework\Assert::assertStringNotContainsString(
                    $escapedComponentName,
                    $this->rendered,
                    'Found Livewire component ['.$component.'] rendered on page.'
                );

                return $this;
            });
        }
    }
}
