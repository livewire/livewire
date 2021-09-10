<?php

namespace Tests\Browser\QueryString;

trait WithSearch
{
    /**
     * @var string
     */
    public $search = '';

    /**
     * @return array
     */
    public function queryStringWithSearch()
    {
        return ['search' => ['except' => '']];
    }

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
