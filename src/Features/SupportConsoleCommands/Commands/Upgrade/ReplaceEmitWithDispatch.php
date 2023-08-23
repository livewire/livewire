<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Illuminate\Console\Command;

class ReplaceEmitWithDispatch extends UpgradeStep
{
    public function handle(Command $console, \Closure $next)
    {
        $console->newLine(2);
        $console->line("<fg=#FB70A9;bg=black;options=bold,reverse> Partial Manual Upgrade: Event dispatching </>");
        $console->newLine();
        $console->line('In v2 you could use the emit() and dispatchBrowserEvent() methods in PHP.');
        $console->line('For version 3, Livewire has unified these two methods into a single method: dispatch()');
        $console->line('This step is partially automated, given parameters must now be named parameters, you will have to do this manually.');
        $console->confirm('Ready to continue?');

        $this->interactiveReplacement(
            console: $console,
            title: '$this->emit is now $this->dispatch.',
            before: '$this->emit(\'post-created\');',
            after: '$this->dispatch(\'post-created\')',
            pattern: '/\$this->emit\((.*)\)/',
            replacement: '$this->dispatch($1)',
            directories: ['app', 'tests']
        );

        $this->manualUpgradeWarning(
            console: $console,
            warning: 'Please update the named parameters manually',
            before: '$this->dispatch(\'post-created\', $post->id);',
            after: '$this->dispatch(\'post-created\', postId: $post->id);'
        );

        $this->interactiveReplacement(
            console: $console,
            title: '$this->emitTo() is now $this->dispatch()->to().',
            before: '$this->emitTo(\'foo\', \'post-created\');',
            after: '$this->dispatch(\'post-created\')->to(\'foo\')',
            pattern: '/\$this->emitTo\((["\'][a-z0-9-.]*["\']),\s?([|"\'][a-zA-Z0-9-.]*["\'])(?:[,])?\s?(.*)\);/',
            replacement: function($matches) {
                $component = $matches[1];
                $eventName = $matches[2];
                $eventData = $matches[3];

                if (empty($eventData)) {
                    return "\$this->dispatch({$eventName})->to($component);";
                }

                return "\$this->dispatch({$eventName}, $eventData)->to($component);";
            },
            directories: ['app', 'tests']
        );

        $this->manualUpgradeWarning(
            console: $console,
            warning: 'Please update the named parameters manually',
            before: '$this->dispatch(\'post-created\', $post->id)->to(\'foo\');',
            after: '$this->dispatch(\'post-created\', postId: $post->id)->to(\'foo\');'
        );

        $this->interactiveReplacement(
            console: $console,
            title: '$this->emitSelf() is now $this->dispatch()->self().',
            before: '$this->emitSelf(\'post-created\');',
            after: '$this->dispatch(\'post-created\')->self();',
            pattern: '/\$this->emitSelf\((.*)\)/',
            replacement: '\$this->dispatch($1)->self()',
            directories: ['app', 'tests']
        );

        $this->manualUpgradeWarning(
            console: $console,
            warning: 'Please update the named parameters manually',
            before: '$this->dispatch(\'post-created\', $post->id)->self();',
            after: '$this->dispatch(\'post-created\', postId: $post->id)->self();'
        );

        $this->interactiveReplacement(
            console: $console,
            title: '$this->dispatchBrowserEvent() is now $this->dispatch().',
            before: '$this->dispatchBrowserEvent(\'post-created\');',
            after: '$this->dispatch(\'post-created\');',
            pattern: '/\$this->dispatchBrowserEvent\((.*)\)/',
            replacement: '\$this->dispatch($1)',
            directories: ['app', 'tests']
        );

        $this->manualUpgradeWarning(
            console: $console,
            warning: 'Please update the named parameters manually',
            before: '$this->dispatch(\'post-created\', [\'postId\' => $post->id]);',
            after: '$this->dispatch(\'post-created\', postId: $post->id);'
        );

        $this->interactiveReplacement(
            console: $console,
            title: 'The $emit helper is now $dispatch.',
            before: '$emit(\'post-created\');',
            after: '$dispatch(\'post-created\')',
            pattern: '/\$emit\((.*)\)/',
            replacement: '\$dispatch($1)',
            directories: ['resources']
        );

        $this->manualUpgradeWarning(
            console: $console,
            warning: 'Please update the named parameters manually',
            before: '$dispatch(\'post-created\', 1);',
            after: '$dispatch(\'post-created\', {postId: 1});'
        );

        $this->manualUpgradeWarning(
            console: $console,
            warning: 'The concept of `emitUp` has been removed entirely. Events are now dispatched as actual browser events and therefore "bubble up" by default.',
            before: ['$this->emitUp(\'post-created\');', '$emitUp(\'post-created\', 1)'],
            after: ['<removed>', '<removed>'],
        );

        if($console->confirm('Continue?', true))
        {
            return $next($console);
        }
    }
}
