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

    use Concerns\HasFunLittleUtilities,
        Concerns\MakesCallsToComponent;

    public function __construct($component, $prefix)
    {
        $this->prefix = $prefix;

        [$dom, $id, $serialized] = app('livewire')->mount($component);

        $this->id = $id;
        $this->updateComponent($dom, $serialized);
    }

    public function updateComponent($dom, $serialized)
    {
        $this->dom = $dom;
        $this->serialized = $serialized;
        $this->instance = ComponentHydrator::hydrate($serialized);
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
