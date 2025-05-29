<?php

namespace Livewire\Drawer;

use Livewire\Component;
use Livewire\Exceptions\RootTagMissingFromViewException;
use Livewire\Livewire;

class UnitTest extends \Tests\TestCase
{
    public function test_root_element_has_id_and_component_data()
    {
        $component = Livewire::test(ComponentRootHasIdAndDataStub::class);

        $this->assertTrue(
            str($component->html())->containsAll([$component->id(), 'foo'])
        );
    }

    public function test_root_element_exists()
    {
        $this->expectException(RootTagMissingFromViewException::class);

        Livewire::test(ComponentRootExists::class);
    }

    public function test_component_data_stored_in_html_is_escaped()
    {
        $component = Livewire::test(ComponentRootHasIdAndDataStub::class);

        $this->assertStringContainsString(
            <<<EOT
{&quot;string&quot;:&quot;foo&quot;,&quot;array&quot;:[[&quot;foo&quot;],{&quot;s&quot;:&quot;arr&quot;}],&quot;object&quot;:[{&quot;foo&quot;:&quot;bar&quot;},{&quot;s&quot;:&quot;arr&quot;}],&quot;number&quot;:1,&quot;quote&quot;:&quot;\&quot;&quot;,&quot;singleQuote&quot;:&quot;&#039;&quot;}
EOT
            ,
            $component->html()
        );
    }

    public function test_if_element_is_a_comment_it_is_skipped_and_id_and_data_inserted_on_next_elemenet()
    {
        $component = Livewire::test(ComponentRootHasIdAndDataStub::class);

        $this->assertStringContainsString(
            <<<EOT
<!-- Test comment <div>Commented out code</div> -->
<span wire:snapshot
EOT
            ,
            $component->html()
        );
    }

    public function test_if_element_is_a_comment_and_contains_html_it_is_skipped_and_id_and_data_inserted_on_next_elemenet()
    {
        $component = Livewire::test(ComponentRootHasIdAndDataStub::class);

        $this->assertStringContainsString(
            <<<EOT
<!-- Test comment <div>Commented out code</div> -->
<span wire:snapshot
EOT
            ,
            $component->html()
        );
    }

    public function test_on_subsequent_renders_root_element_has_id_but_not_component_id()
    {
        $component = Livewire::test(ComponentRootHasIdAndDataStub::class);

        $component->call('$refresh');

        $this->assertStringContainsString($component->id(), $component->html());

        $this->assertStringNotContainsString('foo', $component->html());
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
        return app('view')->make('show-name', ['name' => str()->random(5)]);
    }
}

class ComponentRootExists extends Component
{
    public function render()
    {
        return 'This all you got?!';
    }
}
