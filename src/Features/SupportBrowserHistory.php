<?php

namespace Livewire\RenameMe;

use Livewire\Livewire;
use Livewire\Response;
use Livewire\Component;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use function Livewire\str;

class SupportBrowserHistory
{
    static function init() { return new static; }

    protected $mergedQueryParamsFromDehydratedComponents;

    function __construct()
    {
        Livewire::listen('component.hydrate.initial', function ($component) {
            if (! $properties = $this->getQueryParamsFromComponentProperties($component)->keys()) return;

            $queryParams = request()->query();

            foreach ($properties as $property) {
                $fromQueryString = Arr::get($queryParams, $property);

                if ($fromQueryString === null) {
                    continue;
                }

                $decoded = is_array($fromQueryString)
                    ? json_decode(json_encode($fromQueryString), true)
                    : json_decode($fromQueryString, true);

                $component->$property = $decoded === null ? $fromQueryString : $decoded;
            }
        });

        Livewire::listen('component.dehydrate.initial', function (Component $component, Response $response) {
            if (! $this->shouldSendPath($component)) return;

            $queryParams = $this->mergeComponentPropertiesWithExistingQueryParamsFromOtherComponentsAndTheRequest($component);

            $response->effects['path'] = url()->current().$this->stringifyQueryParams($queryParams);
        });

        Livewire::listen('component.dehydrate.subsequent', function (Component $component, Response $response) {
            if (! $referer = request()->header('Referer')) return;

            $route = $this->getRouteFromReferer($referer);

            if ( ! $this->shouldSendPath($component, $route)) return;

            $queryParams = $this->mergeComponentPropertiesWithExistingQueryParamsFromOtherComponentsAndTheRequest($component);

            if ($route && false !== strpos($route->getActionName(), get_class($component))) {
                $path = $response->effects['path'] = $this->buildPathFromRoute($component, $route, $queryParams);
            } else {
                $path = $this->buildPathFromReferer($referer, $queryParams);
            }

            if ($referer !== $path) {
                $response->effects['path'] = $path;
            }
        });
    }

    protected function getRouteFromReferer($referer)
    {
        try {
            // See if we can get the route from the referer.
            return app('router')->getRoutes()->match(
                Request::create($referer, Livewire::originalMethod())
            );
        } catch (NotFoundHttpException|MethodNotAllowedHttpException $e) {
            // If not, use the current route.
            return app('router')->current();
        }
    }

    protected function shouldSendPath($component, $route = null)
    {
        // If the component is setting $queryString params.
        if (! $this->getQueryParamsFromComponentProperties($component)->isEmpty()) return true;

        $route = $route ?? app('router')->current();

        if (
            $route
            && is_string($action = $route->getActionName())
            // If the component is registered using `Route::get()`.
            && str($action)->contains(get_class($component))
            // AND, the component is tracking route params as its public properties
            && count(array_intersect_key($component->getPublicPropertiesDefinedBySubClass(), array_flip($route->parameterNames())))
        ) {
            return true;
        }

        return false;
    }

    protected function getExistingQueryParams()
    {
        return Livewire::isDefinitelyLivewireRequest()
            ? $this->getQueryParamsFromRefererHeader()
            : request()->query();
    }

    public function getQueryParamsFromRefererHeader()
    {
        if (empty($referer = request()->header('Referer'))) return [];

        parse_str(parse_url($referer, PHP_URL_QUERY), $refererQueryString);

        return $refererQueryString;
    }

    protected function buildPathFromReferer($referer, $queryParams) : string
    {
        return str($referer)->before('?').$this->stringifyQueryParams($queryParams);
    }

    protected function buildPathFromRoute($component, $route, $queryString)
    {
        $boundParameters = array_merge(
            $route->parametersWithoutNulls(),
            array_intersect_key(
                $component->getPublicPropertiesDefinedBySubClass(),
                array_flip($route->parameterNames())
            )
        );

        return app(UrlGenerator::class)->toRoute($route, $boundParameters + $queryString->toArray(), true);
    }

    protected function mergeComponentPropertiesWithExistingQueryParamsFromOtherComponentsAndTheRequest($component)
    {
        if (! $this->mergedQueryParamsFromDehydratedComponents) {
            $this->mergedQueryParamsFromDehydratedComponents = collect($this->getExistingQueryParams());
        }

        $excepts = $this->getExceptsFromComponent($component);

        $this->mergedQueryParamsFromDehydratedComponents = collect(request()->query())
            ->merge($this->mergedQueryParamsFromDehydratedComponents)
            ->merge($this->getQueryParamsFromComponentProperties($component))
            ->reject(function ($value, $key) use ($excepts) {
                return isset($excepts[$key]) && $excepts[$key] === $value;
            })
            ->map(function ($property) {
                return is_bool($property) ? json_encode($property) : $property;
            });

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
        return $queryParams->isEmpty() ? '' : '?'.Arr::query($queryParams->toArray());
    }
}
