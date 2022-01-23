<?php

namespace Livewire;

interface LivewirePropertyType
{
    /**
     * Hydrate the value to be used on the back-end.
     *
     * @param \Livewire\Component $instance
     * @param \Livewire\Request|null $request
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function hydrate($instance, $request, $name, $value);

    /**
     * Dehydrate the value to be used on the front-end.
     *
     * @param \Livewire\Component $instance
     * @param \Livewire\Response|null $response
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function dehydrate($instance, $response, $name, $value);
}
