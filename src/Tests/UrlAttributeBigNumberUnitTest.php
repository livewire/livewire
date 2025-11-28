<?php

namespace Livewire\Tests;

use Livewire\Attributes\Url;
use Livewire\Livewire;

class UrlAttributeBigNumberUnitTest extends \Tests\TestCase
{
    public function test_large_numbers_in_url_remain_strings()
    {
        $search = '22000010620000001134';

        Livewire::withQueryParams(['search' => $search])
            ->test(BigNumberUrlComponent::class)
            ->assertSetStrict('search', $search);
    }
}

class BigNumberUrlComponent extends \Tests\TestComponent
{
    #[Url]
    public string $search = '';
}
