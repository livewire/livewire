<?php

namespace Livewire\Features\SupportPagination;

use Livewire\Features\SupportQueryString\BaseUrl;

#[\Attribute]
class PaginationUrl extends BaseUrl
{
    // In the case of Lazy components, the paginator won't resolve and initialize
    // until the subsequent request. Therefore, we need to override "dehydrate"
    // so its not blocked on a subsequent request by the "mounting" condition
    public function dehydrate($context)
    {
        $this->pushQueryStringEffect($context);
    }
}
