<?php

namespace Livewire\V4\Compiler;

use Livewire\V4\Compiler\Exceptions\InvalidComponentException;
use Livewire\V4\Compiler\Exceptions\CompilationException;
use Livewire\V4\Compiler\Exceptions\ParseException;
use Livewire\V4\Placeholders\PlaceholderCompiler;
use Livewire\V4\Islands\IslandsCompiler;
use Illuminate\Support\Facades\File;
use Livewire\Mechanisms\Mechanism;

class SingleFileComponentCompiler extends Mechanism
{
    protected string $cacheDirectory;
    protected string $classesDirectory;
    protected string $viewsDirectory;
    protected string $scriptsDirectory;
    protected string $metadataDirectory;
    protected array $supportedExtensions;

    // In-memory cache for same-request optimizations
    private static array $compilationCache = [];
    private static array $metadataCache = [];

    public function __construct(?string $cacheDirectory = null, ?array $supportedExtensions = null)
    {
        $this->cacheDirectory = $cacheDirectory ?: storage_path('framework/views/livewire');
        $this->classesDirectory = $this->cacheDirectory . '/classes';
        $this->viewsDirectory = $this->cacheDirectory . '/views';
        $this->scriptsDirectory = $this->cacheDirectory . '/scripts';
        $this->metadataDirectory = $this->cacheDirectory . '/metadata';
        $this->supportedExtensions = $supportedExtensions ?: ['.livewire.php', '.blade.php'];

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

        // Check in-memory cache first (same-request optimization)
        $sourceModTime = filemtime($viewPath);
        $memoryCacheKey = $viewPath . '_' . $sourceModTime;

        if (isset(self::$compilationCache[$memoryCacheKey])) {
            return self::$compilationCache[$memoryCacheKey];
        }

        $hash = $this->generateHash($viewPath, $sourceModTime);

        // Check if already compiled and up to date...
        if ($this->isCompiled($viewPath, $hash, $sourceModTime)) {
            $result = $this->getExistingCompilationResult($viewPath, $hash);
            self::$compilationCache[$memoryCacheKey] = $result;
            return $result;
        }

        // Need to compile - read content only now
        $content = File::get($viewPath);

        // Parse the component...
        $parsed = $this->parseComponent($content);
        $parsed = $this->loadExternalViewAndScriptIfRequired($viewPath, $parsed);

        // Generate compilation result...
        $result = $this->generateCompilationResult($viewPath, $parsed, $hash);

        // Compile islands...
        $islandsCompiler = new IslandsCompiler($this->cacheDirectory);
        $parsed->viewContent = $islandsCompiler->compile($parsed->viewContent, $viewPath);

        // Compile placeholder...
        $placeholderCompiler = new PlaceholderCompiler($this->cacheDirectory);
        $parsed->viewContent = $placeholderCompiler->compile($parsed->viewContent, $viewPath);

        // Generate files and metadata...
        $this->generateFiles($result, $parsed);
        $this->storeCompilationMetadata($result, $parsed, [$viewPath], [$sourceModTime]);

        // Cache in memory for this request
        self::$compilationCache[$memoryCacheKey] = $result;

        return $result;
    }

