<?php

namespace Tests\Unit;

use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Exceptions\RootTagMissingFromViewException;
use Livewire\Livewire;
use Livewire\LivewireManager;

class ComponentRootHasIdAndComponentDataTest extends TestCase
{
    /** @test */
    public function root_element_has_id_and_component_data()
    {
        $component = Livewire::test(ComponentRootHasIdAndDataStub::class);

        $this->assertTrue(Str::contains(
            $component->payload['effects']['html'],
            [$component->id(), 'foo']
        ));
    }

    /** @test */
    public function root_element_exists()
    {
        $this->expectException(RootTagMissingFromViewException::class);

        $component = Livewire::test(ComponentRootExists::class);
    }

    /** @test */
    public function component_data_stored_in_html_is_escaped()
    {
        $component = Livewire::test(ComponentRootHasIdAndDataStub::class);

        $this->assertStringContainsString(
            <<<EOT
{&quot;string&quot;:&quot;foo&quot;,&quot;array&quot;:[&quot;foo&quot;],&quot;object&quot;:{&quot;foo&quot;:&quot;bar&quot;},&quot;number&quot;:1,&quot;quote&quot;:&quot;\&quot;&quot;,&quot;singleQuote&quot;:&quot;'&quot;}
EOT
            , $component->payload['effects']['html']
        );
    }

    /** @test */
    public function on_subsequent_renders_root_element_has_id_but_not_component_id()
    {
        $component = Livewire::test(ComponentRootHasIdAndDataStub::class);

        $component->call('$refresh');

        $this->assertTrue(Str::contains(
            $component->lastRenderedDom, $component->id()
        ));

        $this->assertFalse(Str::contains(
            $component->lastRenderedDom, 'foo'
        ));
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
        return app('view')->make('show-name', ['name' => Str::random(5)]);
    }
}

class ComponentRootExists extends Component
{
    public function render()
    {
        return app('view')->make('rootless-view');
    }
}
