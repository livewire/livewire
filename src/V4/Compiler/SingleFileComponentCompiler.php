<?php

namespace Livewire\V4\Compiler;

use Livewire\Mechanisms\Mechanism;
use Livewire\V4\Compiler\Exceptions\CompilationException;
use Livewire\V4\Compiler\Exceptions\ParseException;
use Livewire\V4\Compiler\Exceptions\InvalidComponentException;
use Illuminate\Support\Facades\File;

class SingleFileComponentCompiler extends Mechanism
{
    protected string $cacheDirectory;
    protected string $classesDirectory;
    protected string $viewsDirectory;
    protected array $supportedExtensions;

    public function __construct(?string $cacheDirectory = null, ?array $supportedExtensions = null)
    {
        $this->cacheDirectory = $cacheDirectory ?: storage_path('framework/livewire');
        $this->classesDirectory = $this->cacheDirectory . '/classes';
        $this->viewsDirectory = $this->cacheDirectory . '/views';
        $this->supportedExtensions = $supportedExtensions ?: ['.blade.php', '.wire.php'];

        $this->ensureDirectoriesExist();
    }

    public function setSupportedExtensions(array $extensions): void
    {
        $this->supportedExtensions = $extensions;
    }

    public function compile(string $viewPath): CompilationResult
    {
        if (! file_exists($viewPath)) {
            throw new CompilationException("View file not found: [{$viewPath}]");
        }

        $content = File::get($viewPath);
        $hash = $this->generateHash($viewPath, $content);

        // Check if already compiled and up to date...
        if ($this->isCompiled($viewPath, $hash)) {
            return $this->getExistingCompilationResult($viewPath, $hash);
        }

        // Parse the component...
        $parsed = $this->parseComponent($content);

        // Generate compilation result...
        $result = $this->generateCompilationResult($viewPath, $parsed, $hash);

        // Generate files...
        $this->generateFiles($result, $parsed);

        return $result;
    }

    public function isCompiled(string $viewPath, ?string $hash = null): bool
    {
        if ($hash === null) {
            $content = File::get($viewPath);
            $hash = $this->generateHash($viewPath, $content);
        }

        $className = $this->generateClassName($viewPath, $hash);
        $classPath = $this->getClassPath($className);
        $viewName = $this->generateViewName($viewPath, $hash);
        $viewPath = $this->getViewPath($viewName);

        return file_exists($classPath) && file_exists($viewPath);
    }

    public function getCompiledPath(string $viewPath): string
    {
        $content = File::get($viewPath);
        $hash = $this->generateHash($viewPath, $content);
        $className = $this->generateClassName($viewPath, $hash);
        return $this->getClassPath($className);
    }

    protected function parseComponent(string $content): ParsedComponent
    {
        // Extract layout directive first if present
        $layoutTemplate = null;
        $layoutData = null;

        if (preg_match('/@layout\s*\(\s*[\'"]([^\'"]+)[\'"]\s*(?:,\s*(\[.*?\]))?\s*\)/s', $content, $layoutMatches)) {
            $layoutTemplate = $layoutMatches[1];
            if (isset($layoutMatches[2])) {
                // Parse the array string - this is a simple implementation
                $layoutData = $this->parseLayoutData($layoutMatches[2]);
            }
            // Remove the layout directive from content for further processing
            $content = preg_replace('/@layout\s*\([^)]+\)\s*/', '', $content);
        }

        // Extract inline partials before processing component
        $inlinePartials = [];
        $content = $this->extractInlinePartials($content, $inlinePartials);

        // Handle external class reference: @php(new App\Livewire\SomeClass)
        if (preg_match('/@php\s*\(\s*new\s+([A-Za-z0-9\\\\]+)(?:::class)?\s*\)/s', $content, $matches)) {
            $externalClass = $matches[1];
            $viewContent = preg_replace('/@php\s*\([^)]+\)/s', '', $content);

            return new ParsedComponent(
                '',
                trim($viewContent),
                true,
                $externalClass,
                $layoutTemplate,
                $layoutData,
                $inlinePartials
            );
        }

        // Handle inline class: @php ... @endphp
        if (preg_match('/@php\s*(.*?)\s*@endphp/s', $content, $matches)) {
            $frontmatter = trim($matches[1]);
            // Use the modified $content (after layout removal) for the view content
            $viewContent = preg_replace('/@php\s*.*?\s*@endphp/s', '', $content);

            // Validate that frontmatter contains a class definition...
            if (!str_contains($frontmatter, 'new class') && !str_contains($frontmatter, 'class ')) {
                throw new ParseException("Invalid component: @php block must contain a class definition");
            }

            return new ParsedComponent(
                $frontmatter,
                trim($viewContent),
                false,
                null,
                $layoutTemplate,
                $layoutData,
                $inlinePartials
            );
        }

        throw new InvalidComponentException("Component must contain either @php(new ClassName) or @php...@endphp block");
    }

