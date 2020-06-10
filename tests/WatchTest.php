<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;
use PHPUnit\Framework\Assert as PHPUnit;

class WatchTest extends TestCase
{
    /** @test */
    public function run_all_callbacks()
    {
        $component = app(LivewireManager::class)->test(WatchWithClosures::class);

        $component->call('$set', 'nodeOne', 'bar');
        $component->call('$set', 'nodeTwo.0', ['foo' => 'bar']);
        $component->call('$set', 'nodeThree.0.biz', 'foo');

        $this->assertEquals([
            'nodeOne' => true,
            'nodeTwo.*' => true,
            'nodeThree.*.biz' => true,
        ], $component->watchersCalled);
    }

    /** @test */
    public function run_all_defined_methods()
    {
        $component = app(LivewireManager::class)->test(WatchWithMethods::class);

        $component->call('$set', 'nodeOne', 'bar');
        $component->call('$set', 'nodeTwo.0', ['foo' => 'bar']);
        $component->call('$set', 'nodeThree.0.biz', 'foo');

        $this->assertEquals([
            'nodeOne' => true,
            'nodeTwo.*' => true,
            'nodeThree.*.biz' => true,
        ], $component->watchersCalled);
    }
}

class WatchWithClosures extends Component
{
    public $nodeOne = 'foo';

    public $nodeTwo = [
        [
            'bar' => 'biz',
        ],
        [
            'biz' => 'fiz',
        ],
    ];

    public $nodeThree = [
        [
            'bar' => 'biz',
        ],
        [
            'biz' => 'fiz',
        ],
    ];

    public $watchersCalled = [
        'nodeOne' => false,
        'nodeTwo.*' => false,
        'nodeThree.*.biz' => false,
    ];

    protected function getWatchers()
    {
        return [
            'nodeOne' => function ($key, $value) {
                PHPUnit::assertNotNull($key);
                PHPUnit::assertNotNull($value);
                $this->watchersCalled['nodeOne'] = true;
            },
            'nodeTwo.*' => function ($key, $value) {
                PHPUnit::assertNotNull($key);
                PHPUnit::assertNotNull($value);
                $this->watchersCalled['nodeTwo.*'] = true;
            },
            'nodeThree.*.biz' => function ($key, $value) {
                PHPUnit::assertNotNull($key);
                PHPUnit::assertNotNull($value);
                $this->watchersCalled['nodeThree.*.biz'] = true;
            },
        ];
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}

class WatchWithMethods extends Component
{
    public $nodeOne = 'foo';

    public $nodeTwo = [
        [
            'bar' => 'biz',
        ],
        [
            'biz' => 'fiz',
        ],
    ];

    public $nodeThree = [
        [
            'bar' => 'biz',
        ],
        [
            'biz' => 'fiz',
        ],
    ];

    public $watchersCalled = [
        'nodeOne' => false,
        'nodeTwo.*' => false,
        'nodeThree.*.biz' => false,
    ];

    protected $watch = [
        'nodeOne' => 'nodeOne',
        'nodeTwo.*' => 'nodeTwo',
        'nodeThree.*.biz' => 'nodeThree',
    ];

    protected function nodeOne($key, $value)
    {
        PHPUnit::assertNotNull($key);
        PHPUnit::assertNotNull($value);
        $this->watchersCalled['nodeOne'] = true;
    }

    protected function nodeTwo($key, $value)
    {
        PHPUnit::assertNotNull($key);
        PHPUnit::assertNotNull($value);
        $this->watchersCalled['nodeTwo.*'] = true;
    }

    protected function nodeThree($key, $value)
    {
        PHPUnit::assertNotNull($key);
        PHPUnit::assertNotNull($value);
        $this->watchersCalled['nodeThree.*.biz'] = true;
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
