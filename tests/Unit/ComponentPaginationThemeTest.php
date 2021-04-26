<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Livewire;
use Livewire\WithPagination;

class ComponentPaginationThemeTest extends TestCase
{
    /** @test * */
    public function pagination_theme_is_set(){
        $obj = new ComponentWithDefaultPagination();
        $this->assertEquals($obj->getPaginationTheme(), 'bootstrap');
    }

    /** @test * */
    public function default_theme_is_defined(){
        $obj = new ComponentWithoutDefaultPaginationTheme();
        $this->assertEquals($obj->getPaginationTheme(), 'tailwind');
    }

    /** @test * */
    public function theme_can_be_set_in_config(){
        config()->set('livewire.pagination_theme', 'bootstrap');
        $obj = new ComponentWithoutDefaultPaginationTheme();
        $this->assertEquals($obj->getPaginationTheme(), 'bootstrap');
    }
}

class ComponentWithDefaultPagination extends Component
{
    use WithPagination;

    public $paginationTheme = 'bootstrap';

    public function render()
    {
        return view('show-name', ['name' => 'example']);
    }
}

class ComponentWithoutDefaultPaginationTheme extends Component
{
    use WithPagination;

    public function render()
    {
        return view('show-name', ['name' => 'example']);
    }
}
