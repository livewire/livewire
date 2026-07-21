<?php

namespace Livewire\Mechanisms\HandleComponents\UpdateEngines;

class HtmlDelta
{
    protected const BLOCK_SIZE = 32;

    protected const MAX_PATCHES = 64;

    protected const MAX_CANDIDATE_PATCHES = 256;

    protected const MAX_SCAN_STEPS = 4096;

    public function encode(string $from, string $to): array
    {
        if ($from === $to) return [];

        $single = $this->singlePatch($from, $to);
        $fromStart = $single['start'];
        $fromEnd = $fromStart + $single['delete'];
        $toStart = $fromStart;
        $toEnd = $toStart + strlen($single['insert']);

        if (
            $fromEnd - $fromStart < self::BLOCK_SIZE
            || $toEnd - $toStart < self::BLOCK_SIZE
        ) {
            return [$this->encodePatch($single)];
        }

        $patches = $this->findAnchoredPatches(
            $from,
            $to,
            $fromStart,
            $fromEnd,
            $toStart,
            $toEnd,
        );

        if ($patches === null) {
            return [$this->encodePatch($single)];
        }

        $patches = $this->coalescePatches($from, $patches);

        if (count($patches) > self::MAX_PATCHES) {
            return [$this->encodePatch($single)];
        }

        return array_map($this->encodePatch(...), $patches);
    }

    public function apply(string $html, array $patches): string
    {
        // Accept the original single-patch shape so a cached test state or a
        // rolling deployment can safely reconstruct an older delta.
        if (isset($patches['start'])) $patches = [$patches];

        $length = strlen($html);
        $cursor = 0;
        $result = '';

        foreach ($patches as $patch) {
            if (! is_array($patch)) {
                throw new \InvalidArgumentException('Invalid HTML delta patch.');
            }

            $start = $patch['start'] ?? null;
            $delete = $patch['delete'] ?? null;
            $encodedInsert = $patch['insert'] ?? null;

            if (
                ! is_int($start)
                || ! is_int($delete)
                || ! is_string($encodedInsert)
                || $start < $cursor
                || $delete < 0
                || $start + $delete > $length
            ) {
                throw new \InvalidArgumentException('Invalid HTML delta range.');
            }

            $insert = base64_decode($encodedInsert, strict: true);

            if ($insert === false) {
                throw new \InvalidArgumentException('Invalid base64 insert in HTML delta.');
            }

            $result .= substr($html, $cursor, $start - $cursor).$insert;
            $cursor = $start + $delete;
        }

        return $result.substr($html, $cursor);
    }

    public function hash(string $html): string
    {
        return hash('sha256', $html);
    }

    protected function singlePatch(string $from, string $to): array
    {
        $prefix = $this->commonPrefixLength($from, $to);
        $suffix = $this->commonSuffixLength($from, $to, $prefix);

        return [
            'start' => $prefix,
            'delete' => strlen($from) - $prefix - $suffix,
            'insert' => substr($to, $prefix, strlen($to) - $prefix - $suffix),
        ];
    }

    protected function findAnchoredPatches(
        string $from,
        string $to,
        int $fromStart,
        int $fromEnd,
        int $toStart,
        int $toEnd,
    ): ?array {
        $index = $this->buildBlockIndex($from, $fromStart, $fromEnd);

        if ($index === []) {
            return [[
                'start' => $fromStart,
                'delete' => $fromEnd - $fromStart,
                'insert' => substr($to, $toStart, $toEnd - $toStart),
            ]];
        }

        $patches = [];
        $fromCursor = $fromStart;
        $toCursor = $toStart;
        $scan = $toCursor;
        $scanSteps = 0;

        while (
            $scan + self::BLOCK_SIZE <= $toEnd
            && $fromCursor + self::BLOCK_SIZE <= $fromEnd
        ) {
            $block = substr($to, $scan, self::BLOCK_SIZE);
            $candidates = $index[$this->blockHash($block)] ?? [];
            $matchFrom = $this->findMatchingCandidate(
                $from,
                $block,
                $candidates,
                $fromCursor,
            );

            if ($matchFrom === null) {
                $scan++;

                if (++$scanSteps > self::MAX_SCAN_STEPS) return null;

                continue;
            }

            $matchTo = $scan;

            while (
                $matchFrom > $fromCursor
                && $matchTo > $toCursor
                && $from[$matchFrom - 1] === $to[$matchTo - 1]
            ) {
                $matchFrom--;
                $matchTo--;
            }

            if ($matchFrom > $fromCursor || $matchTo > $toCursor) {
                $patches[] = [
                    'start' => $fromCursor,
                    'delete' => $matchFrom - $fromCursor,
                    'insert' => substr($to, $toCursor, $matchTo - $toCursor),
                ];

                if (count($patches) > self::MAX_CANDIDATE_PATCHES) return null;
            }

            $matchLength = $this->commonRangeLength(
                $from,
                $matchFrom,
                $fromEnd,
                $to,
                $matchTo,
                $toEnd,
            );

            $fromCursor = $matchFrom + $matchLength;
            $toCursor = $matchTo + $matchLength;
            $scan = $toCursor;
        }

        if ($fromCursor < $fromEnd || $toCursor < $toEnd) {
            $patches[] = [
                'start' => $fromCursor,
                'delete' => $fromEnd - $fromCursor,
                'insert' => substr($to, $toCursor, $toEnd - $toCursor),
            ];
        }

        return array_values(array_filter(
            $patches,
            fn ($patch) => $patch['delete'] !== 0 || $patch['insert'] !== '',
        ));
    }

