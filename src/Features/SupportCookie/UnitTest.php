<?php

namespace Livewire\Features\SupportCookie;

use Illuminate\Support\Facades\Cookie as FacadesCookie;
use Livewire\Attributes\Cookie;
use Tests\TestCase;
use Livewire\Livewire;
use Tests\TestComponent;

class UnitTest extends TestCase
{
    public function test_it_creates_a_cookie_key()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[Cookie]
            public $count = 0;

            function render() {
                return <<<'HTML'
                    <div>foo{{ $count }}</div>
                HTML;
            }
        });

        $cookie = FacadesCookie::getQueuedCookies()[0];

        $this->assertEquals('lw'.crc32($component->instance()->getName().'count'), $cookie->getName());
    }

    public function test_it_creates_a_dynamic_cookie_id()
    {
        Livewire::test(new class extends TestComponent {
            public $post = ['id' => 2];

            #[Cookie(key: 'baz.{post.id}')]
            public $count = 0;

            function render() {
                return <<<'HTML'
                    <div>foo{{ $count }}</div>
                HTML;
            }
        });

        $cookie = FacadesCookie::getQueuedCookies()[0];

        $this->assertEquals('baz.2', $cookie->getName());
    }
}
