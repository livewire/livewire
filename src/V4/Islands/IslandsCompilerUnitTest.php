<?php

namespace Livewire\V4\Islands;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\DataProvider;

class IslandsCompilerUnitTest extends \Tests\TestCase
{
    protected IslandsCompiler $compiler;
    protected $tempPath;
    protected $cacheDir;

    public function setUp(): void
    {
        parent::setUp();

        $this->tempPath = sys_get_temp_dir() . '/livewire_compiler_test_' . uniqid();
        $this->cacheDir = $this->tempPath . '/cache';

        File::makeDirectory($this->tempPath, 0755, true);
        File::makeDirectory($this->cacheDir, 0755, true);

        $this->compiler = new IslandsCompiler($this->cacheDir);
    }

    // @todo: Add tests for transformed computed properties in islands and ensure custom data overrides computed properties...

    #[DataProvider('contentProvider')]
    public function test_can_compile_islands($content, $expectedCompiled, $expectedFiles)
    {
        View::addNamespace('islands', __DIR__ . '/fixtures');

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
                <div>
                    @island
                        <div>anonymous island</div>
                    @endisland
                </div>
                HTML,
                <<< HTML
                <div>
                    @island('basic_0', key: 'basic_island_0', view: 'livewire-compiled::basic_island_0')
                </div>
                HTML,
                [
                    'basic_island_0.blade.php' => <<< HTML
                    <div>anonymous island</div>
                    HTML,
                ]
            ],
            [
                <<< HTML
                <div>
                    @island('bob')
                        <div>bob island</div>
                    @endisland
                </div>
                HTML,
                <<< HTML
                <div>
                    @island('bob', key: 'basic_island_bob_0', view: 'livewire-compiled::basic_island_bob_0')
                </div>
                HTML,
                [
                    'basic_island_bob_0.blade.php' => <<< HTML
                    <div>bob island</div>
                    HTML,
                ]
            ],
            [
                <<< HTML
                <div>
                    @island ('bob')
                        <div>bob island</div>
                    @endisland
                </div>
                HTML,
                <<< HTML
                <div>
                    @island('bob', key: 'basic_island_bob_0', view: 'livewire-compiled::basic_island_bob_0')
                </div>
                HTML,
                [
                    'basic_island_bob_0.blade.php' => <<< HTML
                    <div>bob island</div>
                    HTML,
                ]
            ],
            [
                <<< HTML
                <div>
                    @island('bob', view: 'islands::test-view')
                </div>
                HTML,
                <<< HTML
                <div>
                    @island('bob', key: 'basic_island_bob_0', view: 'livewire-compiled::basic_island_bob_0')
                </div>
                HTML,
                [
                    'basic_island_bob_0.blade.php' => <<< HTML
                    <div>
                        External island test view
                    </div>
                    HTML,
                ]
            ],
            [
                <<< HTML
                <div>
                    @island
                        <div>outer island</div>
                        @island
                            <div>inner island</div>
                        @endisland
                    @endisland
                </div>
                HTML,
                <<< HTML
                <div>
                    @island('basic_0', key: 'basic_island_0', view: 'livewire-compiled::basic_island_0')
                </div>
                HTML,
                [
                    'basic_island_0.blade.php' => <<< HTML
                    <div>outer island</div>
                    HTML,
                    'basic_island_1.blade.php' => <<< HTML
                    <div>inner island</div>
                    HTML,
                ]
            ],
            [
                <<< HTML
                <div>
                    @island('bob', data: ['some' => 'data'], mode: 'append', defer: true)
                        <div>outer island</div>
                        @island
                            <div>inner island</div>
                        @endisland
                    @endisland
                </div>
                HTML,
                <<< HTML
                <div>
                    @island('bob', key: 'basic_island_bob_0', data: ['some' => 'data'], mode: 'append', defer: true, view: 'livewire-compiled::basic_island_bob_0')
                </div>
                HTML,
                [
                    'basic_island_bob_0.blade.php' => <<< HTML
                    <div>outer island</div>
                    HTML,
                    'basic_island_1.blade.php' => <<< HTML
                    <div>inner island</div>
                    HTML,
                ]
            ],
            [
                <<< HTML
                <div>
                    @island('bob', data: ['some' => 'data'], mode: 'append', defer: true)
                        <div>outer island</div>
                        @island
                            <div>inner island</div>
                        @endisland
                    @endisland
                </div>
                HTML,
                <<< HTML
                <div>
                    @island('bob', key: 'basic_island_bob_0', data: ['some' => 'data'], mode: 'append', defer: true, view: 'livewire-compiled::basic_island_bob_0')
                </div>
                HTML,
                [
                    'basic_island_bob_0.blade.php' => <<< HTML
                    <div>outer island</div>
                    HTML,
                    'basic_island_1.blade.php' => <<< HTML
                    <div>inner island</div>
                    HTML,
                ]
            ],
            [
                <<< HTML
                <div>
                    @island('bob', ['some' => 'data'], 'append', false, true)
                        <div>outer island</div>
                        @island
                            <div>inner island</div>
                        @endisland
                    @endisland
                </div>
                HTML,
                <<< HTML
                <div>
                    @island('bob', key: 'basic_island_bob_0', ['some' => 'data'], 'append', false, true, view: 'livewire-compiled::basic_island_bob_0')
                </div>
                HTML,
                [
                    'basic_island_bob_0.blade.php' => <<< HTML
                    <div>outer island</div>
                    HTML,
                    'basic_island_1.blade.php' => <<< HTML
                    <div>inner island</div>
                    HTML,
                ]
            ],
            [
                <<< HTML
                <div>
                    @island('bob')
                        <p>Bob 1</p>
                    @endisland

                    @island('bob')
                        <p>Bob 2</p>
                    @endisland
                </div>
                HTML,
                <<< HTML
                <div>
                    @island('bob', key: 'basic_island_bob_0', view: 'livewire-compiled::basic_island_bob_0')

                    @island('bob', key: 'basic_island_bob_1', view: 'livewire-compiled::basic_island_bob_1')
                </div>
                HTML,
                [
                    'basic_island_bob_0.blade.php' => <<< HTML
                    <p>Bob 1</p>
                    HTML,
                    'basic_island_bob_1.blade.php' => <<< HTML
                    <p>Bob 2</p>
                    HTML,
                ]
            ],
            [
                <<< HTML
                <div>
                    View/component without any islands
                </div>
                HTML,
                <<< HTML
                <div>
                    View/component without any islands
                </div>
                HTML,
                []
            ],
        ];
    }

    public function test_it_throws_an_exception_if_an_island_is_not_closed()
    {
        $content = <<< HTML
        <div>
            @island
        </div>
        HTML;

        $currentPath = __DIR__ . '/fixtures/basic.livewire.php';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Start island directive found without a matching end island directive');

        $this->compiler->compile($content, $currentPath);
    }

    public function test_it_throws_an_exception_if_an_end_island_found_without_a_start_island()
    {
        $content = <<< HTML
        <div>
            @endisland
        </div>
        HTML;

        $currentPath = __DIR__ . '/fixtures/basic.livewire.php';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('End island directive found without a matching start island directive');

        $this->compiler->compile($content, $currentPath);
    }

    #[DataProvider('paramsProvider')]
    public function test_params_are_parsed_correctly($paramsString, $expectedResponse)
    {
        $response = $this->compiler->parseParams($paramsString);

        $this->assertEquals($expectedResponse, $response);
    }

    public static function paramsProvider()
    {
        return [
            [
                'bob',
                [
                    'name' => 'bob',
                    'view' => null,
                    'params' => [],
                    'placeholder' => null,
                ],
            ],
            [
                'bob, view: \'random\'',
                [
                    'name' => 'bob',
                    'view' => 'random',
                    'params' => [],
                    'placeholder' => null,
                ],
            ],
            [
                'name: "bob", view: "other"',
                [
                    'name' => 'bob',
                    'view' => 'other',
                    'params' => [],
                    'placeholder' => null,
                ],
            ],
        ];
    }

    public function test_it_can_compile_an_inline_island_with_a_placeholder_parameter()
    {
        $currentPath = __DIR__ . '/fixtures/basic.livewire.php';

        $content = <<< HTML
        <div>
            @island(defer: true, placeholder: 'Custom inline placeholder!')
                <div>Island content</div>
            @endisland
        </div>
        HTML;

        $compiled = $this->compiler->compile($content, $currentPath);

        $expectedCompiled = <<< HTML
        <div>
            @island('basic_0', key: 'basic_island_0', defer: true, view: 'livewire-compiled::basic_island_0')
        </div>
        HTML;

        $this->assertEquals($expectedCompiled, $compiled);

        $compiledIslandFiles = glob($this->cacheDir . '/views/*.blade.php');

        $this->assertCount(2, $compiledIslandFiles);

        $expectedFiles = [
            'basic_island_0.blade.php' => <<< HTML
            <div>Island content</div>
            HTML,
            'basic_island_0_placeholder.blade.php' => <<< HTML
            Custom inline placeholder!
            HTML,
        ];

        foreach ($compiledIslandFiles as $compiledIslandFile) {
            $compiledIslandContent = File::get($compiledIslandFile);
            $compiledIslandFileName = basename($compiledIslandFile);

            $this->assertStringContainsString($expectedFiles[$compiledIslandFileName], $compiledIslandContent);
        }
    }

    public function test_it_can_compile_an_inline_island_with_a_placeholder_directive()
    {
        $currentPath = __DIR__ . '/fixtures/basic.livewire.php';

        $content = <<< HTML
        <div>
            @island(defer: true)
                @placeholder
                    <p>Custom placeholder!</p>
                @endplaceholder

                <div>Island content</div>
            @endisland
        </div>
        HTML;

        $compiled = $this->compiler->compile($content, $currentPath);

        $expectedCompiled = <<< HTML
        <div>
            @island('basic_0', key: 'basic_island_0', defer: true, view: 'livewire-compiled::basic_island_0')
        </div>
        HTML;

        $this->assertEquals($expectedCompiled, $compiled);

        $compiledIslandFiles = glob($this->cacheDir . '/views/*.blade.php');

        $this->assertCount(2, $compiledIslandFiles);

        $expectedFiles = [
            'basic_island_0.blade.php' => <<< HTML
            <div>Island content</div>
            HTML,
            'basic_island_0_placeholder.blade.php' => <<< HTML
            <p>Custom placeholder!</p>
            HTML,
        ];

        foreach ($compiledIslandFiles as $compiledIslandFile) {
            $compiledIslandContent = File::get($compiledIslandFile);
            $compiledIslandFileName = basename($compiledIslandFile);

            $this->assertStringContainsString($expectedFiles[$compiledIslandFileName], $compiledIslandContent);
        }
    }

    public function test_it_can_compile_an_external_island_with_a_placeholder_parameter()
    {
        View::addNamespace('islands', __DIR__ . '/fixtures');

        $currentPath = __DIR__ . '/fixtures/basic.livewire.php';

        $content = <<< HTML
        <div>
            @island(defer: true, view: 'islands::test-view', placeholder: 'Custom inline placeholder!')
        </div>
        HTML;

        $compiled = $this->compiler->compile($content, $currentPath);

        $expectedCompiled = <<< HTML
        <div>
            @island('basic_0', key: 'basic_island_0', defer: true, view: 'livewire-compiled::basic_island_0')
        </div>
        HTML;

        $this->assertEquals($expectedCompiled, $compiled);

        $compiledIslandFiles = glob($this->cacheDir . '/views/*.blade.php');

        $this->assertCount(2, $compiledIslandFiles);

        $expectedFiles = [
            'basic_island_0.blade.php' => <<< HTML
            <div>
                External island test view
            </div>
            HTML,
            'basic_island_0_placeholder.blade.php' => <<< HTML
            Custom inline placeholder!
            HTML,
        ];

        foreach ($compiledIslandFiles as $compiledIslandFile) {
            $compiledIslandContent = File::get($compiledIslandFile);
            $compiledIslandFileName = basename($compiledIslandFile);

            $this->assertStringContainsString($expectedFiles[$compiledIslandFileName], $compiledIslandContent);
        }
    }
}
