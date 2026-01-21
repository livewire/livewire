<?php

namespace Livewire\Compiler;

use Livewire\Component;
use Livewire\Compiler\Parser\SingleFileParser;
use Livewire\Compiler\Parser\MultiFileParser;
use Livewire\Compiler\Compiler;
use Livewire\Compiler\CacheManager;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;

class UnitTest extends \Tests\TestCase
{
    protected $tempPath;
    protected $cacheDir;

    public function setUp(): void
    {
        parent::setUp();

        $this->tempPath = sys_get_temp_dir() . '/livewire_compiler_test_' . uniqid();
        $this->cacheDir = $this->tempPath . '/cache';

        File::makeDirectory($this->tempPath, 0755, true);
        File::makeDirectory($this->cacheDir, 0755, true);
    }

    protected function tearDown(): void
    {
        if (File::exists($this->tempPath)) {
            File::deleteDirectory($this->tempPath);
        }

        parent::tearDown();
    }

    public function test_can_compile_sfc_component()
    {
        $compiler = new Compiler(new CacheManager($this->cacheDir));

        $class = $compiler->compile(__DIR__ . '/Fixtures/sfc-component.blade.php');

        $this->assertInstanceOf(Component::class, new $class);
    }

    public function test_can_parse_sfc_component()
    {
        $compiler = new Compiler(new CacheManager($this->cacheDir));

        $parser = SingleFileParser::parse($compiler, __DIR__ . '/Fixtures/sfc-component.blade.php');

        $classContents = $parser->generateClassContents('view-path.blade.php');
        $scriptContents = $parser->generateScriptContents();
        $viewContents = $parser->generateViewContents();

        $this->assertStringContainsString('new class extends Component', $classContents);
        $this->assertStringContainsString('use Livewire\Component;', $classContents);
        $this->assertStringContainsString("return app('view')->file('view-path.blade.php', \$data);", $classContents);
        $this->assertStringNotContainsString('new class extends Component', $viewContents);
        $this->assertStringNotContainsString('new class extends Component', $scriptContents);
        $this->assertStringContainsString("console.log('Hello from script');", $scriptContents);
        $this->assertStringNotContainsString("console.log('Hello from script');", $classContents);
        $this->assertStringNotContainsString("console.log('Hello from script');", $viewContents);
        $this->assertStringContainsString('<div>{{ $message }}</div>', $viewContents);
        // Ensure that use statements are also available inside the view...
        $this->assertStringContainsString('use Livewire\Component;', $viewContents);
        $this->assertStringNotContainsString('<div>{{ $message }}</div>', $classContents);
        $this->assertStringNotContainsString('<div>{{ $message }}</div>', $scriptContents);
    }

    public function test_wont_parse_blade_script()
    {
        $compiler = new Compiler(new CacheManager($this->cacheDir));

        $parser = SingleFileParser::parse($compiler, __DIR__ . '/Fixtures/sfc-component-with-blade-script.blade.php');

        $classContents = $parser->generateClassContents('view-path.blade.php');
        $scriptContents = $parser->generateScriptContents();
        $viewContents = $parser->generateViewContents();

        $this->assertStringContainsString('new class extends Component', $classContents);
        $this->assertStringContainsString('use Livewire\Component;', $classContents);
        $this->assertStringContainsString("return app('view')->file('view-path.blade.php', \$data);", $classContents);
        $this->assertStringNotContainsString('new class extends Component', $viewContents);
        // Script should NOT be extracted when wrapped in @script/@endscript
        $this->assertNull($scriptContents);
        $this->assertStringNotContainsString("console.log('Hello from script');", $classContents);
        // The script content should remain in the view portion when wrapped in @script/@endscript
        $this->assertStringContainsString("console.log('Hello from script');", $viewContents);
        $this->assertStringContainsString('<div>{{ $message }}</div>', $viewContents);
        // Ensure that use statements are also available inside the view...
        $this->assertStringContainsString('use Livewire\Component;', $viewContents);
        $this->assertStringNotContainsString('<div>{{ $message }}</div>', $classContents);
    }

