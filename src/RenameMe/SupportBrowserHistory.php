<?php

namespace Livewire\RenameMe;

use Livewire\Livewire;
use Livewire\Response;
use Livewire\Component;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;

class SupportBrowserHistory
{
    static function init() { return new static; }

    protected $mergedQueryParamsFromDehydratedComponents;

    function __construct()
    {
        $this->mergedQueryParamsFromDehydratedComponents = collect($this->getExistingQueryParams());

        Livewire::listen('component.hydrate.initial', function ($component) {
            if (! $properties = $this->getQueryParamsFromComponentProperties($component)->keys()) return;

            $queryParams = request()->query();

            foreach ($properties as $property) {
                $fromQueryString = Arr::get($queryParams, $property);

                if ($fromQueryString !== null) {
                    $component->$property = ($decoded = json_decode($fromQueryString)) === null ? $fromQueryString : $decoded;
                }
            }
        });

        Livewire::listen('component.dehydrate.initial', function (Component $component, Response $response) {
            $queryParams = $this->mergeComponentPropertiesWithExistingQueryParams($component);

            $response->effects['path'] = url()->current().$this->stringifyQueryParams($queryParams);
        });

        Livewire::listen('component.dehydrate.subsequent', function (Component $component, Response $response) {
            if (! $referrer = request()->header('Referrer')) return;

            $route = app('router')->getRoutes()->match(
                Request::create($referrer, 'GET')
            );

            $queryParams = $this->mergeComponentPropertiesWithExistingQueryParams($component);

            if (false !== strpos($route->getActionName(), get_class($component))) {
                $response->effects['path'] = $this->buildPathFromRoute($component, $route, $queryParams);
            } else if ($queryParams->isNotEmpty()) {
                $response->effects['path'] = $this->buildPathFromReferrer($referrer, $queryParams);
            }
        });
    }

    protected function getExistingQueryParams()
    {
        return Livewire::isLivewireRequest()
            ? $this->getQueryParamsFromReferrerHeader()
            : request()->query();
    }

    public function getQueryParamsFromReferrerHeader()
    {
        if (empty($referrer = request()->header('Referrer'))) return [];

        parse_str(parse_url($referrer, PHP_URL_QUERY), $referrerQueryString);

        return $referrerQueryString;
    }

    protected function buildPathFromReferrer($referrer, $queryParams)
    {
        return Str::before($referrer, '?').$this->stringifyQueryParams($queryParams);
    }

    protected function buildPathFromRoute($component, $route, $queryString)
    {
        $boundParameters = array_intersect_key(
            $component->getPublicPropertiesDefinedBySubClass(),
            $route->parametersWithoutNulls()
        );

        return app(UrlGenerator::class)->toRoute($route, $boundParameters + $queryString->toArray(), true);
    }

    protected function mergeComponentPropertiesWithExistingQueryParams($component)
    {
        $excepts = $this->getExceptsFromComponent($component);

        $this->mergedQueryParamsFromDehydratedComponents = collect($this->mergedQueryParamsFromDehydratedComponents)
            ->merge($this->getQueryParamsFromComponentProperties($component))
            ->reject(function ($value, $key) use ($excepts) {
                return isset($excepts[$key]) && $excepts[$key] === $value;
            })
            ->map(function ($property) {
                return is_bool($property) ? json_encode($property) : $property;
            })
            ->sortKeys();

        return $this->mergedQueryParamsFromDehydratedComponents;
    }

    protected function getExceptsFromComponent($component)
    {
        return collect($component->getQueryString())
            ->filter(function ($value) {
                return isset($value['except']);
            })
            ->mapWithKeys(function ($value, $key) {
                return [$key => $value['except']];
            });
    }

    protected function getQueryParamsFromComponentProperties($component)
    {
        return collect($component->getQueryString())
            ->mapWithKeys(function($value, $key) use ($component) {
                $key = is_string($key) ? $key : $value;

                return [$key => $component->{$key}];
            });
    }

    protected function stringifyQueryParams($queryParams)
    {
        return $queryParams->isEmpty() ? '' : '?'.http_build_query($queryParams->toArray());
    }
}
