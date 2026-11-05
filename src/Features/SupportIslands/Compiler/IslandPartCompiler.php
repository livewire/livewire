<?php

namespace Livewire\Features\SupportIslands\Compiler;

use InvalidArgumentException;
use PhpParser\Node\Scalar\String_;
use PhpParser\ParserFactory;

class IslandPartCompiler
{
    public array $names = [];

    protected string $marker;

    public function prepare(string $contents): string
    {
        // Only the compiler sees these placeholders. PHP and comments must not
        // contribute directives (or loops) to the surrounding Blade scope.
        $hidden = [];
        $prefix = '__LIVEWIRE_PART_' . hash('xxh128', $contents) . '_';
        $this->marker = $prefix;
        $hide = function ($value) use (&$hidden, $prefix) {
            $key = $prefix . count($hidden) . '__';
            $hidden[$key] = $value;
            return $key;
        };
        $contents = preg_replace_callback('/\{\{--.*?--\}\}|@verbatim\b.*?@endverbatim\b|@php(?!\s*\()(.*?)@endphp\b/s', fn ($m) => $hide($m[0]), $contents);
        $masked = '';
        $php = '';
        foreach (token_get_all($contents) as $token) {
            $text = is_array($token) ? $token[1] : $token;
            if ($php !== '' || (is_array($token) && in_array($token[0], [T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO]))) {
                $php .= $text;
                if (is_array($token) && $token[0] === T_CLOSE_TAG) {
                    $masked .= $hide($php);
                    $php = '';
                }
            } else {
                $masked .= $text;
            }
        }
        $contents = $masked . ($php === '' ? '' : $hide($php));

        $islands = [];
        $loops = [];
        $part = null;
        $edits = [];
        $offset = 0;

        while (preg_match('/(?<!@)@(\w+)\b/', $contents, $match, PREG_OFFSET_CAPTURE, $offset)) {
            $directive = $match[1][0];
            $start = $match[0][1];
            $end = $start + strlen($match[0][0]);
            $expression = null;
            $next = $end + strspn($contents, " \t", $end);
            if (($contents[$next] ?? '') === '(') {
                $end = $this->expressionEnd($contents, $next);
                $expression = substr($contents, $next + 1, $end - $next - 2);
            }
            $offset = $end;

            if ($directive === 'island') $islands[] = $start;
            if ($directive === 'endisland') {
                if ($part && $part['island'] === end($islands)) $this->fail('Missing @endislandPart before @endisland.');
                array_pop($islands);
            }
            if (in_array($directive, ['foreach', 'forelse', 'for', 'while'])) $loops[] = ['type' => $directive, 'active' => true];
            if ($directive === 'empty' && $expression === null && $loops && end($loops)['type'] === 'forelse') $loops[array_key_last($loops)]['active'] = false;
            if (in_array($directive, ['endforeach', 'endforelse', 'endfor', 'endwhile'])) array_pop($loops);

            if ($directive === 'islandPart') {
                if (! $islands) $this->fail('@islandPart must be declared inside an @island.');
                if ($part) $this->fail('Nested @islandPart declarations are not supported.');
                $name = $this->parseName($expression ?? '');
                $id = count($this->names);
                $this->names[$id] = $name;
                $part = ['start' => $start, 'body' => $end, 'id' => $id, 'name' => $name, 'island' => end($islands), 'repeated' => in_array(true, array_column($loops, 'active'), true)];
            } elseif ($directive === 'endislandPart') {
                if (! $part) $this->fail('@endislandPart has no matching @islandPart.');
                $body = strtr(substr($contents, $part['body'], $start - $part['body']), $hidden);
                $body = $this->addAttribute($body, $part['name'], $part['repeated']);
                $edits[] = [$part['start'], $end - $part['start'], $this->marker . 'START:' . $part['id'] . ']' . $body . $this->marker . 'END:' . $part['id'] . ']'];
                $part = null;
            }
        }

        if ($part) $this->fail('Missing @endislandPart.');

        foreach (array_reverse($edits) as [$start, $length, $replacement]) {
            $contents = substr_replace($contents, $replacement, $start, $length);
        }

        return strtr($contents, $hidden);
    }

    public function extract(string $contents, callable $callback): string
    {
        $marker = preg_quote($this->marker, '/');

        return preg_replace_callback('/' . $marker . 'START:(\d+)\](.*?)' . $marker . 'END:\1\]/s', fn ($match) => $callback($match[1], $match[2]), $contents);
    }

    protected function parseName(string $expression): string
    {
        try {
            $nodes = (new ParserFactory)->createForNewestSupportedVersion()->parse('<?php part(' . $expression . ');');
            $args = $nodes[0]->expr->args;
            if (count($nodes) === 1
                && count($args) === 1
                && ! $args[0]->unpack
                && ($args[0]->name === null || $args[0]->name->toString() === 'name')
                && $args[0]->value instanceof String_
                && trim($args[0]->value->value) !== ''
            ) {
                return $args[0]->value->value;
            }
        } catch (\Throwable $e) {
            // Present the declaration contract rather than a generated PHP error.
        }

        $this->fail('@islandPart accepts only a non-empty literal template name.');
    }

