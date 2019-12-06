<?php

namespace Tests;

use Livewire\Livewire;
use Livewire\Component;

class PublicPropertyCastersTest extends TestCase
{
    /** @test */
    public function public_properties_can_be_cast()
    {
        Livewire::test(ComponentWithPublicPropertyCasters::class)
            ->call('storeTypeOfs')
            ->assertSet('typeOfs.date', 'Carbon\Carbon')
            ->assertSet('typeOfs.collection', 'Illuminate\Support\Collection')
            ->assertSet('typeOfs.allCaps', 'FOO');
    }
}

class AllCapsCaster {
    public function hydrate($value)
    {
        return strtoupper($value);
    }

    public function dehydrate($value)
    {
        return strtolower($value);
    }
}

class ComponentWithPublicPropertyCasters extends Component
{
    public $date;
    public $collection;
    public $allCaps;
    public $typeOfs;

    protected $casts = [
        'date' => 'date',
        'collection' => 'collection',
        'allCaps' => AllCapsCaster::class,
    ];

    public function mount()
    {
        $this->date = \Carbon\Carbon::parse('a year ago');
        $this->collection = collect(['foo', 'bar']);
        $this->allCaps = 'FOO';
    }

    public function storeTypeOfs()
    {
        $this->typeOfs = [
            'date' => get_class($this->date),
            'collection' => get_class($this->collection),
            'allCaps' => $this->allCaps,
        ];
    }

    public function render()
    {
        return view('null-view');
    }
}
