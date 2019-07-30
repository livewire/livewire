<?php

namespace Tests;

use Livewire\Watchers\DumpWatcher;

class DumpWatcherTest extends TestCase
{
    /** @test */
    function dump_watcher_stores_dumps()
    {
        $dumpString = 'Livewire is awesome!';

        $watcher = app()->make(DumpWatcher::class);
        $this->assertEquals(0, count($watcher->dumps));
        
        dump($dumpString);
        
        $this->assertEquals(1, count($watcher->dumps));
        $this->assertContains($dumpString, $watcher->dumps[0]);
    }

}

