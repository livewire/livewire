<?php

namespace Livewire\Features\SupportSubtreeHashes;

class SubtreeHasher
{
    protected static $voidTags = [
        'area' => 1, 'base' => 1, 'br' => 1, 'col' => 1, 'embed' => 1, 'hr' => 1, 'img' => 1,
        'input' => 1, 'link' => 1, 'meta' => 1, 'source' => 1, 'track' => 1, 'wbr' => 1,
    ];

    protected static $rawTextTags = [
        'script' => 1, 'style' => 1, 'textarea' => 1, 'title' => 1,
    ];

    public const HASH_LENGTH = 11;

    // Below this size a placeholder saves too little to be worth a hash...
    public function __construct(protected int $minimumBytes = 1024) {}

    // Annotate every keyed element with a hash of its server-rendered markup, and
    // replace the ones the browser already holds with an empty placeholder. A
    // component's HTML has a root element to leave alone, a fragment doesn't...
    public function process(string $html, array $known = [], bool $hasRoot = true): string
    {
        $elements = $this->findKeyedElements($html, $hasRoot);

        if (empty($elements)) return $html;

        $output = '';
        $cursor = 0;

        $this->emit($html, $elements, 0, count($elements), $known, $output, $cursor);

        return $output.substr($html, $cursor);
    }

    // 64 bits, base64url encoded into 11 characters...
    public static function hash(string $markup): string
    {
        return rtrim(strtr(base64_encode(hash('xxh64', $markup, true)), '+/', '-_'), '=');
    }

    public static function parse(mixed $hashes): array
    {
        if (is_string($hashes)) $hashes = str_split($hashes, static::HASH_LENGTH);

        if (! is_array($hashes)) return [];

        return array_flip(array_filter($hashes, fn ($hash) => is_string($hash) && strlen($hash) === static::HASH_LENGTH));
    }

    protected function emit($html, $elements, $from, $to, $known, &$output, &$cursor)
    {
        $i = $from;

        while ($i < $to) {
            $element = $elements[$i];

            // Find the slice of elements nested inside this one...
            $j = $i + 1;
            while ($j < $to && $elements[$j]['start'] < $element['end']) $j++;

            $markup = substr($html, $element['start'], $element['end'] - $element['start']);
            $hash = static::hash($markup);
            $nameEnd = $element['start'] + 1 + strlen($element['name']);

            $output .= substr($html, $cursor, $element['start'] - $cursor);

            if (isset($known[$hash])) {
                $output .= '<'.$element['name'].' wire:key="'.$element['key'].'" wire:hash="'.$hash.'" wire:same>';

                if (! isset(static::$voidTags[$element['name']])) $output .= '</'.$element['name'].'>';

                $cursor = $element['end'];
            } else {
                $output .= substr($html, $element['start'], $nameEnd - $element['start']).' wire:hash="'.$hash.'"';
                $cursor = $nameEnd;

                $this->emit($html, $elements, $i + 1, $j, $known, $output, $cursor);
            }

            $i = $j;
        }
    }

    protected function findKeyedElements(string $html, bool $hasRoot = true): array
    {
        $length = strlen($html);
        $stack = [];
        $elements = [];
        $position = 0;
        $isFirstElement = $hasRoot;

        while (($position = strpos($html, '<', $position)) !== false) {
            if (substr_compare($html, '<!--', $position, 4) === 0) {
                $end = strpos($html, '-->', $position + 4);
                if ($end === false) break;
                $position = $end + 3;
                continue;
            }

            if (($html[$position + 1] ?? '') === '/') {
                $end = strpos($html, '>', $position);
                if ($end === false) break;

                $name = strtolower(rtrim(substr($html, $position + 2, strcspn($html, " \t\n\r>", $position + 2))));

                // Pop until the matching open tag, treating anything in between as implicitly closed...
                for ($k = count($stack) - 1; $k >= 0; $k--) {
                    if ($stack[$k]['name'] === $name) break;
                }

                if ($k >= 0) {
                    while (count($stack) > $k) {
                        $open = array_pop($stack);
                        $closesHere = count($stack) === $k;

                        if ($open['index'] !== null) {
                            $elements[$open['index']]['end'] = $closesHere ? $end + 1 : $position;
                        }
                    }
                }

                $position = $end + 1;
                continue;
            }

            $nameLength = strspn($html, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-:.', $position + 1);

            if ($nameLength === 0) { $position++; continue; }

            $name = strtolower(substr($html, $position + 1, $nameLength));

            // Walk the attributes, honoring quotes, so a ">" inside an Alpine expression doesn't end the tag...
            $cursor = $position + 1 + $nameLength;
            $quote = null;
            while ($cursor < $length) {
                $cursor += strcspn($html, $quote === null ? '>"\'' : $quote, $cursor);
                if ($cursor >= $length) break;
                $char = $html[$cursor];
                if ($quote !== null) { $quote = null; $cursor++; continue; }
                if ($char === '>') break;
                $quote = $char;
                $cursor++;
            }

            if ($cursor >= $length) break;

            $tag = substr($html, $position, $cursor - $position + 1);
            $selfClosing = $html[$cursor - 1] === '/' || isset(static::$voidTags[$name]);
            $parent = end($stack) ?: null;
            $opaque = $parent['opaque'] ?? false;
            $index = null;

            $isComponent = preg_match('/\swire:id\s*=/', $tag);

            // A nested component's root is the child's own business, so only plain keyed elements count...
            if (! $isFirstElement && ! $opaque && ! $isComponent
                && preg_match('/\swire:key\s*=\s*(?:"([^"]*)"|\'([^\']*)\')/', $tag, $match)
                && ($key = ($match[1] ?? '') !== '' ? $match[1] : ($match[2] ?? '')) !== ''
            ) {
                $index = count($elements);
                $elements[] = ['name' => $name, 'key' => $key, 'start' => $position, 'end' => null];
            }

            // Nested components and templates own their content, so leave them alone...
            $opensOpaque = $opaque
                || $name === 'template'
                || (! $isFirstElement && $isComponent);

            $isFirstElement = false;

            if ($selfClosing) {
                if ($index !== null) $elements[$index]['end'] = $cursor + 1;
                $position = $cursor + 1;
                continue;
            }

            if (isset(static::$rawTextTags[$name])) {
                $end = stripos($html, '</'.$name, $cursor);
                if ($end === false) break;
                $close = strpos($html, '>', $end);
                if ($close === false) break;
                if ($index !== null) $elements[$index]['end'] = $close + 1;
                $position = $close + 1;
                continue;
            }

            $stack[] = ['name' => $name, 'index' => $index, 'opaque' => $opensOpaque];
            $position = $cursor + 1;
        }

        // Elements that never closed can't be safely replaced, and small ones aren't worth it...
        return array_values(array_filter($elements, fn ($element) => $element['end'] !== null
            && $element['end'] - $element['start'] >= $this->minimumBytes
        ));
    }
}
