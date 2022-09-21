<?php

namespace Tests;

use Synthetic\SyntheticManager;

class AnnotationsParsingTest extends TestCase
{
    /** @test */
    public function parses_annotations()
    {
        $assert = function ($subject, $expect) {
            $this->assertEquals(json_encode($expect), json_encode(
                (new SyntheticManager)->parseAnnotations($subject)
            ));
        };

        $assert('/** @foo */', ['foo' => []]);
        $assert('/** @foo m-d-y */', ['foo' => ['m-d-y']]);
        $assert(<<<EOD
        /**
         * @foo
         * @bar
         */
        EOD, ['foo' => [], 'bar' => []]);
        $assert(<<<EOD
        /**
         * @foo boo
         * @bar far
         */
        EOD, ['foo' => ['boo'], 'bar' => ['far']]);
        $assert(<<<EOD
        /**
         * @foo ['bar' => 'baz']
         */
        EOD, ['foo' => ["['bar' => 'baz']"]]);
    }
}
