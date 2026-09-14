<?php

namespace Livewire\Features\SupportHtmlAttributeForwarding;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Tests\TestCase;
use Livewire\Livewire;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use Livewire\Features\SupportLazyLoading\SupportLazyLoading;
use Sushi\Sushi;

class UnitTest extends TestCase
{
    public function test_html_attributes_are_forwarded_to_component()
    {
        Livewire::test([
            new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <!-- ... -->

                        <livewire:alert
                            type="error"
                            class="mb-4"
                            id="error-alert"
                            data-testid="my-alert"
                        />
                    </div>
                    HTML;
                }
            },
            'alert' => new class extends Component {
                public string $type = 'info';

                public function render()
                {
                    return <<<'HTML'
                    <div {{ $attributes->merge(['class' => 'alert alert-'.$type]) }}>
                        <!-- ... -->
                    </div>
                    HTML;
                }
            }
        ])
        ->assertDontSeeHtml('type="error"')
        ->assertSeeHtml('class="alert alert-error mb-4"')
        ->assertSeeHtml('id="error-alert"')
        ->assertSeeHtml('data-testid="my-alert"')
        ;
    }

    public function test_array_is_not_forwarded_as_html_attribute_to_component_placeholder()
    {
        SupportLazyLoading::$disableWhileTesting = false;

        Livewire::component('alert', LazyAlert::class);

        $html = Livewire::mount('alert', [
            'lazy' => true,
            'pageFilters' => ['status' => 'active'],
            'id' => 'error-alert',
        ]);

        $this->assertStringContainsString('id="error-alert"', $html);
        $this->assertStringNotContainsString('pageFilters=', $html);
    }

    public function test_array_is_not_forwarded_as_html_attribute_to_component_render()
    {
        Livewire::component('alert', AlertWithAttributes::class);

        $html = Livewire::mount('alert', [
            'pageFilters' => ['status' => 'active'],
            'id' => 'error-alert',
        ]);

        $this->assertStringContainsString('id="error-alert"', $html);
        $this->assertStringNotContainsString('pageFilters=', $html);
    }

    public function test_array_is_not_forwarded_as_html_attribute_to_an_island()
    {
        Livewire::component('alert', AlertWithIslandAttributes::class);

        $html = Livewire::mount('alert', [
            'pageFilters' => ['status' => 'active'],
            'id' => 'error-alert',
        ]);

        $this->assertStringContainsString('id="error-alert"', $html);
        $this->assertStringNotContainsString('pageFilters=', $html);
    }

    public function test_eloquent_model_is_not_forwarded_as_html_attribute_to_component_placeholder()
    {
        SupportLazyLoading::$disableWhileTesting = false;

        Livewire::component('alert', LazyAlert::class);

        $html = Livewire::mount('alert', [
            'lazy' => true,
            'record' => RecordModel::first(),
            'id' => 'error-alert',
        ]);

        $this->assertStringContainsString('id="error-alert"', $html);
        $this->assertStringNotContainsString('record=', $html);
    }

    public function test_eloquent_model_is_not_forwarded_as_html_attribute_to_component_render()
    {
        Livewire::component('alert', AlertWithAttributes::class);

        $html = Livewire::mount('alert', [
            'record' => RecordModel::first(),
            'id' => 'error-alert',
        ]);

        $this->assertStringContainsString('id="error-alert"', $html);
        $this->assertStringNotContainsString('record=', $html);
    }

    public function test_eloquent_model_is_not_forwarded_as_html_attribute_to_an_island()
    {
        Livewire::component('alert', AlertWithIslandAttributes::class);

        $html = Livewire::mount('alert', [
            'record' => RecordModel::first(),
            'id' => 'error-alert',
        ]);

        $this->assertStringContainsString('id="error-alert"', $html);
        $this->assertStringNotContainsString('record=', $html);
    }

    public function test_htmlable_objects_are_forwarded_as_html_attributes_to_component_placeholder()
    {
        SupportLazyLoading::$disableWhileTesting = false;

        Livewire::component('alert', LazyAlert::class);

        $html = Livewire::mount('alert', [
            'lazy' => true,
            'id' => 'error-alert',
            'title' => new HtmlString('Hello World'),
        ]);

        $this->assertStringContainsString('id="error-alert"', $html);
        $this->assertStringContainsString('title="Hello World"', $html);
    }

    public function test_htmlable_objects_are_forwarded_as_html_attributes_to_component_render()
    {
        Livewire::component('alert', AlertWithAttributes::class);

        $html = Livewire::mount('alert', [
            'id' => 'error-alert',
            'title' => new HtmlString('Hello World'),
        ]);

        $this->assertStringContainsString('id="error-alert"', $html);
        $this->assertStringContainsString('title="Hello World"', $html);
    }

    public function test_htmlable_objects_are_forwarded_as_html_attributes_to_an_island()
    {
        Livewire::component('alert', AlertWithIslandAttributes::class);

        $html = Livewire::mount('alert', [
            'id' => 'error-alert',
            'title' => new HtmlString('Hello World'),
        ]);

        $this->assertStringContainsString('id="error-alert"', $html);
        $this->assertStringContainsString('title="Hello World"', $html);
    }

    public function test_arrays_and_objects_are_not_dehydrated_into_the_component_snapshot()
    {
        Livewire::component('alert', AlertWithAttributes::class);

        $html = Livewire::mount('alert', [
            'record' => RecordModel::first(),
            'pageFilters' => ['status' => 'active'],
            'id' => 'error-alert',
        ]);

        // Without filtering, these would be serialized into the wire:snapshot attribute...
        $this->assertStringNotContainsString('first@example.com', $html);
        $this->assertStringNotContainsString('pageFilters', $html);
    }

    public function test_non_html_attributes_are_available_to_blade_props()
    {
        Livewire::component('alert', AlertWithProps::class);

        $html = Livewire::mount('alert', [
            'rows' => [
                ['id' => 1, 'name' => 'Alice'],
                ['id' => 2, 'name' => 'Bob'],
            ],
            'id' => 'rows-component',
        ]);

        $this->assertStringContainsString('Alice', $html);
        $this->assertStringContainsString('Bob', $html);
        $this->assertStringContainsString('id="rows-component"', $html);
        $this->assertStringNotContainsString('rows=', $html);
    }

    public function test_non_html_attributes_are_available_as_blade_props_without_default()
    {
        Livewire::component('alert', AlertWithPropsWithoutDefault::class);

        $html = Livewire::mount('alert', [
            'rows' => [
                ['id' => 1, 'name' => 'Alice'],
            ],
            'id' => 'rows-component',
        ]);

        $this->assertStringContainsString('Alice', $html);
        $this->assertStringContainsString('id="rows-component"', $html);
        $this->assertStringNotContainsString('rows=', $html);
    }

    public function test_html_attributes_are_forwarded_while_non_html_attributes_are_available_as_blade_props()
    {
        Livewire::component('alert', AlertWithProps::class);

        $html = Livewire::mount('alert', [
            'rows' => [
                ['id' => 1, 'name' => 'Alice'],
            ],
            'id' => 'rows-component',
        ]);

        $this->assertStringContainsString('Alice', $html);
        $this->assertStringContainsString('id="rows-component"', $html);
        $this->assertStringNotContainsString('rows="', $html);
    }

    public function test_non_html_attributes_are_available_as_blade_props_in_placeholder()
    {
        SupportLazyLoading::$disableWhileTesting = false;

        Livewire::component('alert', LazyAlertWithProps::class);

        $html = Livewire::mount('alert', [
            'lazy' => true,
            'rows' => [
                ['id' => 1, 'name' => 'Alice'],
            ],
            'id' => 'rows-component',
        ]);

        $this->assertStringContainsString('Alice', $html);
        $this->assertStringContainsString('id="rows-component"', $html);
        $this->assertStringNotContainsString('rows=', $html);
    }

    public function test_eloquent_model_are_available_as_blade_props_without_being_dehydrated()
    {
        Livewire::component('alert', AlertWithRecordProps::class);

        $html = Livewire::mount('alert', [
            'record' => RecordModel::first(),
            'id' => 'rows-component',
        ]);

        $this->assertStringContainsString('First User', $html);
        $this->assertStringContainsString('id="rows-component"', $html);
        $this->assertStringNotContainsString('first@example.com', $html);
    }
}

