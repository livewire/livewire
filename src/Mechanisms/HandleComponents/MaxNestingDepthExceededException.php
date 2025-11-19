<?php

namespace Livewire\Mechanisms\HandleComponents;

class MaxNestingDepthExceededException extends \Exception
{
    public function __construct(string $path, int $maxDepth)
    {
        $message = "Property path [{$path}] exceeds the maximum nesting depth of {$maxDepth} levels. "
            . "You can configure this limit in config/livewire.php under 'max_nesting_depth'.";

        parent::__construct($message);
    }
}
