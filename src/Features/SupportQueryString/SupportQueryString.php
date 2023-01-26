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
                if (empty($options = $component->getQueryString())) return;

                $this->fillPropertiesFromInitialQueryString($component, $options);
            };
        });

        on('dehydrate', function ($synth, $target, $context) {
            if (! $synth instanceof LivewireSynth) return;
            if (! $context->initial) return;

            if (empty($queryString = $target->getQueryString())) return;

            $context->addEffect('queryString', $queryString);
        });
    }

    protected function fillPropertiesFromInitialQueryString($component, $options)
    {
        $queryParams = request()->query();

        foreach ($options as $property => $options) {
            if (! is_array($options)) {
                $property = $options;
                $options = [];
            }

            $fromQueryString = Arr::get($queryParams, $options['as'] ?? $property);

            if ($fromQueryString === null) {
                continue;
            }

            $decoded = is_array($fromQueryString)
                ? json_decode(json_encode($fromQueryString), true)
                : json_decode($fromQueryString, true);

            data_set($component, $property, $decoded === null ? $fromQueryString : $decoded);
        }
    }

    protected function getQueryParamsFromComponentProperties($component)
    {
        return collect($component->getQueryString())
            ->mapWithKeys(function($value, $key) use ($component) {
                $key = is_string($key) ? $key : $value;
                $alias = $value['as'] ?? $key;

                return [$alias => data_get($component, $key)];
            });
    }
}
