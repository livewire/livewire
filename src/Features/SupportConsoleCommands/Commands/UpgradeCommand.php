<?php

namespace Livewire\Features\SupportConsoleCommands\Commands;

use Illuminate\Console\Command;
use Illuminate\Pipeline\Pipeline;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\AddLiveModifierToEntangleDirectives;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\AddLiveModifierToWireModelDirectives;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\ChangeDefaultLayoutView;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\ChangeDefaultNamespace;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\ChangeLazyToBlurModifierOnWireModelDirectives;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\ChangeTestAssertionMethods;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\ChangeWireLoadDirectiveToWireInit;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\ClearViewCache;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\RemoveDeferModifierFromEntangleDirectives;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\RemoveDeferModifierFromWireModelDirectives;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\RemovePrefetchModifierFromWireClickDirective;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\RemovePreventModifierFromWireSubmitDirective;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\RepublishNavigation;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\ThirdPartyUpgradeNotice;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\UpgradeAlpineInstructions;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\UpgradeConfigInstructions;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\ReplaceEmitWithDispatch;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\UpgradeIntroduction;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\ChangeForgetComputedToUnset;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'livewire:upgrade')]
class UpgradeCommand extends Command
{
    protected $signature = 'livewire:upgrade {--run-only=}';

    protected $description = 'Interactive upgrade helper to migrate from v2 to v3';

    protected static $thirdPartyUpgradeSteps = [];

    public function handle()
    {
        app(Pipeline::class)->send($this)->through(collect([
            UpgradeIntroduction::class,

            // Automated steps
            ChangeDefaultNamespace::class,
            ChangeDefaultLayoutView::class,
            AddLiveModifierToWireModelDirectives::class,
            RemoveDeferModifierFromWireModelDirectives::class,
            ChangeLazyToBlurModifierOnWireModelDirectives::class,
            AddLiveModifierToEntangleDirectives::class,
            RemoveDeferModifierFromEntangleDirectives::class,
            RemovePreventModifierFromWireSubmitDirective::class,
            RemovePrefetchModifierFromWireClickDirective::class,
            ChangeWireLoadDirectiveToWireInit::class,
            RepublishNavigation::class,
            ChangeTestAssertionMethods::class,
            ChangeForgetComputedToUnset::class,

            // Partially automated steps
            ReplaceEmitWithDispatch::class,

            // Manual steps
            UpgradeConfigInstructions::class,
            UpgradeAlpineInstructions::class,

            // Third-party steps
            ... static::$thirdPartyUpgradeSteps,

            ClearViewCache::class,
        ])->when($this->option('run-only'), function($collection) {
            return $collection->filter(fn($step) => str($step)->afterLast('\\')->kebab()->is($this->option('run-only')));
        })->toArray())
        ->thenReturn();
    }

    public static function addThirdPartyUpgradeStep($step)
    {
        if(empty(static::$thirdPartyUpgradeSteps)) {
            static::$thirdPartyUpgradeSteps[] = ThirdPartyUpgradeNotice::class;
        }

        static::$thirdPartyUpgradeSteps[] = $step;
    }
}
