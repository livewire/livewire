<?php

namespace Livewire\V4\Paginators;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute]
class BasePaginator extends LivewireAttribute
{
    public function __construct(
        //
    ) {}

    public function dehydrate($context)
    {
        $methodName = $this->getName();

        $paginator = $this->component->{$methodName}();

        $context->pushMemo('paginators', [
            'hasNextPage' => $paginator->hasMorePages(),
            'hasPreviousPage' => ! $paginator->onFirstPage(),
        ], 'default');
    }
}