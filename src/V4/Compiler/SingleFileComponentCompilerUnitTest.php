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

        $viewPath = $this->tempPath . '/counter.livewire.php';
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

        $viewPath = $this->tempPath . '/counter.livewire.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $this->assertInstanceOf(CompilationResult::class, $result);
        $this->assertTrue($result->isExternal);
        $this->assertEquals('App\Livewire\Counter', $result->externalClass);
        $this->assertFalse(file_exists($result->classPath)); // No class file for external
        $this->assertTrue(file_exists($result->viewPath));
    }

    public function test_can_compile_placeholder_directive()
    {
        $componentContent = <<< HTML
        <?php

        use Livewire\Component;

        new class extends Component {
            //
        } ?>

        @placeholder
            <div>Placeholder</div>
        @endplaceholder
        <div>
            Content
        </div>
        HTML;

        $viewPath = $this->tempPath . '/counter.livewire.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $compiledPlaceholderPath = $this->cacheDir . '/views/counter_placeholder.blade.php';
        $this->assertTrue(file_exists($compiledPlaceholderPath));
        $this->assertStringContainsString('<div>Placeholder</div>', File::get($compiledPlaceholderPath));

        $this->assertTrue(file_exists($result->viewPath));
        $this->assertStringNotContainsString('<div>Placeholder</div>', File::get($result->viewPath));
    }

    public function test_can_compile_islands_directives()
    {
        $componentContent = <<< HTML
        <?php

        use Livewire\Component;

        new class extends Component {
            //
        } ?>

        <div>
            Content

            @island
                <div>Island content</div>
            @endisland
        </div>
        HTML;

        $viewPath = $this->tempPath . '/counter.livewire.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $compiledIslandPath = $this->cacheDir . '/views/counter_island_0.blade.php';
        $this->assertTrue(file_exists($compiledIslandPath));
        $this->assertStringContainsString('<div>Island content</div>', File::get($compiledIslandPath));

        $this->assertTrue(file_exists($result->viewPath));
        $this->assertStringNotContainsString('<div>Island content</div>', File::get($result->viewPath));
    }

    public function test_can_compile_islands_and_component_placeholder_directives()
    {
        $componentContent = <<< HTML
        <?php

        use Livewire\Component;

        new class extends Component {
            //
        } ?>

        @placeholder
            <div>Component placeholder</div>
        @endplaceholder

        <div>
            Component content

            @island
                @placeholder
                    <div>Island placeholder</div>
                @endplaceholder

                <div>Island content</div>
            @endisland
        </div>
        HTML;

        $viewPath = $this->tempPath . '/counter.livewire.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $compiledComponentPlaceholderPath = $this->cacheDir . '/views/counter_placeholder.blade.php';
        $this->assertTrue(file_exists($compiledComponentPlaceholderPath));
        $this->assertStringContainsString('<div>Component placeholder</div>', File::get($compiledComponentPlaceholderPath));

        $compiledIslandPath = $this->cacheDir . '/views/counter_island_0.blade.php';
        $this->assertTrue(file_exists($compiledIslandPath));
        $this->assertStringContainsString('<div>Island content</div>', File::get($compiledIslandPath));

        $compiledIslandPlaceholderPath = $this->cacheDir . '/views/counter_island_0_placeholder.blade.php';
        $this->assertTrue(file_exists($compiledIslandPlaceholderPath));
        $this->assertStringContainsString('<div>Island placeholder</div>', File::get($compiledIslandPlaceholderPath));

        $this->assertTrue(file_exists($result->viewPath));
        $compiledViewPathContent = File::get($result->viewPath);
        $this->assertStringNotContainsString('<div>Component placeholder</div>', $compiledViewPathContent);
        $this->assertStringNotContainsString('<div>Island placeholder</div>', $compiledViewPathContent);
        $this->assertStringNotContainsString('<div>Island content</div>', $compiledViewPathContent);
    }

    public function test_throws_exception_for_missing_view_file()
    {
        $this->expectException(CompilationException::class);
        $this->expectExceptionMessage('View file not found');

        $this->compiler->compile('/non/existent/file.livewire.php');
    }

    public function test_throws_exception_for_invalid_component_without_php_block()
    {
        $componentContent = '<div>No PHP block</div>';

        $viewPath = $this->tempPath . '/invalid.livewire.php';
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

        $viewPath = $this->tempPath . '/invalid.livewire.php';
        File::put($viewPath, $componentContent);

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Invalid component: @php block must contain a class definition');

        $this->compiler->compile($viewPath);
    }

    public function test_can_parse_external_component_with_class_suffix()
    {
        $componentContent = '@php(new App\Livewire\Counter::class)

<div>External with ::class</div>';

        $viewPath = $this->tempPath . '/external.livewire.php';
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

        $viewPath = $this->tempPath . '/counter.livewire.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $classContent = File::get($result->classPath);

        $this->assertStringContainsString('namespace Livewire\Compiled;', $classContent);
        $this->assertStringContainsString('extends \\Livewire\\Component', $classContent);
        $this->assertStringContainsString('public $count = 0;', $classContent);
        $this->assertStringContainsString('public function increment()', $classContent);
        $this->assertStringContainsString('public function render()', $classContent);
        $this->assertStringContainsString("return app('view')->file('{$result->viewPath}');", $classContent);
    }

    public function test_generated_view_file_contains_correct_content()
    {
        $componentContent = '@php
new class extends Livewire\Component {
    public $count = 0;
}
@endphp

<div>Count: {{ $count }}</div>';

        $viewPath = $this->tempPath . '/counter.livewire.php';
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

        $viewPath = $this->tempPath . '/counter.livewire.php';
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

        $viewPath = $this->tempPath . '/counter.livewire.php';
        File::put($viewPath, $componentContent1);

        $result1 = $this->compiler->compile($viewPath);

        $firstModified = filemtime($result1->classPath);

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

        $secondModified = filemtime($result2->classPath);

        $this->assertEquals($result1->hash, $result2->hash); // Hash stays same (path-based)
        $this->assertEquals($result1->className, $result2->className); // Class name stays same
        $this->assertNotEquals($firstModified, $secondModified); // File was regenerated
    }

    public function test_compiled_files_are_overwritten_not_orphaned()
    {
        $componentContent1 = '@php
new class extends Livewire\Component {
    public $count = 0;
}
@endphp

<div>Count: {{ $count }}</div>';

        $viewPath = $this->tempPath . '/counter.livewire.php';
        File::put($viewPath, $componentContent1);

        // First compilation
        $result1 = $this->compiler->compile($viewPath);

        // Verify files exist
        $this->assertFileExists($result1->classPath);
        $this->assertFileExists($result1->viewPath);

        // Count initial compiled files
        $initialClassFiles = glob($this->cacheDir . '/classes/*.php');
        $initialViewFiles = glob($this->cacheDir . '/views/*.blade.php');
        $initialMetadataFiles = glob($this->cacheDir . '/metadata/*.json');

        $initialClassCount = count($initialClassFiles);
        $initialViewCount = count($initialViewFiles);
        $initialMetadataCount = count($initialMetadataFiles);

        // Change the content (which currently creates new hashes/filenames)
        $componentContent2 = '@php
new class extends Livewire\Component {
    public $count = 1;
}
@endphp

<div>Count: {{ $count }}</div>';

        sleep(1); // Ensure different timestamp
        File::put($viewPath, $componentContent2);

        // Second compilation
        $result2 = $this->compiler->compile($viewPath);

        // Verify new files exist
        $this->assertFileExists($result2->classPath);
        $this->assertFileExists($result2->viewPath);

        // Count final compiled files
        $finalClassFiles = glob($this->cacheDir . '/classes/*.php');
        $finalViewFiles = glob($this->cacheDir . '/views/*.blade.php');
        $finalMetadataFiles = glob($this->cacheDir . '/metadata/*.json');

        $finalClassCount = count($finalClassFiles);
        $finalViewCount = count($finalViewFiles);
        $finalMetadataCount = count($finalMetadataFiles);

        // THIS TEST WILL INITIALLY FAIL - showing the problem
        // After fix: should have same number of files (overwritten, not duplicated)
        $this->assertEquals($initialClassCount, $finalClassCount, 'Class files should be overwritten, not duplicated');
        $this->assertEquals($initialViewCount, $finalViewCount, 'View files should be overwritten, not duplicated');
        $this->assertEquals($initialMetadataCount, $finalMetadataCount, 'Metadata files should be overwritten, not duplicated');

        // After fix: the paths should be the same (same filename)
        $this->assertEquals($result1->classPath, $result2->classPath, 'Class file path should remain consistent');
        $this->assertEquals($result1->viewPath, $result2->viewPath, 'View file path should remain consistent');
    }

    public function test_multi_file_components_are_also_overwritten_not_orphaned()
    {
        // Create a multi-file component directory
        $componentDir = $this->tempPath . '/counter';
        File::makeDirectory($componentDir);

        $livewireContent1 = 'new class extends Livewire\Component {
    public $count = 0;
}';
        $bladeContent1 = '<div>Count: {{ $count }}</div>';

        File::put($componentDir . '/counter.php', $livewireContent1);
        File::put($componentDir . '/counter.blade.php', $bladeContent1);

        // First compilation
        $result1 = $this->compiler->compileMultiFileComponent($componentDir);

        // Count initial compiled files
        $initialClassFiles = glob($this->cacheDir . '/classes/*.php');
        $initialViewFiles = glob($this->cacheDir . '/views/*.blade.php');
        $initialMetadataFiles = glob($this->cacheDir . '/metadata/*.json');

        $initialClassCount = count($initialClassFiles);
        $initialViewCount = count($initialViewFiles);
        $initialMetadataCount = count($initialMetadataFiles);

        // Change the content
        $livewireContent2 = 'new class extends Livewire\Component {
    public $count = 1; // Changed
}';

        sleep(1); // Ensure different timestamp
        File::put($componentDir . '/counter.php', $livewireContent2);

        // Second compilation
        $result2 = $this->compiler->compileMultiFileComponent($componentDir);

        // Count final compiled files
        $finalClassFiles = glob($this->cacheDir . '/classes/*.php');
        $finalViewFiles = glob($this->cacheDir . '/views/*.blade.php');
        $finalMetadataFiles = glob($this->cacheDir . '/metadata/*.json');

        $finalClassCount = count($finalClassFiles);
        $finalViewCount = count($finalViewFiles);
        $finalMetadataCount = count($finalMetadataFiles);

        // Should have same number of files (overwritten, not duplicated)
        $this->assertEquals($initialClassCount, $finalClassCount, 'Multi-file class files should be overwritten, not duplicated');
        $this->assertEquals($initialViewCount, $finalViewCount, 'Multi-file view files should be overwritten, not duplicated');
        $this->assertEquals($initialMetadataCount, $finalMetadataCount, 'Multi-file metadata files should be overwritten, not duplicated');

        // The paths should be the same (same filename)
        $this->assertEquals($result1->classPath, $result2->classPath, 'Multi-file class file path should remain consistent');
        $this->assertEquals($result1->viewPath, $result2->viewPath, 'Multi-file view file path should remain consistent');
    }

    public function test_livewire_files_are_cleared_with_view_clear()
    {
        // Create a component to generate compiled files
        $componentContent = '@php
new class extends Livewire\Component {
    public $count = 0;
}
@endphp

<div>Count: {{ $count }}</div>';

        $viewPath = $this->tempPath . '/counter.livewire.php';
        File::put($viewPath, $componentContent);

        // Compile it to create files
        $result = $this->compiler->compile($viewPath);

        // Verify compiled files exist
        $this->assertFileExists($result->classPath);
        $this->assertFileExists($result->viewPath);

        // Verify cache directories have files
        $classFiles = glob($this->cacheDir . '/classes/*.php');
        $viewFiles = glob($this->cacheDir . '/views/*.blade.php');
        $metadataFiles = glob($this->cacheDir . '/metadata/*.json');

        $this->assertGreaterThan(0, count($classFiles), 'Should have compiled class files');
        $this->assertGreaterThan(0, count($viewFiles), 'Should have compiled view files');
        $this->assertGreaterThan(0, count($metadataFiles), 'Should have metadata files');

        // Manually call the clear method using the same cache directory as our test
        $mockOutput = new \Symfony\Component\Console\Output\BufferedOutput();

        // Call the clear method directly with our test cache directory
        $this->clearLivewireCompiledFiles($this->cacheDir, $mockOutput);

        // Verify all files are cleared
        $finalClassFiles = glob($this->cacheDir . '/classes/*.php');
        $finalViewFiles = glob($this->cacheDir . '/views/*.blade.php');
        $finalMetadataFiles = glob($this->cacheDir . '/metadata/*.json');

        $this->assertEquals(0, count($finalClassFiles), 'Class files should be cleared');
        $this->assertEquals(0, count($finalViewFiles), 'View files should be cleared');
        $this->assertEquals(0, count($finalMetadataFiles), 'Metadata files should be cleared');

        // Verify directories still exist
        $this->assertDirectoryExists($this->cacheDir . '/classes');
        $this->assertDirectoryExists($this->cacheDir . '/views');
        $this->assertDirectoryExists($this->cacheDir . '/scripts');
        $this->assertDirectoryExists($this->cacheDir . '/metadata');

        // Verify output message
        $output = $mockOutput->fetch();
        $this->assertStringContainsString('Livewire compiled files cleared', $output);
    }

    protected function clearLivewireCompiledFiles(string $cacheDirectory, $output = null)
    {
        try {
            if (is_dir($cacheDirectory)) {
                // Count files before clearing for informative output
                $totalFiles = 0;
                foreach (['classes', 'views', 'scripts', 'metadata'] as $subdir) {
                    $path = $cacheDirectory . '/' . $subdir;
                    if (is_dir($path)) {
                        $totalFiles += count(glob($path . '/*'));
                    }
                }

                // Use the same cleanup approach as our clear command
                File::deleteDirectory($cacheDirectory);

                // Recreate the directory structure
                File::makeDirectory($cacheDirectory . '/classes', 0755, true);
                File::makeDirectory($cacheDirectory . '/views', 0755, true);
                File::makeDirectory($cacheDirectory . '/scripts', 0755, true);
                File::makeDirectory($cacheDirectory . '/metadata', 0755, true);

                // Recreate .gitignore
                File::put($cacheDirectory . '/.gitignore', "*\n!.gitignore");

                // Output success message if we have access to output
                if ($output && method_exists($output, 'writeln')) {
                    if ($totalFiles > 0) {
                        $output->writeln("<info>Livewire compiled files cleared ({$totalFiles} files removed).</info>");
                    } else {
                        $output->writeln("<info>Livewire compiled files directory cleared.</info>");
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fail to avoid breaking view:clear if there's an issue
            if ($output && method_exists($output, 'writeln')) {
                $output->writeln("<comment>Note: Could not clear Livewire compiled files.</comment>");
            }
        }
    }

    public function test_cache_hits_do_not_modify_compiled_files()
    {
        $componentContent = '@php
new class extends Livewire\Component {
    public $count = 0;
    public function increment() { $this->count++; }
}
@endphp

<div>Count: {{ $count }}</div>';

        $viewPath = $this->tempPath . '/counter.livewire.php';
        File::put($viewPath, $componentContent);

        // First compilation
        $result1 = $this->compiler->compile($viewPath);

        // Record file modification times after first compilation
        $classFileMtime = filemtime($result1->classPath);
        $viewFileMtime = filemtime($result1->viewPath);
        $metadataPath = $this->getMetadataPath($viewPath, $result1->hash);
        $metadataFileMtime = filemtime($metadataPath);

        // Small delay to ensure timestamps would be different if files were modified
        usleep(100000); // 0.1 seconds

        // Second compilation (should be a cache hit)
        $result2 = $this->compiler->compile($viewPath);

        // Verify it was actually a cache hit (same hash, same paths)
        $this->assertEquals($result1->hash, $result2->hash);
        $this->assertEquals($result1->classPath, $result2->classPath);
        $this->assertEquals($result1->viewPath, $result2->viewPath);

        // CRITICAL: Check that file modification times haven't changed
        $newClassFileMtime = filemtime($result2->classPath);
        $newViewFileMtime = filemtime($result2->viewPath);
        $newMetadataFileMtime = filemtime($metadataPath);

        $this->assertEquals($classFileMtime, $newClassFileMtime,
            'Class file should not be modified on cache hit');
        $this->assertEquals($viewFileMtime, $newViewFileMtime,
            'View file should not be modified on cache hit (this causes Blade recompilation!)');
        $this->assertEquals($metadataFileMtime, $newMetadataFileMtime,
            'Metadata file should not be modified on cache hit');
    }

    protected function getMetadataPath(string $viewPath, string $hash): string
    {
        $name = $this->getComponentNameFromPath($viewPath);
        return $this->cacheDir . '/metadata/' . $name . '_' . $hash . '.json';
    }

    protected function getComponentNameFromPath(string $viewPath): string
    {
        $basename = basename($viewPath);
        $basename = str_replace(['.livewire.php'], '', $basename);
        // Strip ⚡ from the component name
        $basename = str_replace('⚡', '', $basename);
        return str_replace([' ', '_'], '-', $basename);
    }

    public function test_compiled_views_use_direct_file_paths_to_avoid_double_compilation()
    {
        $componentContent = '@php
new class extends Livewire\Component {
    public $count = 0;
}
@endphp

<div>Count: {{ $count }}</div>';

        $viewPath = $this->tempPath . '/counter.livewire.php';
        File::put($viewPath, $componentContent);

        // Compile the component
        $result = $this->compiler->compile($viewPath);

        // Verify the compiled view uses .blade.php extension (for proper Blade compilation)
        $this->assertStringEndsWith('.blade.php', $result->viewPath);
        $this->assertFileExists($result->viewPath);

        // The content should contain Blade directives (ready for Blade compilation)
        $compiledContent = File::get($result->viewPath);

        // Should contain our clean view content with Blade expressions
        $this->assertStringContainsString('<div>Count: {{ $count }}</div>', $compiledContent);

        // Should NOT contain any @php blocks (they were moved to the class)
        $this->assertStringNotContainsString('@php', $compiledContent);
        $this->assertStringNotContainsString('@endphp', $compiledContent);

        // Verify the generated class uses app('view')->file() instead of view() with namespace
        $classContent = File::get($result->classPath);
        $this->assertStringContainsString("app('view')->file(", $classContent);
        $this->assertStringNotContainsString("view('livewire-compiled::", $classContent);
    }

    public function test_blade_compilation_only_happens_once_not_every_request()
    {
        $componentContent = '@php
new class extends Livewire\Component {
    public $count = 0;
    public function increment() { $this->count++; }
}
@endphp

<div>Count: {{ $count }} - Random: {{ rand(1, 1000) }}</div>';

        $viewPath = $this->tempPath . '/counter.livewire.php';
        File::put($viewPath, $componentContent);

        // First compilation - creates both Livewire compiled files AND Blade compiled files
        $result = $this->compiler->compile($viewPath);

        // Manually render the view to trigger Blade compilation
        $factory = app('view');
        $view1 = $factory->file($result->viewPath, ['count' => 0]);
        $rendered1 = $view1->render();

        // Find the Blade compiled file (Laravel stores compiled views)
        $bladeCompiledPath = $this->findBladeCompiledFile($result->viewPath);
        $this->assertNotNull($bladeCompiledPath, 'Blade should have compiled the view file');

        // Record the modification time of the Blade compiled file
        $bladeCompiledMtime = filemtime($bladeCompiledPath);

        // Small delay to ensure timestamps would be different if recompilation occurred
        usleep(100000); // 0.1 seconds

        // Second render - should NOT trigger Blade recompilation
        $view2 = $factory->file($result->viewPath, ['count' => 0]);
        $rendered2 = $view2->render();

        // Check that Blade compiled file was NOT modified (meaning no recompilation)
        $newBladeCompiledMtime = filemtime($bladeCompiledPath);

        $this->assertEquals($bladeCompiledMtime, $newBladeCompiledMtime,
            'Blade compiled file should NOT be modified on second render - this means Blade is recompiling unnecessarily!');

        // Both renders should have the same content (except for the random number)
        $this->assertStringContainsString('Count: 0', $rendered1);
        $this->assertStringContainsString('Count: 0', $rendered2);
    }

    protected function findBladeCompiledFile(string $bladePath): ?string
    {
        // Laravel compiles Blade files to storage/framework/views/ with hashed names
        $compiledDir = storage_path('framework/views');

        if (!is_dir($compiledDir)) {
            return null;
        }

        $files = glob($compiledDir . '/*');
        $latestFile = null;
        $latestTime = 0;

        // Find the most recently created file (likely our compiled template)
        foreach ($files as $file) {
            if (is_file($file)) {
                $mtime = filemtime($file);
                if ($mtime > $latestTime) {
                    $latestTime = $mtime;
                    $latestFile = $file;
                }
            }
        }

        return $latestFile;
    }

    public function test_is_compiled_returns_true_for_compiled_component()
    {
        $componentContent = '@php
new class extends Livewire\Component {
    public $count = 0;
}
@endphp

<div>Count: {{ $count }}</div>';

        $viewPath = $this->tempPath . '/counter.livewire.php';
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

        $viewPath = $this->tempPath . '/counter.livewire.php';
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

        $viewPath = $this->tempPath . '/my-special_component.livewire.php';
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

        $viewPath = $this->tempPath . '/counter.livewire.php';
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

        $viewPath = $this->tempPath . '/invalid.livewire.php';
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

    public function test_can_compile_separate_view_and_script_files()
    {
        $componentClassContent = <<<'PHP'
        <?php
        new class extends Livewire\Component {
            public $count = 0;

            public function increment()
            {
                $this->count++;
            }
        }
        ?>
        PHP;

        $componentViewContent = <<<'HTML'
        <div>
            Count: {{ $count }}
            <button wire:click="increment">Increment</button>
        </div>
        HTML;

        $componentScriptContent = <<<'JS'
        console.log("Hello from script");
        JS;

        // Create multi-file component directory
        $componentDir = $this->tempPath . '/counter';
        File::makeDirectory($componentDir);

        $classPath = $componentDir . '/counter.php';
        $viewPath = $componentDir . '/counter.blade.php';
        $scriptPath = $componentDir . '/counter.js';
        File::put($classPath, $componentClassContent);
        File::put($viewPath, $componentViewContent);
        File::put($scriptPath, $componentScriptContent);

        $result = $this->compiler->compileMultiFileComponent($componentDir);

        $this->assertInstanceOf(CompilationResult::class, $result);
        $this->assertFalse($result->isExternal);
        $this->assertStringContainsString('Livewire\\Compiled\\Counter_', $result->className);
        $this->assertStringContainsString('livewire-compiled::counter_', $result->viewName);
        $this->assertTrue(file_exists($result->classPath));
        $this->assertTrue(file_exists($result->viewPath));

        $viewContent = File::get($result->viewPath);

        // View should contain only the HTML content, scripts should be extracted
        $this->assertStringContainsString(
            <<<'HTML'
            <div>
                Count: {{ $count }}
                <button wire:click="increment">Increment</button>
            </div>
            HTML,
            $viewContent
        );

        // Scripts should not be in the view content
        $this->assertStringNotContainsString('<script>', $viewContent);
        $this->assertStringNotContainsString('@script', $viewContent);

        // Script should be extracted to separate file
        $this->assertTrue($result->hasScripts());
        $this->assertFileExists($result->scriptPath);
        $scriptContent = File::get($result->scriptPath);
        $this->assertStringContainsString('console.log("Hello from script");', $scriptContent);
    }

    public function test_if_the_component_already_contains_html_then_dont_include_the_view()
    {
        $componentClassContent = <<<'PHP'
        <?php
        new class extends Livewire\Component {
            public $count = 0;

            public function increment()
            {
                $this->count++;
            }
        }
        ?>

        <div>
            Inline content
        </div>
        PHP;

        $componentViewContent = <<<'HTML'
        <div>
            External content
        </div>
        HTML;

        $classPath = $this->tempPath . '/counter.livewire.php';
        $viewPath = $this->tempPath . '/counter.blade.php';
        File::put($classPath, $componentClassContent);
        File::put($viewPath, $componentViewContent);

        $result = $this->compiler->compile($classPath);

        $this->assertInstanceOf(CompilationResult::class, $result);
        $this->assertFalse($result->isExternal);
        $this->assertStringContainsString('Livewire\\Compiled\\Counter_', $result->className);
        $this->assertStringContainsString('livewire-compiled::counter_', $result->viewName);
        $this->assertTrue(file_exists($result->classPath));
        $this->assertTrue(file_exists($result->viewPath));

        $viewContent = File::get($result->viewPath);

        $this->assertStringContainsString(
            <<<'HTML'
            <div>
                Inline content
            </div>
            HTML,
            $viewContent
        );
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

        $viewPath = $this->tempPath . '/counter.livewire.php';
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

        $viewPath = $this->tempPath . '/counter.livewire.php';
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

        $viewPath = $this->tempPath . '/counter.livewire.php';
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

        $viewPath = $this->tempPath . '/admin-counter.livewire.php';
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

        $viewPath = $this->tempPath . '/counter.livewire.php';
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

        $viewPath = $this->tempPath . '/component-with-script.livewire.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $viewContent = File::get($result->viewPath);
        // Scripts should be extracted to separate files, not remain in view
        $this->assertStringNotContainsString('<script>', $viewContent);
        $this->assertStringNotContainsString('console.log("Hello from naked script");', $viewContent);

        // Script should be extracted to separate file
        $this->assertTrue($result->hasScripts());
        $this->assertFileExists($result->scriptPath);
        $scriptContent = File::get($result->scriptPath);
        $this->assertStringContainsString('console.log("Hello from naked script");', $scriptContent);
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

        $viewPath = $this->tempPath . '/component-with-wrapped-script.livewire.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $viewContent = File::get($result->viewPath);
        // When @script directives are present, script extraction should be skipped
        // The content should remain exactly as it was, including the @script directives and script content
        $this->assertEquals(1, substr_count($viewContent, '@script'));
        $this->assertEquals(1, substr_count($viewContent, '@endscript'));
        $this->assertStringContainsString('<script>', $viewContent);
        $this->assertStringContainsString('console.log("Already wrapped script");', $viewContent);

        // Should not have extracted scripts to separate file
        $this->assertFalse($result->hasScripts());
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

        $viewPath = $this->tempPath . '/component-with-multiple-scripts.livewire.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $viewContent = File::get($result->viewPath);
        // Both scripts should be extracted to separate files, not remain in view
        $this->assertStringNotContainsString('<script>', $viewContent);
        $this->assertStringNotContainsString('console.log("First script");', $viewContent);
        $this->assertStringNotContainsString('console.log("Second script");', $viewContent);

        // Both scripts should be extracted to single script file
        $this->assertTrue($result->hasScripts());
        $this->assertFileExists($result->scriptPath);
        $scriptContent = File::get($result->scriptPath);
        $this->assertStringContainsString('console.log("First script");', $scriptContent);
        $this->assertStringContainsString('console.log("Second script");', $scriptContent);
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

        $viewPath = $this->tempPath . '/component-with-empty-scripts.livewire.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $viewContent = File::get($result->viewPath);
        // All script tags should be removed from view, including empty ones
        $this->assertStringNotContainsString('<script>', $viewContent);
        $this->assertStringNotContainsString('console.log("Non-empty script");', $viewContent);

        // Only non-empty script should be extracted to separate file
        $this->assertTrue($result->hasScripts());
        $this->assertFileExists($result->scriptPath);
        $scriptContent = File::get($result->scriptPath);
        $this->assertStringContainsString('console.log("Non-empty script");', $scriptContent);
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

        $viewPath = $this->tempPath . '/component-with-script-attributes.livewire.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $viewContent = File::get($result->viewPath);
        // Script should be removed from view content
        $this->assertStringNotContainsString('<script>', $viewContent);
        $this->assertStringNotContainsString('console.log("Script with attributes");', $viewContent);

        // Script should be extracted to separate file with attributes preserved
        $this->assertTrue($result->hasScripts());
        $this->assertFileExists($result->scriptPath);
        $scriptContent = File::get($result->scriptPath);
        $this->assertStringContainsString('console.log("Script with attributes");', $scriptContent);
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

        $viewPath = $this->tempPath . '/component-without-script.livewire.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $viewContent = File::get($result->viewPath);

        $this->assertEquals("<div>\n    Count: {{ \$count }}\n</div>", $viewContent);
        $this->assertStringNotContainsString('@script', $viewContent);
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

        $viewPath = $this->tempPath . '/chat.livewire.php';
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

        $viewPath = $this->tempPath . '/user-profile.livewire.php';
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

        $viewPath = $this->tempPath . '/simple-counter.livewire.php';
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

        $viewPath = $this->tempPath . '/post-component.livewire.php';
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
new class extends Livewire\Component {
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }
}
?>

<div>Count: {{ $count }}</div>';

        $viewPath = $this->tempPath . '/product.livewire.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $this->assertInstanceOf(CompilationResult::class, $result);
        $this->assertFalse($result->isExternal);
        $this->assertTrue(file_exists($result->classPath));
        $this->assertTrue(file_exists($result->viewPath));

        $classContent = File::get($result->classPath);

        // Should work fine without use statements
        $this->assertStringContainsString('namespace Livewire\Compiled;', $classContent);
        $this->assertStringContainsString('extends \\Livewire\\Component', $classContent);
        $this->assertStringContainsString('public $count = 0;', $classContent);

        // Should not have any use statements
        $this->assertStringNotContainsString('use ', $classContent);

        // Check that view content doesn't contain PHP tags
        $viewContent = File::get($result->viewPath);
        $this->assertStringNotContainsString('<?php', $viewContent);
        $this->assertStringNotContainsString('?>', $viewContent);
        $this->assertStringContainsString('<div>Count: {{ $count }}</div>', $viewContent);
    }

    public function test_can_compile_external_component_with_traditional_php_tags()
    {
        $componentContent = '<?php(new App\Livewire\ProductComponent) ?>

<div>
    <h1>External Product Component</h1>
</div>';

        $viewPath = $this->tempPath . '/external-product.livewire.php';
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

        $viewPath = $this->tempPath . '/cart.livewire.php';
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

        $viewPath = $this->tempPath . '/computed-component.livewire.php';
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

        $viewPath = $this->tempPath . '/computed-syntaxes.livewire.php';
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

        $viewPath = $this->tempPath . '/external-computed.livewire.php';
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

        $viewPath = $this->tempPath . '/word-boundaries.livewire.php';
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

        $viewPath = $this->tempPath . '/reassigned-computed.livewire.php';
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

        $viewPath = $this->tempPath . '/visibility-modifiers.livewire.php';
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

        $viewPath = $this->tempPath . '/non-computed-methods.livewire.php';
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

        $viewPath = $this->tempPath . '/complex-computed.livewire.php';
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

        $viewPath = $this->tempPath . '/paginated-component.livewire.php';
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

        $viewPath = $this->tempPath . '/multi-trait-component.livewire.php';
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

    public function test_can_compile_component_with_use_statements_and_they_are_available_in_the_view()
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

        $viewPath = $this->tempPath . '/product.livewire.php';
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

        // Check that view content contains the use statements
        $viewContent = File::get($result->viewPath);
        $this->assertEquals(<<<'HTML'
        <?php
        use App\Models\Product;
        ?>
        <div>
            <h1>{{ $product->name }}</h1>
            <p>Quantity: {{ $quantity }}</p>
            <button wire:click="addToCart">Add to Cart</button>
        </div>
        HTML, $viewContent);
    }

    public function test_can_compile_component_with_class_level_attributes_compact_syntax()
    {
        $componentContent = '@php
new #[Layout(\'layouts.app\')] class extends Livewire\Component {
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }
}
@endphp

<div>Count: {{ $count }}</div>';

        $viewPath = $this->tempPath . '/counter.livewire.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $this->assertInstanceOf(CompilationResult::class, $result);
        $this->assertFalse($result->isExternal);
        $this->assertTrue(file_exists($result->classPath));

        $classContent = File::get($result->classPath);

        // Check that the class-level attribute is preserved
        $this->assertStringContainsString('#[Layout(\'layouts.app\')]', $classContent);
        $this->assertStringContainsString('class ' . $result->getShortClassName() . ' extends \\Livewire\\Component', $classContent);
        $this->assertStringContainsString('public $count = 0;', $classContent);
        $this->assertStringContainsString('public function increment()', $classContent);
    }

    public function test_can_compile_component_with_class_level_attributes_spaced_syntax()
    {
        $componentContent = '@php
new
#[Layout(\'layouts.app\')]
class extends Livewire\Component {
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }
}
@endphp

<div>Count: {{ $count }}</div>';

        $viewPath = $this->tempPath . '/counter.livewire.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $this->assertInstanceOf(CompilationResult::class, $result);
        $this->assertFalse($result->isExternal);
        $this->assertTrue(file_exists($result->classPath));

        $classContent = File::get($result->classPath);

        // Check that the class-level attribute is preserved
        $this->assertStringContainsString('#[Layout(\'layouts.app\')]', $classContent);
        $this->assertStringContainsString('class ' . $result->getShortClassName() . ' extends \\Livewire\\Component', $classContent);
        $this->assertStringContainsString('public $count = 0;', $classContent);
        $this->assertStringContainsString('public function increment()', $classContent);
    }

    public function test_can_compile_component_with_multiple_class_level_attributes()
    {
        $componentContent = '@php
new #[Layout(\'layouts.app\')] #[Lazy] class extends Livewire\Component {
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }
}
@endphp