    public function test_wont_parse_nested_script()
    {
        $compiler = new Compiler(new CacheManager($this->cacheDir));

        $parser = SingleFileParser::parse($compiler, __DIR__ . '/Fixtures/sfc-component-with-nested-script.blade.php');

        $classContents = $parser->generateClassContents('view-path.blade.php');
        $scriptContents = $parser->generateScriptContents();
        $viewContents = $parser->generateViewContents();

        $this->assertStringContainsString('new class extends Component', $classContents);
        $this->assertStringContainsString('use Livewire\Component;', $classContents);
        $this->assertStringContainsString("return app('view')->file('view-path.blade.php', \$data);", $classContents);
        $this->assertStringNotContainsString('new class extends Component', $viewContents);

        // Only the root-level script should be extracted
        $this->assertStringContainsString("console.log('This SHOULD be extracted - it is at root level');", $scriptContents);
        $this->assertStringNotContainsString("console.log('This should NOT be extracted - it is nested inside div');", $scriptContents);

        // The nested script should remain in the view portion
        $this->assertStringContainsString("console.log('This should NOT be extracted - it is nested inside div');", $viewContents);
        $this->assertStringContainsString('<div>', $viewContents);
        $this->assertStringContainsString('{{ $message }}', $viewContents);
    }

    public function test_wont_parse_scripts_inside_assets_or_script_directives()
    {
        $compiler = new Compiler(new CacheManager($this->cacheDir));

        $parser = SingleFileParser::parse($compiler, __DIR__ . '/Fixtures/sfc-component-with-assets-and-script-directives.blade.php');

        $classContents = $parser->generateClassContents('view-path.blade.php');
        $scriptContents = $parser->generateScriptContents();
        $viewContents = $parser->generateViewContents();

        $this->assertStringContainsString('new class extends Component', $classContents);
        $this->assertStringContainsString('use Livewire\Component;', $classContents);
        $this->assertStringContainsString("return app('view')->file('view-path.blade.php', \$data);", $classContents);
        $this->assertStringNotContainsString('new class extends Component', $viewContents);

        // Scripts inside @assets/@endassets should NOT be extracted
        $this->assertNull($scriptContents);
        $this->assertStringNotContainsString("console.log('This should NOT be extracted - it is inside @assets');", $classContents);
        $this->assertStringNotContainsString("console.log('This should NOT be extracted - it is inside @script');", $classContents);

        // Both scripts should remain in the view portion when wrapped in directives
        $this->assertStringContainsString("console.log('This should NOT be extracted - it is inside @assets');", $viewContents);
        $this->assertStringContainsString("console.log('This should NOT be extracted - it is inside @script');", $viewContents);
        $this->assertStringContainsString('@assets', $viewContents);
        $this->assertStringContainsString('@endassets', $viewContents);
        $this->assertStringContainsString('@script', $viewContents);
        $this->assertStringContainsString('@endscript', $viewContents);
    }

    public function test_script_hoists_imports_and_wraps_in_export_function()
    {
        $compiler = new Compiler(new CacheManager($this->cacheDir));

        $parser = SingleFileParser::parse($compiler, __DIR__ . '/Fixtures/sfc-component-with-imports.blade.php');

        $scriptContents = $parser->generateScriptContents();

        // Check that imports are hoisted to the top
        $this->assertStringContainsString("import { Alpine } from 'alpinejs'", $scriptContents);
        $this->assertStringContainsString("import { debounce } from './utils'", $scriptContents);

        // Check that the script is wrapped in export function run()
        $this->assertMatchesRegularExpression('/export function run\([^)]*\) \{/', $scriptContents);
        $this->assertStringContainsString("console.log('Component initialized');", $scriptContents);

        // Ensure imports appear before the export function
        $importPos = strpos($scriptContents, 'import');
        $exportPos = strpos($scriptContents, 'export function run');
        $this->assertLessThan($exportPos, $importPos, 'Imports should appear before export function');

        // Ensure the function body contains the actual logic (not the imports)
        preg_match('/export function run\([^)]*\) \{(.+)\}/s', $scriptContents, $matches);
        $functionBody = $matches[1] ?? '';
        $this->assertStringNotContainsString('import', $functionBody, 'Import statements should not be in function body');
        $this->assertStringContainsString("console.log('Component initialized');", $functionBody);
    }

