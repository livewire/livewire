<?php

namespace Livewire\RenameMe;

use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Livewire;
use Livewire\Response;

// FIXME: this needs to also include any data from sub-components

class SupportRouteHistory
{
    static function init() { return new static; }

    protected $allQueryStringProperties = [];

    function __construct()
    {
        Livewire::listen('component.hydrate.initial', function ($component, $request) {
            if (empty($properties = $component->getQueryStringProperties())) return;

            // Get the query string from the client
            parse_str(parse_url(request()->header('Referrer', ''), PHP_URL_QUERY), $query);

            foreach ($properties as $property => $currentValue) {
                $fromQueryString = Arr::get($query, $property);

                if ($fromQueryString !== null) {
                    $component->$property = ($decoded = json_decode($fromQueryString)) === null ? $fromQueryString : $decoded;
                }
            }
        });

        Livewire::listen('component.dehydrate.initial', function (Component $component, Response $response) use (&$initialized) {
            $url = url()->current();

            // Load the query string from the request and the expected query params from the component
            parse_str(parse_url($url, PHP_URL_QUERY), $currentQueryString);
            $componentsQueryString = $this->mergeAndGetQueryString($component->getQueryStringProperties());

            // If there are missing parameters, add them to the URL
            if (! empty($diff = array_diff_assoc($componentsQueryString, $currentQueryString))) {
                $join = count($currentQueryString) ? '&' : '?';
                $url = $url.$join.http_build_query($diff);
            }

            $response->effects['routePath'] = $url;
        });

        Livewire::listen('component.dehydrate.subsequent', function (Component $component, Response $response) {
            if (empty($referrer = request()->header('Referrer'))) {
                return;
            }

            // Get the query string from the client
            parse_str(parse_url($referrer, PHP_URL_QUERY), $referrerQueryString);

            // Get all the merged query strings from all components that have rendered
            $currentComponentQueryString = $component->getQueryStringProperties();
            $componentsQueryString = $this->mergeAndGetQueryString($currentComponentQueryString);

            // Merge the two together
            $queryString = array_merge(
                $componentsQueryString,
                $referrerQueryString,
                $currentComponentQueryString
            );
            $response->effects['__debug'] = $queryString;

            $route = app('router')->getRoutes()->match(
                Request::create($referrer, 'GET')
            );

            $routePath = false !== strpos($route->getActionName(), get_class($component))
                ? $this->buildPathFromRoute($queryString, $component, $route)
                : $this->buildPathFromReferrer($queryString, $referrer);

            if (url($routePath) !== $referrer) {
                $response->effects['routePath'] = $routePath;
            }
        });
    }

    protected function buildPathFromRoute($queryString, $component, $route)
    {
        $boundParameters = array_intersect_key(
            $component->getPublicPropertiesDefinedBySubClass(),
            $route->parametersWithoutNulls()
        );

        $parameters = array_merge($queryString, $boundParameters);

        // Sort parameters so that the URL is predictable
        ksort($parameters);

        return app(UrlGenerator::class)->toRoute($route, $parameters, false);
    }

    protected function buildPathFromReferrer($queryString, $referrer)
    {
        if (empty($queryString)) {
            return $referrer;
        }

        ksort($queryString);

        $url = Str::before($referrer, '?');

        return $url.'?'.http_build_query($queryString);
    }

    protected function mergeAndGetQueryString($params)
    {
        $this->allQueryStringProperties = array_merge($this->allQueryStringProperties, $params);

        return $this->allQueryStringProperties;
    }
}
