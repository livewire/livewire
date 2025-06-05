<?php

namespace Livewire\v4\Compiler;

use Livewire\v4\Compiler\Exceptions\CompilationException;
use Livewire\v4\Compiler\Exceptions\ParseException;
use Livewire\v4\Compiler\Exceptions\InvalidComponentException;
use Illuminate\Support\Facades\File;

class SingleFileComponentCompilerUnitTest extends \Tests\TestCase
{
    protected $compiler;
    protected $tempPath;
    protected $cacheDir;

    public function setUp(): void
    {
        parent::setUp();

        $this->tempPath = sys_get_temp_dir() . '/livewire_compiler_test_' . uniqid();
        $this->cacheDir = $this->tempPath . '/cache';

        File::makeDirectory($this->tempPath, 0755, true);
        File::makeDirectory($this->cacheDir, 0755, true);

        $this->compiler = new SingleFileComponentCompiler($this->cacheDir);
    }

    protected function tearDown(): void
    {
        if (File::exists($this->tempPath)) {
            File::deleteDirectory($this->tempPath);
        }

        parent::tearDown();
    }

    public function test_can_compile_inline_component()
    {
        $componentContent = '@php
new class extends Livewire\Component {
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }
}
@endphp

<div>
    Count: {{ $count }}
    <button wire:click="increment">Increment</button>
</div>';

        $viewPath = $this->tempPath . '/counter.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $this->assertInstanceOf(CompilationResult::class, $result);
        $this->assertFalse($result->isExternal);
        $this->assertStringContainsString('Livewire\\Compiled\\Counter_', $result->className);
        $this->assertStringContainsString('livewire-compiled::counter_', $result->viewName);
        $this->assertTrue(file_exists($result->classPath));
        $this->assertTrue(file_exists($result->viewPath));
    }

    public function test_can_compile_external_component()
    {
        $componentContent = '@php(new App\Livewire\Counter)

<div>
    Count: {{ $count }}
    <button wire:click="increment">Increment</button>
</div>';

        $viewPath = $this->tempPath . '/counter.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $this->assertInstanceOf(CompilationResult::class, $result);
        $this->assertTrue($result->isExternal);
        $this->assertEquals('App\Livewire\Counter', $result->externalClass);
        $this->assertFalse(file_exists($result->classPath)); // No class file for external
        $this->assertTrue(file_exists($result->viewPath));
    }

    public function test_throws_exception_for_missing_view_file()
    {
        $this->expectException(CompilationException::class);
        $this->expectExceptionMessage('View file not found');

        $this->compiler->compile('/non/existent/file.blade.php');
    }

    public function test_throws_exception_for_invalid_component_without_php_block()
    {
        $componentContent = '<div>No PHP block</div>';

        $viewPath = $this->tempPath . '/invalid.blade.php';
        File::put($viewPath, $componentContent);

        $this->expectException(InvalidComponentException::class);
        $this->expectExceptionMessage('Component must contain either @php(new ClassName) or @php...@endphp block');

        $this->compiler->compile($viewPath);
    }

    public function test_throws_exception_for_invalid_php_block_content()
    {
        $componentContent = '@php
echo "This is not a class";
@endphp

<div>Invalid content</div>';

        $viewPath = $this->tempPath . '/invalid.blade.php';
        File::put($viewPath, $componentContent);

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Invalid component: @php block must contain a class definition');

        $this->compiler->compile($viewPath);
    }

    public function test_can_parse_external_component_with_class_suffix()
    {
        $componentContent = '@php(new App\Livewire\Counter::class)

<div>External with ::class</div>';

        $viewPath = $this->tempPath . '/external.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $this->assertTrue($result->isExternal);
        $this->assertEquals('App\Livewire\Counter', $result->externalClass);
    }

    public function test_generated_class_file_contains_correct_content()
    {
        $componentContent = '@php
new class extends Livewire\Component {
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }
}
@endphp