    public function test_script_wraps_in_export_function_even_without_imports()
    {
        $compiler = new Compiler(new CacheManager($this->cacheDir));

        $parser = SingleFileParser::parse($compiler, __DIR__ . '/Fixtures/sfc-component.blade.php');

        $scriptContents = $parser->generateScriptContents();

        // Check that the script is wrapped in export function run() even without imports
        $this->assertMatchesRegularExpression('/export function run\([^)]*\) \{/', $scriptContents);
        $this->assertStringContainsString("console.log('Hello from script');", $scriptContents);

        // Ensure no import statements are present
        $this->assertStringNotContainsString('import', $scriptContents);
    }

    public function test_parser_adds_trailing_semicolon_to_class_contents()
    {
        $compiler = new Compiler(new CacheManager($this->cacheDir));

        $parser = SingleFileParser::parse($compiler, __DIR__ . '/Fixtures/sfc-component-without-trailing-semicolon.blade.php');

        $classContents = $parser->generateClassContents('view-path.blade.php');

        $this->assertStringContainsString('};', $classContents);
    }

    public function test_can_compile_mfc_component()
    {
        $compiler = new Compiler(new CacheManager($this->cacheDir));

        $class = $compiler->compile(__DIR__ . '/Fixtures/mfc-component');

        $this->assertInstanceOf(Component::class, new $class);
    }

    public function test_can_parse_mfc_component()
    {
        $compiler = new Compiler(new CacheManager($this->cacheDir));

        $parser = MultiFileParser::parse($compiler, __DIR__ . '/Fixtures/mfc-component');

        $classContents = $parser->generateClassContents('view-path.blade.php');
        $scriptContents = $parser->generateScriptContents();
        $viewContents = $parser->generateViewContents();

        $this->assertStringContainsString('new class extends Component', $classContents);
        $this->assertStringContainsString('use Livewire\Component;', $classContents);
        $this->assertStringContainsString("return app('view')->file('view-path.blade.php', \$data);", $classContents);
        $this->assertStringNotContainsString('new class extends Component', $viewContents);
        $this->assertStringNotContainsString('new class extends Component', $scriptContents);
        $this->assertStringContainsString("console.log('Hello from script');", $scriptContents);
        $this->assertStringNotContainsString("console.log('Hello from script');", $classContents);
        $this->assertStringNotContainsString("console.log('Hello from script');", $viewContents);
        $this->assertStringContainsString('<div>{{ $message }}</div>', $viewContents);
        // Ensure that use statements are NOT available inside the view when parsing a multi-file component...
        $this->assertStringNotContainsString('use Livewire\Component;', $viewContents);
        $this->assertStringNotContainsString('<div>{{ $message }}</div>', $classContents);
        $this->assertStringNotContainsString('<div>{{ $message }}</div>', $scriptContents);
    }

    public function test_can_parse_placeholder_directive()
    {
        $compiler = new Compiler($cacheManager = new CacheManager($this->cacheDir));

        $class = $compiler->compile(__DIR__ . '/Fixtures/sfc-component-with-placeholder.blade.php');

        $classContents = file_get_contents($cacheManager->getClassPath(__DIR__ . '/Fixtures/sfc-component-with-placeholder.blade.php'));
        $viewContents = file_get_contents($cacheManager->getViewPath(__DIR__ . '/Fixtures/sfc-component-with-placeholder.blade.php'));
        $placeholderContents = file_get_contents($cacheManager->getPlaceholderPath(__DIR__ . '/Fixtures/sfc-component-with-placeholder.blade.php'));

        $this->assertStringNotContainsString('@placeholder', $viewContents);
        $this->assertStringContainsString('public function placeholder()', $classContents);
        $this->assertStringContainsString('Loading...', $placeholderContents);
    }

