<?php

namespace Livewire\Compiler\Parser;

class Parser
{
    public static function extractPlaceholderPortion(string &$contents): ?string
    {
        $islandsPattern = '/(@'.'island.*?@endisland)/s';

        $replacements = [];

        $contents = preg_replace_callback($islandsPattern, function($matches) use (&$replacements) {
            // Use brackets around the number to prevent substring matching issues
            // e.g. "ISLANDREPLACEMENT:[1]" won't match as part of "ISLANDREPLACEMENT:[10]"
            $key = 'ISLANDREPLACEMENT:[' . count($replacements) . ']';

            $replacements[$key] = $matches[0];

            return $key;
        }, $contents);

        $placeholderPattern = '/@'.'placeholder(.*?)@endplaceholder/s';

        $placeholderPortion = null;

        if (preg_match($placeholderPattern, $contents, $matches)) {
            $fullMatch = $matches[0];

            $placeholderPortion = $matches[1];

            $contents = str_replace($fullMatch, '', $contents);
        }

        foreach ($replacements as $key => $replacement) {
            $contents = str_replace($key, $replacement, $contents);
        }

        return $placeholderPortion ?? null;
    }

    public function generatePlaceholderContents(): ?string
    {
        return $this->placeholderPortion ?? null;
    }

    protected function stripTrailingPhpTag(string $contents): string
    {
        if (str_ends_with($contents, '?>')) {
            return substr($contents, 0, -2);
        }
        return $contents;
    }

    protected function ensureAnonymousClassHasReturn(string $contents): string
    {
        // Find the position of the first "new"...
        if (preg_match('/\bnew\b/', $contents, $newMatch, PREG_OFFSET_CAPTURE)) {
            $newPosition = $newMatch[0][1];

            // Check if "return new" exists and find where "new" starts in that match...
            $hasReturnNew = preg_match('/\breturn\s+(new\b)/', $contents, $returnNewMatch, PREG_OFFSET_CAPTURE);

            // If "return new" does not exist or "new" is not at the same position as "return new", add "return"...
            if (!$hasReturnNew || $returnNewMatch[1][1] !== $newPosition) {
                $contents = substr_replace($contents, 'return ', $newPosition, 0);
            }
        }

        return $contents;
    }

    protected function ensureAnonymousClassHasTrailingSemicolon(string $contents): string
    {
        if (!preg_match('/\bnew\b/', $contents)) {
            return $contents;
        }

        // Find last closing brace and ensure it has a trailing semicolon
        if (preg_match('/}(?:\s*)$/', $contents)) {
            return preg_replace('/}(\s*)$/', '};$1', $contents);
        }

        if (preg_match('/}(?:\s*);/', $contents)) {
            return $contents;
        }

        // If we get here, we have a closing brace followed by other content
        return $contents;
    }

    protected function injectViewMethod(string $contents, string $viewFileName): string
    {
        $pattern = '/}(\s*);/';
        preg_match_all($pattern, $contents, $matches, PREG_OFFSET_CAPTURE);
        $lastMatch = end($matches[0]);

        if ($lastMatch) {
            $position = $lastMatch[1];
            return substr_replace($contents, <<<PHP

    protected function view(\$data = [])
    {
        return app('view')->file('{$viewFileName}', \$data);
    }
}
PHP
            , $position, 1);
        }

        return $contents;
    }

    protected function injectPlaceholderMethod(string $contents, string $placeholderFileName): string
    {
        $pattern = '/}(\s*);/';
        preg_match_all($pattern, $contents, $matches, PREG_OFFSET_CAPTURE);
        $lastMatch = end($matches[0]);

        if ($lastMatch) {
            $position = $lastMatch[1];
            return substr_replace($contents, <<<PHP

    public function placeholder()
    {
        return app('view')->file('{$placeholderFileName}');
    }
}
PHP
            , $position, 1);
        }

        return $contents;
    }

    protected function injectScriptMethod(string $contents, string $scriptFileName): string
    {
        $pattern = '/}(\s*);/';
        preg_match_all($pattern, $contents, $matches, PREG_OFFSET_CAPTURE);
        $lastMatch = end($matches[0]);

        if ($lastMatch) {
            $position = $lastMatch[1];
            return substr_replace($contents, <<<PHP

    public function scriptModuleSrc()
    {
        return '{$scriptFileName}';
    }
}
PHP
            , $position, 1);
        }

        return $contents;
    }

    protected function injectStyleMethod(string $contents, string $styleFileName): string
    {
        $pattern = '/}(\s*);/';
        preg_match_all($pattern, $contents, $matches, PREG_OFFSET_CAPTURE);
        $lastMatch = end($matches[0]);

        if ($lastMatch) {
            $position = $lastMatch[1];
            return substr_replace($contents, <<<PHP

    public function styleModuleSrc()
    {
        return '{$styleFileName}';
    }
}
PHP
            , $position, 1);
        }

        return $contents;
    }

    protected function injectGlobalStyleMethod(string $contents, string $globalStyleFileName): string
    {
        $pattern = '/}(\s*);/';
        preg_match_all($pattern, $contents, $matches, PREG_OFFSET_CAPTURE);
        $lastMatch = end($matches[0]);

        if ($lastMatch) {
            $position = $lastMatch[1];
            return substr_replace($contents, <<<PHP

    public function globalStyleModuleSrc()
    {
        return '{$globalStyleFileName}';
    }
}
PHP
            , $position, 1);
        }

        return $contents;
    }

    protected function injectUseStatementsFromClassPortion(string $contents, string $classPortion): string
    {
        // Extract everything between <?php and "new"
        if (preg_match('/\<\?php(.*?)new/s', $classPortion, $matches)) {
            $preamble = $matches[1];

            // Extract all use statements
            if (preg_match_all('/use\s+[^;]+;/s', $preamble, $useMatches)) {
                $useStatements = implode("\n", $useMatches[0]);
            }
        }

        // Only add PHP tags and use statements if we found any
        if (isset($useStatements) && $useStatements) {
            $contents = "<?php\n" . $useStatements . "\n?>\n\n" . $contents;
        }

        return $contents;
    }
}
