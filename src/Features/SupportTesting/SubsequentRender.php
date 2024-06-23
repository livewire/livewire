<?php

namespace Livewire\Features\SupportTesting;

class SubsequentRender extends Render
{
    function __construct(
        protected RequestBroker $requestBroker,
        protected ComponentState $lastState,
    ) {}

    static function make($requestBroker, $lastState, $calls = [], $updates = [], $cookies = [])
    {
        $instance = new static($requestBroker, $lastState);

        return $instance->makeSubsequentRequest($calls, $updates, $cookies);
    }

    function makeSubsequentRequest($calls = [], $updates = [], $cookies = []) {
        $uri = app('livewire')->getUpdateUri();

        $encodedSnapshot = json_encode($this->lastState->getSnapshot());

        $payload = [
            'components' => [
                [
                    'snapshot' => $encodedSnapshot,
                    'calls' => $calls,
                    'updates' => $updates,
                ],
            ],
        ];

        [$response, $componentInstance, $componentView] = $this->extractComponentAndBladeView(function () use ($uri, $payload, $cookies) {
            return $this->requestBroker->temporarilyDisableExceptionHandlingAndMiddleware(function ($requestBroker) use ($uri, $payload, $cookies) {
                return $requestBroker->addHeaders(['X-Livewire' => true])->call('POST', $uri, $payload, $cookies);
            });
        });

        app('livewire')->flushState();

        if (! $response->isOk()) {
            return new ComponentState(
                $componentInstance,
                $response,
                null,
                '',
                [],
                [],
            );
        }

        $json = $response->json();

        // Set "original" to Blade view for assertions like "assertViewIs()"...
        $response->original = $componentView;

        $componentResponsePayload = $json['components'][0];

        $snapshot = json_decode($componentResponsePayload['snapshot'], true);

        $effects = $componentResponsePayload['effects'];

        // If no new HTML has been rendered, let's forward the last known HTML...
        $html = $effects['html'] ?? $this->lastState->getHtml(stripInitialData: true);
        $view = $componentView ?? $this->lastState->getView();

        return new ComponentState(
            $componentInstance,
            $response,
            $view,
            $html,
            $snapshot,
            $effects,
        );
    }
}
