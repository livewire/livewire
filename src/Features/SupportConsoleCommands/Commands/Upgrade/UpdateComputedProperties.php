<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Features\SupportConsoleCommands\Commands\UpgradeCommand;

class UpdateComputedProperties extends UpgradeStep
{
    public function handle(UpgradeCommand $console, \Closure $next)
    {
        $console->line("<fg=#FB70A9;bg=black;options=bold,reverse> Computed properties now have a new syntax </>");
        $console->newLine();

        $console->line("This means all component methods like <options=underscore>getPostProperty</> must be changed to <options=underscore>getPosts</> with an attribute called <options=underscore>computed</> above it.");

        $confirm = $console->confirm("Would you like to change all occurrences of methods like getPostProperty to methods like posts with the special attribute above it?", true);

        if ($confirm) {
            $console->line("Changing all occurrences of methods like <options=underscore>getPostsProperty</> to <options=underscore>posts</>.");
            $console->newLine();

            $replacements = collect(Arr::wrap(['app']))->map(function ($directory) {
                return collect($this->filesystem()->allFiles($directory))->map(function ($path) {
                    return [
                        'path' => $path,
                        'content' => $this->filesystem()->get($path),
                    ];
                });
            })
            ->flatten(1)
            ->map(function (array $file) {
                $upgrade = $this->upgradeComputedProperties($file['content']);

                if($upgrade['occurrences'] === 0) {
                    return null;
                }

                $contentOrFalse = $this->addComputedPropertyImport($upgrade['content']);
                if($contentOrFalse === false) {
                    return null;
                }

                $this->filesystem()->put($file['path'], $contentOrFalse);


                return [
                    $file['path'], $upgrade['occurrences'],
                ];
            })->filter();

            if ($replacements->isEmpty()) {
                $console->line("No occurrences of <options=underscore>\$this->forgetComputed()</> were found.");
            }

            if ($replacements->isNotEmpty()) {
                $console->table(['File', 'Occurrences'], $replacements);
            }
        }


        $console->newLine(4);

        return $next($console);
    }

    /**
     * Upgrades methods whom names start with 'get' and end with 'Property' to methods which names are
     * just the part that was between 'get' and 'property'
     *
     * @param string $content
     * @return array{content: string, occurrences: int}
     */
    private function upgradeComputedProperties(string $content): array
    {
        //Get all the computed properties that need to be upgraded.
        $matches = [];
        $pattern = '/(.+) public function get(.+)Property\(\)/';
        preg_match_all($pattern, $content, $matches);

        //Destructure the matches to make it easier to understand them
        [$lines, $whiteSpacesBefore, $propertyNames] = $matches;

        //Upgrade the content
        foreach ($lines as $index => $line) {
            //Get the whitespace in front of $this->forgetComputed
            $whiteSpaceChars = '';
            if(preg_match('/^\s+/', $line, $matches) !== false) {
                $whiteSpaceChars = $matches[0];
            }

            //Assemble search and replace values
            $searchFor = Str::of('get')->append($propertyNames[$index])->append('Property()');
            $replaceWith = Str::of($propertyNames[$index])->lcfirst()->append('()');

            //Upgrade the line
            $upgradedLine = Str::of($whiteSpaceChars)
                ->append("#[Computed]\n")
                ->append($line)
                ->replace($searchFor, $replaceWith);

            $content = Str::of($content)->replace($line, $upgradedLine);
        }

        return [
            'content' => $content,
            'occurrences' => count($lines)
        ];
    }

    /**
     * Returns updated content if it was added or didn't need to be added. false otherwise.
     *
     * @param string $content
     * @return bool|string
     */
    private function addComputedPropertyImport(string $content): false|string {
        if(preg_match('/^(?:.+)?namespace +[^;]+;\n+/m', $content, $matches) === false) {
            return false;
        }

        if(str_contains($content, 'use Livewire\Attributes\Computed;')) {
            return $content;
        }

        [$namespaceDeclarationWithNewLines] = $matches;

        $searchFor = Str::of($matches[0]);
        $replaceWith = Str::of($namespaceDeclarationWithNewLines)
            ->replace("\n", "")
            ->append("\n\n")
            ->append("use Livewire\Attributes\Computed;\n");


        return Str::of($content)->replace($searchFor, $replaceWith)->toString();
    }
}
