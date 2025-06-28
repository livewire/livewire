<?php

namespace Livewire\V4\Paginators;

use Livewire\WithPagination;
use Livewire\ComponentHook;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Container\Container;

class SupportPaginators extends ComponentHook
{
    static $paginators = [];

    function skip()
    {
        return ! in_array(WithPagination::class, class_uses_recursive($this->component));
    }

    static function provide()
    {
        Container::getInstance()->resolving(LengthAwarePaginator::class, function ($paginator) {
            $instance = app('livewire')->current();

            if ($instance) {
                $instance->setPaginatorInstance($paginator);
            }
        });
    }

    public function dehydrate($context)
    {
        $payloads = $this->component->getPaginatorPayloads();

        if (count($payloads)) {
            $context->addEffect('paginators', $payloads);
        }
    }
}