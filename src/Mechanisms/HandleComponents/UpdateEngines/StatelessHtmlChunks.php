<?php

namespace Livewire\Mechanisms\HandleComponents\UpdateEngines;

class StatelessHtmlChunks
{
    protected const MIN_BLOCK_SIZE = 256;

    protected const MAX_BLOCK_SIZE = 65536;

    protected const MAX_HTML_BYTES = 67108864;

    protected const MAX_MANIFEST_BYTES = 524288;

    protected const MAX_MANIFEST_BLOCKS = 65536;

    protected const MAX_OPS = 65536;

    protected const MAX_SCAN_STEPS = 65536;

    protected const MAX_STRONG_CHECKSUM_BYTES = 8388608;

    public function manifest(string $html, int $blockSize): string
    {
        $this->validateBlockSize($blockSize);
        $this->validateHtml($html);

        $blockCount = $html === ''
            ? 0
            : intdiv(strlen($html) + $blockSize - 1, $blockSize);

        if ($blockCount > self::MAX_MANIFEST_BLOCKS) {
            throw new \InvalidArgumentException('HTML chunk manifest is too large.');
        }

        $manifest = '';

        for ($offset = 0, $length = strlen($html); $offset < $length; $offset += $blockSize) {
            $block = substr($html, $offset, $blockSize);

            $manifest .= pack('N2', $this->weakChecksum($block), $this->strongChecksum($block));
        }

        if (strlen($manifest) > self::MAX_MANIFEST_BYTES) {
            throw new \InvalidArgumentException('HTML chunk manifest is too large.');
        }

        return base64_encode($manifest);
    }

    public function decodeManifest(
        string $encodedManifest,
        int $baseLength,
        int $blockSize,
    ): array {
        $this->validateBlockSize($blockSize);
        $this->validateHtmlLength($baseLength);

        $expectedBlocks = $baseLength === 0
            ? 0
            : intdiv($baseLength + $blockSize - 1, $blockSize);

        if (
            $expectedBlocks > self::MAX_MANIFEST_BLOCKS
            || strlen($encodedManifest) > 4 * intdiv(self::MAX_MANIFEST_BYTES + 2, 3)
        ) {
            throw new \InvalidArgumentException('HTML chunk manifest is too large.');
        }

        $manifest = base64_decode($encodedManifest, strict: true);

        if (
            $manifest === false
            || $encodedManifest !== base64_encode($manifest)
            || strlen($manifest) > self::MAX_MANIFEST_BYTES
            || strlen($manifest) % 8 !== 0
        ) {
            throw new \InvalidArgumentException('Invalid HTML chunk manifest.');
        }

        if (strlen($manifest) !== $expectedBlocks * 8) {
            throw new \InvalidArgumentException('HTML chunk manifest length does not match its baseline.');
        }

        $blocks = [];

        for ($index = 0; $index < $expectedBlocks; $index++) {
            $signature = unpack('Nweak/Nstrong', substr($manifest, $index * 8, 8));
            $offset = $index * $blockSize;

            $blocks[] = [
                'offset' => $offset,
                'length' => min($blockSize, $baseLength - $offset),
                'weak' => $signature['weak'],
                'strong' => $signature['strong'],
            ];
        }

        return $blocks;
    }

