<?php
namespace Livewire\Watchers;

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\VarDumper;

class DumpWatcher
{

    public $dumps = [];

    public function __construct()
    {
        VarDumper::setHandler(function ($var) {
            $this->recordDump((new HtmlDumper)->dump(
                (new VarCloner)->cloneVar($var), true
            ));
        });
    }

    public function recordDump($dump)
    {
        $this->dumps[] = $dump;
        return $this;
    }
}
