<?php

namespace Tests\Browser\FileUploads;

use Illuminate\Http\UploadedFile;
use Laravel\Dusk\Browser;
use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test_set_uploaded_file_from_livewire_component()
    {
        $this->browse(function (Browser $browser) {
            $fakeFile = UploadedFile::fake()->create('foo');

            Livewire::visit($browser, Component::class)
                ->waitForLivewire()
                ->attach('@foo', $fakeFile)
                ->waitUsing(5, 75, function () use ($browser) {
                    $browser->script([
                        'foo = livewire.first().get("foo")',
                        'livewire.first().set("foo", foo)',
                    ]);

                    return $browser->assertScript('foo.indexOf("livewire-files:") >= 0', true);
                });
        });
    }
}
