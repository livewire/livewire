<?php

namespace Livewire\Compiler\Parser;

class Parser
{
    public static function extractPlaceholderPortion(string &$contents): ?string
    {
        $pattern = '/@'.'placeholder(.*?)@endplaceholder/s';

        if (preg_match($pattern, $contents, $matches)) {
            $fullMatch = $matches[0];
            $match = $matches[1];

            $contents = str_replace($fullMatch, '', $contents);

            return $match;
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
        if (preg_match('/\bnew\b/', $contents) && !preg_match('/\breturn\s+new\b/', $contents)) {
            return preg_replace('/\bnew\b/', 'return new', $contents, 1);
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

    protected function view()
    {
        return app('view')->file('{$viewFileName}');
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
