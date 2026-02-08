<?php

namespace Livewire\Features\SupportBetterExceptions;

use Livewire\ComponentHook;
use Illuminate\Foundation\Exceptions\Renderer\Renderer;
use Illuminate\Foundation\Exceptions\Renderer\Mappers\BladeMapper;

class SupportBetterExceptions extends ComponentHook
{
    /**
     * The Livewire source mapper instance.
     */
    protected static ?LivewireSourceMapper $mapper = null;

    /**
     * Boot the feature.
     */
    public static function provide()
    {
        static::$mapper = new LivewireSourceMapper();

        // Extend the exception Renderer to include Livewire source mapping
        static::extendExceptionRenderer();
    }

    /**
     * Extend Laravel's exception renderer to map Livewire compiled files.
     */
    protected static function extendExceptionRenderer()
    {
        // We need to extend the Renderer singleton to use our combined mapper
        // This is done by decorating the BladeMapper binding
        app()->extend(BladeMapper::class, function (BladeMapper $bladeMapper) {
            return new CombinedSourceMapper($bladeMapper, static::$mapper);
        });
    }

    /**
     * Get the source mapper instance.
     */
    public static function getMapper(): ?LivewireSourceMapper
    {
        return static::$mapper;
    }
}