class RecordModel extends Model
{
    use Sushi;

    protected $rows = [
        ['id' => 1, 'name' => 'First User', 'email' => 'first@example.com'],
    ];
}

#[Lazy]
class LazyAlert extends Component
{
    public function placeholder()
    {
        return '<div {{ $attributes }}>Loading...</div>';
    }

    public function render()
    {
        return '<div>Alert</div>';
    }
}

class AlertWithAttributes extends Component
{
    public function render()
    {
        return '<div {{ $attributes }}>Alert</div>';
    }
}

class AlertWithIslandAttributes extends Component
{
    public function render()
    {
        return <<<'HTML'
        <div>
            @island(name: 'content')
                <div {{ $attributes }}>Island</div>
            @endisland
        </div>
        HTML;
    }
}

class AlertWithProps extends Component
{
    public function render()
    {
        return <<<'HTML'
        @props(['rows' => []])
        <ul {{ $attributes }}>
            @foreach ($rows as $row)
                <li wire:key="{{ $row['id'] }}">{{ $row['name'] }}</li>
            @endforeach
        </ul>
        HTML;
    }
}

class AlertWithPropsWithoutDefault extends Component
{
    public function render()
    {
        return <<<'HTML'
        @props(['rows'])
        <div {{ $attributes }}>
            {{ $rows[0]['name'] }}
        </div>
        HTML;
    }
}

#[Lazy]
class LazyAlertWithProps extends Component
{
    public function placeholder()
    {
        return <<<'HTML'
        @props(['rows'])
        <div {{ $attributes }}>{{ $rows[0]['name'] }}</div>
        HTML;
    }

    public function render()
    {
        return '<div>Alert</div>';
    }
}

class AlertWithRecordProps extends Component
{
    public function render()
    {
        return <<<'HTML'
        @props(['record'])
        <div {{ $attributes }}>{{ $record->name }}</div>
        HTML;
    }
}