    public function test_ignores_placeholders_in_islands()
    {
        $compiler = new Compiler($cacheManager = new CacheManager($this->cacheDir));

        $class = $compiler->compile(__DIR__ . '/Fixtures/sfc-component-with-placeholder-in-island.blade.php');

        $classContents = file_get_contents($cacheManager->getClassPath(__DIR__ . '/Fixtures/sfc-component-with-placeholder-in-island.blade.php'));
        $viewContents = file_get_contents($cacheManager->getViewPath(__DIR__ . '/Fixtures/sfc-component-with-placeholder-in-island.blade.php'));

        $this->assertStringContainsString('@placeholder', $viewContents);
        $this->assertStringNotContainsString('public function placeholder()', $classContents);
    }

    public function test_can_re_compile_simple_sfc_component()
    {
        $compiler = new Compiler(new CacheManager($this->cacheDir));

        $class = $compiler->compile(__DIR__ . '/Fixtures/sfc-component.blade.php');
        $class = $compiler->compile(__DIR__ . '/Fixtures/sfc-component.blade.php');

        $this->assertInstanceOf(Component::class, new $class);
    }

    public function test_compiler_will_recompile_if_source_file_is_older_than_compiled_file()
    {
        $compiler = new Compiler($cacheManager = new CacheManager($this->cacheDir));

        // First compilation
        $compiler->compile($sourcePath = __DIR__ . '/Fixtures/sfc-component.blade.php');

        $compiledPath = $cacheManager->getClassPath($sourcePath);

        // Set the compiled file modification time to 10 seconds ago...
        $staleFileMtime = filemtime($sourcePath) - 10;

        touch($compiledPath, $staleFileMtime);

        $this->assertEquals($staleFileMtime, filemtime($compiledPath));

        // Compile again
        $compiler->compile($sourcePath);

        // Assert the file was modified
        $this->assertNotEquals($staleFileMtime, filemtime($compiledPath));

        // Set the compiled file modification time to 10 seconds ago...
        $freshFileMtime = filemtime($sourcePath) + 10;

        touch($compiledPath, $freshFileMtime);

        $compiler->compile($sourcePath);

        $this->assertEquals($freshFileMtime, filemtime($compiledPath));
    }

    public function test_can_hook_into_sfc_compilation()
    {
        $compiler = new Compiler($cacheManager = new CacheManager($this->cacheDir));

        $compiler->prepareViewsForCompilationUsing(function ($contents) {
            return str_replace('div', 'span', $contents);
        });

        $compiler->compile($sourcePath = __DIR__ . '/Fixtures/sfc-component.blade.php');

        $viewContents = file_get_contents($cacheManager->getViewPath($sourcePath));

        $this->assertStringContainsString('<span>{{ $message }}</span>', $viewContents);
    }

    public function test_can_hook_into_mfc_compilation()
    {
        $compiler = new Compiler($cacheManager = new CacheManager($this->cacheDir));

        $compiler->prepareViewsForCompilationUsing(function ($contents) {
            return str_replace('div', 'span', $contents);
        });

        $compiler->compile($sourcePath = __DIR__ . '/Fixtures/mfc-component');

        $viewContents = file_get_contents($cacheManager->getViewPath($sourcePath));

        $this->assertStringContainsString('<span>{{ $message }}</span>', $viewContents);
    }

    public function test_clear_compiled_files_deletes_cache_directory()
    {
        $cacheManager = new CacheManager($this->cacheDir);
        $compiler = new Compiler($cacheManager);

        // Compile a component to create the cache directories
        $compiler->compile(__DIR__ . '/Fixtures/sfc-component.blade.php');

        // Verify the directories exist
        $this->assertTrue(is_dir($this->cacheDir . '/classes'));
        $this->assertTrue(is_dir($this->cacheDir . '/views'));

        // Clear the compiled files
        $cacheManager->clearCompiledFiles();

        // Verify the cache directory is deleted
        $this->assertFalse(is_dir($this->cacheDir));
    }

