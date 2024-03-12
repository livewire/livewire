<?php

namespace Livewire\Features\SupportLegacyModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Component;
use Livewire\Livewire;
use Sushi\Sushi;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    use RefreshDatabase;

    /** @test */
    public function null_model_property_can_be_bind()
    {
        config()->set('livewire.legacy_model_binding', true);

        Livewire::visit(new class extends Component {
            public NullModelProperty $model;

            public $title;

            public function render()
            {
                return <<<'HTML'
                <div>
                    <p dusk="title">{{ $title }}</p>
                
                    <input dusk="input" wire:model="model.title" />
                
                    <button dusk="button" wire:click="title">
                        Set title
                    </button>
                </div>
                HTML;
            }

            public function rules()
            {
                return [
                    'model.title' => ['required'],
                ];
            }

            public function title()
            {
                $this->title = 'Fake title';
            }
        })
            ->assertSee('Set title')
            ->waitForLivewire()->click('@button')
            ->waitForTextIn('@title', 'Fake title')
            ->assertSeeIn('@title', 'Fake title');
    }
}

class NullModelProperty extends Model
{
    use Sushi;

    protected $rows = [
        ['id' => 1, 'title' => 'Initial title'],
    ];

    protected $fillable = [
        'title',
    ];
}
