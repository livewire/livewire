<?php

namespace Livewire\Testing;

use Livewire\Livewire;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;

class TestableLivewire
{
    public $prefix;
    public $payload = [];
    public $componentName;
    public $lastValidator;
    public $lastRenderedView;
    public $lastResponse;
    public $rawMountedResponse;

    use Concerns\MakesAssertions,
        Concerns\MakesCallsToComponent,
        Concerns\HasFunLittleUtilities;

    public function __construct($name, $prefix, $params = [])
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

        $this->prefix = $prefix;

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

        Route::get($randomRoutePath, function () use ($name, $params) {
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

    public function pretendWereSendingAComponentUpdateRequest($message, $payload)
    {
        $laravelTestingWrapper = new MakesHttpRequestsWrapper(app());

        $response = null;

        $laravelTestingWrapper->temporarilyDisableExceptionHandlingAndMiddleware(function ($wrapper) use (&$response, $message, $payload) {
            $response = $wrapper->call('POST', '/livewire/message/'.$this->componentName, [
                'id' => $this->payload['id'],
                'name' => $this->payload['name'],
                'data' => $this->payload['data'],
                'children' => $this->payload['children'],
                'checksum' => $this->payload['checksum'],
                'errorBag' => $this->payload['errorBag'],
                'actionQueue' => [['type' => $message, 'payload' => $payload]],
            ]);
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
        return $this->lastRenderedView->gatherData()[$key];
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
