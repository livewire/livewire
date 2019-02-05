<?php

use Livewire\Livewire;
use Livewire\LivewireComponent;
use PHPUnit\Framework\TestCase;
use Livewire\LivewireManager;

class InputFormTest extends TestCase
{
    /** @test */
    function can_sync_form_data()
    {
        $this->instance->syncForm('job_application', 'name', 'Jackson');
        $this->assertequals('123abc', $this->instance->forms->job_application->name);
    }

    /** @test */
    function synced_data_shows_up_as_dirty_if_changed_from_something_other_than_sync()
    {
        $this->instance->onRequest();
        $this->instance->sync('modelnumber', '123abc');
        $this->assertEmpty($this->instance->dirtySyncs());

        $this->instance->onRequest();
        $this->instance->changeModelNumber('456def');
        $this->assertContains('modelNumber', $this->instance->dirtySyncs());
    }

    public function setUp()
    {
        $this->instance = new Faucet('faucet', new \StdClass);
    }
}

class JobApplication extends LivewireComponent {
    public function mounted()
    {
        $this->forms->add('job_application', [
            'name' => 'required',
            'twitter' => 'min:5|regex:/^\@/',
        ]);
    }

    public function render()
    {
        //
    }
}
