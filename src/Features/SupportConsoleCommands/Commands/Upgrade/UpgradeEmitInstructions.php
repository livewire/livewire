<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Illuminate\Console\Command;

class UpgradeEmitInstructions extends UpgradeStep
{
    public function handle(Command $console, \Closure $next)
    {
        $console->line("<fg=#FB70A9;bg=black;options=bold,reverse> Manual Upgrade: Event dispatching </>");
        $console->newLine();
        $console->line('In v2 you could use the emit() and dispatchBrowserEvent() mmethods in PHP.');
        $console->line('For version 3, Livewire has unified these two methods into a single method: dispatch()');
        $console->line('You must rename all instances of emit() to dispatch() in your components.');
        $console->line('All parameters must be named, for example:');
        $console->newLine();

        $console->line('<fg=red;>-$this->emit(\'post-created\');</>');
        $console->line('<fg=green;>+$this->dispatch(\'post-created\');</>');
        $console->newLine();

        $console->line('<fg=red;>-$this->emitTo(\'foo\', \'post-created\');</>');
        $console->line('<fg=green;>+$this->dispatch(\'post-created\')->to(\'foo\');</>');
        $console->newLine();

        $console->line('<fg=red;>-$this->emitSelf(\'foo\', \'post-created\');</>');
        $console->line('<fg=green;>+$this->dispatch(\'post-created\')->self();</>');
        $console->newLine();

        $console->line('<fg=red;>-$this->emit(\'post-created\', $post->id);</>');
        $console->line('<fg=green;>+$this->dispatch(\'post-created\', postId: $post->id);</>');
        $console->newLine();

        $console->line('<fg=red;>-$this->dispatchBrowserEvent(\'post-created\');</>');
        $console->line('<fg=green;>+$this->dispatch(\'post-created\');</>');
        $console->newLine();

        $console->line('<fg=red;>-$this->dispatchBrowserEvent(\'post-created\', [\'postId\' => $post->id]);</>');
        $console->line('<fg=green;>+$this->dispatch(\'post-created\', postId: $post->id);</>');
        $console->newLine();

        $console->line('The same changes apply when you\'ve used the $emit helper on the front-end:');
        $console->newLine();

        $console->line('<fg=red;>-<button wire:click="$emit(\'post-created\')">...</button></>');
        $console->line('<fg=green;>+<button wire:click="$dispatch(\'post-created\')">...</button></>');
        $console->newLine();

        $console->line('<fg=red;>-<button wire:click="$emit(\'post-created\', 1)">...</button></>');
        $console->line('<fg=green;>+<button wire:click="$dispatch(\'post-created\', {postId: 1})">...</button></>');
        $console->newLine();

        $console->line('The concept of `emitUp` has been removed entirely. Events are not dispatched as actual browser events and therefore "bubble up" by default.');
        $console->newLine();

        $console->line('<fg=red;>-$this->emitUp(\'post-created\');</>');
        $console->line('<fg=red;>-<button wire:click="$emitUp(\'post-created\', 1)">...</button></>');

        if($console->confirm('Continue?', true))
        {
            return $next($console);
        }
    }
}
