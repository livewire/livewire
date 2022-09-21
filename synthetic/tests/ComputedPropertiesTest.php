<?php

namespace Tests;

use Synthetic\SyntheticFacade as Synthetic;
use Synthetic\Synthetic as SyntheticTrait;

class ComputedPropertiesTest extends TestCase
{
    /** @test */
    public function computed_properties_track_dependancies_and_dont_re_compute_unless_changed()
    {
        Synthetic::test(new ComputedPropertiesSubject)
            ->assertEquals(3, fn ($data) => count($data['filtered']))
            ->assertEquals(3, fn ($data) => count($data['otherFiltered']))
            ->assertEquals(2, fn ($data) => $data['computedCalls'])
            ->set('query', 'b')
            ->assertEquals(4, fn ($data) => $data['computedCalls'])
            ->assertEquals(2, fn ($data) => count($data['filtered']))
            ->assertEquals(2, fn ($data) => count($data['otherFiltered']))
            ->set('arbitraryProperty', 'bar')
            ->assertEquals(4, fn ($data) => $data['computedCalls'])
            ->assertEquals(2, fn ($data) => count($data['filtered']))
            ->assertEquals(2, fn ($data) => count($data['otherFiltered']))
        ;
    }

    /** @test */
    public function can_use_the_alternate_method_annotation_syntax()
    {
        Synthetic::test(new ComputedPropertiesSubject)
            ->assertEquals(3, fn ($data) => count($data['otherFiltered']))
            ->assertEquals(1, fn ($data) => $data['computedCalls'])
            ->set('query', 'b')
            ->assertEquals(2, fn ($data) => $data['computedCalls'])
            ->assertEquals(2, fn ($data) => count($data['otherFiltered']))
            ->set('arbitraryProperty', 'bar')
            ->assertEquals(2, fn ($data) => $data['computedCalls'])
            ->assertEquals(2, fn ($data) => count($data['otherFiltered']))
        ;
    }
}

class ComputedPropertiesSubject extends Synthetic {
    use SyntheticTrait;

    public $query = '';

    public $computedCalls = 0;

    public $items = ['foo', 'bar', 'baz'];

    public $arbitraryProperty = 'baz';

    function computedFiltered()
    {
        $this->computedCalls++;

        return array_filter($this->items, fn ($i) => str_contains($i, $this->query));
    }

    /** @computed */
    function otherFiltered()
    {
        $this->computedCalls++;

        return array_filter($this->items, fn ($i) => str_contains($i, $this->query));
    }
}
