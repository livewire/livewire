<?php

namespace Livewire\Features\SupportTesting;

use Livewire\Mechanisms\HandleComponents\UpdateEngines\HtmlDelta;
use Livewire\Mechanisms\HandleComponents\UpdateEngines\RenderFragmentTree;
use Livewire\Mechanisms\HandleComponents\UpdateEngines\StatelessHtmlChunks;

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

        $snapshot = $this->resolveSnapshot($componentResponsePayload, $encodedSnapshot);

        $effects = $componentResponsePayload['effects'];

        [ $html, $serverRenderedHtml, $serverRenderedHtmlHash, $effects ] = $this->resolveRenderedHtml($effects);
        $view = $componentView ?? $this->lastState->getView();
        $renderRevision = $this->lastState->getRenderRevision();

        if (is_string($serverRenderedHtmlHash)
            && $serverRenderedHtmlHash !== $this->lastState->getServerRenderedHtmlHash()
        ) {
            $renderRevision++;
        }

        return new ComponentState(
            $componentInstance,
            $response,
            $view,
            $html,
            $snapshot,
            $effects,
            $serverRenderedHtml,
            $serverRenderedHtmlHash,
            $renderRevision,
        );
    }

    protected function resolveRenderedHtml(array $effects): array
    {
        $previousHtml = $this->lastState->getServerRenderedHtml();
        $previousHash = $this->lastState->getServerRenderedHtmlHash();

        if (is_string($effects['html'] ?? null)) {
            $hash = is_string($effects['render']['target'] ?? null)
                ? $effects['render']['target']
                : (is_string($effects['htmlHash'] ?? null)
                    ? $effects['htmlHash']
                    : hash('sha256', $effects['html']));

            $this->assertTransportIntegrity($effects['html'], $hash, $effects['render']['bytes'] ?? null);

            return [
                $effects['html'],
                $effects['html'],
                $hash,
                $effects,
            ];
        }

        if (is_array($effects['render'] ?? null)
            && ($effects['render']['v'] ?? null) === 1
            && is_string($previousHtml)
            && is_string($previousHash)
        ) {
            $render = $effects['render'];
            $mode = $render['mode'] ?? null;

            if (! in_array($mode, ['same', 'splice', 'chunks', 'fragments'], true)
                || ! is_string($render['base'] ?? null)
                || ! hash_equals($previousHash, $render['base'])
                || ! is_string($render['target'] ?? null)
                || ! is_int($render['bytes'] ?? null)
            ) {
                throw new \InvalidArgumentException('Invalid render transport descriptor.');
            }

            $serverRenderedHtml = match ($mode) {
                'same' => $previousHtml,
                'splice' => app(HtmlDelta::class)->apply($previousHtml, $render['patches'] ?? []),
                'chunks' => app(StatelessHtmlChunks::class)->apply($previousHtml, $render['ops'] ?? []),
                'fragments' => app(RenderFragmentTree::class)->apply($previousHtml, $render['ops'] ?? []),
            };

            $this->assertTransportIntegrity($serverRenderedHtml, $render['target'], $render['bytes']);

            $effects['html'] = $serverRenderedHtml;

            return [
                $serverRenderedHtml,
                $serverRenderedHtml,
                $render['target'],
                $effects,
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
                $effects + ['html' => $serverRenderedHtml],
            ];
        }

        return [
            $this->lastState->getHtml(stripInitialData: true),
            $previousHtml,
            $previousHash,
            $effects,
        ];
    }

    protected function resolveSnapshot(array $response, string $previousSnapshot): array
    {
        if (is_string($response['snapshot'] ?? null)) {
            return json_decode($response['snapshot'], true, 512, JSON_THROW_ON_ERROR);
        }

        $delta = $response['snapshotDelta'] ?? null;

        if (! is_array($delta)
            || ($delta['v'] ?? null) !== 1
            || ! is_string($delta['base'] ?? null)
            || ! hash_equals(hash('sha256', $previousSnapshot), $delta['base'])
            || ! is_string($delta['target'] ?? null)
            || ! is_int($delta['bytes'] ?? null)
            || ! is_array($delta['patches'] ?? null)
        ) {
            throw new \InvalidArgumentException('Invalid snapshot transport descriptor.');
        }

        $snapshot = app(HtmlDelta::class)->apply($previousSnapshot, $delta['patches']);

        $this->assertTransportIntegrity($snapshot, $delta['target'], $delta['bytes']);

        return json_decode($snapshot, true, 512, JSON_THROW_ON_ERROR);
    }

    protected function assertTransportIntegrity(string $value, string $hash, ?int $bytes): void
    {
        if (preg_match('/^[a-f0-9]{64}$/', $hash) !== 1
            || ($bytes !== null && $bytes !== strlen($value))
            || ! hash_equals($hash, hash('sha256', $value))
        ) {
            throw new \InvalidArgumentException('Transport integrity check failed.');
        }
    }
}
