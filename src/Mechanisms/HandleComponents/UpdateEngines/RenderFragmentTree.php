<?php

namespace Livewire\Mechanisms\HandleComponents\UpdateEngines;

class RenderFragmentTree
{
    protected const MARKER_PATTERN = '/<!--\[if (FRAGMENT|ENDFRAGMENT):([^\]]*)\]><!\[endif\]-->/';

    protected const MARKER_PREFIX_PATTERN = '/<!--\[if (?:FRAGMENT|ENDFRAGMENT):/';

    protected const MAX_HTML_BYTES = 67108864;

    protected const MAX_NODES = 4096;

    protected const MAX_TOKEN_BYTES = 256;

    protected const MAX_METADATA_BYTES = 4096;

    public function manifest(string $html): ?array
    {
        $tree = $this->parse($html);

        if ($tree === null) return null;

        return [
            'root' => $tree['root']['skeleton'],
            'nodes' => array_map(
                fn ($token) => [
                    $token,
                    $tree['nodes'][$token]['content'],
                    $tree['nodes'][$token]['skeleton'],
                ],
                $tree['order'],
            ),
        ];
    }

    public function encode(string $html, array $manifest): ?array
    {
        $baseline = $this->decodeManifest($manifest);
        $tree = $this->parse($html);

        if ($tree === null) return null;

        if ($tree['root']['skeleton'] !== $baseline['root']) return null;

        $ops = [];

        foreach ($tree['root']['children'] as $token) {
            if (! $this->selectChangedNodes($html, $tree, $baseline, $token, $ops)) {
                return null;
            }
        }

        return $ops;
    }

    public function apply(string $html, array $ops): string
    {
        $tree = $this->parse($html);

        if ($tree === null) {
            throw new \InvalidArgumentException('Cannot apply fragment operations to malformed HTML.');
        }

        if (count($ops) > self::MAX_NODES) {
            throw new \InvalidArgumentException('Too many render fragment operations.');
        }

        $replacements = [];
        $seen = [];

        foreach ($ops as $op) {
            if (
                ! is_array($op)
                || ! array_is_list($op)
                || count($op) !== 2
                || ! is_string($op[0])
                || ! is_string($op[1])
                || ! isset($tree['nodes'][$op[0]])
                || isset($seen[$op[0]])
            ) {
                throw new \InvalidArgumentException('Invalid render fragment operation.');
            }

            $seen[$op[0]] = true;
            $node = $tree['nodes'][$op[0]];

            $replacements[] = [
                'start' => $node['contentStart'],
                'end' => $node['contentEnd'],
                'html' => $op[1],
            ];
        }

        usort($replacements, fn ($left, $right) => $left['start'] <=> $right['start']);

        $previousEnd = 0;
        $resultLength = strlen($html);

        foreach ($replacements as $replacement) {
            if ($replacement['start'] < $previousEnd) {
                throw new \InvalidArgumentException('Render fragment operations overlap.');
            }

            $previousEnd = $replacement['end'];
            $resultLength += strlen($replacement['html'])
                - ($replacement['end'] - $replacement['start']);

            if ($resultLength > self::MAX_HTML_BYTES) {
                throw new \InvalidArgumentException('Rendered fragment output is too large.');
            }
        }

        $result = $html;

        foreach (array_reverse($replacements) as $replacement) {
            $result = substr($result, 0, $replacement['start'])
                .$replacement['html']
                .substr($result, $replacement['end']);
        }

        if ($this->parse($result) === null) {
            throw new \InvalidArgumentException('Render fragment operations produced malformed HTML.');
        }

        return $result;
    }

    protected function selectChangedNodes(
        string $html,
        array $tree,
        array $baseline,
        string $token,
        array &$ops,
    ): bool {
        $node = $tree['nodes'][$token];
        $previous = $baseline['nodes'][$token] ?? null;

        if ($previous === null) return false;

        if ($node['content'] === $previous['content']) return true;

        if ($node['skeleton'] !== $previous['skeleton']) {
            $ops[] = [
                $token,
                substr($html, $node['contentStart'], $node['contentEnd'] - $node['contentStart']),
            ];

            return true;
        }

        foreach ($node['children'] as $childToken) {
            if (! $this->selectChangedNodes($html, $tree, $baseline, $childToken, $ops)) {
                return false;
            }
        }

        return true;
    }

