<?php

namespace Livewire;

abstract class PropertyHandler
{
    /**
     *  When an update is triggered, a request is sent to the server INCLUDING the last-known component state.
     *  The server "hydrates" or "deserializes" the component from that state and performs any updates.
     */
    abstract public static function hydrate($value);

    /**
     * In addition to render HTML, Livewire "dehydrates" or "serializes" the component's state
     * of a public property, so it can be passed to the front-end.
     */
    abstract public function dehydrate();

    /**
     * Some Properties like fx Carbon does need some information if the property does get updated.
     * As this is only needed for some properties, you can overwrite this method if needed.
     */
    public function update($value)
    {
        return $value;
    }
}
