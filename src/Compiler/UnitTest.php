<?php

namespace Livewire\Compiler;

use Livewire\Component;
use Livewire\Compiler\Parser\SingleFileParser;
use Livewire\Compiler\Parser\MultiFileParser;
use Livewire\Compiler\Compiler;
use Livewire\Compiler\CacheManager;
use Illuminate\Support\Facades\File;

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
        $parser = SingleFileParser::parse(__DIR__ . '/Fixtures/sfc-component.blade.php');

        $classContents = $parser->generateClassContents('view-path.blade.php');
        $scriptContents = $parser->generateScriptContents();
        $viewContents = $parser->generateViewContents();

        $this->assertStringContainsString('new class extends Component', $classContents);
        $this->assertStringContainsString('use Livewire\Component;', $classContents);
        $this->assertStringContainsString("return app('view')->file('view-path.blade.php');", $classContents);
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

    public function test_parser_adds_trailing_semicolon_to_class_contents()
    {
        $parser = SingleFileParser::parse(__DIR__ . '/Fixtures/sfc-component-without-trailing-semicolon.blade.php');

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
        $parser = MultiFileParser::parse(__DIR__ . '/Fixtures/mfc-component');

        $classContents = $parser->generateClassContents('view-path.blade.php');
        $scriptContents = $parser->generateScriptContents();
        $viewContents = $parser->generateViewContents();

        $this->assertStringContainsString('new class extends Component', $classContents);
        $this->assertStringContainsString('use Livewire\Component;', $classContents);
        $this->assertStringContainsString("return app('view')->file('view-path.blade.php');", $classContents);
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
}
