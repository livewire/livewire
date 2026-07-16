<?php

namespace Livewire\Features\SupportTesting;

use Livewire\Mechanisms\HandleComponents\UpdateEngines\HtmlDelta;

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
                    'render' => $this->lastState->getRenderMetadata(),
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
        $response->baseResponse->original = $componentView;

        $componentResponsePayload = $json['components'][0];

        $snapshot = json_decode($componentResponsePayload['snapshot'], true);

        $effects = $componentResponsePayload['effects'];

        [ $html, $serverRenderedHtml, $serverRenderedHtmlHash ] = $this->resolveRenderedHtml($effects);
        $view = $componentView ?? $this->lastState->getView();

        return new ComponentState(
            $componentInstance,
            $response,
            $view,
            $html,
            $snapshot,
            $effects,
            $serverRenderedHtml,
            $serverRenderedHtmlHash,
        );
    }

    protected function resolveRenderedHtml(array $effects): array
    {
        $previousHtml = $this->lastState->getServerRenderedHtml();
        $previousHash = $this->lastState->getServerRenderedHtmlHash();

        if (is_string($effects['html'] ?? null)) {
            return [
                $effects['html'],
                $effects['html'],
                is_string($effects['htmlHash'] ?? null) ? $effects['htmlHash'] : null,
            ];
        }

        if (is_array($effects['htmlDelta'] ?? null)
            && is_string($previousHtml)
            && is_string($previousHash)
            && is_string($effects['htmlDelta']['base'] ?? null)
            && is_string($effects['htmlHash'] ?? null)
            && hash_equals($previousHash, $effects['htmlDelta']['base'])
        ) {
            $serverRenderedHtml = app(HtmlDelta::class)->apply(
                $previousHtml,
                $effects['htmlDelta']['patches'] ?? $effects['htmlDelta']['patch'] ?? [],
            );

            return [
                $serverRenderedHtml,
                $serverRenderedHtml,
                $effects['htmlHash'],
            ];
        }

        return [
            $this->lastState->getHtml(stripInitialData: true),
            $previousHtml,
            $previousHash,
        ];
    }
}