    public function encode(
        string $html,
        string $encodedManifest,
        int $baseLength,
        int $blockSize,
    ): array {
        $this->validateHtml($html);

        $blocks = $this->decodeManifest($encodedManifest, $baseLength, $blockSize);

        if ($html === '') return [];

        $index = [];

        foreach ($blocks as $block) {
            $length = $block['length'];
            $weak = (string) $block['weak'];
            $strong = (string) $block['strong'];

            if (! isset($index[$length][$weak][$strong])) {
                $index[$length][$weak][$strong] = [
                    'first' => $block['offset'],
                    'offsets' => [],
                ];
            }

            $index[$length][$weak][$strong]['offsets'][$block['offset']] = true;
        }

        $windowLengths = array_keys($index);
        rsort($windowLengths, SORT_NUMERIC);

        $states = $this->initializeWindowStates($html, 0, $windowLengths);
        $ops = [];
        $add = '';
        $offset = 0;
        $scanSteps = 0;
        $strongChecksumBytes = 0;
        $htmlLength = strlen($html);

        while ($offset < $htmlLength) {
            $match = $this->findMatch(
                $html,
                $offset,
                $states,
                $windowLengths,
                $index,
                $add === '' ? $this->nextCopyOffset($ops) : null,
                $strongChecksumBytes,
            );

            if ($match !== null) {
                $this->flushAdd($ops, $add);
                $this->appendCopy($ops, $match['offset'], $match['length']);

                $offset += $match['length'];
                $states = $this->initializeWindowStates($html, $offset, $windowLengths);

                continue;
            }

            if (++$scanSteps > self::MAX_SCAN_STEPS) {
                throw new \InvalidArgumentException('HTML chunk scan exceeded its work limit.');
            }

            $add .= $html[$offset];
            $states = $this->rollWindowStates($html, $offset, $states, $windowLengths);
            $offset++;
        }

        $this->flushAdd($ops, $add);

        if (count($ops) > self::MAX_OPS) {
            throw new \InvalidArgumentException('HTML chunk recipe contains too many operations.');
        }

        return $ops;
    }

    public function apply(string $base, array $ops): string
    {
        $this->validateHtml($base);

        if (count($ops) > self::MAX_OPS) {
            throw new \InvalidArgumentException('HTML chunk recipe contains too many operations.');
        }

        $result = '';

        foreach ($ops as $op) {
            if (! is_array($op) || ! array_is_list($op) || ! isset($op[0])) {
                throw new \InvalidArgumentException('Invalid HTML chunk operation.');
            }

            if ($op[0] === 'c') {
                if (
                    count($op) !== 3
                    || ! is_int($op[1])
                    || ! is_int($op[2])
                    || $op[1] < 0
                    || $op[2] <= 0
                    || $op[1] + $op[2] > strlen($base)
                ) {
                    throw new \InvalidArgumentException('Invalid HTML chunk copy range.');
                }

                $this->guardOutputLength(strlen($result), $op[2]);

                $result .= substr($base, $op[1], $op[2]);

                continue;
            }

            if ($op[0] === 'a') {
                if (count($op) !== 2 || ! is_string($op[1])) {
                    throw new \InvalidArgumentException('Invalid HTML chunk add operation.');
                }

                $addition = base64_decode($op[1], strict: true);

                if (
                    $addition === false
                    || $addition === ''
                    || $op[1] !== base64_encode($addition)
                ) {
                    throw new \InvalidArgumentException('Invalid base64 in HTML chunk operation.');
                }

                $this->guardOutputLength(strlen($result), strlen($addition));

                $result .= $addition;

                continue;
            }

            throw new \InvalidArgumentException('Unknown HTML chunk operation.');
        }

        $this->validateHtml($result);

        return $result;
    }

    protected function findMatch(
        string $html,
        int $offset,
        array $states,
        array $windowLengths,
        array $index,
        ?int $preferredOffset,
        int &$strongChecksumBytes,
    ): ?array {
        foreach ($windowLengths as $length) {
            if (! isset($states[$length])) continue;

            $weak = (string) $this->combineWeakChecksum($states[$length]);
            $candidates = $index[$length][$weak] ?? null;

            if ($candidates === null) continue;

            if ($strongChecksumBytes + $length > self::MAX_STRONG_CHECKSUM_BYTES) {
                throw new \InvalidArgumentException('HTML chunk checksum work exceeded its byte limit.');
            }

            $strongChecksumBytes += $length;

            $strong = (string) $this->strongChecksum(substr($html, $offset, $length));
            $matches = $candidates[$strong] ?? null;

            if ($matches === null) continue;

            $matchOffset = $preferredOffset !== null
                && isset($matches['offsets'][$preferredOffset])
                    ? $preferredOffset
                    : $matches['first'];

            return [
                'offset' => $matchOffset,
                'length' => $length,
            ];
        }

        return null;
    }

