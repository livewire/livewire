<?php

namespace Livewire\V4\WithMethod;

use Livewire\ComponentHook;
use Livewire\Drawer\Utils;
use function Livewire\wrap;

class SupportWithMethod extends ComponentHook
{
    public function render($view, $data)
    {
        // Check if the component has a with() method
        if (! method_exists($this->component, 'with')) {
            return;
        }

        // Call the with() method and get the additional data
        $withData = wrap($this->component)->with();

        // Ensure the with() method returns an array
        if (! is_array($withData)) {
            return;
        }

        // Merge the with() data with the existing view data, giving precedence to with() data
        $mergedData = array_merge($data, $withData);

        // Update the view's data with the merged data
        $view->with($withData);
    }
}