<?php

namespace Livewire\V4\Islands;

use PHPUnit\Framework\Attributes\DataProvider;

class IslandsCompilerUnitTest extends \Tests\TestCase
{
    #[DataProvider('contentProvider')]
    public function test_can_compile_islands($content, $expectedCompiled)
    {
        $currentPath = __DIR__ . '/fixtures/basic.livewire.php';

        $compiled = (new IslandsCompiler)->compile($content, $currentPath);

        $this->assertEquals($expectedCompiled, $compiled);
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
                    @island('anonymous_0', key: 'basic_island_0', view: 'livewire-compiled::basic_island_0')
                </div>
                HTML
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
                HTML
            ],
            [
                <<< HTML
                <div>
                    @island('bob', view: 'test-view')
                </div>
                HTML,
                <<< HTML
                <div>
                    @island('bob', key: 'basic_island_bob_0', view: 'livewire-compiled::basic_island_bob_0')
                </div>
                HTML
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
                    @island('anonymous_0', key: 'basic_island_0', view: 'livewire-compiled::basic_island_0')
                </div>
                HTML
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
                HTML
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
                HTML
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
                HTML
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
                HTML
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

        (new IslandsCompiler)->compile($content, $currentPath);
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

        (new IslandsCompiler)->compile($content, $currentPath);
    }

    #[DataProvider('paramsProvider')]
    public function test_params_are_parsed_correctly($paramsString, $expectedResponse)
    {
        $response = (new IslandsCompiler)->parseParams($paramsString);

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
                ],
            ],
            [
                'bob, view: \'random\'',
                [
                    'name' => 'bob',
                    'view' => 'random',
                    'params' => [],
                ],
            ],
            [
                'name: "bob", view: "other"',
                [
                    'name' => 'bob',
                    'view' => 'other',
                    'params' => [],
                ],
            ],
        ];
    }
}
