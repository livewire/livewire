<?php

namespace Livewire\Mechanisms\HandleComponents\UpdateEngines;

class RenderCandidateSelector
{
    public function __construct(
        protected float $minimumRelativeSavings = 0.2,
        protected int $minimumAbsoluteSavingsBytes = 2048,
        protected bool $compressionAware = true,
    ) {
        if ($minimumRelativeSavings < 0 || $minimumRelativeSavings >= 1) {
            throw new \InvalidArgumentException('Relative render savings must be between zero and one.');
        }

        if ($minimumAbsoluteSavingsBytes < 0) {
            throw new \InvalidArgumentException('Absolute render savings must not be negative.');
        }
    }

    public function select(
        array $full,
        array $candidates,
        int $requestManifestTax = 0,
    ): array {
        if ($requestManifestTax < 0) {
            throw new \InvalidArgumentException('Render manifest tax must not be negative.');
        }

        $fullSize = $this->measure($full);
        $selected = $full;
        $selectedScore = $this->score($fullSize);

        foreach ($candidates as $candidate) {
            if (! is_array($candidate)) {
                throw new \InvalidArgumentException('Render candidates must be arrays.');
            }

            $candidateSize = $this->measure($candidate);
            $effectiveSize = [
                'raw' => $candidateSize['raw'] + $requestManifestTax,
                'gzip' => $candidateSize['gzip'] === null
                    ? null
                    : $candidateSize['gzip'] + $requestManifestTax,
            ];

            if (! $this->meetsThresholds($fullSize, $effectiveSize)) continue;

            $score = $this->score($effectiveSize);

            if ($score >= $selectedScore) continue;

            $selected = $candidate;
            $selectedScore = $score;
        }

        return $selected;
    }

    public function sizes(array $candidate): array
    {
        return $this->measure($candidate);
    }

    protected function measure(array $candidate): array
    {
        $json = json_encode($candidate, JSON_THROW_ON_ERROR);

        return [
            'raw' => strlen($json),
            'gzip' => $this->gzipSize($json),
        ];
    }

    protected function gzipSize(string $json): ?int
    {
        if (! $this->compressionAware || ! function_exists('gzencode')) return null;

        $compressed = gzencode($json, 1);

        return $compressed === false ? null : strlen($compressed);
    }

    protected function meetsThresholds(array $full, array $candidate): bool
    {
        if (! $this->dimensionMeetsThresholds($full['raw'], $candidate['raw'])) return false;

        if ($full['gzip'] === null || $candidate['gzip'] === null) return true;

        return $this->dimensionMeetsThresholds($full['gzip'], $candidate['gzip']);
    }

    protected function dimensionMeetsThresholds(int $full, int $candidate): bool
    {
        $savings = $full - $candidate;

        return $savings >= $this->minimumAbsoluteSavingsBytes
            && $candidate <= $full * (1 - $this->minimumRelativeSavings);
    }

    protected function score(array $size): int
    {
        return $size['gzip'] ?? $size['raw'];
    }
}