    protected function parse(string $html): ?array
    {
        if (strlen($html) > self::MAX_HTML_BYTES || preg_match('//u', $html) !== 1) return null;

        $markerCount = preg_match_all(self::MARKER_PATTERN, $html, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        $prefixCount = preg_match_all(self::MARKER_PREFIX_PATTERN, $html);

        if ($markerCount === false || $prefixCount === false || $markerCount !== $prefixCount) {
            return null;
        }

        $nodes = [];
        $order = [];
        $rootChildren = [];
        $stack = [];

        foreach ($matches as $match) {
            $marker = $match[0][0];
            $offset = $match[0][1];
            $kind = $match[1][0];
            $encodedMetadata = $match[2][0];
            $metadata = $this->parseMetadata($encodedMetadata);

            if ($metadata === null) return null;

            if ($kind === 'FRAGMENT') {
                $token = null;

                if (($metadata['type'] ?? null) === 'transport') {
                    $token = $this->transportToken($metadata);

                    if ($token === null) return null;

                    if (isset($nodes[$token]) || count($nodes) >= self::MAX_NODES) return null;

                    $parent = $this->closestTransportToken($stack);

                    $nodes[$token] = [
                        'token' => $token,
                        'parent' => $parent,
                        'children' => [],
                        'start' => $offset,
                        'contentStart' => $offset + strlen($marker),
                    ];

                    if ($parent === null) {
                        $rootChildren[] = $token;
                    } else {
                        $nodes[$parent]['children'][] = $token;
                    }

                    $order[] = $token;
                }

                $stack[] = [
                    'encoded' => $encodedMetadata,
                    'token' => $token,
                ];

                continue;
            }

            if ($stack === []) return null;

            $open = array_pop($stack);

            if ($open['encoded'] !== $encodedMetadata) return null;

            if ($open['token'] === null) continue;

            $token = $this->transportToken($metadata);

            if ($token !== $open['token']) return null;

            $nodes[$token]['contentEnd'] = $offset;
            $nodes[$token]['end'] = $offset + strlen($marker);
        }

        if ($stack !== []) return null;

        foreach ($order as $token) {
            if (! isset($nodes[$token]['contentEnd'], $nodes[$token]['end'])) return null;

            $nodes[$token]['content'] = $this->digest(substr(
                $html,
                $nodes[$token]['contentStart'],
                $nodes[$token]['contentEnd'] - $nodes[$token]['contentStart'],
            ));
            $nodes[$token]['skeleton'] = $this->digest($this->skeleton(
                $html,
                $nodes[$token]['contentStart'],
                $nodes[$token]['contentEnd'],
                $nodes[$token]['children'],
                $nodes,
            ));
        }

        return [
            'root' => [
                'children' => $rootChildren,
                'skeleton' => $this->digest($this->skeleton(
                    $html,
                    0,
                    strlen($html),
                    $rootChildren,
                    $nodes,
                )),
            ],
            'nodes' => $nodes,
            'order' => $order,
        ];
    }

    protected function skeleton(
        string $html,
        int $start,
        int $end,
        array $children,
        array $nodes,
    ): string {
        $skeleton = '';
        $cursor = $start;

        foreach ($children as $token) {
            $child = $nodes[$token];

            $skeleton .= substr($html, $cursor, $child['contentStart'] - $cursor);
            $cursor = $child['contentEnd'];
        }

        return $skeleton.substr($html, $cursor, $end - $cursor);
    }

    protected function parseMetadata(string $encoded): ?array
    {
        if ($encoded === '' || strlen($encoded) > self::MAX_METADATA_BYTES) return null;

        $metadata = [];

        foreach (explode('|', $encoded) as $pair) {
            $parts = explode('=', $pair, 2);

            if (
                count($parts) !== 2
                || $parts[0] === ''
                || $parts[1] === ''
                || preg_match('/^[A-Za-z][A-Za-z0-9_-]*$/D', $parts[0]) !== 1
                || array_key_exists($parts[0], $metadata)
            ) {
                return null;
            }

            $metadata[$parts[0]] = $parts[1];
        }

        ksort($metadata);

        return $metadata;
    }

    protected function transportToken(array $metadata): ?string
    {
        foreach (['token', 'id', 'name', 'key'] as $key) {
            $token = $metadata[$key] ?? null;

            if (
                is_string($token)
                && $token !== ''
                && strlen($token) <= self::MAX_TOKEN_BYTES
            ) {
                return $token;
            }
        }

        return null;
    }

    protected function closestTransportToken(array $stack): ?string
    {
        for ($index = count($stack) - 1; $index >= 0; $index--) {
            if ($stack[$index]['token'] !== null) return $stack[$index]['token'];
        }

        return null;
    }

    protected function decodeManifest(array $manifest): array
    {
        if (
            count($manifest) !== 2
            || ! array_key_exists('root', $manifest)
            || ! array_key_exists('nodes', $manifest)
            || ! is_string($manifest['root'])
            || ! $this->isDigest($manifest['root'])
            || ! is_array($manifest['nodes'])
            || ! array_is_list($manifest['nodes'])
            || count($manifest['nodes']) > self::MAX_NODES
        ) {
            throw new \InvalidArgumentException('Invalid render fragment manifest.');
        }

        $nodes = [];

        foreach ($manifest['nodes'] as $entry) {
            if (
                ! is_array($entry)
                || ! array_is_list($entry)
                || count($entry) !== 3
                || ! is_string($entry[0])
                || $entry[0] === ''
                || strlen($entry[0]) > self::MAX_TOKEN_BYTES
                || ! is_string($entry[1])
                || ! is_string($entry[2])
                || ! $this->isDigest($entry[1])
                || ! $this->isDigest($entry[2])
                || isset($nodes[$entry[0]])
            ) {
                throw new \InvalidArgumentException('Invalid render fragment manifest node.');
            }

            $nodes[$entry[0]] = [
                'content' => $entry[1],
                'skeleton' => $entry[2],
            ];
        }

        return [
            'root' => $manifest['root'],
            'nodes' => $nodes,
        ];
    }

    protected function digest(string $value): string
    {
        return hash('crc32b', $value).hash('adler32', $value);
    }

    protected function isDigest(string $value): bool
    {
        return preg_match('/^[0-9a-f]{16}$/D', $value) === 1;
    }
}
