<?php

namespace LegacyTests\Browser;

use Override;

// Thanks to https://github.com/appstract/laravel-dusk-safari for most of this source.
trait SupportsSafari
{
    protected static $safariProcess;

    /**
     * This is an override and customisation of orchestra/testbench-dusk/src/TestCase.php
     * 
     * @return void 
     */
    #[Override]
    public static function setUpBeforeClass(): void
    {
        static::setUpBeforeClassForInteractsWithWebDriverOptions();

        if (! isset($_ENV['DUSK_DRIVER_URL'])) {
            if (static::$useSafari) {
                static::startSafariDriver();
            } else {
                static::startChromeDriver(['port' => 9515]);
            }
        }

        /**
         * As we don't want to call `parent::setUpBeforeClass();` which would call the
         * method we are overriding. The method requires us to call it's parent but
         * we can't do that. So we have had to create a copy of the method below
         * and call it here instead.
         */
        static::parentOfParentSetUpBeforeClass();
        static::startServing();
    }

    /**
     * This is purely a copy of the core method from orchestra/testbench-core/src/TestCase.php
     * As we can't call a parent of a parent, this seems to be the only way around it.
     * 
     * @return void 
     */
    public static function parentOfParentSetUpBeforeClass()
    {
        static::setUpBeforeClassUsingPHPUnit();

        /** @phpstan-ignore-next-line */
        if (static::usesTestingConcern(Pest\WithPest::class)) {
            static::setUpBeforeClassUsingPest(); // @phpstan-ignore-line
        }

        static::setUpBeforeClassUsingTestCase();
        static::setUpBeforeClassUsingWorkbench();
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
