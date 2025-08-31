<?php

namespace Livewire\V4\Placeholders;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;

class PlaceholderCompilerUnitTest extends \Tests\TestCase
{
    protected PlaceholderCompiler $compiler;
    protected $tempPath;
    protected $cacheDir;

    public function setUp(): void
    {
        parent::setUp();

        $this->tempPath = sys_get_temp_dir() . '/livewire_compiler_test_' . uniqid();
        $this->cacheDir = $this->tempPath . '/cache';

        File::makeDirectory($this->tempPath, 0755, true);
        File::makeDirectory($this->cacheDir, 0755, true);

        $this->compiler = new PlaceholderCompiler($this->cacheDir);
    }

    #[DataProvider('contentProvider')]
    public function test_can_compile_placeholders($content, $expectedCompiled, $expectedFiles)
    {
        $currentPath = __DIR__ . '/fixtures/basic.livewire.php';

        $compiled = $this->compiler->compile($content, $currentPath);

        $this->assertEquals($expectedCompiled, $compiled);

        $compiledIslandFiles = glob($this->cacheDir . '/views/*.blade.php');

        $this->assertCount(count($expectedFiles), $compiledIslandFiles);

        foreach ($compiledIslandFiles as $compiledIslandFile) {
            $compiledIslandContent = File::get($compiledIslandFile);
            $compiledIslandFileName = basename($compiledIslandFile);

            $this->assertStringContainsString($expectedFiles[$compiledIslandFileName], $compiledIslandContent);
        }
    }

    public static function contentProvider()
    {
        return [
            [
                <<< HTML
                @placeholder
                    <p>Custom placeholder!</p>
                @endplaceholder
                <div>
                    Content
                </div>
                HTML,
                <<< HTML

                <div>
                    Content
                </div>
                HTML,
                [
                    'basic_placeholder.blade.php' => <<< HTML
                    <p>Custom placeholder!</p>
                    HTML,
                ]
            ],
        ];
    }

    public function test_it_throws_an_exception_if_a_placeholder_is_not_closed()
    {
        $content = <<< HTML
        @placeholder
            <p>Custom placeholder!</p>
        <div>
            Content
        </div>
        HTML;

        $currentPath = __DIR__ . '/fixtures/basic.livewire.php';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Start placeholder directive found without a matching end placeholder directive');

        $this->compiler->compile($content, $currentPath);
    }

    public function test_it_throws_an_exception_if_an_end_placeholder_found_without_a_start_placeholder()
    {
        $content = <<< HTML
            <p>Custom placeholder!</p>
        @endplaceholder
        <div>
            Content
        </div>
        HTML;

        $currentPath = __DIR__ . '/fixtures/basic.livewire.php';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('End placeholder directive found without a matching start placeholder directive');

        $this->compiler->compile($content, $currentPath);
    }

    public function test_it_throws_an_exception_if_multiple_placeholders_are_found()
    {
        $content = <<< HTML
        @placeholder
            <p>Custom placeholder!</p>
        @endplaceholder

        
        <div>
            Content
            @placeholder
                <p>Custom placeholder!</p>
            @endplaceholder
        </div>
        HTML;

        $currentPath = __DIR__ . '/fixtures/basic.livewire.php';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('There should only be one @placeholder directive per view');

        $this->compiler->compile($content, $currentPath);
    }

    public function test_it_throws_an_exception_if_nested_placeholders_are_found()
    {
        $content = <<< HTML
        @placeholder
            @placeholder
                <p>Custom placeholder!</p>
            @endplaceholder
        @endplaceholder
        <div>
            Content
        </div>
        HTML;

        $currentPath = __DIR__ . '/fixtures/basic.livewire.php';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('There should only be one @placeholder directive per view');

        $this->compiler->compile($content, $currentPath);
    }
}
