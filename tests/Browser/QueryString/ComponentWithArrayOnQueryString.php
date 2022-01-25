<?php

namespace Tests\Browser\QueryString;

use Illuminate\Support\Facades\View;
use Livewire\Component;

class ComponentWithArrayOnQueryString extends Component
{

    public $notCreateHistoryWhenEmptyArrayMessage = '';
    public $notCreateHistoryWhenArrayHasSameContentMessage = '';

    public $filters = [
        'search' => null,
        'status' => null
    ];

    protected $queryString = [
        'filters' => [
            'search' => null,
            'status' => null,
        ] 
    ];

    public function doNotCreateHistoryWhenEmptyArray()
    {
        $this->notCreateHistoryWhenEmptyArrayMessage = 'history-not-created-when-array-is-empty';
    }

    public function doNotCreateHistoryWhenArrayHasSameContent()
    {
        $this->notCreateHistoryWhenArrayHasSameContentMessage = 'history-not-created-when-array-has-same-content';
    }

    public function render()
    {
        return View::file(__DIR__.'/component-with-nullable-array.blade.php');
    }
}
