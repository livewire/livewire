<?php

namespace Livewire\Features\SupportSession;

use Illuminate\Support\Facades\Session as FacadesSession;
use Livewire\Attributes\Session;
use Tests\TestCase;
use Livewire\Livewire;
use Tests\TestComponent;
use Livewire\Features\SupportSession\Contracts\SessionPrefix;

class UnitTest extends TestCase
{
    /** @test */
    public function it_uses_the_session_key_method_if_contract_is_implemented()
    {
        Livewire::test(new class extends TestComponent implements SessionPrefix {
            #[Session(key: 'baz')]
            public $count = 0;

            public function sessionPrefix(): string
            {
                return 'foo';
            }

            function render() {
                return <<<'HTML'
                    <div>foo{{ $count }}</div>
                HTML;
            }
        })
            ->call('$refresh');

        $this->assertTrue(FacadesSession::has('foobaz'));
    }
}