    protected function extractInlinePartials(string $content, array &$inlinePartials): string
    {
        // Pattern to match @partial('name', [...])...@endpartial blocks
        $pattern = '/@partial\s*\(\s*[\'"]([^\'"]+)[\'"]\s*(?:,\s*(\[.*?\]))?\s*\)(.*?)@endpartial/s';

        return preg_replace_callback($pattern, function ($matches) use (&$inlinePartials) {
            $partialName = $matches[1];
            $partialData = isset($matches[2]) && !empty(trim($matches[2])) ? $matches[2] : '[]';
            $partialContent = trim($matches[3]);

            // Generate a unique view name for this partial using content hash instead of time
            $partialHash = substr(md5($partialContent . $partialName), 0, 8);
            // Keep dashes in view name for consistency with tests
            $partialViewName = 'livewire-compiled::partial_' . $partialName . '_' . $partialHash;
            // Keep dashes in file name too (tests expect this)
            $partialFileName = 'partial_' . $partialName . '_' . $partialHash . '.blade.php';

            // Store the partial information
            $inlinePartials[] = [
                'name' => $partialName,
                'data' => $partialData,
                'content' => $partialContent,
                'viewName' => $partialViewName,
                'fileName' => $partialFileName
            ];

            // Replace with a reference to the compiled partial view
            $dataParam = $partialData !== '[]' ? ", {$partialData}" : '';
            return "@partial('{$partialName}', '{$partialViewName}'{$dataParam})";
        }, $content);
    }

    protected function parseLayoutData(string $arrayString): ?array
    {
        // Simple array parsing - handles basic key-value pairs
        // This could be enhanced for more complex array structures
        try {
            return eval("return $arrayString;");
        } catch (\ParseError $e) {
            // If eval fails, return null and let the layout work without data
            return null;
        }
    }

    protected function generateCompilationResult(string $viewPath, ParsedComponent $parsed, string $hash): CompilationResult
    {
        $className = $this->generateClassName($viewPath, $hash);
        $classPath = $this->getClassPath($className);
        $viewName = $this->generateViewName($viewPath, $hash);
        $compiledViewPath = $this->getViewPath($viewName);

        return new CompilationResult(
            className: $className,
            classPath: $classPath,
            viewName: $viewName,
            viewPath: $compiledViewPath,
            isExternal: $parsed->isExternal,
            externalClass: $parsed->externalClass,
            hash: $hash
        );
    }

    protected function generateFiles(CompilationResult $result, ParsedComponent $parsed): void
    {
        // Always generate the view file...
        $this->generateView($result, $parsed);

        // Generate partial view files if present...
        if (!empty($parsed->inlinePartials)) {
            $this->generatePartialViews($parsed);
        }

        // Only generate class file for inline components...
        if ($result->shouldGenerateClass()) {
            $this->generateClass($result, $parsed);
        }
    }

    protected function generatePartialViews(ParsedComponent $parsed): void
    {
        foreach ($parsed->inlinePartials as $partial) {
            $partialPath = $this->viewsDirectory . '/' . $partial['fileName'];
            File::put($partialPath, $partial['content']);
        }
    }

    protected function generateClass(CompilationResult $result, ParsedComponent $parsed): void
    {
        $namespace = $result->getClassNamespace();
        $className = $result->getShortClassName();
        $viewName = $result->viewName;

        // Extract class definition from frontmatter...
        $classBody = $this->extractClassBody($parsed->frontmatter);

        // Generate layout attribute if present
        $layoutAttribute = '';
        if ($parsed->hasLayout()) {
            $layoutAttribute = $this->generateLayoutAttribute($parsed->layoutTemplate, $parsed->layoutData);
        }

        // Generate partial lookup array if partials exist
        $partialLookupProperty = '';
        if (!empty($parsed->inlinePartials)) {
            $partialLookupProperty = $this->generatePartialLookupProperty($parsed->inlinePartials);
        }

        $classContent = "<?php

namespace {$namespace};

{$layoutAttribute}class {$className} extends \\Livewire\\Component
{
{$partialLookupProperty}{$classBody}

    public function render()
    {
        return view('{$viewName}');
    }
}
";

        File::put($result->classPath, $classContent);
    }

    protected function generateLayoutAttribute(?string $template, ?array $data): string
    {
        if (empty($template)) {
            return '';
        }

        $dataString = '';
        if (!empty($data)) {
            $dataString = ', ' . $this->arrayToString($data);
        }

        return "#[\\Livewire\\Attributes\\Layout('{$template}'{$dataString})]\n";
    }

