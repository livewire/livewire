<?php

namespace LegacyTests\Browser\QueryString;

trait WithSearch
{
    /**
     * @var string
     */
    public $search = '';

    /**
     * @var array
     */
    protected $queryStringWithSearch = ['search' => []];

    /**
     * @return void
     */
    public function initializeWithSearch()
    {
        $this->search = request()->query('search', $this->search);
    }
}
