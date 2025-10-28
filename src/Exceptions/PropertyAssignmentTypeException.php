<?php

namespace Livewire\Exceptions;

use Exception;
use Livewire\Component;
use Livewire\Volt\Component as VoltComponent;
use ReflectionClass;

class PropertyAssignmentTypeException extends Exception
{
    use BypassViewHandler;

    public function __construct(Component $component, string $levelName, mixed $value)
    {
        $value_string = gettype($value);

        $reflection = new ReflectionClass($component);
        $this->file = $reflection->getFileName() ?? $this->file;

        $className = $reflection->getShortName();
        if ($component instanceof VoltComponent) {
            $className = str($this->file)->after(resource_path())->ltrim(DIRECTORY_SEPARATOR)->value();
        }

        $this->message = "Error assigning {$value_string} to {$levelName} on component {$className}";

        if ($this->file && is_readable($this->file)) {
            $lines = file($this->file);
            foreach ($lines as $lineNumber => $line) {
                if (str_contains($line, "\${$levelName}")) {
                    $this->line = $lineNumber + 1;
                    break;
                }
            }
        }
    }
}
