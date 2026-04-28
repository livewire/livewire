<?php

namespace Livewire\Exceptions;

class MaxNestingDepthExceededException extends \Exception
{
    public function __construct(string $path, int $maxDepth)
    {
        $message = "Property path [{$path}] exceeds the maximum nesting depth of {$maxDepth} levels. "
            . "You can configure this limit in config/livewire.php under 'payload.max_nesting_depth' or inside the component as \$maxNestingDepth or #[MaxNestingDepth(...)].";

        parent::__construct($message);
    }
}
