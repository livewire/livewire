<?php

namespace Tests\Unit\Components;

use Carbon\Carbon;
use Livewire\Attributes\DateFormat;
use Livewire\Attributes\ModelKey;
use Livewire\Component;
use Tests\Unit\Models\ModelForSerialization;

class ComponentWithComplexTypedProperties extends Component
{
    public ?Carbon $date = null;

    #[DateFormat('H:i, d M Y')]
    public ?Carbon $foo = null;

    public ?ModelForSerialization $model = null;

    #[ModelKey('id')]
    public ?ModelForSerialization $attributedModel = null;

    #[ModelKey('id', 'title', true)]
    public ?ModelForSerialization $complexAttributedModel = null;

    protected $rules = [
        'model' => ['nullable'],
        'model.id' => ['required'],
        'model.title' => ['required'],
        'attributedModel' => ['nullable'],
        'attributedModel.id' => ['required'],
        'attributedModel.title' => ['required'],
        'complexAttributedModel' => ['nullable'],
        'complexAttributedModel.id' => ['required'],
        'complexAttributedModel.title' => ['required'],
    ];

    public function render()
    {
        return view('complex-typed-properties');
    }
}
