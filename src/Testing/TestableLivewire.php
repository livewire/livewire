<?php

namespace Livewire\Testing;

use Symfony\Component\DomCrawler\Crawler;
use Livewire\Connection\ComponentHydrator;

class TestableLivewire
{
    public $id;
    public $prefix;
    public $instance;
    public $dom;
    public $serialized;
    public $dirtyInputs;

    use Concerns\HasFunLittleUtilities,
        Concerns\MakesCallsToComponent;

    public function __construct($component, $prefix)
    {
        $this->prefix = $prefix;

        $this->updateComponent(
            app('livewire')->mount($component)
        );
    }

    public function updateComponent($output)
    {
        $this->id = $output->id;
        $this->dom = $output->dom;
        $this->serialized = $output->serialized;
        $this->dirtyInputs = $output->dirtyInputs;
        $this->instance = ComponentHydrator::hydrate($this->serialized);
    }

    public function __get($property)
    {
        return $this->instance->{$property};
    }

    public function __call($method, $params)
    {
        return $this->runAction($method, $params);
    }
}
