<?php

namespace Livewire\Tests;

use function Livewire\invade;

class InvadeHelperUnitTest extends \Tests\TestCase
{
    /** @test */
    public function get_property()
    {
        $thing = new class
        {
            private $foo = 'bar';
        };

        $this->assertEquals('bar', invade($thing)->foo);
    }

    /** @test */
    public function set_property()
    {
        $thing = new class
        {
            private $foo = 'bar';
        };

        invade($thing)->foo = 'baz';

        $this->assertEquals('baz', invade($thing)->foo);
    }

    /** @test */
    public function call_method()
    {
        $thing = new class
        {
            private $foo = 'bar';

            private function getFoo()
            {
                return $this->foo;
            }
        };

        $this->assertEquals('bar', invade($thing)->getFoo());
    }
}
