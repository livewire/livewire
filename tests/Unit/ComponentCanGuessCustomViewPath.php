<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Livewire;
use Illuminate\Database\Eloquent\Model;

class ComponentCanGuessCustomViewPath extends TestCase
{
    /** @test */
    public function can_guess_view_path_with_custom_views_directory()
    {
        $this->app['config']->set('livewire.view_path', 'custom-views-directory-to-guess');

        Livewire::test(ComponentWithCustomViewsDirectoryToGuess::class);
    }
}

class ComponentWithCustomViewsDirectoryToGuess extends Component
{
    //
}
