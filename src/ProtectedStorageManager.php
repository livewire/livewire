<?php

namespace Livewire;

use Livewire\ComponentCacheManager;

class ProtectedStorageManager
{
    public function hydrate($unHydratedInstance, $request)
    {
        // Grab the protected properties out of the cache.
        $protectedOrPrivateProperties = (new ComponentCacheManager($unHydratedInstance))
            ->get('__protected_properties', []);

        foreach ($protectedOrPrivateProperties as $property => $value) {
            $unHydratedInstance->setProtectedPropertyValue($property, $value);
        }
    }

    public function dehydrate($instance, $response)
    {
        // Store the protected properties in the cache.
        if ($protectedOrPrivateProperties = $instance->getProtectedOrPrivatePropertiesDefinedBySubClass()) {
            (new ComponentCacheManager($instance))->put(
                '__protected_properties',
                $protectedOrPrivateProperties
            );
        }
    }
}