    public function compileMultiFileComponent(string $directory): CompilationResult
    {
        if (! file_exists($directory) || ! is_dir($directory)) {
            throw new CompilationException("Directory not found: [{$directory}]");
        }

        // Get the component name from the directory
        $directoryName = basename($directory);
        // For directories with ⚡, use the base name without ⚡ for the files inside
        $componentName = str_contains($directoryName, '⚡') ? str_replace('⚡', '', $directoryName) : $directoryName;

        // Define the expected file paths
        $livewireFilePath = $directory . '/' . $componentName . '.php';
        $bladeFilePath = $directory . '/' . $componentName . '.blade.php';
        $jsFilePath = $directory . '/' . $componentName . '.js';

        // Check if both required files exist
        if (! file_exists($livewireFilePath)) {
            throw new CompilationException("Livewire file not found: [{$livewireFilePath}]");
        }

        if (! file_exists($bladeFilePath)) {
            throw new CompilationException("Blade file not found: [{$bladeFilePath}]");
        }

        // Collect source files and their modification times
        $sourceFiles = [$livewireFilePath, $bladeFilePath];
        $sourceModTimes = [filemtime($livewireFilePath), filemtime($bladeFilePath)];

        if (file_exists($jsFilePath)) {
            $sourceFiles[] = $jsFilePath;
            $sourceModTimes[] = filemtime($jsFilePath);
        }

        // Check in-memory cache first
        $latestModTime = max($sourceModTimes);
        $memoryCacheKey = $directory . '_' . $latestModTime;

        if (isset(self::$compilationCache[$memoryCacheKey])) {
            return self::$compilationCache[$memoryCacheKey];
        }

        $hash = $this->generateMultiFileHash($directory, $sourceModTimes);

        // Check if already compiled and up to date...
        if ($this->isMultiFileCompiled($directory, $hash, $sourceFiles, $sourceModTimes)) {
            $result = $this->getExistingMultiFileCompilationResult($directory, $hash);
            self::$compilationCache[$memoryCacheKey] = $result;
            return $result;
        }

        // Need to compile - read contents only now
        $livewireContent = File::get($livewireFilePath);
        $bladeContent = File::get($bladeFilePath);
        $jsContent = file_exists($jsFilePath) ? File::get($jsFilePath) : '';

        // Remove PHP opening tags from livewire content if present
        $livewireContent = preg_replace('/^<\?php\s*/', '', trim($livewireContent));

        // Concatenate the contents to simulate a single file component
        $content = "@php\n" . $livewireContent . "\n@endphp\n" . $bladeContent;

        // Parse the component using the concatenated content...
        $parsed = $this->parseComponent($content);

        // Add dedicated JS file content to the parsed scripts if it exists
        if (!empty($jsContent)) {
            $parsed = $this->addDedicatedJsContent($parsed, $jsContent);
        }

        // Generate compilation result using the directory as the "view path"...
        $result = $this->generateMultiFileCompilationResult($directory, $parsed, $hash);

        // Compile islands...
        $islandsCompiler = new IslandsCompiler($this->cacheDirectory);
        $parsed->viewContent = $islandsCompiler->compile($parsed->viewContent, $directory);

        // Compile placeholder...
        $placeholderCompiler = new PlaceholderCompiler($this->cacheDirectory);
        $parsed->viewContent = $placeholderCompiler->compile($parsed->viewContent, $directory);

        // Generate files and metadata...
        $this->generateFiles($result, $parsed);
        $this->storeCompilationMetadata($result, $parsed, $sourceFiles, $sourceModTimes);

        // Cache in memory for this request
        self::$compilationCache[$memoryCacheKey] = $result;

        return $result;
    }

    public function isCompiled(string $viewPath, ?string $hash = null, ?int $sourceModTime = null): bool
    {
        if ($hash === null) {
            $sourceModTime = $sourceModTime ?? filemtime($viewPath);
            $hash = $this->generateHash($viewPath, $sourceModTime);
        }

        $metadataPath = $this->getMetadataPath($viewPath, $hash);

        // Check if metadata file exists
        if (! file_exists($metadataPath)) {
            return false;
        }

        // Load metadata from cache or file
        $metadata = $this->loadCompilationMetadata($metadataPath);
        if (! $metadata) {
            return false;
        }

        // Check if any source files have been modified
        foreach ($metadata['sourceFiles'] as $index => $sourceFile) {
            if (! file_exists($sourceFile)) {
                return false;
            }

            $currentModTime = filemtime($sourceFile);
            $cachedModTime = $metadata['sourceModTimes'][$index];

            if ($currentModTime > $cachedModTime) {
                return false;
            }
        }

        // Check if compiled files still exist
        if (! file_exists($metadata['classPath']) || ! file_exists($metadata['viewPath'])) {
            return false;
        }

        // If has scripts, check script file exists
        if ($metadata['hasScripts'] && ! file_exists($metadata['scriptPath'])) {
            return false;
        }

        return true;
    }

    public function getCompiledPath(string $viewPath): string
    {
        $sourceModTime = filemtime($viewPath);
        $hash = $this->generateHash($viewPath, $sourceModTime);
        $className = $this->generateClassName($viewPath, $hash);
        return $this->getClassPath($className);
    }

