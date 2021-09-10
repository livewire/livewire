<?php

namespace Tests\Browser\QueryString;

trait WithSearch
{
    /**
     * @var string
     */
    public $search = '';

    /**
     * @var array
     */
    protected $queryStringWithSearch = ['search' => ['except' => '']];

    /**
     * @return void
     */
    public function updatedSearch()
    {
        $this->resetPage();
    }

    /**
     * @return void
     */
    public function initializeWithSearch()
    {
        $this->search = request()->query('search', $this->search);
    }
}
