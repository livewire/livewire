<?php

namespace Livewire\Features\SupportLegacyModels\Tests\Concerns;

trait EnableLegacyModels
{
    // Enable model binding for these tests
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('livewire.eloquent_model_binding', true);
    }
}
