<?php

namespace Livewire\Macros;

use Illuminate\Routing\RouteRegistrar;

class RouteRegistrarWithAllowedAttributes extends RouteRegistrar
{
    public function __construct(\Illuminate\Routing\Router $router)
    {
        parent::__construct($router);

        return $this;
    }

    public function allowAttributes(...$params)
    {
        array_push($this->allowedAttributes, ...$params);

        return $this;
    }
}
