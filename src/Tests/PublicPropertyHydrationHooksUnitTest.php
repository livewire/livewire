<?php

namespace Livewire\Tests;

use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestComponent;

class PublicPropertyHydrationHooksUnitTest extends \Tests\TestCase
{
    public function test_public_properties_can_be_cast()
    {
        $this->markTestSkipped('This test needs to be split, so each property type is tested as part of their dedicated Synth');

        Livewire::test(ComponentWithPublicPropertyCasters::class)
            ->call('storeTypeOfs')
            ->assertSetStrict('typeOfs.date', 'Carbon\Carbon')
            ->assertSetStrict('typeOfs.dateWithFormat', 'Carbon\Carbon')
            ->assertSetStrict('typeOfs.collection', 'Illuminate\Support\Collection')
            ->assertSetStrict('typeOfs.allCaps', 'FOO')
            ->assertSetStrict('typeOfs.stringable', 'Illuminate\Support\Stringable')
            ->assertSetStrict('dateWithFormat', '00-01-01')
            ->assertSetStrict('collection', function ($value) {
                return $value->toArray() === ['foo', 'bar'];
            })
            ->assertSetStrict('allCaps', 'foo')
            ->assertSetStrict('stringable', 'Be excellent to each other')
            ->set('dateWithFormat', '00-02-02')
            ->assertSetStrict('dateWithFormat', '00-02-02');
    }
}

class ComponentWithPublicPropertyCasters extends TestComponent
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
}
