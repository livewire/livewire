<?php

namespace Livewire\Features\SupportQueryString;

use function Livewire\on;
use function Livewire\before;
use Livewire\Mechanisms\UpdateComponents\Synthesizers\LivewireSynth;
use Illuminate\Support\Arr;

class SupportQueryString
{
    function boot()
    {
        before('mount', function ($name, $params, $parent, $key, $hijack) {
            return function ($component) {
                $this->fillPropertiesFromInitialQueryString($component);
            };
        });

        on('dehydrate', function ($synth, $target, $context) {
            if (! $synth instanceof LivewireSynth) return;
            if (! $context->initial) return;

            if (empty($queryString = $target->getQueryString())) return;

            $context->addEffect('queryString', $queryString);
        });
    }

    protected function fillPropertiesFromInitialQueryString($component)
    {
        if (! $properties = $this->getQueryParamsFromComponentProperties($component)->keys()) return;

        $queryParams = request()->query();

        foreach ($component->getQueryString() ?? [] as $property => $options) {
            if (! is_array($options)) {
                $property = $options;
            }

            $fromQueryString = Arr::get($queryParams, $options['as'] ?? $property);

            if ($fromQueryString === null) {
                continue;
            }

            $decoded = is_array($fromQueryString)
                ? json_decode(json_encode($fromQueryString), true)
                : json_decode($fromQueryString, true);

            $component->$property = $decoded === null ? $fromQueryString : $decoded;
        }
    }

    protected function getExceptsFromComponent($component)
    {
        return collect($component->getQueryString())
            ->filter(function ($value) {
                return isset($value['except']);
            })
            ->mapWithKeys(function ($value, $key) {
                $key = $value['as'] ?? $key;
                return [$key => $value['except']];
            });
    }

    protected function getQueryParamsFromComponentProperties($component)
    {
        return collect($component->getQueryString())
            ->mapWithKeys(function($value, $key) use ($component) {
                $key = is_string($key) ? $key : $value;
                $alias = $value['as'] ?? $key;

                return [$alias => $component->{$key}];
            });
    }
}
