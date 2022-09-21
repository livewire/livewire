<?php

namespace Tests;

use Orchestra\Testbench\TestCase as Testbench;
use Synthetic\SyntheticServiceProvider;

class TestCase extends Testbench
{
    protected function getPackageProviders($app)
    {
        return [
            SyntheticServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        //
    }
}
