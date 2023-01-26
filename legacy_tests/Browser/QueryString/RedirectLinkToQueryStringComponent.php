<?php

namespace LegacyTests\Browser\QueryString;

use Livewire\Component as BaseComponent;

class RedirectLinkToQueryStringComponent extends BaseComponent
{
    public $showNestedComponent = false;

    public function render()
    {
        return <<< 'HTML'
<div>
    <a dusk="link" href="{{ url('/livewire-dusk/LegacyTests%5CBrowser%5CQueryString%5CNestedComponent') }}">Link</a>
</div>
HTML;
    }
}
