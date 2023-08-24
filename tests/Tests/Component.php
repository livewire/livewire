<?php

namespace Tests\Tests;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public function mount()
    {
        $this->addErrors();
    }

    public function addErrors(): void
    {
        $this->addError('first', 'first error');
        $this->addError('second', 'second error');
        $this->addError('third', 'third error');
    }

    public function addFilterErrors(): void
    {
        $this->resetErrorBag();

        $this->addError('first', 'first error');
        $this->addError('second', 'second error');
    }

    public function render()
    {
        return View::file(__DIR__ . '/view.blade.php');
    }
}
