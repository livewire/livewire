<?php


namespace Livewire\ProtectedStorage;

use Livewire\Component;

class ProtectedStorageInBrowser implements ProtectedStorage
{

    public function getProtectedDataForPayload(Component $instance)
    {
        return encrypt(serialize($instance->getProtectedOrPrivatePropertiesDefinedBySubClass()));
    }

    public function saveProtectedData(Component $instance)
    {
        return null;
    }

    public function restoreProtectedData(Component $unHydratedInstance, $payloadData)
    {
        if($payloadData == null) {
            return;
        }

        /** @noinspection UnserializeExploitsInspection */
        $protectedOrPrivateProperties = unserialize(decrypt($payloadData));

        foreach ($protectedOrPrivateProperties as $property => $value) {
            $unHydratedInstance->setProtectedPropertyValue($property, $value);
        }

    }

}
