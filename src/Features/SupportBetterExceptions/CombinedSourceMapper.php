<?php

namespace Livewire\Features\SupportBetterExceptions;

use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Exceptions\Renderer\Mappers\BladeMapper;
use Illuminate\View\Compilers\BladeCompiler;
use Symfony\Component\ErrorHandler\Exception\FlattenException;

/**
 * A combined source mapper that handles both Blade and Livewire compiled files.
 *
 * This class decorates Laravel's BladeMapper to also handle Livewire's
 * compiled SFC and MFC files, mapping them back to their original source.
 */
class CombinedSourceMapper extends BladeMapper
{
    /**
     * The original Blade mapper instance.
     */
    protected BladeMapper $bladeMapper;

    /**
     * The Livewire source mapper instance.
     */
    protected LivewireSourceMapper $livewireMapper;

    public function __construct(BladeMapper $bladeMapper, LivewireSourceMapper $livewireMapper)
    {
        $this->bladeMapper = $bladeMapper;
        $this->livewireMapper = $livewireMapper;
    }

    /**
     * Map cached view paths to their original paths.
     *
     * First maps Livewire compiled files, then delegates to the original
     * BladeMapper for standard Blade view mapping.
     */
    public function map(FlattenException $exception): FlattenException
    {
        // First, apply Livewire source mapping
        $exception = $this->livewireMapper->map($exception);

        // Then, apply the original Blade mapping
        return $this->bladeMapper->map($exception);
    }
}
