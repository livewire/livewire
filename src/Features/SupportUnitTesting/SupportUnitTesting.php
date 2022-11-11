<?php

namespace Livewire\Features\SupportUnitTesting;

use function Livewire\store;
use function Livewire\on;
use Livewire\Mechanisms\DataStore;
use Livewire\Mechanisms\UpdateComponents\Synthesizers\LivewireSynth;
use Livewire\Component;
use Illuminate\Validation\ValidationException;

class SupportUnitTesting
{
    function boot()
    {
        if (! app()->environment('testing')) return;

        \Tests\TestCase::onApplicationBoot();

        $this->registerTestingMacros();

        on('dehydrate', function ($synth, $target, $context) {
            if (! $synth instanceof LivewireSynth) return;

            return function ($value) use ($context, $target) {
                store($target)->set('testing.html', $context->effects['html'] ?? null);

                $errors = $target->getErrorBag();

                if (! $errors->isEmpty()) {
                    store($target)->set('testing.errors', $errors);
                }

                // ??
                // Componentstore($target)->set('testing.view', null);

                return $value;
            };
        });

        on('render', function ($target, $view, $data) {
            return function () use ($target, $view) {
                store($target)->set('testing.view', $view);
            };
        });

        on('mount', function ($name, $params, $parent, $key, $hijack) {
            return function ($target) {
                return function ($html) use ($target) {
                    store($target)->set('testing.html', $html);
                };
            };
        });

        on('hydrate', function ($synth, $rawValue, $meta) {
            if (! $synth instanceof LivewireSynth) return;

            return function ($target) {
                store($target)->set('testing.validator', null);
            };
        });

        on('exception', function ($target, $e, $stopPropagation) {
            if (! $target instanceof Component) return;
            if (! $e instanceof ValidationException) return;

            store($target)->set('testing.validator', $e->validator);
        });
    }

    protected function registerTestingMacros()
    {
        // Usage: $this->assertSeeLivewire('counter');
        \Illuminate\Testing\TestResponse::macro('assertSeeLivewire', function ($component) {
            if (is_subclass_of($component, Component::class)) {
                $component = $component->getName();
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
                $component = $component->getName();
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
                    $component = $component->getName();
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
                    $component = $component->getName();
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
