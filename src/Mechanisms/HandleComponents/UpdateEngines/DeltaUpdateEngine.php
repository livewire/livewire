<?php

namespace Livewire\Mechanisms\HandleComponents\UpdateEngines;

use Livewire\Component;
use Livewire\Mechanisms\HandleComponents\ComponentContext;

class DeltaUpdateEngine implements UpdateEngine
{
    public function __construct(
        protected RenderStateStore $states,
        protected HtmlDelta $deltas,
    ) {}

    public function mount(Component $component, string $html, ComponentContext $context): void
    {
        if (! $this->shouldTrack($html)) return;

        $hash = $this->deltas->hash($html);

        $context->addMemo('delta', [
            'revision' => 0,
            'hash' => $hash,
        ]);
    }

    public function update(
        Component $component,
        ?string $html,
        array $memo,
        ComponentContext $context,
        array $renderMetadata = [],
    ): void {
        $previous = $memo['delta'] ?? null;

        if ($html === null) {
            if ($previous) {
                $context->addMemo('delta', $previous);
                $context->addEffect('htmlHash', $previous['hash']);
            }

            return;
        }

        if (! $this->shouldTrack($html)) {
            $context->addEffect('html', $html);

            return;
        }

        $hash = $this->deltas->hash($html);
        $revision = ($previous['revision'] ?? -1) + 1;
        $clientHash = $renderMetadata['htmlHash'] ?? null;
        $previousHash = $previous['hash'] ?? null;
        $previousHtml = null;

        if (
            is_string($clientHash)
            && is_string($previousHash)
            && hash_equals($previousHash, $clientHash)
        ) {
            $previousHtml = $this->getPreviousHtml($component->getId(), $previousHash);
        }

        if ($previousHtml !== null) {
            $effect = [
                'base' => $previousHash,
                'patches' => $this->deltas->encode($previousHtml, $html),
            ];

            if ($this->shouldSendDelta($effect, $html, $hash)) {
                $context->addEffect('htmlDelta', $effect);
            } else {
                $context->addEffect('html', $html);
            }
        } else {
            $context->addEffect('html', $html);
        }

        $context->addEffect('htmlHash', $hash);
        $context->addMemo('delta', [
            'revision' => $revision,
            'hash' => $hash,
        ]);

        $this->rememberHtml($component->getId(), $hash, $html);
    }

    protected function shouldSendDelta(array $effect, string $html, string $hash): bool
    {
        $minimumSavings = min(1, max(0, (float) config('livewire.delta.minimum_savings', 0.1)));
        $deltaPayload = json_encode([
            'htmlDelta' => $effect,
            'htmlHash' => $hash,
        ], JSON_THROW_ON_ERROR);
        $fullHtmlPayload = json_encode([
            'html' => $html,
            'htmlHash' => $hash,
        ], JSON_THROW_ON_ERROR);

        if (! $this->savesEnough($deltaPayload, $fullHtmlPayload, $minimumSavings)) {
            return false;
        }

        if (! config('livewire.delta.compression_aware', true)) {
            return true;
        }

        $compressedDelta = $this->gzip($deltaPayload);
        $compressedFullHtml = $this->gzip($fullHtmlPayload);

        if ($compressedDelta === null || $compressedFullHtml === null) {
            return true;
        }

        if (! $this->savesEnough($compressedDelta, $compressedFullHtml, $minimumSavings)) {
            return false;
        }

        $minimumCompressedSavings = max(
            0,
            (int) config('livewire.delta.minimum_compressed_savings_bytes', 1024),
        );

        return strlen($compressedFullHtml) - strlen($compressedDelta)
            >= $minimumCompressedSavings;
    }

    protected function savesEnough(string $delta, string $fullHtml, float $minimumSavings): bool
    {
        return strlen($delta) < strlen($fullHtml) * (1 - $minimumSavings);
    }

    protected function gzip(string $payload): ?string
    {
        if (! function_exists('gzencode')) return null;

        $compressed = gzencode($payload, 1);

        return is_string($compressed) ? $compressed : null;
    }

    protected function shouldTrack(string $html): bool
    {
        $minimumHtmlBytes = max(
            0,
            (int) config('livewire.delta.minimum_html_bytes', 8192),
        );

        return strlen($html) >= $minimumHtmlBytes;
    }

    protected function getPreviousHtml(string $componentId, string $hash): ?string
    {
        try {
            return $this->states->get($componentId, $hash);
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    protected function rememberHtml(string $componentId, string $hash, string $html): void
    {
        try {
            $this->states->put($componentId, $hash, $html);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
