<?php

namespace Livewire\Testing;

use Mockery;
use Livewire\Livewire;
use Illuminate\Support\Str;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\View;
use Livewire\GenerateSignedUploadUrl;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Traits\Macroable;
use Facades\Livewire\GenerateSignedUploadUrl as GenerateSignedUploadUrlFacade;

class TestableLivewire
{
    public $payload = [];
    public $componentName;
    public $lastValidator;
    public $lastRenderedView;
    public $lastResponse;
    public $rawMountedResponse;

    use Macroable { __call as macroCall; }

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
            'locale' => $output->locale,
            'redirectTo' => $output->redirectTo,
            'dirtyInputs' => $output->dirtyInputs,
            'updatesQueryString' => $output->updatesQueryString,
        ];
    }

    public function pretendWereMountingAComponentOnAPage($name, $params)
    {
        $randomRoutePath = '/testing-livewire/'.Str::random(20);

        $this->registerRouteBeforeExistingRoutes($randomRoutePath, function () use ($name, $params) {
            return View::file(__DIR__.'/../views/mount-component.blade.php', [
                'name' => $name,
                'params' => $params,
            ]);
        });

        $laravelTestingWrapper = new MakesHttpRequestsWrapper(app());

        $response = null;

        $laravelTestingWrapper->temporarilyDisableExceptionHandlingAndMiddleware(function ($wrapper) use ($randomRoutePath, &$response) {
            $response = $wrapper->call('GET', $randomRoutePath);
        });

        return $response;
    }

    private function registerRouteBeforeExistingRoutes($path, $closure)
    {
        // To prevent this route from overriding wildcard routes registered within the application,
        // We have to make sure that this route is registered before other existing routes.
        $livewireTestingRoute = new Route(['GET', 'HEAD'], $path, $closure);

        $existingRoutes = app('router')->getRoutes();

        // Make an empty collection.
        $runningCollection = new RouteCollection;

        // Add this testing route as the first one.
        $runningCollection->add($livewireTestingRoute);

        // Now add the existing routes after it.
        foreach ($existingRoutes as $route) {
            $runningCollection->add($route);
        }

        // Now set this route collection as THE route collection for the app.
        app('router')->setRoutes($runningCollection);
    }

    public function pretendWereSendingAComponentUpdateRequest($message, $payload)
    {
        return $this->callEndpoint('POST', '/livewire/message/'.$this->componentName, [
            'id' => $this->payload['id'],
            'name' => $this->payload['name'],
            'data' => $this->payload['data'],
            'children' => $this->payload['children'],
            'checksum' => $this->payload['checksum'],
            'locale' => $this->payload['locale'],
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
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $params);
        }

        return $this->lastResponse->$method(...$params);
    }
}
