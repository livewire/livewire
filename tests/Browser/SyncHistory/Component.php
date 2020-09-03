<?php

namespace Tests\Browser\SyncHistory;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public User $user;
    public bool $liked = false;

    protected $queryString = ['liked'];

    public function mount()
    {
        $this->liked = request()->query('liked', $this->liked);
    }

    public function setUser($id)
    {
        $this->user = User::findOrFail($id);
    }

    public function toggleLike()
    {
        $this->liked = !$this->liked;
    }

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
