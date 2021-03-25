<?php

namespace Tests\Browser;

use Symfony\Component\Process\Process;

// Thanks to https://github.com/appstract/laravel-dusk-safari for most of this source.
trait SupportsSafari
{
    protected static $safariDriver = '/usr/bin/safaridriver';

    protected static $safariProcess;

    public function onlyRunOnChrome()
    {
        static::$useSafari && $this->markTestSkipped();
    }

    public static function startSafariDriver(array $arguments = [])
    {
        static::$safariProcess = new Process(
            array_merge([static::$safariDriver], $arguments)
        );

        static::$safariProcess->start();

        static::afterClass(function () {
            if (static::$safariProcess) {
                static::$safariProcess->stop();
            }
        });
    }

    public static function useSafaridriver($path)
    {
        static::$safariDriver = $path;
    }
}
