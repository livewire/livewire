<?php

namespace Livewire\V4\JsModules;

trait HandleJsModules
{
    public function jsModule()
    {
        return [$this->jsModuleSource(), $this->jsModuleLastModified()];
    }

    public function hasJsModule()
    {
        return $this->hasJsModuleSource();
    }

    protected function hasJsModuleSource()
    {
        return false;
    }

    protected function jsModuleSource()
    {
        return false;
    }

    protected function jsModuleLastModified()
    {
        return false;
    }
}