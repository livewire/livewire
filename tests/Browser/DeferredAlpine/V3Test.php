<?php

namespace Tests\Browser\DeferredAlpine;

class V3Test extends Test
{
    public function setUp(): void
    {
        static::$useAlpineV3 = true;

        parent::setUp();
    }
}
