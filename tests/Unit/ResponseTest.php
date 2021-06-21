<?php

namespace Tests\Unit;

use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Support\Facades\Route;
use Livewire\Request;
use Livewire\Response;

class ResponseTest extends TestCase
{
    /** @test */
    public function subsequent_response_works_with_in_component_but_not_in_request()
    {
        $request = new Request([
            'fingerprint' => [],
            'updates' => [],
            'serverMemo' => [
                'data' => [
                    'count' => 0
                ],
            ]
        ]);

        $response = new Response($request);
        $response->memo = [
            'data' => [
                'count' => 1,
                'aNewProperty' => 'A New Value',
            ],
        ];

        $this->assertEquals(
            [
                'effects' => [],
                'serverMemo' => ['data' => ['count' => 1, 'aNewProperty' => 'A New Value']]
            ],
            $response->toSubsequentResponse()
        );
    }

    /** @test */
    public function subsequent_response_works_with_properties_in_request_but_not_in_component()
    {
        $request = new Request([
            'fingerprint' => [],
            'updates' => [],
            'serverMemo' => [
                'data' => [
                    'count' => 0,
                    'aNewProperty' => 'A New Value',
                ],
            ]
        ]);

        $response = new Response($request);

        $response->memo = [
            'data' => [
                'count' => 1,
            ],
        ];

        $response->effects = [
            'dirty' => ['count', 'aNewProperty']
        ];

        $this->assertEquals(
            [
                'effects' => [
                    'dirty' => ['count', 'aNewProperty']
                ],
                'serverMemo' => ['data' => ['count' => 1, 'aNewProperty' => null]]
            ],
            $response->toSubsequentResponse()
        );
    }
}