    public function test_directories_are_lazily_recreated_after_clearing()
    {
        $cacheManager = new CacheManager($this->cacheDir);
        $compiler = new Compiler($cacheManager);

        // Compile a component to create the cache directories
        $compiler->compile(__DIR__ . '/Fixtures/sfc-component.blade.php');

        // Clear the compiled files
        $cacheManager->clearCompiledFiles();

        // Verify the cache directory is deleted
        $this->assertFalse(is_dir($this->cacheDir));

        // Compile again - this should lazily recreate the directories
        $class = $compiler->compile(__DIR__ . '/Fixtures/sfc-component.blade.php');

        // Verify the directories were recreated
        $this->assertTrue(is_dir($this->cacheDir . '/classes'));
        $this->assertTrue(is_dir($this->cacheDir . '/views'));

        // Verify the component still works
        $this->assertInstanceOf(Component::class, new $class);
    }

    #[DataProvider('classReturnProvider')]
    public function test_anonymous_class_has_return_statement_added_if_required($classContents, $expectedOutput)
    {
        $parser = new SingleFileParser(
            path: '',
            contents: '',
            scriptPortion: null,
            stylePortion: null,
            globalStylePortion: null,
            classPortion: $classContents,
            placeholderPortion: null,
            viewPortion: '',
        );

        $generatedClassContents = $parser->generateClassContents();

        $this->assertEquals($expectedOutput, $generatedClassContents);
    }

    public static function classReturnProvider()
    {
        return [
            [
                <<<'EOT'
                <?php

                return new class extends \Livewire\Component
                {
                    public $message = 'Hello World';
                };
                ?>
                EOT,
                <<<'EOT'
                <?php

                return new class extends \Livewire\Component
                {
                    public $message = 'Hello World';
                };

                EOT,
            ],
            [
                <<<'EOT'
                <?php

                new class extends \Livewire\Component
                {
                    public $message = 'Hello World';
                };
                ?>
                EOT,
                <<<'EOT'
                <?php

                return new class extends \Livewire\Component
                {
                    public $message = 'Hello World';
                };

                EOT,
            ],
            [
                <<<'EOT'
                <?php

                return new class extends \Livewire\Component
                {
                    public $message = 'Hello World';
                };
                ?>
                EOT,
                <<<'EOT'
                <?php

                return new class extends \Livewire\Component
                {
                    public $message = 'Hello World';
                };

                EOT,
            ],
            [
                <<<'EOT'
                <?php

                use Livewire\Component;

                new class extends Component {
                    public function getData()
                    {
                        return new Collection([]);
                    }
                };
                ?>
                EOT,
                <<<'EOT'
                <?php

                use Livewire\Component;

                return new class extends Component {
                    public function getData()
                    {
                        return new Collection([]);
                    }
                };

                EOT,
            ],
            [
                <<<'EOT'
                <?php

                use Livewire\Component;

                new class extends Component {
                    public function getData()
                    {
                        return new class extends Model {
                            protected $table = 'users';
                        };
                    }
                };
                ?>
                EOT,
                <<<'EOT'
                <?php

                use Livewire\Component;

                return new class extends Component {
                    public function getData()
                    {
                        return new class extends Model {
                            protected $table = 'users';
                        };
                    }
                };

                EOT,
            ],
            [
                <<<'EOT'
                <?php

                use Livewire\Attributes\Layout;
                use Livewire\Component;

                new #[Layout('layouts.app')] class extends Component {
                    public function getData()
                    {
                        return new class extends Model {
                            protected $table = 'users';
                        };
                    }
                };
                ?>
                EOT,
                <<<'EOT'
                <?php

                use Livewire\Attributes\Layout;
                use Livewire\Component;

                return new #[Layout('layouts.app')] class extends Component {
                    public function getData()
                    {
                        return new class extends Model {
                            protected $table = 'users';
                        };
                    }
                };

                EOT,
            ],
            [
                <<<'EOT'
                <?php

                use Livewire\Attributes\Layout;
                use Livewire\Component;

                new
                #[Layout('layouts.app')]
                class extends Component {
                    public function getData()
                    {
                        return new class extends Model {
                            protected $table = 'users';
                        };
                    }
                };
                ?>
                EOT,
                <<<'EOT'
                <?php

                use Livewire\Attributes\Layout;
                use Livewire\Component;

                return new
                #[Layout('layouts.app')]
                class extends Component {
                    public function getData()
                    {
                        return new class extends Model {
                            protected $table = 'users';
                        };
                    }
                };

                EOT,
            ],
        ];
    }
}