    public function parseComponent(string $content): ParsedComponent
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
            $content = preg_replace('/@layout\s*\([^)]+\)\s*/', '', $content, limit: 1);
        }

        // Handle external class reference: @php(new App\Livewire\SomeClass)
        if (preg_match('/@php\s*\(\s*new\s+([A-Za-z0-9\\\\]+)(?:::class)?\s*\)/s', $content, $matches)) {
            $externalClass = $matches[1];
            $viewContent = preg_replace('/@php\s*\([^)]+\)/s', '', $content, limit: 1);

            // Extract scripts from view content and clean it
            $scripts = $this->extractScripts($viewContent);
            $cleanViewContent = $this->removeScripts($viewContent);

            return new ParsedComponent(
                '',
                trim($cleanViewContent),
                true,
                $externalClass,
                $layoutTemplate,
                $layoutData,
                $scripts,
            );
        }

        // Handle inline class: @php ... @endphp
        if (preg_match('/@php\s*(.*?)\s*@endphp/s', $content, $matches)) {
            $frontmatter = trim($matches[1]);
            // Validate that frontmatter contains a class definition...
            if (str_contains($frontmatter, 'new class') || str_contains($frontmatter, 'class ')) {
                // Use the modified $content (after layout removal) for the view content
                $viewContent = preg_replace('/@php\s*.*?\s*@endphp/s', '', $content, limit: 1);

                // Extract scripts from view content and clean it
                $scripts = $this->extractScripts($viewContent);
                $cleanViewContent = $this->removeScripts($viewContent);

                return new ParsedComponent(
                    $frontmatter,
                    trim($cleanViewContent),
                    false,
                    null,
                    $layoutTemplate,
                    $layoutData,
                    $scripts,
                );
            }
        }

        // Handle external class reference with traditional PHP tags: < ?php(new App\Livewire\SomeClass) ? >
        if (preg_match('/<\?php\s*\(\s*new\s+([A-Za-z0-9\\\\]+)(?:::class)?\s*\)\s*\?>/s', $content, $matches)) {
            $externalClass = $matches[1];
            $viewContent = preg_replace('/<\?php\s*\([^)]+\)\s*\?>/s', '', $content, limit: 1);

            // Extract scripts from view content and clean it
            $scripts = $this->extractScripts($viewContent);
            $cleanViewContent = $this->removeScripts($viewContent);

            return new ParsedComponent(
                '',
                trim($cleanViewContent),
                true,
                $externalClass,
                $layoutTemplate,
                $layoutData,
                $scripts,
            );
        }

        // Handle inline class with traditional PHP tags: < ?php ... ? >
        if (preg_match('/<\?php\s*(.*?)\s*\?>/s', $content, $matches)) {
            $frontmatter = trim($matches[1]);

            // Validate that frontmatter contains a class definition...
            if (str_contains($frontmatter, 'new class') || str_contains($frontmatter, 'class ')) {
                // Use the modified $content (after layout removal) for the view content
                $viewContent = preg_replace('/<\?php\s*.*?\s*\?>/s', '', $content, limit: 1);

                // Extract scripts from view content and clean it
                $scripts = $this->extractScripts($viewContent);
                $cleanViewContent = $this->removeScripts($viewContent);

                return new ParsedComponent(
                    $frontmatter,
                    trim($cleanViewContent),
                    false,
                    null,
                    $layoutTemplate,
                    $layoutData,
                    $scripts,
                );
            }
        }

        throw new InvalidComponentException("Component must contain either <?php(new ClassName) or <?php...?> block");
    }

    protected function loadExternalViewAndScriptIfRequired(string $viewPath, ParsedComponent $parsed): ParsedComponent
    {
        if ($parsed->viewContent !== '') {
            return $parsed;
        }

        // Handle external view/script loading based on file extension
        if (str_ends_with($viewPath, '.livewire.php')) {
            $viewFilePath = str_replace('.livewire.php', '.blade.php', $viewPath);
            $scriptFilePath = str_replace('.livewire.php', '.js', $viewPath);
        } else if (str_ends_with($viewPath, '.blade.php') && str_contains($viewPath, '⚡')) {
            // For ⚡ blade files, try to find accompanying view/script files
            // Remove ⚡ to find the base name, then look for .blade.php and .js files
            $basePathWithoutEmoji = str_replace('⚡', '', $viewPath);
            $basePathWithoutExtension = substr($basePathWithoutEmoji, 0, -strlen('.blade.php'));
            $viewFilePath = $basePathWithoutExtension . '.blade.php';
            $scriptFilePath = $basePathWithoutExtension . '.js';
        } else {
            // Fallback for other cases
            $viewFilePath = $viewPath;
            $scriptFilePath = preg_replace('/\.(livewire\.php|blade\.php)$/', '.js', $viewPath);
        }

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

        // Generate script path if component has scripts
        $scriptPath = null;
        if ($parsed->hasScripts()) {
            $scriptPath = $this->getScriptPath($this->generateScriptName($viewPath, $hash));
        }

        return new CompilationResult(
            className: $className,
            classPath: $classPath,
            viewName: $viewName,
            viewPath: $compiledViewPath,
            isExternal: $parsed->isExternal,
            externalClass: $parsed->externalClass,
            hash: $hash,
            scriptPath: $scriptPath,
        );
    }

    protected function generateScriptName(string $viewPath, string $hash): string
    {
        $name = $this->getComponentNameFromPath($viewPath);
        return "{$name}_{$hash}";
    }

    protected function getScriptPath(string $scriptName): string
    {
        return $this->scriptsDirectory . '/' . $scriptName . '.js';
    }

    protected function generateFiles(CompilationResult $result, ParsedComponent $parsed): void
    {
        // Always generate the view file...
        $this->generateView($result, $parsed);

        // Only generate class file for inline components...
        if ($result->shouldGenerateClass()) {
            $this->generateClass($result, $parsed);
        }

        // Generate script file if component has scripts...
        if ($parsed->hasScripts() && $result->hasScripts()) {
            $this->generateScriptFile($result, $parsed);
        }
    }

    protected function generateScriptFile(CompilationResult $result, ParsedComponent $parsed): void
    {
        $scriptContent = $this->compileScripts($parsed->scripts);
        File::put($result->scriptPath, $scriptContent);
    }

    protected function compileScripts(array $scripts): string
    {
        $allContent = [];
        $allImports = [];

        foreach ($scripts as $script) {
            $content = $script['content'];

            // Extract imports and remaining code
            $parsed = $this->parseJavaScriptImports($content);

            $allImports = array_merge($allImports, $parsed['imports']);
            $allContent[] = $parsed['code'];
        }

        // Deduplicate imports by their full statement
        $uniqueImports = array_unique($allImports);

        // Build the final module
        $output = [];

        // Add hoisted imports at the top
        if (!empty($uniqueImports)) {
            $output[] = "// Hoisted imports";
            $output = array_merge($output, $uniqueImports);
            $output[] = ""; // Empty line after imports
        }

        // Wrap remaining code in export default function
        $output[] = "export function run() {";

        if (!empty($allContent)) {
            // Add each script content as a section
            foreach ($allContent as $index => $content) {
                if (!empty(trim($content))) {
                    $output[] = "    // Script section " . ($index + 1);
                    $output[] = $this->indentJavaScript($content, 1);
                    $output[] = "";
                }
            }
        } else {
            $output[] = "    // No script content";
        }

        $output[] = "}";

        return implode("\n", $output);
    }

    /**
     * Parse JavaScript content to extract import statements and remaining code.
     */
    protected function parseJavaScriptImports(string $content): array
    {
        $imports = [];
        $remainingLines = [];

        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $trimmedLine = trim($line);

            // Check for ES6 import statements
            if ($this->isImportStatement($trimmedLine)) {
                $imports[] = $trimmedLine;
            } else {
                $remainingLines[] = $line;
            }
        }

        return [
            'imports' => $imports,
            'code' => implode("\n", $remainingLines)
        ];
    }

    /**
     * Check if a line is an ES6 import statement.
     */
    protected function isImportStatement(string $line): bool
    {
        // Match various import patterns:
        // import foo from 'module'
        // import { foo } from 'module'
        // import * as foo from 'module'
        // import 'module' (side-effect import)
        // import foo, { bar } from 'module'

        if (empty($line)) {
            return false;
        }

        // Basic import statement pattern
        if (preg_match('/^import\s+/', $line)) {
            return true;
        }

        return false;
    }

    /**
     * Indent JavaScript code by the specified number of levels (4 spaces per level).
     */
    protected function indentJavaScript(string $code, int $levels): string
    {
        $indent = str_repeat('    ', $levels);
        $lines = explode("\n", $code);

        $indentedLines = [];
        foreach ($lines as $line) {
            if (trim($line) === '') {
                $indentedLines[] = ''; // Keep empty lines empty
            } else {
                $indentedLines[] = $indent . $line;
            }
        }

        return implode("\n", $indentedLines);
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
        } else if (view()->exists('layouts::app')) { // Provide a default layout attribute...
            $layoutAttribute = $this->generateLayoutAttribute('layouts::app', []);
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

        // Generate jsModuleSource method if component has scripts
        $jsModuleSourceMethod = '';
        if ($parsed->hasScripts() && $result->scriptPath) {
            $jsModuleSourceMethod = $this->generateJsModuleSourceMethod($parsed->scripts, $result->scriptPath);
        }

        // Escape the view path for PHP string literal
        $escapedViewPath = addcslashes($result->viewPath, "'\\");

        $renderMethod = "    public function render()
    {
        return app('view')->file('{$result->viewPath}');
    }";

        $classContent = "<?php

{$preClassSection}{$layoutAttribute}{$classAttributesSection}class {$className} extends \\Livewire\\Component
{
{$classBody}
{$jsModuleSourceMethod}
{$renderMethod}
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
        // Scripts are already extracted during parsing, so view content is clean
        $processedViewContent = $parsed->viewContent;

        // Transform computed property references if this is an inline component
        if ($parsed->hasInlineClass()) {
            $processedViewContent = $this->transformComputedPropertyReferences($processedViewContent, $parsed->frontmatter);
        }

        // Prepend any use statements to the view content if there are any
        $preClassCode = $this->extractPreClassCode($parsed->frontmatter);
        if (!empty($preClassCode)) {
            $processedViewContent = <<<HTML
            <?php
            {$preClassCode}
            ?>
            {$processedViewContent}
            HTML;
        }

        File::put($result->viewPath, $processedViewContent);

        // This is a fix for a gnarly issue: blade's compiler uses filemtimes to determine if a compiled view has become expired.
        // AND it's comparison includes equals like this: $path >= $cachedPath
        // AND file_put_contents won't update the filemtime if the contents are the same
        // THEREFORE because we are creating a blade file at the same "second" that it is compiled
        // both the source file and the cached file's filemtime's match, therefore it become's in a perpetual state
        // of always being expired. So we mutate the source file to be one second behind so that the cached
        // view file is one second ahead. Phew. this one took a minute to find lol.
        $original = filemtime($result->viewPath);
        touch($result->viewPath, $original - 1);
    }

    /**
     * Extract script tags from view content and return structured script data.
     */
    protected function extractScripts(string $viewContent): array
    {
        $scripts = [];

        // Don't process if there are no script tags
        if (!str_contains($viewContent, '<script')) {
            return $scripts;
        }

        // Skip script extraction if @script directives are present (backward compatibility)
        if (str_contains($viewContent, '@script')) {
            return $scripts;
        }

        // Match script tags with their content and closing tags
        $pattern = '/<script\b([^>]*)>(.*?)<\/script>/s';

        preg_match_all($pattern, $viewContent, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $fullScriptTag = $match[0];
            $attributes = trim($match[1]);
            $scriptContent = trim($match[2]);

            // Skip empty scripts
            if (empty($scriptContent)) {
                continue;
            }

            $scripts[] = [
                'content' => $scriptContent,
                'attributes' => $this->parseScriptAttributes($attributes),
                'fullTag' => $fullScriptTag,
            ];
        }

        return $scripts;
    }

    /**
     * Remove script tags from view content.
     */
    protected function removeScripts(string $viewContent): string
    {
        // Don't process if there are no script tags
        if (!str_contains($viewContent, '<script')) {
            return $viewContent;
        }

        // Skip script removal if @script directives are present (backward compatibility)
        if (str_contains($viewContent, '@script')) {
            return $viewContent;
        }

        // Remove script tags but preserve whitespace structure
        $pattern = '/<script\b[^>]*>.*?<\/script>/s';

        return preg_replace($pattern, '', $viewContent);
    }

    /**
     * Parse script tag attributes into structured format.
     */
    protected function parseScriptAttributes(string $attributesString): array
    {
        $attributes = [];

        if (empty(trim($attributesString))) {
            return $attributes;
        }

        // Simple attribute parsing - can be enhanced later for complex cases
        if (preg_match('/type=["\']([^"\']*)["\']/', $attributesString, $matches)) {
            $attributes['type'] = $matches[1];
        }

        if (preg_match('/src=["\']([^"\']*)["\']/', $attributesString, $matches)) {
            $attributes['src'] = $matches[1];
        }

        if (preg_match('/defer/', $attributesString)) {
            $attributes['defer'] = true;
        }

        if (preg_match('/async/', $attributesString)) {
            $attributes['async'] = true;
        }

        return $attributes;
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

    protected function generateHash(string $viewPath, int $sourceModTime): string
    {
        // Use path-only hash like Blade - consistent filename regardless of content changes
        return hash('xxh128', 'v3' . $viewPath);
    }

    protected function generateMultiFileHash(string $directory, array $sourceModTimes): string
    {
        // Use directory path only - consistent filename regardless of content changes
        return hash('xxh128', 'v3' . $directory);
    }

    protected function isMultiFileCompiled(string $directory, string $hash, array $sourceFiles, array $sourceModTimes): bool
    {
        $metadataPath = $this->getMetadataPath($directory, $hash);

        // Check if metadata file exists
        if (! file_exists($metadataPath)) {
            return false;
        }

        // Load metadata from cache or file
        $metadata = $this->loadCompilationMetadata($metadataPath);
        if (! $metadata) {
            return false;
        }

        // Check if any source files have been modified
        foreach ($sourceFiles as $index => $sourceFile) {
            if (! file_exists($sourceFile)) {
                return false;
            }

            $currentModTime = filemtime($sourceFile);
            $cachedModTime = $metadata['sourceModTimes'][$index];

            if ($currentModTime > $cachedModTime) {
                return false;
            }
        }

        // Check if compiled files still exist
        if (! file_exists($metadata['classPath']) || ! file_exists($metadata['viewPath'])) {
            return false;
        }

        // If has scripts, check script file exists
        if ($metadata['hasScripts'] && ! file_exists($metadata['scriptPath'])) {
            return false;
        }

        return true;
    }

    protected function generateClassName(string $viewPath, string $hash): string
    {
        $name = $this->getComponentNameFromPath($viewPath);
        $className = str_replace(['-', '.'], '', ucwords($name, '-.'));
        return "{$className}_{$hash}";
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

        // Generate script path if component has scripts
        $scriptPath = null;
        if ($parsed->hasScripts()) {
            $scriptPath = $this->getScriptPath($this->generateScriptName($directory, $hash));
        }

        return new CompilationResult(
            className: $className,
            classPath: $classPath,
            viewName: $viewName,
            viewPath: $compiledViewPath,
            isExternal: $parsed->isExternal,
            externalClass: $parsed->externalClass,
            hash: $hash,
            scriptPath: $scriptPath,
        );
    }

    protected function getExistingMultiFileCompilationResult(string $directory, string $hash): CompilationResult
    {
        $metadataPath = $this->getMetadataPath($directory, $hash);
        $metadata = $this->loadCompilationMetadata($metadataPath);

        return new CompilationResult(
            className: $metadata['className'],
            classPath: $metadata['classPath'],
            viewName: $metadata['viewName'],
            viewPath: $metadata['viewPath'],
            isExternal: $metadata['isExternal'],
            externalClass: $metadata['externalClass'],
            hash: $metadata['hash'],
            scriptPath: $metadata['scriptPath'] ?? null,
        );
    }

    protected function getExistingCompilationResult(string $viewPath, string $hash): CompilationResult
    {
        $metadataPath = $this->getMetadataPath($viewPath, $hash);
        $metadata = $this->loadCompilationMetadata($metadataPath);

        return new CompilationResult(
            className: $metadata['className'],
            classPath: $metadata['classPath'],
            viewName: $metadata['viewName'],
            viewPath: $metadata['viewPath'],
            isExternal: $metadata['isExternal'],
            externalClass: $metadata['externalClass'],
            hash: $metadata['hash'],
            scriptPath: $metadata['scriptPath'] ?? null,
        );
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

        // Strip ⚡ from the component name
        $basename = str_replace('⚡', '', $basename);

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
        File::ensureDirectoryExists($this->scriptsDirectory);
        File::ensureDirectoryExists($this->metadataDirectory);
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

    protected function generateJsModuleSourceMethod(array $scripts, string $scriptPath): string
    {
        // Escape the script path for PHP string literal
        $escapedScriptPath = addcslashes($scriptPath, "'\\");

        return "\n    protected function hasJsModuleSource(): bool\n    {\n        return true;\n    }\n\n    protected function jsModuleSource(): string\n    {\n        \$scriptPath = '{$escapedScriptPath}';\n        if (! file_exists(\$scriptPath)) {\n            throw new \\RuntimeException(\"Script file not found: [{\$scriptPath}]\");\n        }\n        return file_get_contents(\$scriptPath);\n    }\n\n    protected function jsModuleModifiedTime(): int\n    {\n        \$scriptPath = '{$escapedScriptPath}';\n        return file_exists(\$scriptPath) ? filemtime(\$scriptPath) : filemtime(__FILE__);\n    }\n";
    }

    protected function addDedicatedJsContent(ParsedComponent $parsed, string $jsContent): ParsedComponent
    {
        $scripts = $parsed->scripts;
        $scripts[] = [
            'content' => $jsContent,
            'attributes' => [],
            'fullTag' => '', // No full tag for dedicated JS
        ];
        return new ParsedComponent(
            $parsed->frontmatter,
            $parsed->viewContent,
            $parsed->isExternal,
            $parsed->externalClass,
            $parsed->layoutTemplate,
            $parsed->layoutData,
            $scripts,
        );
    }

    // === METADATA CACHE METHODS ===

    protected function getMetadataPath(string $pathOrDirectory, string $hash): string
    {
        $name = $this->getComponentNameFromPath($pathOrDirectory);
        return $this->metadataDirectory . '/' . $name . '_' . $hash . '.json';
    }

    protected function storeCompilationMetadata(CompilationResult $result, ParsedComponent $parsed, array $sourceFiles, array $sourceModTimes): void
    {
        $metadataPath = $this->getMetadataPath($sourceFiles[0], $result->hash);

        $metadata = [
            'version' => 'v3',
            'compiledAt' => time(),
            'sourceFiles' => $sourceFiles,
            'sourceModTimes' => $sourceModTimes,
            'className' => $result->className,
            'classPath' => $result->classPath,
            'viewName' => $result->viewName,
            'viewPath' => $result->viewPath,
            'isExternal' => $result->isExternal,
            'externalClass' => $result->externalClass,
            'hash' => $result->hash,
            'hasScripts' => $parsed->hasScripts(),
            'scriptPath' => $result->scriptPath,
        ];

        File::put($metadataPath, json_encode($metadata, JSON_PRETTY_PRINT));

        // Cache in memory for this request
        self::$metadataCache[$metadataPath] = $metadata;
    }

    protected function loadCompilationMetadata(string $metadataPath): ?array
    {
        // Check memory cache first
        if (isset(self::$metadataCache[$metadataPath])) {
            return self::$metadataCache[$metadataPath];
        }

        if (! file_exists($metadataPath)) {
            return null;
        }

        try {
            $metadata = json_decode(File::get($metadataPath), true);

            if (! is_array($metadata) || ! isset($metadata['version']) || ! in_array($metadata['version'], ['v2', 'v3'])) {
                // Invalid or old version metadata
                return null;
            }

            // Cache in memory for this request
            self::$metadataCache[$metadataPath] = $metadata;

            return $metadata;
        } catch (\Exception $e) {
            // Corrupted metadata file
            return null;
        }
    }

    public static function clearMemoryCache(): void
    {
        self::$compilationCache = [];
        self::$metadataCache = [];
    }
}
