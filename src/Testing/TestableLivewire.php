<?php

namespace Livewire\Testing;

use Mockery;
use Livewire\Livewire;
use Illuminate\Support\Str;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Routing\RouteCollection;
use Livewire\GenerateSignedUploadUrl;
use Facades\Livewire\GenerateSignedUploadUrl as GenerateSignedUploadUrlFacade;

class TestableLivewire
{
    public $payload = [];
    public $componentName;
    public $lastValidator;
    public $lastRenderedView;
    public $lastResponse;
    public $rawMountedResponse;

    use Concerns\MakesAssertions,
        Concerns\MakesCallsToComponent,
        Concerns\HasFunLittleUtilities;

    public function __construct($name, $params = [])
    {
        Livewire::listen('view:render', function ($view) {
            $this->lastRenderedView = $view;
        });

        Livewire::listen('failed-validation', function ($validator) {
            $this->lastValidator = $validator;
        });

        Livewire::listen('mounted', function ($response) {
            $this->rawMountedResponse = $response;
        });

        // Don't actually generate S3 signedUrls during testing.
        // Can't use ::partialMock because it's not available in older versions of Laravel.
        $mock = Mockery::mock(GenerateSignedUploadUrl::class);
        $mock->makePartial()->shouldReceive('forS3')->andReturn([]);
        GenerateSignedUploadUrlFacade::swap($mock);

        // This allows the user to test a component by it's class name,
        // and not have to register an alias.
        if (class_exists($name)) {
            $componentClass = $name;
            app('livewire')->component($name = Str::random(20), $componentClass);
        }

        $this->componentName = $name;

        $this->lastResponse = $this->pretendWereMountingAComponentOnAPage($name, $params);

        if (! $this->lastResponse->exception) {
            $this->updateComponent($this->rawMountedResponse);
        }
    }

    public function updateComponent($output)
    {
        $this->payload = [
            'id' => $output->id,
            'name' => $output->name,
            'dom' => $output->dom,
            'data' => $output->data,
            'children' => $output->children,
            'events' => $output->events,
            'eventQueue' => $output->eventQueue,
            'dispatchQueue' => $output->dispatchQueue,
            'errorBag' => $output->errorBag,
            'checksum' => $output->checksum,
            'redirectTo' => $output->redirectTo,
            'dirtyInputs' => $output->dirtyInputs,
            'updatesQueryString' => $output->updatesQueryString,
        ];
    }

    public function pretendWereMountingAComponentOnAPage($name, $params)
    {
        $randomRoutePath = '/testing-livewire/'.Str::random(20);
        $this->createTestingRoute($randomRoutePath, $name, $params);

        $laravelTestingWrapper = new MakesHttpRequestsWrapper(app());

        $response = null;

        $laravelTestingWrapper->temporarilyDisableExceptionHandlingAndMiddleware(function ($wrapper) use ($randomRoutePath, &$response) {
            $response = $wrapper->call('GET', $randomRoutePath);
        });

        return $response;
    }

    private function createTestingRoute($path, $name, $params)
    {
        $router = app('router');
        $routes = $router->getRoutes();

        $testRoute = new Route(['GET', 'HEAD'], $path, function () use ($name, $params) {
            return View::file(__DIR__.'/../views/mount-component.blade.php', [
                'name' => $name,
                'params' => $params,
            ]);
        });

        $newRouteCollection = new RouteCollection;
        $newRouteCollection->add($testRoute);

        foreach ($routes as $route) {
            $newRouteCollection->add($route);
        }

        $router->setRoutes($newRouteCollection);
    }

    public function pretendWereSendingAComponentUpdateRequest($message, $payload)
    {
        return $this->callEndpoint('POST', '/livewire/message/'.$this->componentName, [
            'id' => $this->payload['id'],
            'name' => $this->payload['name'],
            'data' => $this->payload['data'],
            'children' => $this->payload['children'],
            'checksum' => $this->payload['checksum'],
            'errorBag' => $this->payload['errorBag'],
            'actionQueue' => [['type' => $message, 'payload' => $payload]],
        ]);
    }

    public function callEndpoint($method, $url, $payload)
    {
        $laravelTestingWrapper = new MakesHttpRequestsWrapper(app());

        $response = null;

        $laravelTestingWrapper->temporarilyDisableExceptionHandlingAndMiddleware(function ($wrapper) use (&$response, $method, $url, $payload) {
            $response = $wrapper->call($method, $url, $payload);
        });

        return $response;
    }

    public function id()
    {
        return $this->payload['id'];
    }

    public function instance()
    {
        return Livewire::activate($this->componentName, $this->id());
    }

    public function viewData($key)
    {
        return $this->lastRenderedView->getData()[$key];
    }

    public function get($property)
    {
        return data_get($this->payload['data'], $property);
    }

    public function __get($property)
    {
        return $this->get($property);
    }

    public function __call($method, $params)
    {
        return $this->lastResponse->$method(...$params);
    }
}
