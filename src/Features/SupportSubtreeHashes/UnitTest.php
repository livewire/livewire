<?php

namespace Livewire\Features\SupportSubtreeHashes;

class UnitTest extends \Tests\TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config(['livewire.skip_unchanged.enabled' => true]);
    }

    public function test_nothing_is_hashed_unless_enabled()
    {
        config(['livewire.skip_unchanged.enabled' => false]);

        $component = \Livewire\Livewire::test(new class extends \Livewire\Component {
            public function render() { return '<div><p wire:key="a">'.str_repeat('a', 1100).'</p></div>'; }
        });

        $this->assertStringNotContainsString('wire:hash', $component->html());
    }

    public function test_keyed_elements_are_annotated_with_a_hash_of_their_markup()
    {
        $html = '<div><ul><li wire:key="a">A</li><li wire:key="b">B</li></ul></div>';

        $output = (new SubtreeHasher(minimumBytes: 0))->process($html);

        $this->assertStringContainsString('<li wire:hash="'.SubtreeHasher::hash('<li wire:key="a">A</li>').'" wire:key="a">A</li>', $output);
        $this->assertStringContainsString('<li wire:hash="'.SubtreeHasher::hash('<li wire:key="b">B</li>').'" wire:key="b">B</li>', $output);
    }

    public function test_known_subtrees_are_replaced_with_placeholders()
    {
        $hash = SubtreeHasher::hash('<li wire:key="a" class="x">A <b>bold</b></li>');

        $output = (new SubtreeHasher(minimumBytes: 0))->process(
            '<div><ul><li wire:key="a" class="x">A <b>bold</b></li><li wire:key="b">B</li></ul></div>',
            [$hash => 0],
        );

        $this->assertStringContainsString('<li wire:key="a" wire:hash="'.$hash.'" wire:same></li>', $output);
        $this->assertStringNotContainsString('bold', $output);
        $this->assertStringContainsString('>B</li>', $output);
    }

    public function test_changed_parents_still_skip_unchanged_children()
    {
        $child = '<span wire:key="child">same</span>';
        $hash = SubtreeHasher::hash($child);

        $output = (new SubtreeHasher(minimumBytes: 0))->process(
            '<div><p wire:key="parent">changed '.$child.'</p></div>',
            [$hash => 0],
        );

        $this->assertStringContainsString('<p wire:hash="', $output);
        $this->assertStringContainsString('changed <span wire:key="child" wire:hash="'.$hash.'" wire:same></span>', $output);
    }

    public function test_quoted_angle_brackets_do_not_end_a_tag()
    {
        $html = '<div><tr wire:key="row" x-show="count > 1"><td x-text="a > b ? \'<\' : \'>\'">1</td></tr></div>';

        $output = (new SubtreeHasher(minimumBytes: 0))->process($html, [SubtreeHasher::hash('<tr wire:key="row" x-show="count > 1"><td x-text="a > b ? \'<\' : \'>\'">1</td></tr>') => 0]);

        $this->assertSame('<div><tr wire:key="row" wire:hash="'.SubtreeHasher::hash('<tr wire:key="row" x-show="count > 1"><td x-text="a > b ? \'<\' : \'>\'">1</td></tr>').'" wire:same></tr></div>', $output);
    }

    public function test_root_nested_components_and_templates_are_left_alone()
    {
        $html = '<div wire:key="root"><div wire:id="child"><p wire:key="inner">x</p></div><div wire:key="keyed-child" wire:id="other">z</div><template x-for="i in items"><p wire:key="tpl">y</p></template></div>';

        $this->assertSame($html, (new SubtreeHasher(minimumBytes: 0))->process($html));
    }

    public function test_void_and_raw_text_elements_are_handled()
    {
        $html = '<div><input wire:key="field" value="1"><script>if (a < b) document.write("<p wire:key=\'nope\'>")</script><p wire:key="after">ok</p></div>';

        $output = (new SubtreeHasher(minimumBytes: 0))->process($html, [SubtreeHasher::hash('<input wire:key="field" value="1">') => 0]);

        $this->assertStringContainsString('<input wire:key="field" wire:hash="', $output);
        $this->assertStringNotContainsString('</input>', $output);
        $this->assertStringContainsString("<p wire:key='nope'>", $output);
        $this->assertStringContainsString('<p wire:hash="', $output);
    }

    public function test_a_double_quote_in_a_single_quoted_key_is_escaped_in_the_placeholder()
    {
        $markup = '<p wire:key=\'a"b\'>x</p>';

        $output = (new SubtreeHasher(minimumBytes: 0))->process('<div>'.$markup.'</div>', [SubtreeHasher::hash($markup) => 0]);

        $this->assertSame('<div><p wire:key="a&quot;b" wire:hash="'.SubtreeHasher::hash($markup).'" wire:same></p></div>', $output);
    }

    public function test_elements_closed_implicitly_are_never_tracked()
    {
        $html = '<div><ul wire:key="list"><li wire:key="a">A<li wire:key="b">B</ul></div>';

        $output = (new SubtreeHasher(minimumBytes: 0))->process($html);

        $this->assertStringContainsString('<li wire:key="a">A<li wire:key="b">B', $output);
        $this->assertStringContainsString('<ul wire:hash="', $output);
    }

    public function test_raw_text_only_ends_at_its_own_end_tag()
    {
        $html = '<div><script>let s = "</scripture><p wire:key=\'nope\'>"</script><p wire:key="after">ok</p></div>';

        $output = (new SubtreeHasher(minimumBytes: 0))->process($html);

        $this->assertStringContainsString("<p wire:key='nope'>", $output);
        $this->assertStringContainsString('<p wire:hash="', $output);
    }

    public function test_replaced_regions_and_what_holds_them_are_never_tracked()
    {
        $html = '<div><section wire:key="outer"><div wire:key="widget" wire:replace>x</div></section><p wire:key="other">y</p></div>';

        $output = (new SubtreeHasher(minimumBytes: 0))->process($html);

        $this->assertStringContainsString('<section wire:key="outer"><div wire:key="widget" wire:replace>x</div></section>', $output);
        $this->assertStringContainsString('<p wire:hash="', $output);
    }

    public function test_the_snapshot_says_whether_hashes_are_in_use()
    {
        $render = fn () => \Livewire\Livewire::test(new class extends \Livewire\Component {
            public function render() { return '<div></div>'; }
        })->snapshot['memo'];

        $this->assertTrue($render()['hashes'] ?? false);

        config(['livewire.skip_unchanged.enabled' => false]);

        $this->assertArrayNotHasKey('hashes', $render());
    }

    public function test_small_elements_are_not_hashed()
    {
        $html = '<div><p wire:key="small">x</p><p wire:key="large">'.str_repeat('y', 1100).'</p></div>';

        $output = (new SubtreeHasher)->process($html);

        $this->assertStringContainsString('<p wire:key="small">x</p>', $output);
        $this->assertStringContainsString('<p wire:hash="', $output);
    }

    public function test_hashes_are_parsed_from_a_concatenated_string()
    {
        $a = SubtreeHasher::hash('a');
        $b = SubtreeHasher::hash('b');

        $this->assertSame(11, strlen($a));
        $this->assertSame([$a => 0, $b => 1], SubtreeHasher::parse($a.$b));
        $this->assertSame([$a => 0], SubtreeHasher::parse([$a, 'bogus', 5]));
        $this->assertSame([], SubtreeHasher::parse(null));
    }

    public function test_known_hashes_from_the_request_are_used_on_update()
    {
        $component = \Livewire\Livewire::test(new class extends \Livewire\Component {
            public $items = ['a', 'b'];

            public $title = 'one';

            public function render() { return <<<'HTML'
                <div>
                    <h1>{{ $title }}</h1>
                    @foreach ($items as $item)
                        <p wire:key="{{ $item }}">{{ str_repeat($item, 1100) }}</p>
                    @endforeach
                </div>
            HTML; }
        });

        $this->assertStringContainsString('<p wire:hash="', $component->html());
        $this->assertStringNotContainsString('wire:same', $component->html());

        preg_match('/<p wire:hash="([^"]+)" wire:key="a"/', $component->html(), $match);

        $this->claimHashes($component->id(), [$match[1]]);

        $component->set('title', 'two');

        $this->assertStringContainsString('<h1>two</h1>', $component->html());
        $this->assertStringContainsString('<p wire:key="a" wire:hash="'.$match[1].'" wire:same></p>', $component->html());
        $this->assertStringContainsString(str_repeat('b', 1100), $component->html());
    }

    public function test_annotating_keeps_what_other_features_add_to_the_root()
    {
        \Livewire\Livewire::component('modelable-child', new class extends \Livewire\Component {
            #[\Livewire\Attributes\Modelable]
            public $value;

            public function render() { return '<div><p wire:key="row">'.str_repeat('r', 1100).'</p></div>'; }
        });

        $component = \Livewire\Livewire::test(new class extends \Livewire\Component {
            public $foo = 0;

            public function render() { return '<div><livewire:modelable-child wire:model="foo" /></div>'; }
        });

        $this->assertStringContainsString('x-modelable="$wire.value"', $component->html());
    }

    public function test_other_renders_during_an_update_never_get_placeholders()
    {
        $component = \Livewire\Livewire::test(new class extends \Livewire\Component {
            public $partial;

            public function renderPartial()
            {
                // Like a package rendering a partial through the same `render` hook...
                $view = \Mockery::mock(\Illuminate\View\View::class)->shouldIgnoreMissing();

                $finish = \Livewire\trigger('render', $this, $view, []);

                $html = '<div><p wire:key="a">'.str_repeat('a', 1100).'</p></div>';

                $replaceHtml = function ($newHtml) use (&$html) { $html = $newHtml; };

                $this->partial = $finish($html, $replaceHtml);
            }

            public function render() { return <<<'HTML'
                <div>
                    <p wire:key="a">{{ str_repeat('a', 1100) }}</p>
                </div>
            HTML; }
        });

        $this->claimHashes($component->id(), [SubtreeHasher::hash('<p wire:key="a">'.str_repeat('a', 1100).'</p>')]);

        $component->call('renderPartial');

        $this->assertStringNotContainsString('wire:same', $component->get('partial'));
        $this->assertStringNotContainsString('wire:hash', $component->get('partial'));
        $this->assertStringContainsString('wire:same', $component->html());
    }

    // Testable doesn't send hashes, so add them once the feature has read the request...
    protected function claimHashes($id, array $hashes)
    {
        \Livewire\on('request', function () use ($id, $hashes) {
            SupportSubtreeHashes::$known[$id] = array_flip($hashes);
        });
    }
}
