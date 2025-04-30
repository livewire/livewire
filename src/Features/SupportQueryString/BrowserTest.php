<?php

namespace Livewire\Features\SupportQueryString;

use Livewire\Livewire;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Features\SupportTesting\DuskTestable;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_it_does_not_add_null_values_to_the_query_string_array()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                #[Url]
                public array $tableFilters = [
                    'filter_1' => [
                        'value' => null,
                    ],
                    'filter_2' => [
                        'value' => null,
                    ],
                    'filter_3' => [
                        'value' => null,
                    ]
                ];

                public function render() { return <<<'HTML'
                <div>
                    <input wire:model.live="tableFilters.filter_1.value" type="text" dusk="filter_1" />

                    <input wire:model.live="tableFilters.filter_2.value" type="text" dusk="filter_2" />

                    <input wire:model.live="tableFilters.filter_3.value" type="text" dusk="filter_3" />
                </div>
                HTML; }
            },
        ])
        ->assertInputValue('@filter_1', '')
        ->assertInputValue('@filter_2', '')
        ->assertInputValue('@filter_3', '')
        ->assertQueryStringMissing('tableFilters')
        ->type('@filter_1', 'test')
        ->waitForLivewire()
        ->assertScript(
            '(new URLSearchParams(window.location.search)).toString()',
            'tableFilters%5Bfilter_1%5D%5Bvalue%5D=test'
        )
        ->refresh()
        ->assertInputValue('@filter_1', 'test')
        ;
    }

    public function test_keep_option_does_not_duplicate_url_query_string_for_array_parameters_on_page_load()
    {
        Livewire::withQueryParams([
            'filters' => [
                'startDate' => '2024-01-01',
                'endDate' => '2024-09-05',
            ]
        ])->visit([
            new class extends Component
            {
                #[BaseUrl(keep: true)]
                public array $filters = [
                    'startDate' => '',
                    'endDate' => '',
                ];

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <input type="text" dusk="startDate" wire:model.live="filters.startDate" />
                        <input type="text" dusk="endDate" wire:model.live="filters.endDate" />
                    </div>
                    HTML;
                }
            },
        ])
            ->assertScript('return window.location.search', '?filters[startDate]=2024-01-01&filters[endDate]=2024-09-05');
    }

    public function test_does_not_duplicate_url_query_string_for_array_parameters_on_page_load()
    {
        Livewire::withQueryParams([
            'filters' => [
                'startDate' => '2024-01-01',
                'endDate' => '2024-09-05',
            ]
        ])->visit([
            new class extends Component
            {
                #[BaseUrl]
                public array $filters = [
                    'startDate' => '',
                    'endDate' => '',
                ];

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <input type="text" dusk="startDate" wire:model.live="filters.startDate" />
                        <input type="text" dusk="endDate" wire:model.live="filters.endDate" />
                    </div>
                    HTML;
                }
            },
        ])
            ->assertScript('return window.location.search', '?filters[startDate]=2024-01-01&filters[endDate]=2024-09-05');
    }

    public function test_keep_option_does_not_duplicate_url_query_string_for_string_parameter_on_page_load()
    {
        Livewire::withQueryParams([
            'date' => '2024-01-01',
        ])->visit([
            new class extends Component
            {
                #[BaseUrl(keep: true)]
                public $date = '';

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <input type="text" dusk="date" wire:model.live="date" />
                    </div>
                    HTML;
                }
            },
        ])
            ->assertScript('return window.location.search', '?date=2024-01-01');
    }


    public function can_encode_url_containing_spaces_and_commas()
    {
        Livewire::visit([
            new class extends Component
            {
                #[BaseUrl]
                public $space = '';

                #[BaseUrl]
                public $comma = '';

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <input type="text" dusk="space" wire:model.live="space" />
                        <input type="text" dusk="comma" wire:model.live="comma" />
                    </div>
                    HTML;
                }
            },
        ])
            ->waitForLivewire()
            ->type('@space', 'foo bar')
            ->type('@comma', 'foo,bar')
            ->assertScript('return !! window.location.search.match(/space=foo\+bar/)')
            ->assertScript('return !! window.location.search.match(/comma=foo\,bar/)');
    }

    public function test_can_encode_url_containing_reserved_characters()
    {
        Livewire::visit([
            new class extends Component
            {
                #[BaseUrl]
                public $exclamation = '';

                #[BaseUrl]
                public $quote = '';

                #[BaseUrl]
                public $parentheses = '';

                #[BaseUrl]
                public $asterisk = '';

                public function render()
                {
                    return <<<'HTML'
                     <div>
                         <input type="text" dusk="exclamation" wire:model.live="exclamation" />
                         <input type="text" dusk="quote" wire:model.live="quote" />
                         <input type="text" dusk="parentheses" wire:model.live="parentheses" />
                         <input type="text" dusk="asterisk" wire:model.live="asterisk" />
                     </div>
                     HTML;
                }
            },
        ])
            ->waitForLivewire()
            ->type('@exclamation', 'foo!')
            ->type('@parentheses', 'foo(bar)')
            ->type('@asterisk', 'foo*')
            ->assertScript('return !! window.location.search.match(/exclamation=foo\!/)')
            ->assertScript('return !! window.location.search.match(/parentheses=foo\(bar\)/)')
            ->assertScript('return !! window.location.search.match(/asterisk=foo\*/)')
        ;
    }

    public function test_can_use_a_value_other_than_initial_for_except_behavior()
    {
        Livewire::visit([
            new class extends Component
            {
                #[BaseUrl(except: '')]
                public $search = '';

                public function mount()
                {
                    $this->search = 'foo';
                }

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <input type="text" dusk="input" wire:model.live="search" />
                    </div>
                    HTML;
                }
            },
        ])
            ->assertQueryStringHas('search', 'foo')
            ->waitForLivewire()->type('@input', 'bar')
            ->assertQueryStringHas('search', 'bar')
            ->waitForLivewire()->type('@input', ' ')
            ->waitForLivewire()->keys('@input', '{backspace}')
            ->assertQueryStringMissing('search')
        ;
    }

    public function test_except_removes_property_from_query_string_when_original_value_set_from_query_string()
    {
        Livewire::withQueryParams(['filter1' => 'some', 'filter2' => 'none'])->visit([
            new class extends Component
            {
                #[BaseUrl(except: '')]
                public $filter1 = '';

                #[BaseUrl(except: 'all')]
                public $filter2 = 'all';

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <select dusk="filter1" wire:model.change="filter1">
                            <option value="">All</option>
                            <option value="some">Some</option>
                            <option value="none">None</option>
                        </select>
                        <div dusk="output1">
                            @switch($filter1)
                              @case('')
                                <div>All</div>
                              @case('some')
                                <div>Some</div>
                              @break
                            @endswitch
                        </div>
                        <select dusk="filter2" wire:model.change="filter2">
                            <option value="all">All</option>
                            <option value="some">Some</option>
                            <option value="none">None</option>
                        </select>
                        <div dusk="output2">
                            @switch($filter2)
                              @case('all')
                                <div>All</div>
                              @case('some')
                                <div>Some</div>
                              @break
                            @endswitch
                        </div>
                    </div>
                    HTML;
                }
            },
        ])
            ->assertQueryStringHas('filter1', 'some')
            ->assertDontSeeIn('@output1', 'All')
            ->assertSeeIn('@output1', 'Some')
            ->assertQueryStringHas('filter2', 'none')
            ->assertDontSeeIn('@output2', 'All')
            ->assertDontSeeIn('@output2', 'Some')

            ->waitForLivewire()->select('@filter1', '')
            ->assertQueryStringMissing('filter1')
            ->assertSeeIn('@output1', 'All')
            ->assertSeeIn('@output1', 'Some')
            ->assertQueryStringHas('filter2', 'none')
            ->assertDontSeeIn('@output2', 'All')
            ->assertDontSeeIn('@output2', 'Some')

            ->waitForLivewire()->select('@filter2', 'all')
            ->assertQueryStringMissing('filter1')
            ->assertSeeIn('@output1', 'All')
            ->assertSeeIn('@output1', 'Some')
            ->assertQueryStringMissing('filter2')
            ->assertSeeIn('@output2', 'All')
            ->assertSeeIn('@output2', 'Some')

            ->waitForLivewire()->select('@filter1', 'none')
            ->assertQueryStringHas('filter1', 'none')
            ->assertDontSeeIn('@output1', 'All')
            ->assertDontSeeIn('@output1', 'Some')
            ->assertQueryStringMissing('filter2')
            ->assertSeeIn('@output2', 'All')
            ->assertSeeIn('@output2', 'Some')

        ;
    }

    public function test_initial_values_loaded_from_querystring_are_not_removed_from_querystring_on_load_if_they_are_different_to_the_default()
    {
        Livewire::withQueryParams(['perPage' => 25])->visit([
            new class extends Component
            {
                #[BaseUrl]
                public $perPage = '15';

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <input type="text" dusk="input" wire:model.live="perPage" />
                    </div>
                    HTML;
                }
            },
        ])
            ->waitForLivewireToLoad()
            ->assertQueryStringHas('perPage', '25')
            ->assertInputValue('@input', '25')
        ;
    }

    public function test_can_use_except_in_query_string_property()
    {
        Livewire::visit([
            new class extends Component
            {
                protected $queryString = [
                    'search' => [
                        'except' => '',
                        'history' => false,
                    ],
                ];

                public $search = '';

                public function mount()
                {
                    $this->search = 'foo';
                }

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <input type="text" dusk="input" wire:model.live="search" />
                    </div>
                    HTML;
                }
            },
        ])
            ->assertQueryStringHas('search', 'foo')
            ->waitForLivewire()->type('@input', 'bar')
            ->assertQueryStringHas('search', 'bar')
            ->waitForLivewire()->type('@input', ' ')
            ->waitForLivewire()->keys('@input', '{backspace}')
            ->assertQueryStringMissing('search')
        ;
    }

    public function test_can_use_url_on_form_object_properties()
    {
        Livewire::visit([
            new class extends Component
            {
                public FormObject $form;

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <input type="text" dusk="foo.input" wire:model.live="form.foo" />
                        <input type="text" dusk="bob.input" wire:model.live="form.bob" />
                    </div>
                    HTML;
                }
            },
        ])
            ->assertQueryStringMissing('foo')
            ->assertQueryStringMissing('bob')
            ->assertQueryStringMissing('aliased')
            ->waitForLivewire()->type('@foo.input', 'baz')
            ->assertQueryStringHas('foo', 'baz')
            ->assertQueryStringMissing('bob')
            ->assertQueryStringMissing('aliased')
            ->waitForLivewire()->type('@bob.input', 'law')
            ->assertQueryStringHas('foo', 'baz')
            ->assertQueryStringMissing('bob')
            ->assertQueryStringHas('aliased', 'law')
        ;
    }

    public function test_can_use_url_on_string_backed_enum_object_properties()
    {
        Livewire::visit([
            new class extends Component
            {
                #[BaseUrl]
                public StringBackedEnumForUrlTesting $foo = StringBackedEnumForUrlTesting::First;

                public function change()
                {
                    $this->foo = StringBackedEnumForUrlTesting::Second;
                }

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <button wire:click="change" dusk="button">Change</button>
                        <h1 dusk="output">{{ $foo }}</h1>
                    </div>
                    HTML;
                }
            },
        ])
            ->assertQueryStringMissing('foo')
            ->assertSeeIn('@output', 'first')
            ->waitForLivewire()->click('@button')
            ->assertQueryStringHas('foo', 'second')
            ->assertSeeIn('@output', 'second')
            ->refresh()
            ->assertQueryStringHas('foo', 'second')
            ->assertSeeIn('@output', 'second')
        ;
    }

    public function test_can_use_url_on_integer_backed_enum_object_properties()
    {
        Livewire::visit([
            new class extends Component
            {
                #[BaseUrl]
                public IntegerBackedEnumForUrlTesting $foo = IntegerBackedEnumForUrlTesting::First;

                public function change()
                {
                    $this->foo = IntegerBackedEnumForUrlTesting::Second;
                }

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <button wire:click="change" dusk="button">Change</button>
                        <h1 dusk="output">{{ $foo }}</h1>
                    </div>
                    HTML;
                }
            },
        ])
            ->assertQueryStringMissing('foo')
            ->assertSeeIn('@output', '1')
            ->waitForLivewire()->click('@button')
            ->assertQueryStringHas('foo', '2')
            ->assertSeeIn('@output', '2')
            ->refresh()
            ->assertQueryStringHas('foo', '2')
            ->assertSeeIn('@output', '2')
        ;
    }

    public function test_can_use_url_on_string_backed_enum_object_properties_with_initial_invalid_value_on_nullable()
    {
        Livewire::withQueryParams(['foo' => 'bar'])
            ->visit([
            new class extends Component
            {
                #[Url(nullable: true)]
                public ?StringBackedEnumForUrlTesting $foo;

                public function change()
                {
                    $this->foo = StringBackedEnumForUrlTesting::Second;
                }

                public function unsetFoo()
                {
                    $this->foo = null;
                }

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <button wire:click="change" dusk="button">Change</button>
                        <h1 dusk="output">{{ $foo }}</h1>
                        <button wire:click="unsetFoo" dusk="unsetButton">Unset foo</button>
                    </div>
                    HTML;
                }
            },
        ])
            ->assertQueryStringHas('foo', '')
            ->assertSee('foo', null)
            ->waitForLivewire()->click('@button')
            ->assertQueryStringHas('foo', 'second')
            ->assertSeeIn('@output', 'second')
            ->refresh()
            ->assertQueryStringHas('foo', 'second')
            ->assertSeeIn('@output', 'second')
        ;
    }


    public function test_can_use_url_on_integer_backed_enum_object_properties_with_initial_invalid_value_on_nullable()
    {
        Livewire::withQueryParams(['foo' => 5])
            ->visit([
            new class extends Component
            {
                #[Url(nullable: true)]
                public ?IntegerBackedEnumForUrlTesting $foo;

                public function change()
                {
                    $this->foo = IntegerBackedEnumForUrlTesting::Second;
                }

                public function unsetFoo()
                {
                    $this->foo = null;
                }

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <button wire:click="change" dusk="button">Change</button>
                        <h1 dusk="output">{{ $foo }}</h1>
                        <button wire:click="unsetFoo" dusk="unsetButton">Unset foo</button>
                    </div>
                    HTML;
                }
            },
        ])
            ->assertQueryStringHas('foo', '')
            ->assertSee('foo', null)
            ->waitForLivewire()->click('@button')
            ->assertQueryStringHas('foo', '2')
            ->assertSeeIn('@output', '2')
            ->refresh()
            ->assertQueryStringHas('foo', '2')
            ->assertSeeIn('@output', '2')
        ;
    }

    public function test_it_does_not_break_string_typed_properties()
    {
        Livewire::withQueryParams(['foo' => 'bar'])
            ->visit([
                new class extends Component
                {
                    #[BaseUrl]
                    public string $foo = '';

                    public function render()
                    {
                        return <<<'HTML'
                        <div>
                            <h1 dusk="output">{{ $foo }}</h1>
                        </div>
                        HTML;
                    }
                },
            ])
            ->assertSeeIn('@output', 'bar')
        ;
    }

    public function test_can_use_url_on_lazy_component()
    {
        Livewire::visit([
            new class extends Component
            {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <livewire:child lazy />
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component
            {
                #[BaseUrl]
                public $foo = 'bar';

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <div>lazy loaded</div>
                        <input type="text" dusk="foo.input" wire:model.live="foo" />
                    </div>
                    HTML;
                }
            },
        ])
            ->waitForText('lazy loaded')
            ->assertQueryStringMissing('foo')
            ->waitForLivewire()->type('@foo.input', 'baz')
            ->assertQueryStringHas('foo', 'baz')
        ;
    }

    public function test_can_unset_the_array_key_when_using_dot_notation_without_except()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public array $tableFilters = [];

                protected function queryString() {
                    return [
                        'tableFilters.filter_1.value' => [
                            'as' => 'filter',
                        ],
                    ];
                }

                public function clear()
                {
                    unset($this->tableFilters['filter_1']['value']);
                }

                public function render() { return <<<'HTML'
                <div>
                    <input wire:model.live="tableFilters.filter_1.value" type="text" dusk="filter" />

                    <span dusk="output">@json($tableFilters)</span>

                    <button dusk="clear" wire:click="clear">Clear</button>
                </div>
                HTML; }
            },
        ])
            ->assertInputValue('@filter', '')
            ->waitForLivewire()->type('@filter', 'foo')
            ->assertSeeIn('@output', '{"filter_1":{"value":"foo"}}')
            ->waitForLivewire()->click('@clear')
            ->assertInputValue('@filter', '')
            ->assertQueryStringMissing('filter')
        ;
    }

    public function test_can_unset_the_array_key_when_with_except()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public array $tableFilters = [];

                protected function queryString() {
                    return [
                        'tableFilters' => [
                            'filter_1' => [
                                'value' => [
                                    'as' => 'filter',
                                    'except' => '',
                                ],
                            ]
                        ],
                    ];
                }

                public function clear()
                {
                    unset($this->tableFilters['filter_1']['value']);
                }

                public function render() { return <<<'HTML'
                <div>
                    <input wire:model.live="tableFilters.filter_1.value" type="text" dusk="filter" />

                    <span dusk="output">@json($tableFilters)</span>

                    <button dusk="clear" wire:click="clear">Clear</button>
                </div>
                HTML; }
            },
        ])
            ->assertInputValue('@filter', '')
            ->waitForLivewire()->type('@filter', 'foo')
            ->assertSeeIn('@output', '{"filter_1":{"value":"foo"}}')
            ->waitForLivewire()->click('@clear')
            ->assertInputValue('@filter', '')
            ->assertQueryStringMissing('filter')
        ;
    }

    public function test_can_unset_the_array_key_when_without_except()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public array $tableFilters = [];

                protected function queryString() {
                    return [
                        'tableFilters' => [
                            'filter_1' => [
                                'value' => [
                                    'as' => 'filter',
                                ],
                            ]
                        ],
                    ];
                }

                public function clear()
                {
                    unset($this->tableFilters['filter_1']['value']);
                }

                public function render() { return <<<'HTML'
                <div>
                    <input wire:model.live="tableFilters.filter_1.value" type="text" dusk="filter" />

                    <span dusk="output">@json($tableFilters)</span>

                    <button dusk="clear" wire:click="clear">Clear</button>
                </div>
                HTML; }
            },
        ])
            ->assertInputValue('@filter', '')
            ->waitForLivewire()->type('@filter', 'foo')
            ->assertSeeIn('@output', '{"filter_1":{"value":"foo"}}')
            ->waitForLivewire()->click('@clear')
            ->assertInputValue('@filter', '')
            ->assertQueryStringMissing('filter')
        ;
    }

    public function test_can_unset_the_array_key_when_using_dot_notation_with_except()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public array $tableFilters = [];

                protected function queryString() {
                    return [
                        'tableFilters.filter_1.value' => [
                            'as' => 'filter',
                            'except' => ''
                        ],
                    ];
                }

                public function clear()
                {
                    unset($this->tableFilters['filter_1']['value']);
                }

                public function render() { return <<<'HTML'
                <div>
                    <input wire:model.live="tableFilters.filter_1.value" type="text" dusk="filter" />

                    <span dusk="output">@json($tableFilters)</span>

                    <button dusk="clear" wire:click="clear">Clear</button>
                </div>
                HTML; }
            },
        ])
            ->assertInputValue('@filter', '')
            ->waitForLivewire()->type('@filter', 'foo')
            ->assertSeeIn('@output', '{"filter_1":{"value":"foo"}}')
            ->waitForLivewire()->click('@clear')
            ->assertInputValue('@filter', '')
            ->assertQueryStringMissing('filter')
        ;
    }

    public function test_can_handle_empty_querystring_value_as_empty_string()
    {
        Livewire::visit([
            new class extends Component
            {
                #[Url]
                public $foo;

                public function setFoo()
                {
                    $this->foo = 'bar';
                }

                public function unsetFoo()
                {
                    $this->foo = '';
                }

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <button wire:click="setFoo" dusk="setButton">Set foo</button>
                        <button wire:click="unsetFoo" dusk="unsetButton">Unset foo</button>
                        <span dusk="output">@js($foo)</span>
                    </div>
                    HTML;
                }
            },
        ])
            ->assertQueryStringMissing('foo')
            ->waitForLivewire()->click('@setButton')
            ->assertSeeIn('@output', '\'bar\'')
            ->assertQueryStringHas('foo', 'bar')
            ->refresh()
            ->assertQueryStringHas('foo', 'bar')
            ->waitForLivewire()->click('@unsetButton')
            ->assertSeeIn('@output', '\'\'')
            ->assertQueryStringHas('foo', '')
            ->refresh()
            ->assertSeeIn('@output', '\'\'')
            ->assertQueryStringHas('foo', '');
    }

    public function test_can_handle_empty_querystring_value_as_null()
    {
        Livewire::visit([
            new class extends Component
            {
                #[Url(nullable: true)]
                public $foo;

                public function setFoo()
                {
                    $this->foo = 'bar';
                }

                public function unsetFoo()
                {
                    $this->foo = null;
                }

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <button wire:click="setFoo" dusk="setButton">Set foo</button>
                        <button wire:click="unsetFoo" dusk="unsetButton">Unset foo</button>
                        <span dusk="output">@js($foo)</span>
                    </div>
                    HTML;
                }
            },
        ])
            ->assertQueryStringMissing('foo')
            ->waitForLivewire()->click('@setButton')
            ->assertSeeIn('@output', '\'bar\'')
            ->assertQueryStringHas('foo', 'bar')
            ->refresh()
            ->assertQueryStringHas('foo', 'bar')
            ->waitForLivewire()->click('@unsetButton')
            ->assertSeeIn('@output', 'null')
            ->assertQueryStringHas('foo', '')
            ->refresh()
            ->assertSeeIn('@output', 'null')
            ->assertQueryStringHas('foo', '');
    }

    public function test_can_handle_empty_querystring_value_as_null_or_empty_string_based_on_typehinting_of_property()
    {
        Livewire::visit([
            new class extends Component
            {
                #[Url]
                public ?string $nullableFoo;

                #[Url]
                public string $notNullableFoo;

                #[Url]
                public $notTypehintingFoo;

                public function setFoo()
                {
                    $this->nullableFoo = 'bar';
                    $this->notNullableFoo = 'bar';
                    $this->notTypehintingFoo = 'bar';
                }

                public function unsetFoo()
                {
                    $this->nullableFoo = null;
                    $this->notNullableFoo = '';
                    $this->notTypehintingFoo = null;
                }

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <button wire:click="setFoo" dusk="setButton">Set foo</button>
                        <button wire:click="unsetFoo" dusk="unsetButton">Unset foo</button>
                        <span dusk="output-nullableFoo">@js($nullableFoo)</span>
                        <span dusk="output-notNullableFoo">@js($notNullableFoo)</span>
                        <span dusk="output-notTypehintingFoo">@js($notTypehintingFoo)</span>
                    </div>
                    HTML;
                }
            },
        ])
            ->assertQueryStringMissing('nullableFoo')
            ->assertQueryStringMissing('notNullableFoo')
            ->assertQueryStringMissing('notTypehintingFoo')
            ->waitForLivewire()->click('@setButton')
            ->assertSeeIn('@output-nullableFoo', '\'bar\'')
            ->assertSeeIn('@output-notNullableFoo', '\'bar\'')
            ->assertSeeIn('@output-notTypehintingFoo', '\'bar\'')
            ->assertQueryStringHas('nullableFoo', 'bar')
            ->assertQueryStringHas('notNullableFoo', 'bar')
            ->assertQueryStringHas('notTypehintingFoo', 'bar')
            ->refresh()
            ->assertQueryStringHas('nullableFoo', 'bar')
            ->assertQueryStringHas('notNullableFoo', 'bar')
            ->assertQueryStringHas('notTypehintingFoo', 'bar')
            ->waitForLivewire()->click('@unsetButton')
            ->assertSeeIn('@output-nullableFoo', 'null')
            ->assertSeeIn('@output-notNullableFoo', '\'\'')
            ->assertSeeIn('@output-notTypehintingFoo', 'null')
            ->assertQueryStringHas('nullableFoo', '')
            ->assertQueryStringHas('notNullableFoo', '')
            ->assertQueryStringHas('notTypehintingFoo', '')
            ->refresh()
            ->assertSeeIn('@output-nullableFoo', 'null')
            ->assertSeeIn('@output-notNullableFoo', '\'\'')
            ->assertSeeIn('@output-notTypehintingFoo', '\'\'')
            ->assertQueryStringHas('nullableFoo', '')
            ->assertQueryStringHas('notNullableFoo', '')
            ->assertQueryStringHas('notTypehintingFoo', '');
    }

    public function test_can_set_the_correct_query_string_parameter_when_multiple_instances_of_the_same_component_are_used()
    {
        Livewire::visit([
            new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <livewire:child queryParameterName="foo" />
                        <livewire:child queryParameterName="bar" />
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                public $queryParameterName;
                public $value = '';

                protected function queryString()
                {
                    return [
                        'value' => ['as' => $this->queryParameterName],
                    ];
                }

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <input wire:model.live="value" type="text" dusk="input" />
                    </div>
                    HTML;
                }
            },
        ])
            ->waitForLivewire()->type('input', 'test')
            ->assertQueryStringHas('foo', 'test') // Type into the first component's input...
            ->assertQueryStringMissing('bar')
        ;
    }

    public function test_cannot_inject_js_through_query_string()
    {
        $this->beforeServingApplication(function() {
            app('livewire')->component('foo', new class extends Component {
                #[Url]
                public $foo = 'bar';

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <div>Hi!</div>
                        <!-- We wrap the alert in a setTimeout so that the injection has a chance to run first... -->
                        <!-- <script>setTimeout(() => alert('foo'), 100)</script> -->
                    </div>
                    HTML;
                }
            });

            \Illuminate\Support\Facades\Route::get('/foo', function () {
                return app('livewire')->new('foo')();
            })->middleware('web');
        });

        $this->browse(function ($browser) {
            $browser->visit('/foo?constructor.prototype.html=%22%27%3E%3Cimg+src+onerror%3Dalert%281%29%3E&__proto__.html=%22%27%3E%3Cimg+src+onerror%3Dalert%281%29%3E&constructor[prototype][html]=%22%27%3E%3Cimg+src+onerror%3Dalert%281%29%3E&constructor.prototype.html=%22%27%3E%3Cimg+src+onerror%3Dalert%281%29%3E&__proto__.html=%22%27%3E%3Cimg+src+onerror%3Dalert%281%29%3E&__proto__[html]=%22%27%3E%3Cimg+src+onerror%3Dalert%281%29%3E#constructor.prototype.html=%22%27%3E%3Cimg+src+onerror%3Dalert%281%29%3E&__proto__.html=%22%27%3E%3Cimg+src+onerror%3Dalert%281%29%3E&constructor[prototype][html]=%22%27%3E%3Cimg+src+onerror%3Dalert%281%29%3E&constructor.prototype.html=%22%27%3E%3Cimg+src+onerror%3Dalert%281%29%3E&__proto__.html=%22%27%3E%3Cimg+src+onerror%3Dalert%281%29%3E&__proto__[html]=%22%27%3E%3Cimg+src+onerror%3Dalert%281%29%3E');

            try {
                $alert = $browser->driver->switchTo()->alert()->getText();
            } catch (\Facebook\WebDriver\Exception\NoSuchAlertException $e) {
                $this->assertTrue(true);

                return;
            }

            $browser->waitForDialog();
            $browser->acceptDialog();
            $browser->waitForDialog();
            $browser->acceptDialog();

            $this->assertTrue(false, 'Maliciously injected alert detected');
        });
    }

    public function test_it_handles_query_string_params_without_values()
    {
        $id = 'a'.str()->random(10);

        DuskTestable::createBrowser($id, [
            $id => new class extends Component
            {
                #[Url]
                public $foo;

                public function setFoo()
                {
                    $this->foo = 'bar';
                }

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <button wire:click="setFoo" dusk="setButton">Set foo</button>
                        <span dusk="output">@js($foo)</span>
                    </div>
                    HTML;
                }
            }
        ])
        ->visit('/livewire-dusk/'.$id.'?flag')
        ->assertQueryStringMissing('foo')
        ->waitForLivewire()->click('@setButton')
        ->assertSeeIn('@output', '\'bar\'')
        ->assertQueryStringHas('foo', 'bar')
        ->refresh()
        ->assertQueryStringHas('foo', 'bar');
    }

    public function test_it_handles_query_string_encoded_keys()
    {
        Livewire::withQueryParams([
            'foo' => ['bar' => 'baz'],
        ])
            ->visit([
                new class extends Component
                {
                    #[Url]
                    public $foo;

                    public function render()
                    {
                        return <<<'HTML'
                        <div>
                        </div>
                        HTML;
                    }
                }
            ])
            ->assertScript('return window.location.search', '?foo[bar]=baz');
    }

    public function test_it_skips_query_string_encoded_keys_not_tracked_by_livewire()
    {
        Livewire::withQueryParams([
            'foo' => ['bar' => 'baz'],
            'bob' => ['lob' => 'law'],
        ])
            ->visit([
                new class extends Component
                {
                    #[Url]
                    public $foo;

                    public function render()
                    {
                        return <<<'HTML'
                        <div>
                        </div>
                        HTML;
                    }
                }
            ])
            ->assertScript('return window.location.search', '?foo[bar]=baz&bob%5Blob%5D=law');
    }
}

class FormObject extends \Livewire\Form
{
    #[\Livewire\Attributes\Url]
    public $foo = 'bar';

    #[\Livewire\Attributes\Url(as: 'aliased')]
    public $bob = 'lob';
}

enum StringBackedEnumForUrlTesting: string
{
    case First = 'first';
    case Second = 'second';
}

enum IntegerBackedEnumForUrlTesting: int
{
    case First = 1;
    case Second = 2;
}
