<?php

namespace LegacyTests\Browser;

// Thanks to https://github.com/appstract/laravel-dusk-safari for most of this source.
trait SupportsSafari
{
    protected static $safariProcess;

    protected static function defineChromeDriver(): void
    {
        if (static::$useSafari) {
            static::startSafariDriver();
        } else {
            static::startChromeDriver(['--port=9515']);
        }
    }

    public function onlyRunOnChrome()
    {
        static::$useSafari && $this->markTestSkipped();
    }

    public static function startSafariDriver()
    {
        static::$safariProcess = new \Symfony\Component\Process\Process([
            '/usr/bin/safaridriver', '-p 9515',
        ]);

        static::$safariProcess->start();

        static::afterClass(function () {
            if (static::$safariProcess) {
                static::$safariProcess->stop();
            }
        });
    }
}