    protected function initializeWindowStates(string $html, int $offset, array $windowLengths): array
    {
        $states = [];
        $htmlLength = strlen($html);

        foreach ($windowLengths as $length) {
            if ($offset + $length > $htmlLength) continue;

            $states[$length] = $this->weakChecksumParts(substr($html, $offset, $length));
        }

        return $states;
    }

    protected function rollWindowStates(
        string $html,
        int $offset,
        array $states,
        array $windowLengths,
    ): array {
        $htmlLength = strlen($html);

        foreach ($windowLengths as $length) {
            if (! isset($states[$length]) || $offset + $length >= $htmlLength) {
                unset($states[$length]);

                continue;
            }

            [$a, $b] = $states[$length];
            $outgoing = ord($html[$offset]);
            $incoming = ord($html[$offset + $length]);
            $a = ($a - $outgoing + $incoming) & 0xffff;
            $b = ($b - ($length * $outgoing) + $a) & 0xffff;

            $states[$length] = [$a, $b];
        }

        return $states;
    }

    protected function weakChecksum(string $block): int
    {
        return $this->combineWeakChecksum($this->weakChecksumParts($block));
    }

    protected function weakChecksumParts(string $block): array
    {
        $a = 0;
        $b = 0;
        $length = strlen($block);

        for ($index = 0; $index < $length; $index++) {
            $byte = ord($block[$index]);
            $a = ($a + $byte) & 0xffff;
            $b = ($b + (($length - $index) * $byte)) & 0xffff;
        }

        return [$a, $b];
    }

    protected function combineWeakChecksum(array $parts): int
    {
        return (($parts[1] << 16) | $parts[0]) & 0xffffffff;
    }

    protected function strongChecksum(string $block): int
    {
        return unpack('Nchecksum', hash('crc32b', $block, true))['checksum'];
    }

    protected function nextCopyOffset(array $ops): ?int
    {
        if ($ops === []) return null;

        $last = $ops[array_key_last($ops)];

        if ($last[0] !== 'c') return null;

        return $last[1] + $last[2];
    }

    protected function appendCopy(array &$ops, int $offset, int $length): void
    {
        if ($ops !== []) {
            $lastIndex = array_key_last($ops);
            $last = $ops[$lastIndex];

            if ($last[0] === 'c' && $last[1] + $last[2] === $offset) {
                $ops[$lastIndex][2] += $length;

                return;
            }
        }

        $ops[] = ['c', $offset, $length];
    }

    protected function flushAdd(array &$ops, string &$add): void
    {
        if ($add === '') return;

        $ops[] = ['a', base64_encode($add)];
        $add = '';
    }

    protected function validateBlockSize(int $blockSize): void
    {
        if ($blockSize < self::MIN_BLOCK_SIZE || $blockSize > self::MAX_BLOCK_SIZE) {
            throw new \InvalidArgumentException('Invalid HTML chunk block size.');
        }
    }

    protected function validateHtmlLength(int $length): void
    {
        if ($length < 0 || $length > self::MAX_HTML_BYTES) {
            throw new \InvalidArgumentException('HTML chunk input is too large.');
        }
    }

    protected function validateHtml(string $html): void
    {
        $this->validateHtmlLength(strlen($html));

        if (preg_match('//u', $html) !== 1) {
            throw new \InvalidArgumentException('HTML chunk input is not valid UTF-8.');
        }
    }

    protected function guardOutputLength(int $current, int $addition): void
    {
        if ($addition < 0 || $current + $addition > self::MAX_HTML_BYTES) {
            throw new \InvalidArgumentException('HTML chunk output is too large.');
        }
    }
}
