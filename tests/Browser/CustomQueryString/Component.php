<?php

namespace Tests\Browser\CustomQueryString;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;
use Livewire\WithCustomizedQueryString;

class Component extends BaseComponent
{
    use WithCustomizedQueryString;

    protected $queryString = [
        'a' => ['except' => [], 'as' => 'a'],
        'b' => ['except' => [], 'as' => 'b'],
    ];

    public $a = [];
    public $b = '';
    public $output = '';

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }

    public function setOutputToA() {
        $this->output = implode(',', $this->a);
    }

    public function setOutputToB() {
        $this->output = $this->b;
    }

    public function formatQueryParameter(string $property, string $fromQueryString)
    {
        if (gettype($this->{$property}) == 'array') {
            return explode(',', $fromQueryString);
        }

        return $fromQueryString;
    }

    public function formatQueryString($queryParams): string
    {
        if ($queryParams->isEmpty()) {
            return '';
        }

        $build = '';

        $i = 0;
        foreach ($queryParams->toArray() as $key => $value) {
            if ($i > 0) {
                $build .= '&';
            }

            $build .= $key . '=';

            if (is_array($value)) {
                $j = 0;

                foreach ($value as $item) {
                    if ($j > 0) {
                        $build .= ',';
                    }

                    $build .= $item;

                    $j++;
                }
            } else {
                $build .= $value;
            }

            $i++;
        }

        return '?' . $build;
    }
}
