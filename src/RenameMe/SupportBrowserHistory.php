<?php

namespace Livewire\RenameMe;

use Livewire\Livewire;
use Livewire\Response;
use Livewire\Component;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Routing\UrlGenerator;

// FIXME: this needs to also include any data from sub-components
// FIXME: the first request doesn't honor child component query params
// FIXME: Handle the #fragment

class SupportBrowserHistory
{
    static function init() { return new static; }

    protected $allQueryStringProperties = [];

    function __construct()
    {
        Livewire::listen('component.hydrate.initial', function ($component, $request) {
            if (empty($properties = $component->getQueryStringProperties())) return;

            $query = request()->query();

            foreach ($properties as $property => $currentValue) {
                $fromQueryString = Arr::get($query, $property);

                if ($fromQueryString !== null) {
                    $component->$property = ($decoded = json_decode($fromQueryString)) === null ? $fromQueryString : $decoded;
                }
            }
        });

        Livewire::listen('component.dehydrate.initial', function (Component $component, Response $response) {
            $queryString = array_merge(
                request()->query(),
                $this->mergeAndGetQueryString($component->getQueryStringProperties())
            );

            $response->effects['path'] = url()->current();

            ksort($queryString);

            if (! empty($queryString)) {
                $response->effects['path'] = Str::before($response->effects['path'], '?').'?'.http_build_query($queryString);
            }
        });

        Livewire::listen('component.dehydrate.subsequent', function (Component $component, Response $response) {
            if (empty($referrer = request()->header('Referrer'))) {
                return;
            }

            // Get the query string from the client
            parse_str(parse_url($referrer, PHP_URL_QUERY), $referrerQueryString);

            $queryString = array_merge(
                $referrerQueryString,
                $this->mergeAndGetQueryString($component->getQueryStringProperties())
            );

            // Sort by keys to keep it predictable
            ksort($queryString);

            $route = app('router')->getRoutes()->match(
                Request::create($referrer, 'GET')
            );

            if (false !== strpos($route->getActionName(), get_class($component))) {
                $response->effects['path'] = $this->buildPathFromRoute($component, $route, $queryString);
            }
        });
    }

    protected function buildPathFromRoute($component, $route, $queryString)
    {
        $boundParameters = array_intersect_key(
            $component->getPublicPropertiesDefinedBySubClass(),
            $route->parametersWithoutNulls()
        );

        return app(UrlGenerator::class)->toRoute($route, $boundParameters + $queryString, true); // FIXME
    }

    protected function mergeAndGetQueryString($params)
    {
        $this->allQueryStringProperties = array_merge($this->allQueryStringProperties, $params);

        ksort($this->allQueryStringProperties);

        return array_map(function ($property) {
            return is_bool($property) ? json_encode($property) : $property;
        }, $this->allQueryStringProperties);
    }
}
