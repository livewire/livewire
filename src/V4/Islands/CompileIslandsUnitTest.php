<?php

namespace Livewire\V4\Islands;

use PHPUnit\Framework\Attributes\DataProvider;

class CompileIslandsUnitTest extends \Tests\TestCase
{
    #[DataProvider('contentProvider')]
    public function test_can_compile_islands($content, $expectedCompiled)
    {
        ray()->clearScreen();

        $currentPath = __DIR__ . '/fixtures/basic.livewire.php';

        $compiled = $this->compile($content, $currentPath);

        ray('compiled', $content, $compiled);

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
                    @island('anonymous_0', view: 'livewire-compiled::basic_island_0')
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
                    @island('bob', view: 'livewire-compiled::basic_island_bob_0')
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
                    @island('anonymous_0', view: 'livewire-compiled::basic_island_0')
                </div>
                HTML
            ],
            // [
            //     <<< HTML
            //     <div>
            //         @island
            //             <div>outer island</div>
            //             @island
            //                 <div>inner island</div>
            //             @endisland
            //     </div>
            //     HTML,
            //     <<< HTML
            //     <div>
            //         @island('anonymous_0', view: 'livewire-compiled::basic_island_0')
            //     </div>
            //     HTML
            // ],
        ];
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

    protected function compile($content, $currentPath)
    {
        return (new IslandsCompiler)->compile($content, $currentPath);
    }
}
