<?php

namespace Livewire\Tests;

use function Livewire\invade;
use Livewire\Request;
use Livewire\Response;

class InvadeHelperUnitTest extends \Tests\TestCase
{
    public function test_get_property()
    {
        $thing = new class {
            private $foo = 'bar';
        };

        $this->assertEquals('bar', invade($thing)->foo);
    }

    public function test_set_property()
    {
        $thing = new class {
            private $foo = 'bar';
        };

        invade($thing)->foo = 'baz';

        $this->assertEquals('baz', invade($thing)->foo);
    }

    public function test_call_method()
    {
        $thing = new class {
            private $foo = 'bar';

            private function getFoo() {
                return $this->foo;
            }
        };

        $this->assertEquals('bar', invade($thing)->getFoo());
    }
}
