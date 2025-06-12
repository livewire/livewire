<?php

namespace Livewire\V4\Compiler;

use Livewire\V4\Compiler\Exceptions\InvalidComponentException;
use Livewire\V4\Compiler\Exceptions\CompilationException;
use Livewire\V4\Compiler\Exceptions\ParseException;
use Illuminate\Support\Facades\File;
use Livewire\Mechanisms\Mechanism;

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
        $this->ensureCacheDirectoryIsGitIgnored();
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

        // Handle external class reference with traditional PHP tags: < ?php(new App\Livewire\SomeClass) ? >
        if (preg_match('/<\?php\s*\(\s*new\s+([A-Za-z0-9\\\\]+)(?:::class)?\s*\)\s*\?>/s', $content, $matches)) {
            $externalClass = $matches[1];
            $viewContent = preg_replace('/<\?php\s*\([^)]+\)\s*\?>/s', '', $content);

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

        // Handle inline class with traditional PHP tags: < ?php ... ? >
        if (preg_match('/<\?php\s*(.*?)\s*\?>/s', $content, $matches)) {
            $frontmatter = trim($matches[1]);
            // Use the modified $content (after layout removal) for the view content
            $viewContent = preg_replace('/<\?php\s*.*?\s*\?>/s', '', $content);

            // Validate that frontmatter contains a class definition...
            if (!str_contains($frontmatter, 'new class') && !str_contains($frontmatter, 'class ')) {
                throw new ParseException("Invalid component: <"."?php block must contain a class definition");
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
        // Pattern to handle @partial('name', ...), @partial(namedParam: 'value', ...), and bare @partial
        $pattern = '/@partial\s*(?:\((.*?)\))?(.*?)@endpartial/s';

        return preg_replace_callback($pattern, function ($matches) use (&$inlinePartials) {
            $parameters = isset($matches[1]) ? trim($matches[1]) : '';
            $partialContent = trim($matches[2]);

            // Handle different parameter formats
            if (!empty($parameters)) {
                // Try to extract explicit name first (old format)
                if (preg_match('/^[\'"]([^\'"]+)[\'"](?:\s*,\s*(.*))?$/', $parameters, $paramMatches)) {
                    // Has explicit quoted name as first parameter
                    $partialName = $paramMatches[1];
                    $partialData = isset($paramMatches[2]) && !empty(trim($paramMatches[2])) ? trim($paramMatches[2]) : '[]';
                } else {
                    // No explicit name, generate one (handles named parameters like mode: 'hey')
                    $partialName = uniqid('partial_');
                    $partialData = $parameters;
                }
            } else {
                // Bare @partial with no parameters at all
                $partialName = uniqid('partial_');
                $partialData = '[]';
            }

            // Generate a unique view name for this partial using content hash
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

            $processedPartialContent = $partial['content'];

            // For inline components, add computed property guards instead of transforming
            if ($parsed->hasInlineClass()) {
                $computedProperties = $this->extractComputedPropertyNames($parsed->frontmatter);
                $usedComputedProperties = $this->extractUsedComputedProperties($processedPartialContent, $computedProperties);

                if (!empty($usedComputedProperties)) {
                    $guards = $this->generateComputedPropertyGuards($usedComputedProperties);
                    $processedPartialContent = $guards . $processedPartialContent;
                }
            }

            File::put($partialPath, $processedPartialContent);
        }
    }

    /**
     * Extract which computed properties are actually used in the given content.
     */
    protected function extractUsedComputedProperties(string $content, array $computedProperties): array
    {
        $usedProperties = [];

        foreach ($computedProperties as $propertyName) {
            // Pattern to match $propertyName references (same as transformPropertyReferences)
            $pattern = '/\$' . preg_quote($propertyName, '/') . '(?![a-zA-Z0-9_])/';

            if (preg_match($pattern, $content)) {
                $usedProperties[] = $propertyName;
            }
        }

        return $usedProperties;
    }

    /**
     * Generate guard statements for computed properties.
     *
     * Creates: <?php if (! isset($propertyName)) $propertyName = $this->propertyName; ?>
     */
    protected function generateComputedPropertyGuards(array $computedProperties): string
    {
        if (empty($computedProperties)) {
            return '';
        }

        $guards = [];
        foreach ($computedProperties as $propertyName) {
            $guards[] = "if (! isset(\${$propertyName})) \${$propertyName} = \$this->{$propertyName};";
        }

        return "<?php " . implode(' ', $guards) . " ?>\n";
    }

    protected function generateClass(CompilationResult $result, ParsedComponent $parsed): void
    {
        $namespace = $result->getClassNamespace();
        $className = $result->getShortClassName();
        $viewName = $result->viewName;

        // Extract use statements and class definition from frontmatter...
        $useStatements = $this->extractUseStatements($parsed->frontmatter);
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

        // Build the use statements section
        $useStatementsSection = '';
        if (!empty($useStatements)) {
            $useStatementsSection = implode("\n", $useStatements) . "\n\n";
        }

        $classContent = "<?php

namespace {$namespace};

{$useStatementsSection}{$layoutAttribute}class {$className} extends \\Livewire\\Component
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

        // Transform computed property references if this is an inline component
        if ($parsed->hasInlineClass()) {
            $processedViewContent = $this->transformComputedPropertyReferences($processedViewContent, $parsed->frontmatter);
        }

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

    /**
     * Transform computed property references from $propertyName to $this->propertyName.
     *
     * Scans the frontmatter for computed properties and transforms their references
     * in the view content to maintain clean syntax while preserving JIT evaluation.
     */
    protected function transformComputedPropertyReferences(string $viewContent, string $frontmatter): string
    {
        $computedProperties = $this->extractComputedPropertyNames($frontmatter);

        if (empty($computedProperties)) {
            return $viewContent;
        }

        // Check for variable reassignments that would conflict
        $this->validateNoComputedVariableReassignments($viewContent, $computedProperties);

        // Transform each computed property reference
        foreach ($computedProperties as $propertyName) {
            $viewContent = $this->transformPropertyReferences($viewContent, $propertyName);
        }

        return $viewContent;
    }

    /**
     * Extract computed property method names from the frontmatter.
     */
    protected function extractComputedPropertyNames(string $frontmatter): array
    {
        $computedProperties = [];

        // Pattern to match computed attribute and the method name that follows
        // Handles: #[Computed], #[\Livewire\Attributes\Computed], #[Computed(options)]
        $pattern = '/#\[\s*(?:\\\\?Livewire\\\\Attributes\\\\)?Computed[^\]]*\]\s*(?:public|protected|private)?\s*function\s+([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*\(/';

        if (preg_match_all($pattern, $frontmatter, $matches)) {
            $computedProperties = $matches[1];
        }

        return array_unique($computedProperties);
    }

    /**
     * Validate that no computed properties are being reassigned in the view.
     */
    protected function validateNoComputedVariableReassignments(string $viewContent, array $computedProperties): void
    {
        foreach ($computedProperties as $propertyName) {
            // Pattern to match variable assignments like $propertyName =
            $assignmentPattern = '/\$' . preg_quote($propertyName, '/') . '\s*=(?!=)/';

            if (preg_match($assignmentPattern, $viewContent)) {
                throw new CompilationException(
                    "Cannot reassign variable \${$propertyName} as it's reserved for the computed property '{$propertyName}'. " .
                    "Use a different variable name in your view."
                );
            }
        }
    }

    /**
     * Transform references to a specific property from $propertyName to $this->propertyName.
     */
    protected function transformPropertyReferences(string $viewContent, string $propertyName): string
    {
        // Pattern to match $propertyName but not in contexts where it shouldn't be transformed:
        // - Not when it's part of a longer variable name (e.g., $propertyNameOther)
        // - Not when it's being assigned to (handled by validation above)
        // - Not when it's in comments
        $pattern = '/\$' . preg_quote($propertyName, '/') . '(?![a-zA-Z0-9_])/';

        return preg_replace($pattern, '$this->' . $propertyName, $viewContent);
    }

    protected function extractUseStatements(string $frontmatter): array
    {
        $useStatements = [];

        // First, extract the class definition part to exclude trait usage inside the class
        $classBody = '';
        if (preg_match('/new\s+class.*?\{(.*)\}/s', $frontmatter, $matches)) {
            $classBody = $matches[1];
        } elseif (preg_match('/class\s+\w+.*?\{(.*)\}/s', $frontmatter, $matches)) {
            $classBody = $matches[1];
        }

        // Extract everything outside the class body (imports)
        $frontmatterWithoutClassBody = $frontmatter;
        if (!empty($classBody)) {
            $frontmatterWithoutClassBody = str_replace($classBody, '', $frontmatter);
        }

        // Match use statements only in the non-class portion (imports, not trait usage)
        if (preg_match_all('/use\s+[A-Za-z0-9\\\\]+(?:\s+as\s+[A-Za-z0-9_]+)?;/m', $frontmatterWithoutClassBody, $matches)) {
            $useStatements = $matches[0];
        }

        return $useStatements;
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

    protected function ensureCacheDirectoryIsGitIgnored(): void
    {
        $gitignorePath = $this->cacheDirectory . '/.gitignore';

        if (! File::exists($gitignorePath)) {
            File::put($gitignorePath, "*\n!.gitignore");
        }
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