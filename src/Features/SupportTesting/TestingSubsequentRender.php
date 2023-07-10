<?php

namespace Livewire\Features\SupportTesting;

use Livewire\Drawer\Utils;

use function Livewire\on;

class TestingSubsequentRender
{
    function __construct(
        protected $requester,
        protected $lastState,
    ) {}

    static function make($requester, $lastState, $calls = [], $updates = [])
    {
        $instance = new static($requester, $lastState);

        return $instance->renameme($calls, $updates);
    }

    function renameme($calls = [], $updates = []) {
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

        $componentInstance = null;
        $componentView = null;

        $offA = on('dehydrate', function ($component) use (&$componentInstance) {
            $componentInstance = $component;
        });

        $offB = on('render', function ($component, $view) use (&$componentView) {
            return function () use ($view, &$componentView) {
                $componentView = $view;
            };
        });

        $response = $this->requester->temporarilyDisableExceptionHandlingAndMiddleware(function ($requester) use ($uri, $payload) {
            return $requester->withHeaders(['X-Livewire' => true])->post($uri, $payload);
        });

        app('livewire')->flushState();

        $offA(); $offB();

        $json = $response->json();

        $componentResponsePayload = $json['components'][0];

        $snapshot = json_decode($componentResponsePayload['snapshot'], true);

        $effects = $componentResponsePayload['effects'];

        // If no new HTML has been rendered, let's forward the last known HTML...
        $html = $effects['html'] ?? $this->lastState->getHtml(stripInitialData: true);
        $view = $componentView ?? $this->lastState->getView();

        return new TestingState(
            $componentInstance,
            $response,
            $view,
            $html,
            $snapshot,
            $effects,
        );
    }
}