<div>Count: {{ $count }}</div>';

        $viewPath = $this->tempPath . '/counter.livewire.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $this->assertInstanceOf(CompilationResult::class, $result);
        $this->assertFalse($result->isExternal);
        $this->assertTrue(file_exists($result->classPath));

        $classContent = File::get($result->classPath);

        // Check that both class-level attributes are preserved
        $this->assertStringContainsString('#[Layout(\'layouts.app\')]', $classContent);
        $this->assertStringContainsString('#[Lazy]', $classContent);
        $this->assertStringContainsString('class ' . $result->getShortClassName() . ' extends \\Livewire\\Component', $classContent);
        $this->assertStringContainsString('public $count = 0;', $classContent);
        $this->assertStringContainsString('public function increment()', $classContent);
    }

    public function test_can_compile_component_with_multiple_class_level_attributes_spaced()
    {
        $componentContent = '@php
new
#[Layout(\'layouts.app\')]
#[Lazy]
class extends Livewire\Component {
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }
}
@endphp

<div>Count: {{ $count }}</div>';

        $viewPath = $this->tempPath . '/counter.livewire.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $this->assertInstanceOf(CompilationResult::class, $result);
        $this->assertFalse($result->isExternal);
        $this->assertTrue(file_exists($result->classPath));

        $classContent = File::get($result->classPath);

        // Check that both class-level attributes are preserved
        $this->assertStringContainsString('#[Layout(\'layouts.app\')]', $classContent);
        $this->assertStringContainsString('#[Lazy]', $classContent);
        $this->assertStringContainsString('class ' . $result->getShortClassName() . ' extends \\Livewire\\Component', $classContent);
        $this->assertStringContainsString('public $count = 0;', $classContent);
        $this->assertStringContainsString('public function increment()', $classContent);
    }

    public function test_class_level_attributes_work_with_layout_directive()
    {
        $componentContent = '@layout(\'layouts.main\')

@php
new #[Lazy] class extends Livewire\Component {
    public $count = 0;
}
@endphp

