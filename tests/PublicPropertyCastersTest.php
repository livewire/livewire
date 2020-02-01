<?php

namespace Tests;

use Livewire\Castable;
use Livewire\Component;
use Livewire\Livewire;

class PublicPropertyCastersTest extends TestCase
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
            ->assertSet('dateWithFormat', '00-01-01')
            ->assertSet('collection', ['foo', 'bar'])
            ->assertSet('allCaps', 'foo')
            ->set('dateWithFormat', '00-02-02')
            ->assertSet('dateWithFormat', '00-02-02');
    }
}

class AllCapsCaster implements Castable {
    public function cast($value)
    {
        return strtoupper($value);
    }

    public function uncast($value)
    {
        return strtolower($value);
    }
}

class ComponentWithPublicPropertyCasters extends Component
{
    public $date;
    public $dateWithFormat;
    public $collection;
    public $allCaps;
    public $typeOfs;

    protected $casts = [
        'date' => 'date',
        'dateWithFormat' => 'date:y-m-d',
        'collection' => 'collection',
        'allCaps' => AllCapsCaster::class,
    ];

    public function mount()
    {
        $this->date = \Carbon\Carbon::parse('Jan 1 1900');
        $this->dateWithFormat = \Carbon\Carbon::parse('Jan 1 1900');
        $this->collection = collect(['foo', 'bar']);
        $this->allCaps = 'FOO';
    }

    public function storeTypeOfs()
    {
        $this->typeOfs = [
            'date' => get_class($this->date),
            'dateWithFormat' => get_class($this->date),
            'collection' => get_class($this->collection),
            'allCaps' => $this->allCaps,
        ];
    }

    public function render()
    {
        return view('null-view');
    }
}