    protected function buildBlockIndex(string $html, int $start, int $end): array
    {
        $index = [];

        for (
            $offset = $start;
            $offset + self::BLOCK_SIZE <= $end;
            $offset += self::BLOCK_SIZE
        ) {
            $block = substr($html, $offset, self::BLOCK_SIZE);
            $index[$this->blockHash($block)][] = $offset;
        }

        return $index;
    }

    protected function findMatchingCandidate(
        string $html,
        string $block,
        array $candidates,
        int $minimumOffset,
    ): ?int {
        $low = 0;
        $high = count($candidates);

        while ($low < $high) {
            $middle = intdiv($low + $high, 2);

            if ($candidates[$middle] < $minimumOffset) {
                $low = $middle + 1;
            } else {
                $high = $middle;
            }
        }

        for ($index = $low, $count = count($candidates); $index < $count; $index++) {
            $candidate = $candidates[$index];

            if (substr($html, $candidate, self::BLOCK_SIZE) === $block) {
                return $candidate;
            }
        }

        return null;
    }

    protected function coalescePatches(string $from, array $patches): array
    {
        $result = [];

        foreach ($patches as $patch) {
            if ($result === []) {
                $result[] = $patch;

                continue;
            }

            $previousIndex = array_key_last($result);
            $previous = $result[$previousIndex];
            $previousEnd = $previous['start'] + $previous['delete'];
            $gap = $patch['start'] - $previousEnd;

            if ($gap < 0) {
                throw new \LogicException('Generated HTML delta patches overlap.');
            }

            $combined = [
                'start' => $previous['start'],
                'delete' => $patch['start'] + $patch['delete'] - $previous['start'],
                'insert' => $previous['insert']
                    .substr($from, $previousEnd, $gap)
                    .$patch['insert'],
            ];

            if (
                $this->encodedPatchSize($combined)
                <= $this->encodedPatchSize($previous) + $this->encodedPatchSize($patch) + 1
            ) {
                $result[$previousIndex] = $combined;
            } else {
                $result[] = $patch;
            }
        }

        return $result;
    }

    protected function encodedPatchSize(array $patch): int
    {
        return strlen(json_encode($this->encodePatch($patch), JSON_THROW_ON_ERROR));
    }

    protected function encodePatch(array $patch): array
    {
        return [
            'start' => $patch['start'],
            'delete' => $patch['delete'],
            'insert' => base64_encode($patch['insert']),
        ];
    }

    protected function blockHash(string $block): string
    {
        return (string) crc32($block);
    }

    protected function commonPrefixLength(string $from, string $to): int
    {
        return $this->commonRangeLength($from, 0, strlen($from), $to, 0, strlen($to));
    }

    protected function commonSuffixLength(string $from, string $to, int $prefix): int
    {
        $fromLength = strlen($from);
        $toLength = strlen($to);
        $length = min($fromLength, $toLength) - $prefix;
        $offset = 0;

        while (
            $offset + 64 <= $length
            && substr($from, $fromLength - $offset - 64, 64)
                === substr($to, $toLength - $offset - 64, 64)
        ) {
            $offset += 64;
        }

        while (
            $offset < $length
            && $from[$fromLength - $offset - 1] === $to[$toLength - $offset - 1]
        ) {
            $offset++;
        }

        return $offset;
    }

    protected function commonRangeLength(
        string $from,
        int $fromOffset,
        int $fromEnd,
        string $to,
        int $toOffset,
        int $toEnd,
    ): int {
        $length = min($fromEnd - $fromOffset, $toEnd - $toOffset);
        $matched = 0;

        while (
            $matched + 64 <= $length
            && substr($from, $fromOffset + $matched, 64)
                === substr($to, $toOffset + $matched, 64)
        ) {
            $matched += 64;
        }

        while (
            $matched < $length
            && $from[$fromOffset + $matched] === $to[$toOffset + $matched]
        ) {
            $matched++;
        }

        return $matched;
    }
}
