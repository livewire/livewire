<?php

namespace Livewire\RenameMe;

use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\URL;
use Livewire\Component;
use Livewire\Livewire;
use Livewire\Response;

class SupportRouteHistory
{
    static function init() { return new static; }

    function __construct()
    {
        Livewire::listen('component.hydrate.initial', function ($component, $request) {
            if (empty($properties = $component->getQueryStringProperties())) return;

            // Get the query string from the client
            parse_str(parse_url(request()->header('Referrer', ''), PHP_URL_QUERY), $query);

            foreach ($properties as $property) {
                $fromQueryString = Arr::get($query, $property);

                if ($fromQueryString !== null) {
                    $component->$property = ($decoded = json_decode($fromQueryString)) === null ? $fromQueryString : $decoded;
                }
            }
        });

        Livewire::listen('component.dehydrate', function (Component $component, Response $response) {
            $referrer = request()->header('Referrer', url()->current());

            $route = app('router')->getRoutes()->match(
                Request::create($referrer, 'GET')
            );

            if (false !== strpos($route->getActionName(), get_class($component))) {
                $boundParameters = array_intersect_key(
                    $component->getPublicPropertiesDefinedBySubClass(),
                    $route->parametersWithoutNulls()
                );

                // Get the query string from the client
                parse_str(parse_url($referrer, PHP_URL_QUERY), $referrerQueryString);

                $parameters = array_merge(
                    $referrerQueryString,
                    $component->getQueryStringProperties(),
                    $boundParameters
                );

                $routePath = app(UrlGenerator::class)->toRoute($route, $parameters, false);

                if (url($routePath) !== $referrer) {
                    $response->effects['routePath'] = $routePath;
                }
            }
        });
    }
}
