<?php

namespace Tests\Browser\QueryString;

use Illuminate\Support\Facades\View;

enum Status: string
{
    case PUBLISHED = 'published';
    case DRAFT = 'draft';
}

class ComponentWithEnum extends Component
{
    public Status $status = Status::DRAFT;

    /**
     * @var array
     */
    protected $queryString = [
        'status'
    ];

    public function setStatusToDraft()
    {
        $this->status = Status::DRAFT;
    }

    public function render()
    {
        return View::file(__DIR__.'/component-with-enum.blade.php');
    }

}
