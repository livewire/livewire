<?php

namespace Livewire\V4\Paginators;

use Illuminate\Container\Container;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Livewire\ComponentHook;
use Livewire\WithPagination;

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

        Container::getInstance()->resolving(Paginator::class, function ($paginator) {
            $instance = app('livewire')->current();

            if ($instance) {
                $instance->setPaginatorInstance($paginator);
            }
        });

        Container::getInstance()->resolving(CursorPaginator::class, function ($paginator) {
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