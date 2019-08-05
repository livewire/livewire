<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;

class ComponentRootHasIdAndComponentDataTest extends TestCase
{
    /** @test */
    public function root_element_has_id_and_component_data()
    {
        $component = app(LivewireManager::class)->test(ComponentRootHasIdAndDataStub::class);

        $this->assertTrue(str_contains(
            $component->dom,
            [$component->id, $component->data]
        ));
    }

    /** @test */
    public function component_data_stored_in_html_is_escaped()
    {
        $component = app(LivewireManager::class)->test(ComponentRootHasIdAndDataStub::class);

        $this->assertStringContainsString(
            <<<EOT
wire:data="{&quot;string&quot;:&quot;foo&quot;,&quot;array&quot;:[&quot;foo&quot;],&quot;object&quot;:{&quot;foo&quot;:&quot;bar&quot;},&quot;number&quot;:1,&quot;quote&quot;:&quot;\&quot;&quot;,&quot;singleQuote&quot;:&quot;'&quot;}"
EOT
            , $component->dom
        );
    }
}

class ComponentRootHasIdAndDataStub extends Component
{
    public $string = 'foo';
    public $array = ['foo'];
    public $object = ['foo' => 'bar'];
    public $number = 1;
    public $quote = '"';
    public $singleQuote = "'";

    public function render()
    {
        return app('view')->make('null-view');
    }
}
