<?php

namespace Livewire\Features\SupportSession;

use Illuminate\Support\Facades\Session as FacadesSession;
use Livewire\Attributes\Session;
use Tests\TestCase;
use Livewire\Livewire;
use Tests\TestComponent;

class UnitTest extends TestCase
{
    public function test_it_creates_a_session_key()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[Session]
            public $count = 0;

            function render() {
                return <<<'HTML'
                    <div>foo{{ $count }}</div>
                HTML;
            }
        });

        $this->assertTrue(FacadesSession::has('lw'.crc32($component->instance()->getName().'count')));
    }

    public function test_it_creates_a_dynamic_session_id()
    {
        Livewire::test(new class extends TestComponent {
            public $post = ['id' => 2];

            #[Session(key: 'baz.{post.id}')]
            public $count = 0;

            function render() {
                return <<<'HTML'
                    <div>foo{{ $count }}</div>
                HTML;
            }
        });

        $this->assertTrue(FacadesSession::has('baz.2'));
    }
}
