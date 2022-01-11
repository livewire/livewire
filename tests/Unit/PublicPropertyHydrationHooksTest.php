<?php

namespace Tests;

use Illuminate\Support\Str;
use Livewire\Livewire;
use Livewire\Component;
use Tests\Unit\TestCase;

class PublicPropertyHydrationHooksTest extends TestCase
{
    /** @test */
    public function public_properties_can_be_cast()
    {
        Livewire::test(ComponentWithPublicPropertyCasters::class)
            ->call('storeTypeOfs')
            ->assertSet('typeOfs.date', 'Carbon\Carbon')
            ->assertSet('typeOfs.dateWithFormat', 'Carbon\Carbon')
            ->assertSet('typeOfs.collection', 'Illuminate\Support\Collection')
            ->assertSet('typeOfs.allCaps', 'FOO')
            ->assertSet('typeOfs.stringable', 'Illuminate\Support\Stringable')
            ->assertSet('dateWithFormat', '00-01-01')
            ->assertSet('collection', function ($value) {
                return $value->toArray() === ['foo', 'bar'];
            })
            ->assertSet('allCaps', 'foo')
            ->assertSet('stringable', 'Be excellent to each other')
            ->set('dateWithFormat', '00-02-02')
            ->assertSet('dateWithFormat', '00-02-02');
    }
}

class ComponentWithPublicPropertyCasters extends Component
{
    public $date;
    public $dateWithFormat;
    public $collection;
    public $allCaps;
    public $typeOfs;
    public $stringable;

    public function updatedDateWithFormat($value)
    {
        $this->dateWithFormat = \Carbon\Carbon::createFromFormat('y-m-d', $value);
    }

    public function hydrate()
    {
        $this->dateWithFormat = \Carbon\Carbon::createFromFormat('y-m-d', $this->dateWithFormat);
        $this->allCaps = strtoupper($this->allCaps);
        $this->stringable = Str::of('Be excellent to each other');
    }

    public function dehydrate()
    {
        $this->dateWithFormat = $this->dateWithFormat->format('y-m-d');
        $this->allCaps = strtolower($this->allCaps);
        $this->stringable = $this->stringable->__toString();
    }

    public function mount()
    {
        $this->date = \Carbon\Carbon::parse('Jan 1 1900');
        $this->dateWithFormat = \Carbon\Carbon::parse('Jan 1 1900');
        $this->collection = collect(['foo', 'bar']);
        $this->allCaps = 'FOO';
        $this->stringable = Str::of('Be excellent to each other');
    }

    public function storeTypeOfs()
    {
        $this->typeOfs = [
            'date' => get_class($this->date),
            'dateWithFormat' => get_class($this->date),
            'collection' => get_class($this->collection),
            'allCaps' => $this->allCaps,
            'stringable' => get_class($this->stringable),
        ];
    }

    public function render()
    {
        return view('null-view');
    }
}