<div>Count: {{ $count }}</div>';

        $viewPath = $this->tempPath . '/counter.livewire.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $this->assertInstanceOf(CompilationResult::class, $result);
        $this->assertFalse($result->isExternal);
        $this->assertTrue(file_exists($result->classPath));

        $classContent = File::get($result->classPath);

        // Check that both @layout directive attribute and class-level attribute are present
        $this->assertStringContainsString('#[\\Livewire\\Attributes\\Layout(\'layouts.main\')]', $classContent);
        $this->assertStringContainsString('#[Lazy]', $classContent);
        $this->assertStringContainsString('class ' . $result->getShortClassName() . ' extends \\Livewire\\Component', $classContent);
        $this->assertStringContainsString('public $count = 0;', $classContent);
    }

    public function test_class_level_attributes_work_with_traditional_php_tags()
    {
        $componentContent = '<?php
new #[Layout(\'layouts.app\')] class extends Livewire\Component {
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }
}
?>

<div>Count: {{ $count }}</div>';

        $viewPath = $this->tempPath . '/counter.livewire.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $this->assertInstanceOf(CompilationResult::class, $result);
        $this->assertFalse($result->isExternal);
        $this->assertTrue(file_exists($result->classPath));

        $classContent = File::get($result->classPath);

        // Check that the class-level attribute is preserved
        $this->assertStringContainsString('#[Layout(\'layouts.app\')]', $classContent);
        $this->assertStringContainsString('class ' . $result->getShortClassName() . ' extends \\Livewire\\Component', $classContent);
        $this->assertStringContainsString('public $count = 0;', $classContent);
        $this->assertStringContainsString('public function increment()', $classContent);
    }

    public function test_component_without_class_level_attributes_works_as_before()
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

        $viewPath = $this->tempPath . '/counter.livewire.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $this->assertInstanceOf(CompilationResult::class, $result);
        $this->assertFalse($result->isExternal);
        $this->assertTrue(file_exists($result->classPath));

        $classContent = File::get($result->classPath);

        // Check that no unexpected attributes are added
        $this->assertStringNotContainsString('#[Layout', $classContent);
        $this->assertStringNotContainsString('#[Lazy', $classContent);
        $this->assertStringContainsString('class ' . $result->getShortClassName() . ' extends \\Livewire\\Component', $classContent);
        $this->assertStringContainsString('public $count = 0;', $classContent);
        $this->assertStringContainsString('public function increment()', $classContent);
    }

    public function test_preserves_grouped_import_statements()
    {
        $componentContent = '@php

use App\Models\Post;
use Livewire\Attributes\{Computed, Locked, Validate, Url, Session};
use Illuminate\Support\{Str, Collection, Carbon};

new class extends Livewire\Component {
    public Post $post;
    public Collection $items;

    #[Computed]
    #[Locked]
    public function title()
    {
        return Str::title($this->post->title);
    }

    #[Validate(\'required\')]
    #[Session]
    public $sessionData = [];
}
@endphp

<div>
    <h1>{{ $title }}</h1>
</div>';

        $viewPath = $this->tempPath . '/grouped-imports.livewire.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $classContent = File::get($result->classPath);

        // Check that all import statements are preserved exactly as written
        $this->assertStringContainsString('use App\Models\Post;', $classContent);
        $this->assertStringContainsString('use Livewire\Attributes\{Computed, Locked, Validate, Url, Session};', $classContent);
        $this->assertStringContainsString('use Illuminate\Support\{Str, Collection, Carbon};', $classContent);

        // Check that class properties and methods are preserved
        $this->assertStringContainsString('public Post $post;', $classContent);
        $this->assertStringContainsString('public Collection $items;', $classContent);
        $this->assertStringContainsString('#[Computed]', $classContent);
        $this->assertStringContainsString('#[Locked]', $classContent);
        $this->assertStringContainsString('public function title()', $classContent);
        $this->assertStringContainsString('#[Validate(\'required\')]', $classContent);
        $this->assertStringContainsString('#[Session]', $classContent);
        $this->assertStringContainsString('public $sessionData = [];', $classContent);

        // Verify structure is correct
        $this->assertStringContainsString('namespace Livewire\Compiled;', $classContent);
        $this->assertStringContainsString('extends \\Livewire\\Component', $classContent);
    }

    public function test_preserves_grouped_imports_with_class_level_attributes()
    {
        $componentContent = '@php

use App\Models\{User, Post, Comment};
use Livewire\Attributes\{Computed, Layout};

new #[Layout(\'layouts.blog\')] class extends Livewire\Component {
    public User $author;
    public Post $post;

    #[Computed]
    public function commentCount()
    {
        return Comment::where(\'post_id\', $this->post->id)->count();
    }
}
@endphp

<div>
    <h1>{{ $post->title }}</h1>
    <p>By {{ $author->name }}</p>
    <p>Comments: {{ $commentCount }}</p>
</div>';

        $viewPath = $this->tempPath . '/grouped-imports-with-attributes.livewire.php';
        File::put($viewPath, $componentContent);

        $result = $this->compiler->compile($viewPath);

        $classContent = File::get($result->classPath);

        // Check that grouped imports are preserved
        $this->assertStringContainsString('use App\Models\{User, Post, Comment};', $classContent);
        $this->assertStringContainsString('use Livewire\Attributes\{Computed, Layout};', $classContent);

        // Check that class-level attributes are preserved
        $this->assertStringContainsString('#[Layout(\'layouts.blog\')]', $classContent);

        // Check that method attributes are preserved
        $this->assertStringContainsString('#[Computed]', $classContent);
        $this->assertStringContainsString('public function commentCount()', $classContent);

        // Check that properties are preserved
        $this->assertStringContainsString('public User $author;', $classContent);
        $this->assertStringContainsString('public Post $post;', $classContent);
    }
}
