<?php

namespace Livewire\Connection;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Exceptions\ComponentMismatchException;

class ComponentHydrator
{
    public static function dehydrate($instance)
    {
        if ($protectedOrPrivateProperties = $instance->getAllProtectedOrPrivatePropertiesDefinedBySubClass()) {
            session()->put($instance->id.'protected_properties', $protectedOrPrivateProperties);
        }

        return $instance->getAllPublicPropertiesDefinedBySubClass();
    }

    public static function hydrate($component, $id, $publicProperties, $checksum, $browserId = null)
    {
        throw_unless(md5($component.$id) === $checksum, ComponentMismatchException::class);

        $class = app('livewire')->getComponentClass($component);

        $protectedOrPrivateProperties = session()->get($id.'protected_properties', []);

        // Garbage collect from session.
        if ($protectedOrPrivateProperties) {
            [$windowId, $pageId] = explode('.', $browserId);

            // Put the current component.
            session()->put(
                $windowId . '.' . $pageId,
                Arr::prepend(
                    session()->get($windowId . '.' . $pageId, []),
                    $id
                )
            );

            $componentsByPageId = session()->get($windowId, []);

            unset($componentsByPageId[$pageId]);

            $componentsToRemove = collect(Arr::flatten($componentsByPageId));

            // Remove things explicity set with $this->session();
            $sessionKeys = collect(session()->all())->keys();

            $keysToRemove = $componentsToRemove->flatMap(function ($componentId) use ($sessionKeys) {
                return $sessionKeys->filter(function ($sessionKey) use ($componentId) {
                    return Str::startsWith($sessionKey, $componentId);
                });
            });

            session()->forget($keysToRemove->toArray());
        }

        return tap(new $class($id), function ($unHydratedInstance) use ($publicProperties, $protectedOrPrivateProperties) {
            foreach ($publicProperties as $property => $value) {
                $unHydratedInstance->setPropertyValue($property, $value);
            }

            foreach ($protectedOrPrivateProperties as $property => $value) {
                $unHydratedInstance->setProtectedPropertyValue($property, $value);
            }
        });
    }
}
