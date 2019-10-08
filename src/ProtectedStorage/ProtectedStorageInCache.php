<?php


namespace Livewire\ProtectedStorage;


use Livewire\Component;
use Livewire\ComponentCacheManager;

class ProtectedStorageInCache implements ProtectedStorage
{
    public function getProtectedDataForPayload(Component $instance)
    {
        return null;
    }

    public function saveProtectedData(Component $instance)
    {
        // Store the protected properties in the cache.
        if ($protectedOrPrivateProperties = $instance->getProtectedOrPrivatePropertiesDefinedBySubClass()) {
            (new ComponentCacheManager($instance))->put(
                '__protected_properties',
                $protectedOrPrivateProperties
            );
        }
    }

    public function restoreProtectedData(Component $unHydratedInstance, $payloadData)
    {
        $protectedOrPrivateProperties = (new ComponentCacheManager($unHydratedInstance))
            ->get('__protected_properties', []);

        foreach ($protectedOrPrivateProperties as $property => $value) {
            $unHydratedInstance->setProtectedPropertyValue($property, $value);
        }
    }

}
