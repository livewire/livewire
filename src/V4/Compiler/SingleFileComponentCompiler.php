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
        $this->cacheDirectory = $cacheDirectory ?: storage_path('framework/views/livewire');
        $this->classesDirectory = $this->cacheDirectory . '/classes';
        $this->viewsDirectory = $this->cacheDirectory . '/views';
        $this->supportedExtensions = $supportedExtensions ?: ['.livewire.php'];

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
        $parsed = $this->loadExternalViewAndScriptIfRequired($viewPath, $parsed);

        // Generate compilation result...
        $result = $this->generateCompilationResult($viewPath, $parsed, $hash);

        // Generate files...
        $this->generateFiles($result, $parsed);

        return $result;
    }

    public function compileMultiFileComponent(string $directory): CompilationResult
    {
        if (! file_exists($directory) || ! is_dir($directory)) {
            throw new CompilationException("Directory not found: [{$directory}]");
        }

        // Get the component name from the directory
        $componentName = basename($directory);

        // Define the expected file paths
        $livewireFilePath = $directory . '/' . $componentName . '.livewire.php';
        $bladeFilePath = $directory . '/' . $componentName . '.blade.php';

        // Check if both required files exist
        if (! file_exists($livewireFilePath)) {
            throw new CompilationException("Livewire file not found: [{$livewireFilePath}]");
        }

        if (! file_exists($bladeFilePath)) {
            throw new CompilationException("Blade file not found: [{$bladeFilePath}]");
        }

        // Read the contents of both files
        $livewireContent = File::get($livewireFilePath);
        $bladeContent = File::get($bladeFilePath);

        // Remove PHP opening tags from livewire content if present
        $livewireContent = preg_replace('/^<\?php\s*/', '', trim($livewireContent));

        // Concatenate the contents to simulate a single file component
        // Format: @php frontmatter @endphp blade_content
        $content = "@php\n" . $livewireContent . "\n@endphp\n" . $bladeContent;

        // Generate hash based on the directory path and combined content
        $hash = $this->generateMultiFileHash($directory, $livewireContent, $bladeContent);

        // Check if already compiled and up to date...
        if ($this->isMultiFileCompiled($directory, $hash)) {
            return $this->getExistingMultiFileCompilationResult($directory, $hash);
        }

        // Parse the component using the concatenated content...
        $parsed = $this->parseComponent($content);

        // For multi-file components, we don't need to load external view/script
        // since they're already separate files

        // Generate compilation result using the directory as the "view path"...
        $result = $this->generateMultiFileCompilationResult($directory, $parsed, $hash);

        // Generate files...
        $this->generateFiles($result, $parsed);

        return $result;
    }

    public function isCompiled(string $viewPath, ?string $hash = null): bool
    {
        $originalViewLastModified = File::lastModified($viewPath);

        if ($hash === null) {
            $content = File::get($viewPath);
            $hash = $this->generateHash($viewPath, $content);
        }

        $className = $this->generateClassName($viewPath, $hash);
        $classPath = $this->getClassPath($className);
        $viewName = $this->generateViewName($viewPath, $hash);
        $viewPath = $this->getViewPath($viewName);

        try {
            $classLastModified = File::lastModified($classPath);
            $viewLastModified = File::lastModified($viewPath);

            return $originalViewLastModified <= $classLastModified && $originalViewLastModified <= $viewLastModified;
        } catch (\ErrorException $exception) {
            if (! File::exists($classPath) || ! File::exists($viewPath)) {
                return false;
            }

            throw $exception;
        }
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
            );
        }

        throw new InvalidComponentException("Component must contain either @php(new ClassName) or @php...@endphp block");
    }

    protected function loadExternalViewAndScriptIfRequired(string $viewPath, ParsedComponent $parsed): ParsedComponent
    {
        if ($parsed->viewContent !== '') {
            return $parsed;
        }

        $viewFilePath = str_replace('.livewire.php', '.blade.php', $viewPath);
        $scriptFilePath = str_replace('.livewire.php', '.js', $viewPath);

        if (! file_exists($viewFilePath)) {
            return $parsed;
        }

        $parsed->viewContent = trim(File::get($viewFilePath));

        if (! file_exists($scriptFilePath)) {
            return $parsed;
        }

        $scriptFileContents = trim(File::get($scriptFilePath));

        $parsed->viewContent .= <<< HTML
        \n
        <script>
            {$scriptFileContents}
        </script>
        HTML;

        return $parsed;
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

        // Only generate class file for inline components...
        if ($result->shouldGenerateClass()) {
            $this->generateClass($result, $parsed);
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
        $preClassCode = $this->extractPreClassCode($parsed->frontmatter);
        $classBody = $this->extractClassBody($parsed->frontmatter);
        $classLevelAttributes = $this->extractClassLevelAttributes($parsed->frontmatter);

        // Generate layout attribute if present
        $layoutAttribute = '';
        if ($parsed->hasLayout()) {
            $layoutAttribute = $this->generateLayoutAttribute($parsed->layoutTemplate, $parsed->layoutData);
        }

        // Build the pre-class code section (imports, constants, etc.)
        $preClassSection = '';
        if (!empty($preClassCode)) {
            $preClassSection = $preClassCode . "\n\n";
        }

        // Build class-level attributes section
        $classAttributesSection = '';
        if (!empty($classLevelAttributes)) {
            $classAttributesSection = implode("\n", $classLevelAttributes) . "\n";
        }

        $classContent = "<?php

namespace {$namespace};

{$preClassSection}{$layoutAttribute}{$classAttributesSection}class {$className} extends \\Livewire\\Component
{
{$classBody}

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

    protected function extractPreClassCode(string $frontmatter): string
    {
        // Extract everything before the class definition (new class or class keyword)
        // This includes use statements, constants, functions, etc.

        // For anonymous classes, look for "new" keyword (which might be followed by attributes then "class")
        // For named classes, look for the class keyword directly

        $classPos = false;

        // First try to find "new" keyword for anonymous classes
        if (preg_match('/\bnew\s+/', $frontmatter, $matches, PREG_OFFSET_CAPTURE)) {
            $classPos = $matches[0][1];
        }
        // If no "new" found, look for named class definition
        elseif (preg_match('/\bclass\s+\w+/', $frontmatter, $matches, PREG_OFFSET_CAPTURE)) {
            $classPos = $matches[0][1];
        }

        if ($classPos === false) {
            return '';
        }

        // Extract everything before the class/new statement
        $preClassCode = substr($frontmatter, 0, $classPos);

        // Clean up the pre-class code
        $preClassCode = trim($preClassCode);

        // Remove any PHP opening tags that might be present
        $preClassCode = preg_replace('/^<\?php\s*/', '', $preClassCode);

        return trim($preClassCode);
    }

    protected function extractUseStatements(string $frontmatter): array
    {
        // This method is now deprecated in favor of extractPreClassCode
        // Keeping it for backwards compatibility in case it's used elsewhere
        $preClassCode = $this->extractPreClassCode($frontmatter);

        if (empty($preClassCode)) {
            return [];
        }

        // Extract individual use statements from the pre-class code for backwards compatibility
        $lines = explode("\n", $preClassCode);
        $useStatements = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^use\s+/', $line)) {
                $useStatements[] = $line;
            }
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
        // The v1 is a cache version number, the same as how Laravel handles it...
        return hash('xxh128', 'v1'.$viewPath);
    }

    protected function generateMultiFileHash(string $directory, string $livewireContent, string $bladeContent): string
    {
        // Include directory path in hash like the original method, plus a version identifier
        return hash('xxh128', 'v1'.$directory.$livewireContent.$bladeContent);
    }

    protected function isMultiFileCompiled(string $directory, string $hash): bool
    {
        $componentName = basename($directory);
        $livewireFilePath = $directory . '/' . $componentName . '.livewire.php';
        $bladeFilePath = $directory . '/' . $componentName . '.blade.php';

        if (! file_exists($livewireFilePath) || ! file_exists($bladeFilePath)) {
            return false;
        }

        // Get the latest modification time from source files
        $livewireLastModified = File::lastModified($livewireFilePath);
        $bladeLastModified = File::lastModified($bladeFilePath);
        $sourceLastModified = max($livewireLastModified, $bladeLastModified);

        // Check if compiled files exist and are newer than source files
        $className = $this->generateClassName($directory, $hash);
        $classPath = $this->getClassPath($className);
        $viewName = $this->generateViewName($directory, $hash);
        $viewPath = $this->getViewPath($viewName);

        try {
            $classLastModified = File::lastModified($classPath);
            $viewLastModified = File::lastModified($viewPath);

            return $sourceLastModified <= $classLastModified && $sourceLastModified <= $viewLastModified;
        } catch (\ErrorException $exception) {
            if (! File::exists($classPath) || ! File::exists($viewPath)) {
                return false;
            }

            throw $exception;
        }
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

    protected function generateMultiFileCompilationResult(string $directory, ParsedComponent $parsed, string $hash): CompilationResult
    {
        $className = $this->generateClassName($directory, $hash);
        $classPath = $this->getClassPath($className);
        $viewName = $this->generateViewName($directory, $hash);
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

    protected function getExistingMultiFileCompilationResult(string $directory, string $hash): CompilationResult
    {
        $componentName = basename($directory);
        $livewireFilePath = $directory . '/' . $componentName . '.livewire.php';
        $bladeFilePath = $directory . '/' . $componentName . '.blade.php';

        $livewireContent = File::get($livewireFilePath);
        $bladeContent = File::get($bladeFilePath);

        // Remove PHP opening tags from livewire content if present
        $livewireContent = preg_replace('/^<\?php\s*/', '', trim($livewireContent));

        $content = "@php\n" . $livewireContent . "\n@endphp\n" . $bladeContent;

        $parsed = $this->parseComponent($content);
        // No need to load external view/script for multi-file components

        return $this->generateMultiFileCompilationResult($directory, $parsed, $hash);
    }

    protected function getExistingCompilationResult(string $viewPath, string $hash): CompilationResult
    {
        $content = File::get($viewPath);
        $parsed = $this->parseComponent($content);
        $parsed = $this->loadExternalViewAndScriptIfRequired($viewPath, $parsed);

        return $this->generateCompilationResult($viewPath, $parsed, $hash);
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

    protected function extractClassLevelAttributes(string $frontmatter): array
    {
        $attributes = [];

        // Match attributes before the class keyword, handling both compact and spaced syntax
        // Pattern explanation:
        // - Match 'new' followed by optional whitespace and newlines
        // - Capture any attributes (#[...]) that come after 'new' but before 'class'
        // - Handle multiple attributes and various spacing/newline combinations
        if (preg_match('/new\s*\n?\s*((?:#\[[^\]]*\]\s*\n?\s*)*)\s*class/s', $frontmatter, $matches)) {
            $attributesBlock = trim($matches[1]);

            if (!empty($attributesBlock)) {
                // Extract individual attributes from the block
                if (preg_match_all('/#\[[^\]]*\]/', $attributesBlock, $attributeMatches)) {
                    $attributes = $attributeMatches[0];
                }
            }
        }

        return $attributes;
    }
}