    protected function arrayToString(array $data): string
    {
        $parts = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $parts[] = "'{$key}' => '{$value}'";
            } elseif (is_numeric($value)) {
                $parts[] = "'{$key}' => {$value}";
            } elseif (is_bool($value)) {
                $parts[] = "'{$key}' => " . ($value ? 'true' : 'false');
            } else {
                // For complex values, convert to string representation
                $parts[] = "'{$key}' => " . var_export($value, true);
            }
        }
        return '[' . implode(', ', $parts) . ']';
    }

    protected function generateView(CompilationResult $result, ParsedComponent $parsed): void
    {
        $processedViewContent = $this->transformNakedScripts($parsed->viewContent);
        File::put($result->viewPath, $processedViewContent);
    }

    /**
     * Transform naked <script> tags into @script wrapped scripts.
     *
     * This detects script tags that are not already wrapped in @script directives
     * and automatically wraps them for proper Livewire integration.
     */
    protected function transformNakedScripts(string $viewContent): string
    {
        // Don't process if there are no script tags
        if (!str_contains($viewContent, '<script')) {
            return $viewContent;
        }

        // Don't process if there are already @script directives present
        if (str_contains($viewContent, '@script')) {
            return $viewContent;
        }

        // Match script tags that are not already wrapped in @script directives
        // This pattern matches <script> tags with their content and closing </script>
        $pattern = '/<script\b[^>]*>(.*?)<\/script>/s';

        $transformedContent = preg_replace_callback($pattern, function ($matches) {
            $fullScriptTag = $matches[0];
            $scriptContent = $matches[1];

            // Skip empty scripts
            if (empty(trim($scriptContent))) {
                return $fullScriptTag;
            }

            // Wrap the script tag with @script directives
            return "\n@script\n" . $fullScriptTag . "\n@endscript\n";
        }, $viewContent);

        return $transformedContent;
    }

    protected function extractClassBody(string $frontmatter): string
    {
        // Handle anonymous class definition...
        if (preg_match('/new\s+class.*?\{(.*)\}/s', $frontmatter, $matches)) {
            return trim($matches[1]);
        }

        // Handle named class definition...
        if (preg_match('/class\s+\w+.*?\{(.*)\}/s', $frontmatter, $matches)) {
            return trim($matches[1]);
        }

        throw new ParseException("Could not extract class body from frontmatter");
    }

    protected function generateHash(string $viewPath, string $content): string
    {
        return substr(md5($viewPath . $content . filemtime($viewPath)), 0, 8);
    }

    protected function generateClassName(string $viewPath, string $hash): string
    {
        $name = $this->getComponentNameFromPath($viewPath);
        $className = str_replace(['-', '.'], '', ucwords($name, '-.'));
        return "Livewire\\Compiled\\{$className}_{$hash}";
    }

    protected function generateViewName(string $viewPath, string $hash): string
    {
        $name = $this->getComponentNameFromPath($viewPath);
        return "livewire-compiled::{$name}_{$hash}";
    }

    protected function getComponentNameFromPath(string $viewPath): string
    {
        // Handle multiple extensions
        $basename = basename($viewPath);

        // Remove the appropriate extension
        foreach ($this->supportedExtensions as $extension) {
            if (str_ends_with($basename, $extension)) {
                $basename = substr($basename, 0, -strlen($extension));
                break;
            }
        }

        return str_replace([' ', '_'], '-', $basename);
    }

    protected function getClassPath(string $className): string
    {
        $relativePath = str_replace(['Livewire\\Compiled\\', '\\'], ['', '/'], $className) . '.php';
        return $this->classesDirectory . '/' . $relativePath;
    }

    protected function getViewPath(string $viewName): string
    {
        $relativePath = str_replace('livewire-compiled::', '', $viewName) . '.blade.php';
        return $this->viewsDirectory . '/' . $relativePath;
    }

    protected function getExistingCompilationResult(string $viewPath, string $hash): CompilationResult
    {
        $content = File::get($viewPath);
        $parsed = $this->parseComponent($content);
        return $this->generateCompilationResult($viewPath, $parsed, $hash);
    }

    protected function ensureDirectoriesExist(): void
    {
        File::ensureDirectoryExists($this->classesDirectory);
        File::ensureDirectoryExists($this->viewsDirectory);
    }

    protected function generatePartialLookupProperty(array $inlinePartials): string
    {
        $lookupEntries = [];
        foreach ($inlinePartials as $partial) {
            $lookupEntries[] = "        '{$partial['name']}' => '{$partial['viewName']}'";
        }

        $lookupArray = "[\n" . implode(",\n", $lookupEntries) . "\n    ]";

        return "    protected \$partialLookup = {$lookupArray};\n\n";
    }
}