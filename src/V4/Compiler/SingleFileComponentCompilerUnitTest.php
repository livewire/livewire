<?php

namespace Livewire\V4\Compiler;

use Livewire\V4\Compiler\Exceptions\CompilationException;
use Livewire\V4\Compiler\Exceptions\ParseException;
use Livewire\V4\Compiler\Exceptions\InvalidComponentException;
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

    public function test_can_compile_component_with_layout_directive()
    {
        $componentContent = '@layout(\'layouts.app\')

@php
new class extends Livewire\Component {
    public $count = 0;
}
@endphp

<div>Count: {{ $count }}</div>';

        $viewPath = $this->tempPath . '/counter.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $this->assertInstanceOf(CompilationResult::class, $result);
        $this->assertTrue(file_exists($result->classPath));

        $classContent = File::get($result->classPath);
        $this->assertStringContainsString('#[\\Livewire\\Attributes\\Layout(\'layouts.app\')]', $classContent);
    }

    public function test_can_compile_component_with_layout_and_data()
    {
        $componentContent = '@layout(\'layouts.app\', [\'title\' => \'Dashboard\', \'active\' => true])

@php
new class extends Livewire\Component {
    public $count = 0;
}
@endphp

<div>Count: {{ $count }}</div>';

        $viewPath = $this->tempPath . '/counter.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $classContent = File::get($result->classPath);
        $this->assertStringContainsString('#[\\Livewire\\Attributes\\Layout(\'layouts.app\', [\'title\' => \'Dashboard\', \'active\' => true])]', $classContent);
    }

    public function test_layout_directive_is_removed_from_view_content()
    {
        $componentContent = '@layout(\'layouts.app\')

@php
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
        $this->assertStringNotContainsString('@layout', $viewContent);
    }

    public function test_external_component_with_layout_directive()
    {
        $componentContent = '@layout(\'layouts.admin\')

@php(new App\Livewire\AdminCounter)

<div>Admin Counter</div>';

        $viewPath = $this->tempPath . '/admin-counter.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $this->assertTrue($result->isExternal);
        $this->assertEquals('App\Livewire\AdminCounter', $result->externalClass);

        // External components don't generate class files, so layout is stored but not compiled
        $this->assertFalse(file_exists($result->classPath));

        $viewContent = File::get($result->viewPath);
        $this->assertStringNotContainsString('@layout', $viewContent);
    }

    public function test_component_without_layout_works_as_before()
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

        $classContent = File::get($result->classPath);
        $this->assertStringNotContainsString('#[\\Livewire\\Attributes\\Layout', $classContent);
        $this->assertStringContainsString('class', $classContent);
        $this->assertStringContainsString('extends \\Livewire\\Component', $classContent);
    }

    public function test_transforms_naked_script_tags_to_script_directives()
    {
        $componentContent = '@php
new class extends Livewire\Component {
    public $count = 0;
}
@endphp

<div>
    Count: {{ $count }}
</div>

<script>
    console.log("Hello from naked script");
</script>';

        $viewPath = $this->tempPath . '/component-with-script.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $viewContent = File::get($result->viewPath);
        $this->assertStringContainsString('@script', $viewContent);
        $this->assertStringContainsString('@endscript', $viewContent);
        $this->assertStringContainsString('console.log("Hello from naked script");', $viewContent);
    }

    public function test_does_not_transform_already_wrapped_script_directives()
    {
        $componentContent = '@php
new class extends Livewire\Component {
    public $count = 0;
}
@endphp

<div>
    Count: {{ $count }}
</div>

@script
<script>
    console.log("Already wrapped script");
</script>
@endscript';

        $viewPath = $this->tempPath . '/component-with-wrapped-script.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $viewContent = File::get($result->viewPath);
        // Should contain the original @script directives exactly once each
        $this->assertEquals(1, substr_count($viewContent, '@script'));
        $this->assertEquals(1, substr_count($viewContent, '@endscript'));
        $this->assertStringContainsString('console.log("Already wrapped script");', $viewContent);
    }

    public function test_transforms_multiple_naked_scripts()
    {
        $componentContent = '@php
new class extends Livewire\Component {
    public $count = 0;
}
@endphp

<div>
    Count: {{ $count }}
</div>

<script>
    console.log("First script");
</script>

<script type="module">
    console.log("Second script");
</script>';

        $viewPath = $this->tempPath . '/component-with-multiple-scripts.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $viewContent = File::get($result->viewPath);
        $this->assertEquals(2, substr_count($viewContent, '@script'));
        $this->assertEquals(2, substr_count($viewContent, '@endscript'));
        $this->assertStringContainsString('console.log("First script");', $viewContent);
        $this->assertStringContainsString('console.log("Second script");', $viewContent);
    }

    public function test_skips_empty_script_tags()
    {
        $componentContent = '@php
new class extends Livewire\Component {
    public $count = 0;
}
@endphp

<div>
    Count: {{ $count }}
</div>

<script></script>
<script>   </script>
<script>
    console.log("Non-empty script");
</script>';

        $viewPath = $this->tempPath . '/component-with-empty-scripts.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $viewContent = File::get($result->viewPath);
        // Should only wrap the non-empty script
        $this->assertEquals(1, substr_count($viewContent, '@script'));
        $this->assertEquals(1, substr_count($viewContent, '@endscript'));
        $this->assertStringContainsString('console.log("Non-empty script");', $viewContent);
    }

    public function test_preserves_script_attributes()
    {
        $componentContent = '@php
new class extends Livewire\Component {
    public $count = 0;
}
@endphp

<div>
    Count: {{ $count }}
</div>

<script type="module" defer async>
    console.log("Script with attributes");
</script>';

        $viewPath = $this->tempPath . '/component-with-script-attributes.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $viewContent = File::get($result->viewPath);
        $this->assertStringContainsString('@script', $viewContent);
        $this->assertStringContainsString('type="module"', $viewContent);
        $this->assertStringContainsString('defer', $viewContent);
        $this->assertStringContainsString('async', $viewContent);
    }

    public function test_does_not_transform_when_no_scripts_present()
    {
        $componentContent = '@php
new class extends Livewire\Component {
    public $count = 0;
}
@endphp

<div>
    Count: {{ $count }}
</div>';

        $viewPath = $this->tempPath . '/component-without-script.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $viewContent = File::get($result->viewPath);

        $this->assertEquals("<div>\n    Count: {{ \$count }}\n</div>", $viewContent);
        $this->assertStringNotContainsString('@script', $viewContent);
    }

    public function test_can_compile_component_with_inline_partials()
    {
        $componentContent = '@php
new class extends Livewire\Component {
    public $items = ["foo", "bar"];
}
@endphp

<div>
    <h1>Items:</h1>

    @partial("item-list")
    <ul>
        @foreach($items as $item)
            <li>{{ $item }}</li>
        @endforeach
    </ul>
    @endpartial
</div>';

        $viewPath = $this->tempPath . '/component-with-partials.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $this->assertInstanceOf(CompilationResult::class, $result);
        $this->assertTrue(file_exists($result->viewPath));

        // Check that the main view content has been transformed
        $viewContent = File::get($result->viewPath);
        $this->assertStringContainsString('@partial(\'item-list\', \'livewire-compiled::partial_item-list_', $viewContent);
        $this->assertStringNotContainsString('@endpartial', $viewContent);

        // Check that partial view file was created
        $partialFiles = glob($this->cacheDir . '/views/partial_item-list_*.blade.php');
        $this->assertCount(1, $partialFiles);

        // Check partial content
        $partialContent = File::get($partialFiles[0]);
        $this->assertStringContainsString('<ul>', $partialContent);
        $this->assertStringContainsString('@foreach($items as $item)', $partialContent);
        $this->assertStringContainsString('<li>{{ $item }}</li>', $partialContent);
    }

    public function test_can_compile_component_with_inline_partials_with_data()
    {
        $componentContent = '@php
new class extends Livewire\Component {
    public $title = "My List";
}
@endphp

<div>
    @partial("header", ["subtitle" => "Welcome"])
    <h1>{{ $title }}</h1>
    <p>{{ $subtitle }}</p>
    @endpartial
</div>';

        $viewPath = $this->tempPath . '/component-with-partial-data.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        // Check that the main view content includes the data parameter
        $viewContent = File::get($result->viewPath);
        $this->assertStringContainsString('@partial(\'header\', \'livewire-compiled::partial_header_', $viewContent);
        $this->assertStringContainsString('["subtitle" => "Welcome"]', $viewContent);
    }

    public function test_can_compile_component_with_multiple_inline_partials()
    {
        $componentContent = '@php
new class extends Livewire\Component {
    public $users = ["Alice", "Bob"];
}
@endphp

<div>
    @partial("header")
    <h1>Users</h1>
    @endpartial

    @partial("user-list")
    <ul>
        @foreach($users as $user)
            <li>{{ $user }}</li>
        @endforeach
    </ul>
    @endpartial

    @partial("footer")
    <p>© 2024</p>
    @endpartial
</div>';

        $viewPath = $this->tempPath . '/component-with-multiple-partials.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        // Check that all partials were processed
        $viewContent = File::get($result->viewPath);
        $this->assertStringContainsString('@partial(\'header\', \'livewire-compiled::partial_header_', $viewContent);
        $this->assertStringContainsString('@partial(\'user-list\', \'livewire-compiled::partial_user-list_', $viewContent);
        $this->assertStringContainsString('@partial(\'footer\', \'livewire-compiled::partial_footer_', $viewContent);

        // Check that all partial files were created
        $headerFiles = glob($this->cacheDir . '/views/partial_header_*.blade.php');
        $userListFiles = glob($this->cacheDir . '/views/partial_user-list_*.blade.php');
        $footerFiles = glob($this->cacheDir . '/views/partial_footer_*.blade.php');

        $this->assertCount(1, $headerFiles);
        $this->assertCount(1, $userListFiles);
        $this->assertCount(1, $footerFiles);
    }

    public function test_inline_partials_work_with_external_components()
    {
        $componentContent = '@php(new App\Livewire\ExternalComponent)

<div>
    @partial("content")
    <h1>External Component</h1>
    <p>This is from an external component</p>
    @endpartial
</div>';

        $viewPath = $this->tempPath . '/external-with-partials.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $this->assertTrue($result->isExternal);
        $this->assertEquals('App\Livewire\ExternalComponent', $result->externalClass);

        // Check that partial was processed
        $viewContent = File::get($result->viewPath);
        $this->assertStringContainsString('@partial(\'content\', \'livewire-compiled::partial_content_', $viewContent);

        // Check that partial file was created
        $partialFiles = glob($this->cacheDir . '/views/partial_content_*.blade.php');
        $this->assertCount(1, $partialFiles);
    }

    public function test_inline_partials_work_with_layout_directive()
    {
        $componentContent = '@layout(\'layouts.app\')

@php
new class extends Livewire\Component {
    public $message = "Hello";
}
@endphp

<div>
    @partial("greeting")
    <h1>{{ $message }}</h1>
    @endpartial
</div>';

        $viewPath = $this->tempPath . '/component-with-layout-and-partials.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        // Check that layout attribute was added to class
        $classContent = File::get($result->classPath);
        $this->assertStringContainsString('#[\\Livewire\\Attributes\\Layout(\'layouts.app\')]', $classContent);

        // Check that partial was processed
        $viewContent = File::get($result->viewPath);
        $this->assertStringContainsString('@partial(\'greeting\', \'livewire-compiled::partial_greeting_', $viewContent);
        $this->assertStringNotContainsString('@layout', $viewContent);

        // Check that partial file was created
        $partialFiles = glob($this->cacheDir . '/views/partial_greeting_*.blade.php');
        $this->assertCount(1, $partialFiles);
    }

    public function test_parsed_component_has_inline_partials_method()
    {
        $componentContent = '@php
new class extends Livewire\Component {
    public $count = 0;
}
@endphp

<div>
    @partial("counter")
    <span>{{ $count }}</span>
    @endpartial
</div>';

        $viewPath = $this->tempPath . '/test-parsed-partials.blade.php';
        File::put($viewPath, $componentContent);

        // We need to access the parseComponent method through reflection or by using compile
        $result = $this->compiler->compile($viewPath);

        // Verify files were created which indicates partials were parsed
        $partialFiles = glob($this->cacheDir . '/views/partial_counter_*.blade.php');
        $this->assertCount(1, $partialFiles);

        // Verify the partial content
        $partialContent = File::get($partialFiles[0]);
        $this->assertStringContainsString('<span>{{ $count }}</span>', $partialContent);
    }

    public function test_component_without_inline_partials_works_as_before()
    {
        $componentContent = '@php
new class extends Livewire\Component {
    public $count = 0;
}
@endphp

<div>Count: {{ $count }}</div>';

        $viewPath = $this->tempPath . '/no-partials.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $this->assertInstanceOf(CompilationResult::class, $result);

        // Check that no partial files were created
        $partialFiles = glob($this->cacheDir . '/views/partial_*.blade.php');
        $this->assertCount(0, $partialFiles);

        // Check view content is unchanged
        $viewContent = File::get($result->viewPath);
        $this->assertEquals('<div>Count: {{ $count }}</div>', $viewContent);
    }

    public function test_generated_class_contains_partial_lookup_property()
    {
        $componentContent = '@php
new class extends Livewire\Component {
    public $items = ["foo", "bar"];
}
@endphp

<div>
    @partial("item-list")
    <ul>
        @foreach($items as $item)
            <li>{{ $item }}</li>
        @endforeach
    </ul>
    @endpartial

    @partial("header")
    <h1>Items</h1>
    @endpartial
</div>';

        $viewPath = $this->tempPath . '/component-with-lookup.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        // Check that class file contains partialLookup property
        $classContent = File::get($result->classPath);

        $this->assertStringContainsString('protected $partialLookup = [', $classContent);
        $this->assertStringContainsString("'item-list' => 'livewire-compiled::partial_item-list_", $classContent);
        $this->assertStringContainsString("'header' => 'livewire-compiled::partial_header_", $classContent);
    }

    public function test_class_without_partials_does_not_have_lookup_property()
    {
        $componentContent = '@php
new class extends Livewire\Component {
    public $count = 0;
}
@endphp

<div>Count: {{ $count }}</div>';

        $viewPath = $this->tempPath . '/no-partials-class.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        // Check that class file does NOT contain partialLookup property
        $classContent = File::get($result->classPath);
        $this->assertStringNotContainsString('partialLookup', $classContent);
    }

    public function test_preserves_use_statements_in_compiled_class()
    {
        $componentContent = '@php

use App\Models\Conversation;
use Illuminate\Support\Collection;

new class extends Livewire\Component {
    public Conversation $conversation;
    public Collection $messages;

    public function loadMessages()
    {
        $this->messages = collect([]);
    }
}
@endphp

<div>
    <h1>Chat</h1>
</div>';

        $viewPath = $this->tempPath . '/chat.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $classContent = File::get($result->classPath);

        // Check that use statements are preserved in the compiled class
        $this->assertStringContainsString('use App\Models\Conversation;', $classContent);
        $this->assertStringContainsString('use Illuminate\Support\Collection;', $classContent);

        // Check that the class properties use the imported types
        $this->assertStringContainsString('public Conversation $conversation;', $classContent);
        $this->assertStringContainsString('public Collection $messages;', $classContent);

        // Check that the class method is preserved
        $this->assertStringContainsString('public function loadMessages()', $classContent);
    }

    public function test_preserves_use_statements_with_aliases_in_compiled_class()
    {
        $componentContent = '@php

use App\Models\User as AppUser;
use Illuminate\Support\Collection as LaravelCollection;
use App\Events\MessageSent;

new class extends Livewire\Component {
    public AppUser $user;
    public LaravelCollection $items;

    public function sendMessage()
    {
        event(new MessageSent());
    }
}
@endphp

<div>
    <h1>User Profile</h1>
</div>';

        $viewPath = $this->tempPath . '/user-profile.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $classContent = File::get($result->classPath);

        // Check that use statements with aliases are preserved
        $this->assertStringContainsString('use App\Models\User as AppUser;', $classContent);
        $this->assertStringContainsString('use Illuminate\Support\Collection as LaravelCollection;', $classContent);
        $this->assertStringContainsString('use App\Events\MessageSent;', $classContent);

        // Check that the class properties use the aliased types
        $this->assertStringContainsString('public AppUser $user;', $classContent);
        $this->assertStringContainsString('public LaravelCollection $items;', $classContent);

        // Check that the method uses the imported class
        $this->assertStringContainsString('event(new MessageSent());', $classContent);
    }

    public function test_works_without_use_statements()
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

        $viewPath = $this->tempPath . '/simple-counter.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $classContent = File::get($result->classPath);

        // Should work fine without use statements
        $this->assertStringContainsString('namespace Livewire\Compiled;', $classContent);
        $this->assertStringContainsString('extends \\Livewire\\Component', $classContent);
        $this->assertStringContainsString('public $count = 0;', $classContent);

        // Should not have any use statements
        $this->assertStringNotContainsString('use ', $classContent);
    }

    public function test_preserves_use_statements_in_external_components()
    {
        // External components don't generate class files, and they shouldn't have use statements
        // in the view file since they reference external classes
        $componentContent = '@php(new App\Livewire\PostComponent)

<div>
    <h1>Post Component</h1>
</div>';

        $viewPath = $this->tempPath . '/post-component.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        // External components don't generate class files
        $this->assertTrue($result->isExternal);
        $this->assertFalse(file_exists($result->classPath));

        // But the view should be generated properly
        $this->assertTrue(file_exists($result->viewPath));

        $viewContent = File::get($result->viewPath);
        $this->assertEquals('<div>
    <h1>Post Component</h1>
</div>', $viewContent);
    }

    public function test_can_compile_component_with_traditional_php_tags()
    {
        $componentContent = '<?php

use App\Models\Product;

new class extends Livewire\Component {
    public Product $product;
    public $quantity = 1;

    public function addToCart()
    {
        // Add to cart logic
    }
}
?>

<div>
    <h1>{{ $product->name }}</h1>
    <p>Quantity: {{ $quantity }}</p>
    <button wire:click="addToCart">Add to Cart</button>
</div>';

        $viewPath = $this->tempPath . '/product.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $this->assertInstanceOf(CompilationResult::class, $result);
        $this->assertFalse($result->isExternal);
        $this->assertTrue(file_exists($result->classPath));
        $this->assertTrue(file_exists($result->viewPath));

        $classContent = File::get($result->classPath);

        // Check that use statements are preserved
        $this->assertStringContainsString('use App\Models\Product;', $classContent);

        // Check that class properties and methods are preserved
        $this->assertStringContainsString('public Product $product;', $classContent);
        $this->assertStringContainsString('public $quantity = 1;', $classContent);
        $this->assertStringContainsString('public function addToCart()', $classContent);

        // Check that view content doesn't contain PHP tags
        $viewContent = File::get($result->viewPath);
        $this->assertStringNotContainsString('<?php', $viewContent);
        $this->assertStringNotContainsString('?>', $viewContent);
        $this->assertStringContainsString('<h1>{{ $product->name }}</h1>', $viewContent);
    }

    public function test_can_compile_external_component_with_traditional_php_tags()
    {
        $componentContent = '<?php(new App\Livewire\ProductComponent) ?>

<div>
    <h1>External Product Component</h1>
</div>';

        $viewPath = $this->tempPath . '/external-product.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $this->assertInstanceOf(CompilationResult::class, $result);
        $this->assertTrue($result->isExternal);
        $this->assertEquals('App\Livewire\ProductComponent', $result->externalClass);
        $this->assertFalse(file_exists($result->classPath)); // No class file for external
        $this->assertTrue(file_exists($result->viewPath));

        $viewContent = File::get($result->viewPath);
        $this->assertStringNotContainsString('<?php', $viewContent);
        $this->assertStringNotContainsString('?>', $viewContent);
    }

    public function test_traditional_php_tags_work_with_layout_directive()
    {
        $componentContent = '@layout(\'layouts.shop\')

<?php
new class extends Livewire\Component {
    public $total = 0;
}
?>

<div>
    <h1>Shopping Cart</h1>
    <p>Total: ${{ $total }}</p>
</div>';

        $viewPath = $this->tempPath . '/cart.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $classContent = File::get($result->classPath);
        $this->assertStringContainsString('#[\\Livewire\\Attributes\\Layout(\'layouts.shop\')]', $classContent);
        $this->assertStringContainsString('public $total = 0;', $classContent);

        $viewContent = File::get($result->viewPath);
        $this->assertStringNotContainsString('@layout', $viewContent);
        $this->assertStringNotContainsString('<?php', $viewContent);
        $this->assertStringNotContainsString('?>', $viewContent);
    }

    public function test_traditional_php_tags_work_with_inline_partials()
    {
        $componentContent = '<?php
new class extends Livewire\Component {
    public $items = ["apple", "banana"];
}
?>

<div>
    @partial("fruit-list")
    <ul>
        @foreach($items as $item)
            <li>{{ $item }}</li>
        @endforeach
    </ul>
    @endpartial
</div>';

        $viewPath = $this->tempPath . '/fruit-component.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        // Check that partial was processed
        $viewContent = File::get($result->viewPath);
        $this->assertStringContainsString('@partial(\'fruit-list\', \'livewire-compiled::partial_fruit-list_', $viewContent);

        // Check that partial file was created
        $partialFiles = glob($this->cacheDir . '/views/partial_fruit-list_*.blade.php');
        $this->assertCount(1, $partialFiles);

        // Check that class contains partial lookup
        $classContent = File::get($result->classPath);
        $this->assertStringContainsString('protected $partialLookup = [', $classContent);
    }

    /** @test */
    public function it_transforms_computed_property_references_in_view_content()
    {
        $viewContent = '@php
new class extends Livewire\Component {
    public $regularProperty = "regular";

    #[Computed]
    public function computedProperty()
    {
        return "computed value";
    }

    #[\Livewire\Attributes\Computed]
    public function anotherComputed()
    {
        return "another value";
    }
}
@endphp

<div>
    Regular: {{ $regularProperty }}
    Computed: {{ $computedProperty }}
    Another: {{ $anotherComputed }}
    Combined: {{ $computedProperty . $anotherComputed }}
</div>';

        $viewPath = $this->tempPath . '/computed-component.blade.php';
        File::put($viewPath, $viewContent);
        $result = $this->compiler->compile($viewPath);

        $compiledViewContent = File::get($result->viewPath);

        // Regular properties should remain unchanged
        $this->assertStringContainsString('{{ $regularProperty }}', $compiledViewContent);

        // Computed properties should be transformed to $this->
        $this->assertStringContainsString('{{ $this->computedProperty }}', $compiledViewContent);
        $this->assertStringContainsString('{{ $this->anotherComputed }}', $compiledViewContent);
        $this->assertStringContainsString('{{ $this->computedProperty . $this->anotherComputed }}', $compiledViewContent);

        // Original computed property references should not exist
        $this->assertStringNotContainsString('{{ $computedProperty }}', $compiledViewContent);
        $this->assertStringNotContainsString('{{ $anotherComputed }}', $compiledViewContent);
    }

    /** @test */
    public function it_handles_computed_properties_with_various_attribute_syntaxes()
    {
        $viewContent = '@php
new class extends Livewire\Component {
    #[Computed]
    public function basic() { return "basic"; }

    #[Computed(cache: true)]
    public function withOptions() { return "cached"; }

    #[\Livewire\Attributes\Computed(persist: true)]
    public function fullyQualified() { return "persistent"; }

    #[ Computed ]
    public function withSpaces() { return "spaced"; }
}
@endphp

<div>
    {{ $basic }}
    {{ $withOptions }}
    {{ $fullyQualified }}
    {{ $withSpaces }}
</div>';

        $viewPath = $this->tempPath . '/computed-syntaxes.blade.php';
        File::put($viewPath, $viewContent);
        $result = $this->compiler->compile($viewPath);

        $compiledViewContent = File::get($result->viewPath);

        $this->assertStringContainsString('{{ $this->basic }}', $compiledViewContent);
        $this->assertStringContainsString('{{ $this->withOptions }}', $compiledViewContent);
        $this->assertStringContainsString('{{ $this->fullyQualified }}', $compiledViewContent);
        $this->assertStringContainsString('{{ $this->withSpaces }}', $compiledViewContent);
    }

    /** @test */
    public function it_does_not_transform_computed_properties_in_external_components()
    {
        $viewContent = '@php(new App\Livewire\ExternalComponent)

<div>
    {{ $computedProperty }}
</div>';

        $viewPath = $this->tempPath . '/external-computed.blade.php';
        File::put($viewPath, $viewContent);
        $result = $this->compiler->compile($viewPath);

        $compiledViewContent = File::get($result->viewPath);

        // Should not transform since this is an external component
        $this->assertStringContainsString('{{ $computedProperty }}', $compiledViewContent);
        $this->assertStringNotContainsString('{{ $this->computedProperty }}', $compiledViewContent);
    }

    /** @test */
    public function it_preserves_word_boundaries_when_transforming_computed_properties()
    {
        $viewContent = '@php
new class extends Livewire\Component {
    #[Computed]
    public function foo() { return "foo"; }
}
@endphp

<div>
    {{ $foo }}
    {{ $foobar }}
    {{ $foo_bar }}
</div>';

        $viewPath = $this->tempPath . '/word-boundaries.blade.php';
        File::put($viewPath, $viewContent);
        $result = $this->compiler->compile($viewPath);

        $compiledViewContent = File::get($result->viewPath);

        // Only exact matches should be transformed
        $this->assertStringContainsString('{{ $this->foo }}', $compiledViewContent);
        $this->assertStringContainsString('{{ $foobar }}', $compiledViewContent);
        $this->assertStringContainsString('{{ $foo_bar }}', $compiledViewContent);
    }

    /** @test */
    public function it_throws_exception_when_computed_property_is_reassigned_in_view()
    {
        $viewContent = '@php
new class extends Livewire\Component {
    #[Computed]
    public function computedProperty()
    {
        return "computed value";
    }
}
@endphp

<div>
    @php($computedProperty = "reassigned")
    {{ $computedProperty }}
</div>';

        $viewPath = $this->tempPath . '/reassigned-computed.blade.php';
        File::put($viewPath, $viewContent);

        $this->expectException(\Livewire\V4\Compiler\Exceptions\CompilationException::class);
        $this->expectExceptionMessage("Cannot reassign variable \$computedProperty as it's reserved for the computed property 'computedProperty'");

        $this->compiler->compile($viewPath);
    }

    /** @test */
    public function it_handles_computed_properties_with_different_visibility_modifiers()
    {
        $viewContent = '@php
new class extends Livewire\Component {
    #[Computed]
    public function publicComputed() { return "public"; }

    #[Computed]
    protected function protectedComputed() { return "protected"; }

    #[Computed]
    private function privateComputed() { return "private"; }

    #[Computed]
    function noVisibility() { return "none"; }
}
@endphp

<div>
    {{ $publicComputed }}
    {{ $protectedComputed }}
    {{ $privateComputed }}
    {{ $noVisibility }}
</div>';

        $viewPath = $this->tempPath . '/visibility-modifiers.blade.php';
        File::put($viewPath, $viewContent);
        $result = $this->compiler->compile($viewPath);

        $compiledViewContent = File::get($result->viewPath);

        $this->assertStringContainsString('{{ $this->publicComputed }}', $compiledViewContent);
        $this->assertStringContainsString('{{ $this->protectedComputed }}', $compiledViewContent);
        $this->assertStringContainsString('{{ $this->privateComputed }}', $compiledViewContent);
        $this->assertStringContainsString('{{ $this->noVisibility }}', $compiledViewContent);
    }

    /** @test */
    public function it_does_not_transform_non_computed_methods()
    {
        $viewContent = '@php
new class extends Livewire\Component {
    public function regularMethod() { return "regular"; }

    #[SomeOtherAttribute]
    public function attributedMethod() { return "attributed"; }

    #[Computed]
    public function computedMethod() { return "computed"; }
}
@endphp

<div>
    {{ $regularMethod }}
    {{ $attributedMethod }}
    {{ $computedMethod }}
</div>';

        $viewPath = $this->tempPath . '/non-computed-methods.blade.php';
        File::put($viewPath, $viewContent);
        $result = $this->compiler->compile($viewPath);

        $compiledViewContent = File::get($result->viewPath);

        // Only computed methods should be transformed
        $this->assertStringContainsString('{{ $regularMethod }}', $compiledViewContent);
        $this->assertStringContainsString('{{ $attributedMethod }}', $compiledViewContent);
        $this->assertStringContainsString('{{ $this->computedMethod }}', $compiledViewContent);
    }

    /** @test */
    public function it_handles_complex_view_scenarios_with_computed_properties()
    {
        $viewContent = '@php
new class extends Livewire\Component {
    public $items = ["item1", "item2"];

    #[Computed]
    public function total() { return count($this->items); }

    #[Computed]
    public function isEmpty() { return empty($this->items); }
}
@endphp

<div>
    @if($isEmpty)
        <p>No items found</p>
    @else
        <p>Total items: {{ $total }}</p>
        @foreach($items as $item)
            <span>{{ $item }}</span>
        @endforeach
    @endif

    <button wire:click="addItem">Add Item ({{ $total }})</button>
</div>';

        $viewPath = $this->tempPath . '/complex-computed.blade.php';
        File::put($viewPath, $viewContent);
        $result = $this->compiler->compile($viewPath);

        $compiledViewContent = File::get($result->viewPath);

        // Computed properties should be transformed
        $this->assertStringContainsString('@if($this->isEmpty)', $compiledViewContent);
        $this->assertStringContainsString('{{ $this->total }}', $compiledViewContent);
        $this->assertStringContainsString('({{ $this->total }})', $compiledViewContent);

        // Regular properties should remain unchanged
        $this->assertStringContainsString('@foreach($items as $item)', $compiledViewContent);
        $this->assertStringContainsString('{{ $item }}', $compiledViewContent);
    }

    public function test_distinguishes_between_import_statements_and_trait_usage()
    {
        $componentContent = '@php

use Livewire\WithPagination;

new class extends Livewire\Component {
    use WithPagination;

    public $search = "";

    public function render()
    {
        // This method will be overridden in the compiled class
    }
}
@endphp

<div>
    <input wire:model="search" placeholder="Search...">
    {{ $links }}
</div>';

        $viewPath = $this->tempPath . '/paginated-component.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $classContent = File::get($result->classPath);

        // Check that the import statement is preserved at the top
        $this->assertStringContainsString('use Livewire\WithPagination;', $classContent);

        // Check that trait usage is preserved inside the class
        $this->assertStringContainsString('use WithPagination;', $classContent);

        // Verify there's only one occurrence of "use Livewire\WithPagination;" (import)
        $this->assertEquals(1, substr_count($classContent, 'use Livewire\WithPagination;'));

        // Verify there's only one occurrence of "use WithPagination;" (trait)
        $this->assertEquals(1, substr_count($classContent, 'use WithPagination;'));

        // Check that the class structure is correct
        $this->assertStringContainsString('namespace Livewire\Compiled;', $classContent);
        $this->assertStringContainsString('extends \\Livewire\\Component', $classContent);
        $this->assertStringContainsString('public $search = "";', $classContent);
    }

    public function test_multiple_traits_and_imports_work_correctly()
    {
        $componentContent = '@php

use App\Traits\CustomTrait;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

new class extends Livewire\Component {
    use WithPagination;
    use WithFileUploads;
    use CustomTrait;

    public $file;
    public $data = [];
}
@endphp

<div>
    <input type="file" wire:model="file">
    <div>{{ count($data) }} items</div>
</div>';

        $viewPath = $this->tempPath . '/multi-trait-component.blade.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $classContent = File::get($result->classPath);

        // Check that all import statements are preserved at the top
        $this->assertStringContainsString('use App\Traits\CustomTrait;', $classContent);
        $this->assertStringContainsString('use Livewire\WithPagination;', $classContent);
        $this->assertStringContainsString('use Livewire\WithFileUploads;', $classContent);

        // Check that all trait usage statements are preserved inside the class
        $this->assertStringContainsString('use WithPagination;', $classContent);
        $this->assertStringContainsString('use WithFileUploads;', $classContent);
        $this->assertStringContainsString('use CustomTrait;', $classContent);

        // Verify import counts (should appear only once each)
        $this->assertEquals(1, substr_count($classContent, 'use App\Traits\CustomTrait;'));
        $this->assertEquals(1, substr_count($classContent, 'use Livewire\WithPagination;'));
        $this->assertEquals(1, substr_count($classContent, 'use Livewire\WithFileUploads;'));

        // The trait usage should also appear once each (inside the class)
        $lines = explode("\n", $classContent);
        $insideClass = false;
        $traitUsageCount = 0;

        foreach ($lines as $line) {
            if (strpos($line, 'class ') !== false) {
                $insideClass = true;
                continue;
            }
            if ($insideClass && (
                strpos($line, 'use WithPagination;') !== false ||
                strpos($line, 'use WithFileUploads;') !== false ||
                strpos($line, 'use CustomTrait;') !== false
            )) {
                $traitUsageCount++;
            }
        }

        $this->assertEquals(3, $traitUsageCount);
    }

    /** @test */
    public function it_transforms_computed_properties_inside_inline_partials()
    {
        $viewContent = '@php
new class extends Livewire\Component {
    public $count = 0;

    #[Computed]
    public function total() {
        return $this->count * 10;
    }

    #[Computed]
    public function isEmpty() {
        return $this->count === 0;
    }
}
@endphp

<div>
    Count: {{ $count }}

    @partial("summary")
        <p>Total: {{ $total }}</p>
        @if($isEmpty)
            <span>Empty state</span>
        @endif
        <span>Count again: {{ $count }}</span>
    @endpartial
</div>';

        $viewPath = $this->tempPath . '/computed-with-partials.blade.php';
        File::put($viewPath, $viewContent);
        $result = $this->compiler->compile($viewPath);

        // Check that the main view content has computed properties transformed
        $compiledViewContent = File::get($result->viewPath);
        $this->assertStringContainsString('Count: {{ $count }}', $compiledViewContent);
        $this->assertStringContainsString('@partial(\'summary\', \'livewire-compiled::partial_summary_', $compiledViewContent);

        // Check that partial file was created and contains guard statements instead of transformed properties
        $partialFiles = glob($this->cacheDir . '/views/partial_summary_*.blade.php');
        $this->assertCount(1, $partialFiles);

        $partialContent = File::get($partialFiles[0]);

        // Guard statements should be present at the top
        $this->assertStringContainsString('<?php if (! isset($total)) $total = $this->total;', $partialContent);
        $this->assertStringContainsString('if (! isset($isEmpty)) $isEmpty = $this->isEmpty;', $partialContent);

        // Original computed property references should remain unchanged in partial content
        $this->assertStringContainsString('<p>Total: {{ $total }}</p>', $partialContent);
        $this->assertStringContainsString('@if($isEmpty)', $partialContent);

        // Regular properties should remain unchanged as before
        $this->assertStringContainsString('<span>Count again: {{ $count }}</span>', $partialContent);

        // Computed properties should NOT be transformed to $this-> in partials anymore
        $this->assertStringNotContainsString('{{ $this->total }}', $partialContent);
        $this->assertStringNotContainsString('@if($this->isEmpty)', $partialContent);
    }

    /** @test */
    public function it_allows_custom_data_to_override_computed_properties_in_partials()
    {
        $viewContent = '@php
new class extends Livewire\Component {
    public $count = 5;

    #[Computed]
    public function total() {
        return $this->count * 10; // Would be 50
    }

    #[Computed]
    public function status() {
        return "computed";
    }
}
@endphp

<div>
    @partial("summary", ["total" => 999, "status" => "custom"])
        <p>Total: {{ $total }}</p>
        <p>Status: {{ $status }}</p>
        <p>Count: {{ $count }}</p>
    @endpartial
</div>';

        $viewPath = $this->tempPath . '/custom-data-partial.blade.php';
        File::put($viewPath, $viewContent);
        $result = $this->compiler->compile($viewPath);

        // Check that partial file was created
        $partialFiles = glob($this->cacheDir . '/views/partial_summary_*.blade.php');
        $this->assertCount(1, $partialFiles);

        $partialContent = File::get($partialFiles[0]);

        // Guard statements should be present for computed properties
        $this->assertStringContainsString('if (! isset($total)) $total = $this->total;', $partialContent);
        $this->assertStringContainsString('if (! isset($status)) $status = $this->status;', $partialContent);

        // Computed property references should remain as-is (not transformed)
        $this->assertStringContainsString('{{ $total }}', $partialContent);
        $this->assertStringContainsString('{{ $status }}', $partialContent);
        $this->assertStringContainsString('{{ $count }}', $partialContent);

        // Should NOT have transformed references
        $this->assertStringNotContainsString('{{ $this->total }}', $partialContent);
        $this->assertStringNotContainsString('{{ $this->status }}', $partialContent);

        // Verify the main view includes the custom data
        $compiledViewContent = File::get($result->viewPath);
        $this->assertStringContainsString('["total" => 999, "status" => "custom"]', $compiledViewContent);
    }
}
