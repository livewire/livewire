<?php

use Livewire\Component;
use Carbon\Carbon as Clock;

new class extends Component {
    public $year = 2026;

    public function change()
    {
        $this->renderIslandPart('calendar', 'year', 'year', with: ['year' => 2030]);
        $this->skipRender();
    }
};
?>

<div>
    @island(name: 'calendar')
        @islandPart('year')
            <p>{{ Clock::create($year, 1, 1)->year }}</p>
        @endislandPart
    @endisland
</div>