    protected function addAttribute(string $body, string $name, bool $repeated): string
    {
        $offset = 0;
        // Leading setup is ordinary inline Blade, and must also run when the
        // extracted template is rendered independently.
        while ($offset < strlen($body)) {
            if (preg_match('/\G(?:\s+|<!--.*?-->|\{\{--.*?--\}\}|@php\b(?!\s*\().*?@endphp)/s', $body, $match, 0, $offset)) {
                $offset += strlen($match[0]);
                continue;
            }
            if (substr($body, $offset, 5) === '<?php') {
                $offset = $this->phpEnd($body, $offset);
                continue;
            }
            if (preg_match('/\G@(?:php|use)\s*\(/', $body, $match, 0, $offset)) {
                $offset = $this->expressionEnd($body, $offset + strlen($match[0]) - 1);
                continue;
            }
            break;
        }

        if (! preg_match('/\G<([a-zA-Z][\w:.-]*)\b/', $body, $match, 0, $offset)) {
            if (preg_match('/\sdata-island-part\s*=/', $body)) return $body;
            $this->fail('An @islandPart needs a literal root tag or explicit data-island-part targets on its possible roots.');
        }

        $insertion = $offset + strlen($match[0]);
        $cursor = $insertion;
        $key = null;

        while ($cursor < strlen($body)) {
            $cursor += strspn($body, " \t\r\n", $cursor);
            if (in_array($body[$cursor] ?? '', ['>', '/'], true)) break;
            if (substr($body, $cursor, 5) === '<?php') {
                $cursor = $this->phpEnd($body, $cursor);
                continue;
            }
            if (preg_match('/\G@\w+\s*/', $body, $directive, 0, $cursor)) {
                $cursor += strlen($directive[0]);
                if (($body[$cursor] ?? '') === '(') $cursor = $this->expressionEnd($body, $cursor);
                continue;
            }
            if (! preg_match('/\G([^\s=<>\/]+)(\s*=\s*)?/', $body, $attribute, 0, $cursor)) $this->fail('Unable to read the @islandPart root attributes.');
            $attributeName = $attribute[1];
            $cursor += strlen($attribute[0]);
            if (! isset($attribute[2])) continue;
            $start = $cursor;
            $quote = in_array($body[$cursor] ?? '', ['"', "'"], true) ? $body[$cursor++] : null;
            while ($cursor < strlen($body)) {
                if (substr($body, $cursor, 2) === '{{' || substr($body, $cursor, 3) === '{!!') {
                    $close = substr($body, $cursor, 2) === '{{' ? '}}' : '!!}';
                    $cursor = $this->echoEnd($body, $cursor + strlen($close), $close);
                    continue;
                }
                if ($quote ? $body[$cursor] === $quote : preg_match('/[\s>]/', $body[$cursor])) break;
                $cursor++;
            }
            if ($quote) $cursor++;
            $value = substr($body, $start, $cursor - $start);
            if ($attributeName === 'data-island-part' || $attributeName === ':data-island-part') return $body;
            if ($attributeName === 'wire:key') $key = 'data-island-part=' . $value;
            if ($attributeName === ':wire:key') $key = ':data-island-part=' . $value;
        }

        if ($key === null && $repeated) $this->fail('A repeated @islandPart requires a stable wire:key or explicit data-island-part target.');
        $key ??= 'data-island-part="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '"';

        return substr_replace($body, ' ' . $key, $insertion, 0);
    }

    protected function expressionEnd(string $source, int $start): int
    {
        $depth = 0;
        $length = 0;
        foreach (token_get_all('<?php ' . substr($source, $start)) as $token) {
            if (is_array($token) && $token[0] === T_OPEN_TAG) continue;
            $text = is_array($token) ? $token[1] : $token;
            $length += strlen($text);
            if ($token === '(') $depth++;
            if ($token === ')' && --$depth === 0) return $start + $length;
        }
        $this->fail('Unclosed expression in an island part declaration.');
    }

    protected function echoEnd(string $source, int $start, string $close): int
    {
        $depth = 0;
        $offset = $start;
        foreach (token_get_all('<?php ' . substr($source, $start)) as $token) {
            if (is_array($token) && $token[0] === T_OPEN_TAG) continue;
            $text = is_array($token) ? $token[1] : $token;
            if (! is_array($token)) {
                if ($depth === 0 && substr($source, $offset, strlen($close)) === $close) return $offset + strlen($close);
                if (in_array($token, ['(', '[', '{'], true)) $depth++;
                if (in_array($token, [')', ']', '}'], true)) $depth--;
            }
            $offset += strlen($text);
        }
        $this->fail('Unclosed Blade expression in @islandPart root.');
    }

    protected function phpEnd(string $source, int $start): int
    {
        $offset = $start;
        foreach (token_get_all(substr($source, $start)) as $token) {
            $offset += strlen(is_array($token) ? $token[1] : $token);
            if (is_array($token) && $token[0] === T_CLOSE_TAG) return $offset;
        }
        $this->fail('Unclosed PHP setup in @islandPart.');
    }

    protected function fail(string $message): never
    {
        throw new InvalidArgumentException($message);
    }
}
