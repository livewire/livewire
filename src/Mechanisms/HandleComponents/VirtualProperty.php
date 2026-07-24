<?php

namespace Livewire\Mechanisms\HandleComponents;

// A property with no backing declaration on the component — its value is
// provided and managed by an attribute instead (e.g. a #[Factory] method).
// The mechanism consults this contract wherever it enumerates, hydrates,
// or writes properties, so it never has to know about any one feature...
interface VirtualProperty
{
    public function getName();

    // The live value, constructing it first if nothing has yet...
    public function virtualValue();

    // Apply the raw dehydrated wire value for this property...
    public function hydrateVirtualValue($valueOrTuple, $context);

    // A client or server write targeting this property...
    public function setVirtualValue($value);

    // Reset back to an uninitialized state...
    public function unsetVirtualValue();
}
