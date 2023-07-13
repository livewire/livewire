<?php

namespace Livewire\Features\SupportConsoleCommands\Commands;

use Illuminate\Console\Command;
use Illuminate\Pipeline\Pipeline;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\AddLiveModifierToEntangleDirectives;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\AddLiveModifierToWireModelDirectives;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\ChangeDefaultLayoutView;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\ChangeDefaultNamespace;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\ChangeLazyToBlurModifierOnWireModelDirectives;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\ChangeWireLoadDirectiveToWireInit;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\ClearViewCache;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\RemoveDeferModifierFromEntanglelDirectives;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\RemoveDeferModifierFromWireModelDirectives;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\RemovePrefetchModifierFromWireClickDirective;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\RemovePreventModifierFromWireSubmitDirective;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\ShowUpgradeIntroduction;

class UpgradeCommand extends Command
{
    protected $signature = 'livewire:upgrade';

    protected $description = 'Interactive upgrade helper to migrate from v2 to v3';

    public function handle()
    {

        app(Pipeline::class)->send($this)->through([
            ShowUpgradeIntroduction::class,
            ChangeDefaultNamespace::class,
            ChangeDefaultLayoutView::class,
            AddLiveModifierToWireModelDirectives::class,
            RemoveDeferModifierFromWireModelDirectives::class,
            ChangeLazyToBlurModifierOnWireModelDirectives::class,
            AddLiveModifierToEntangleDirectives::class,
            RemoveDeferModifierFromEntanglelDirectives::class,
            RemovePreventModifierFromWireSubmitDirective::class,
            RemovePrefetchModifierFromWireClickDirective::class,
            ChangeWireLoadDirectiveToWireInit::class,

            ClearViewCache::class,
        ])->thenReturn();
    }
}
