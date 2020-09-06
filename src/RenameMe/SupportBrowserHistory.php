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

    protected $mergedQueryParametersFromMultipleComponents = [];

    function __construct()
    {
        $this->mergedQueryParametersFromMultipleComponents = $this->getExistingQueryParams();

        Livewire::listen('component.hydrate.initial', function ($component, $request) {
            if (! $properties = $this->getQueryParamsFromComponentProperties($component)) return;

            $queryParams = request()->query();

            foreach ($properties as $property => $currentValue) {
                $fromQueryString = Arr::get($queryParams, $property);

                if ($fromQueryString !== null) {
                    $component->$property = ($decoded = json_decode($fromQueryString)) === null ? $fromQueryString : $decoded;
                }
            }
        });

        Livewire::listen('component.dehydrate.initial', function (Component $component, Response $response) {
            $queryStringParams = $this->mergeComponentDataWithExistingQueryString($component);

            $response->effects['path'] = url()->current();

            if (! empty($queryStringParams)) {
                $response->effects['path'] = Str::before($response->effects['path'], '?').'?'.http_build_query($queryStringParams);
            }
        });

        Livewire::listen('component.dehydrate.subsequent', function (Component $component, Response $response) {
            $queryParams = $this->mergeComponentDataWithExistingQueryString($component);

            $referrer = request()->header('Referrer');

            if (! $referrer) return;

            $route = app('router')->getRoutes()->match(
                Request::create($referrer, 'GET')
            );

            if (false !== strpos($route->getActionName(), get_class($component))) {
                $response->effects['path'] = $this->buildPathFromRoute($component, $route, $queryParams);
            } else if (!empty($queryParams)) {
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

        // Get the query string from the client
        parse_str(parse_url($referrer, PHP_URL_QUERY), $referrerQueryString);

        return $referrerQueryString;
    }

    protected function buildPathFromReferrer($referrer, $queryString)
    {
        if (empty($queryString)) {
            return null;
        }

        $url = Str::before($referrer, '?');

        return $url.'?'.http_build_query($queryString);
    }

    protected function buildPathFromRoute($component, $route, $queryString)
    {
        $boundParameters = array_intersect_key(
            $component->getPublicPropertiesDefinedBySubClass(),
            $route->parametersWithoutNulls()
        );

        return app(UrlGenerator::class)->toRoute($route, $boundParameters + $queryString, true);
    }

    protected function mergeComponentDataWithExistingQueryString($component)
    {
        $excepts = $this->getExceptsFromComponent($component);

        $this->mergedQueryParametersFromMultipleComponents = collect($this->mergedQueryParametersFromMultipleComponents)
            ->merge($this->getQueryParamsFromComponentProperties($component))
            ->reject(function ($value, $key) use ($excepts) {
                return isset($excepts[$key]) && $excepts[$key] === $value;
            })
            ->map(function ($property) {
                return is_bool($property) ? json_encode($property) : $property;
            })
            ->toArray();

        return $this->mergedQueryParametersFromMultipleComponents;
    }

    protected function getExceptsFromComponent($component)
    {
        return collect($component->getFromQueryString())
            ->filter(function ($value) {
                return isset($value['except']);
            })
            ->mapWithKeys(function ($value, $key) {
                return [$key => $value['except']];
            })
            ->toArray();
    }

    protected function getQueryParamsFromComponentProperties($component)
    {
        return collect($component->getFromQueryString())
            ->mapWithKeys(function ($value, $key) use ($component) {
                $key = is_string($key) ? $key : $value;

                return [$key => $component->{$key}];
            })->toArray();
    }
}
