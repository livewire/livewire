<?php

use Illuminate\Http\Request;
use Livewire\Mechanisms\HandleRequests\HandleRequests;
use Tests\TestCase;

class UnitTest extends TestCase
{
    public function test_livewire_can_run_handle_request_without_components_on_payload(): void
    {
        $handleRequestsInstance = new HandleRequests();
        $request = new Request();

        $result = $handleRequestsInstance->handleUpdate($request);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('components', $result);
        $this->assertArrayHasKey('assets', $result);
        $this->assertIsArray($result['components']);
        $this->assertEmpty($result['components']);
        $this->assertIsArray($result['assets']);
        $this->assertEmpty($result['assets']);

    }
}
