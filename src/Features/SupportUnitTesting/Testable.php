<?php

namespace Livewire\Features\SupportUnitTesting;

use Synthetic\Testing\Testable as BaseTestable;
use Synthetic\TestableSynthetic;
use PHPUnit\Framework\Assert as PHPUnit;
use Livewire\Mechanisms\DataStore;
use Livewire\Features\SupportValidation\TestsValidation;
use Livewire\Features\SupportFileDownloads\TestsFileDownloads;
use Livewire\Features\SupportEvents\TestsEvents;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Arr;
use Livewire\Features\SupportRedirects\TestsRedirects;

use function Livewire\store;

class Testable extends BaseTestable
{
    use
        MakesAssertions,
        TestsEvents,
        TestsRedirects,
        TestsValidation,
        TestsFileDownloads;

    function html($stripInitialData = false)
    {
        $html = store($this->target)->get('testing.html');

        if ($stripInitialData) {
            $removeMe = (string) str($html)->betweenFirst(
                'wire:initial-data="', '"'
            );

            $html = str_replace($removeMe, '', $html);
        }

        return $html;
    }

    function view()
    {
        return store($this->target)->get('testing.view');
    }

    public function viewData($key)
    {
        return $this->view()->getData()[$key];
    }

    public function id()
    {
        return $this->target->id();
    }

    public function instance()
    {
        return $this->target;
    }

    function call($method, ...$params)
    {
        if ($method === '$refresh') {
            return parent::commit();
        }

        if ($method === '$set') {
            return parent::set(...$params);
        }

        return parent::call($method, ...$params);
    }

    public function fill($values)
    {
        //
    }

    public function toggle($name)
    {
        //
    }

    public function updateProperty($name, $value = null)
    {
        return parent::set($name, $value);
    }

    public function syncInput($name, $value)
    {
        //
    }

    public function syncUploadedFiles($name, $files, $isMultiple = false)
    {
        //
    }

    public function sendMessage($message, $payload)
    {
        //
    }

    public function dump()
    {
        //
    }

    public function tap($callback)
    {
        //
    }

}