<div>Count: {{ $count }}</div>';

        $viewPath = $this->tempPath . '/counter.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $classContent = File::get($result->classPath);

        $this->assertStringContainsString('namespace Livewire\Compiled;', $classContent);
        $this->assertStringContainsString('extends \\Livewire\\Component', $classContent);
        $this->assertStringContainsString('public $count = 0;', $classContent);
        $this->assertStringContainsString('public function increment()', $classContent);
        $this->assertStringContainsString('public function render()', $classContent);
        $this->assertStringContainsString("return view('{$result->viewName}');", $classContent);
    }

    public function test_generated_view_file_contains_correct_content()
    {
        $componentContent = '@php
new class extends Livewire\Component {
    public $count = 0;
}
@endphp

<div>Count: {{ $count }}</div>';

        $viewPath = $this->tempPath . '/counter.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $viewContent = File::get($result->viewPath);

        $this->assertEquals('<div>Count: {{ $count }}</div>', $viewContent);
        $this->assertStringNotContainsString('@php', $viewContent);
        $this->assertStringNotContainsString('@endphp', $viewContent);
    }

    public function test_caching_works_correctly()
    {
        $componentContent = '@php
new class extends Livewire\Component {
    public $count = 0;
}
@endphp

<div>Count: {{ $count }}</div>';

        $viewPath = $this->tempPath . '/counter.blade.php';
        File::put($viewPath, $componentContent);

        // First compilation
        $result1 = $this->compiler->compile($viewPath);
        $firstModified = filemtime($result1->classPath);

        // Second compilation should use cache
        sleep(1); // Ensure time difference
        $result2 = $this->compiler->compile($viewPath);
        $secondModified = filemtime($result2->classPath);

        $this->assertEquals($result1->className, $result2->className);
        $this->assertEquals($result1->hash, $result2->hash);
        $this->assertEquals($firstModified, $secondModified); // File not regenerated
    }

    public function test_cache_invalidation_when_file_changes()
    {
        $componentContent1 = '@php
new class extends Livewire\Component {
    public $count = 0;
}
@endphp

<div>Count: {{ $count }}</div>';

        $viewPath = $this->tempPath . '/counter.blade.php';
        File::put($viewPath, $componentContent1);

        $result1 = $this->compiler->compile($viewPath);

        // Change the content
        $componentContent2 = '@php
new class extends Livewire\Component {
    public $count = 1;
}
@endphp

<div>Count: {{ $count }}</div>';

        sleep(1); // Ensure different timestamp
        File::put($viewPath, $componentContent2);

        $result2 = $this->compiler->compile($viewPath);

        $this->assertNotEquals($result1->hash, $result2->hash);
        $this->assertNotEquals($result1->className, $result2->className);
    }

    public function test_is_compiled_returns_true_for_compiled_component()
    {
        $componentContent = '@php
new class extends Livewire\Component {
    public $count = 0;
}
@endphp

<div>Count: {{ $count }}</div>';

        $viewPath = $this->tempPath . '/counter.blade.php';
        File::put($viewPath, $componentContent);

        $this->assertFalse($this->compiler->isCompiled($viewPath));

        $this->compiler->compile($viewPath);

        $this->assertTrue($this->compiler->isCompiled($viewPath));
    }

    public function test_get_compiled_path_returns_correct_path()
    {
        $componentContent = '@php
new class extends Livewire\Component {
    public $count = 0;
}
@endphp

<div>Count: {{ $count }}</div>';

        $viewPath = $this->tempPath . '/counter.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);
        $compiledPath = $this->compiler->getCompiledPath($viewPath);

        $this->assertEquals($result->classPath, $compiledPath);
    }

    public function test_handles_component_name_normalization()
    {
        $componentContent = '@php
new class extends Livewire\Component {
    public $count = 0;
}
@endphp

<div>Count: {{ $count }}</div>';

        $viewPath = $this->tempPath . '/my-special_component.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $this->assertStringContainsString('MySpecialComponent_', $result->className);
        $this->assertStringContainsString('livewire-compiled::my-special-component_', $result->viewName);
    }

    public function test_handles_named_class_definition()
    {
        $componentContent = '@php
class Counter extends Livewire\Component {
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }
}
@endphp

<div>Count: {{ $count }}</div>';

        $viewPath = $this->tempPath . '/counter.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $classContent = File::get($result->classPath);

        $this->assertStringContainsString('public $count = 0;', $classContent);
        $this->assertStringContainsString('public function increment()', $classContent);
    }

    public function test_extract_class_body_throws_exception_for_invalid_content()
    {
        $componentContent = '@php
$notAClass = "invalid";
@endphp

<div>Invalid</div>';

        $viewPath = $this->tempPath . '/invalid.blade.php';
        File::put($viewPath, $componentContent);

        // We need to manually trigger the parseComponent since the validation happens there
        $this->expectException(ParseException::class);

        $this->compiler->compile($viewPath);
    }

    public function test_ensures_cache_directories_exist()
    {
        $newCacheDir = $this->tempPath . '/new_cache';

        $this->assertFalse(file_exists($newCacheDir));

        new SingleFileComponentCompiler($newCacheDir);

        $this->assertTrue(file_exists($newCacheDir . '/classes'));
        $this->assertTrue(file_exists($newCacheDir . '/views'));
    }
}
