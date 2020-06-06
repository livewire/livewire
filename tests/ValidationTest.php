<?php

namespace Tests;

use Carbon\Carbon;
use Illuminate\Support\ViewErrorBag;
use Livewire\Castable;
use Livewire\Component;
use Livewire\LivewireManager;

class ValidationTest extends TestCase
{
    /** @test */
    public function validate_component_properties()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        $component->runAction('runValidation');

        $this->assertStringNotContainsString('The foo field is required', $component->payload['dom']);
        $this->assertStringContainsString('The bar field is required', $component->payload['dom']);
    }

    /** @test */
    public function validate_component_properties_with_custom_message()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        $component->runAction('runValidationWithCustomMessage');

        $this->assertStringContainsString('Custom Message', $component->payload['dom']);
    }

    /** @test */
    public function validate_component_properties_with_custom_attribute()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        $component->runAction('runValidationWithCustomAttribute');

        $this->assertStringContainsString('The foobar field is required.', $component->payload['dom']);
    }

    /** @test */
    public function validate_component_properties_with_date_cast()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        // before:today
        $component
            ->set('date', 'yesterday')
            ->runAction('runValidationWithDateCastAttribute')
            ->assertHasNoErrors('date');

        $component
            ->set('date', 'tomorrow')
            ->runAction('runValidationWithDateCastAttribute')
            ->assertHasErrors(['date' => 'before']);
    }

    /** @test */
    public function validate_component_properties_with_collection_cast()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        // array|size:4
        $component
            ->set('collection', 'not an array')
            ->runAction('runValidationWithCollectionCastAttribute')
            ->assertHasErrors(['collection' => 'size']);

        $component
            ->set('collection', [1, 2, 3, 4])
            ->runAction('runValidationWithCollectionCastAttribute')
            ->assertHasNoErrors('collection');
    }

    /** @test */
    public function validate_component_properties_with_custom_cast()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        // array
        $component
            ->set('custom', 'not a uuid')
            ->runAction('runValidationWithCustomCastAttribute')
            ->assertHasErrors(['custom' => 'uuid']);

        $component
            ->set('custom', '26e72cac-9dcf-4b30-b7f9-78b14632cbe7')
            ->runAction('runValidationWithCustomCastAttribute')
            ->assertHasNoErrors('custom');
    }

    /** @test */
    public function validate_nested_component_properties()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        $component->runAction('runNestedValidation');

        $this->assertStringContainsString('emails.1 must be a valid email address.', $component->payload['dom']);
    }

    /** @test */
    public function validate_deeply_nested_component_properties()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        $component->runAction('runDeeplyNestedValidation');

        $this->assertStringContainsString('items.1.baz field is required', $component->payload['dom']);
        $this->assertStringNotContainsString('items.0.baz field is required', $component->payload['dom']);
    }

    /** @test */
    public function validation_errors_persist_across_requests()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        $component->call('runValidation')
            ->assertSee('The bar field is required')
            ->set('foo', 'bar')
            ->assertSee('The bar field is required');
    }

    /** @test */
    public function old_validation_errors_are_overwritten_if_new_request_has_errors()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        $component->call('runValidation')
            ->set('foo', '')
            ->call('runValidation')
            ->call('$refresh')
            ->assertSee('The foo field is required');
    }

    /** @test */
    public function old_validation_is_cleared_if_new_validation_passes()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        $component
            ->set('foo', '')
            ->set('bar', '')
            ->call('runValidation')
            ->assertSee('The foo field is required')
            ->assertSee('The bar field is required')
            ->set('foo', 'foo')
            ->set('bar', 'bar')
            ->call('runValidation')
            ->assertDontSee('The foo field is required')
            ->assertDontSee('The bar field is required');
    }

    /** @test */
    public function can_validate_only_a_specific_field_and_preserve_other_validation_messages()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        $component
            ->set('foo', 'foo')
            ->set('bar', '')
            ->call('runValidation')
            ->assertDontSee('The foo field is required')
            ->assertSee('The bar field is required')
            ->set('foo', '')
            ->call('runValidationOnly', 'foo')
            ->assertSee('The foo field is required')
            ->assertSee('The bar field is required');
    }

    /** @test */
    public function can_validate_only_a_specific_field_with_deeply_nested_array()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        $component
            ->runAction('runDeeplyNestedValidationOnly', 'items.0.baz')
            ->assertDontSee('items.0.baz field is required')
            ->runAction('runDeeplyNestedValidationOnly', 'items.1.baz')
            ->assertSee('items.1.baz field is required');
    }

    /** @test */
    public function validation_errors_are_shared_for_all_views()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        app('view')->share('errors', $errors = new ViewErrorBag);

        $component
            ->set('bar', '')
            ->call('runValidation')
            ->assertSee('sharedError:The bar field is required');

        $this->assertTrue(app('view')->shared('errors') === $errors);
    }

    /** @test */
    public function multi_word_validation_rules_are_assertable()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        $component
            ->set('foo', 'bar123&*(O)')
            ->call('runValidationWithMultiWordRule')
            ->assertHasErrors(['foo' => 'alpha_dash']);
    }

    /** @test */
    public function can_assert_has_no_errors_when_no_validation_has_faile_and_specific_keys_are_supplied()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        $component
            ->set('foo', 'foo')
            ->set('bar', 'bar')
            ->call('runValidation')
            ->assertHasNoErrors(['foo' => 'required']);
    }
}

class CustomCaster implements Castable
{
    public function cast($value)
    {
        return new CustomCastObject($value);
    }

    public function uncast($value)
    {
        return $value->id ?? null;
    }
}

class CustomCastObject
{
    public $id;

    public function __construct($id) {
        $this->id = $id;
    }
}

class ForValidation extends Component
{
    public $foo = 'foo';
    public $bar = '';
    public $emails = ['foo@bar.com', 'invalid-email'];
    public $items = [
        ['foo' => 'bar', 'baz' => 'blab'],
        ['foo' => 'bar', 'baz' => ''],
    ];

    public $custom;
    public $date;
    public $collection;

    protected $casts = [
        'custom' => CustomCaster::class,
        'date' => 'date',
        'collection' => 'collection',
    ];

    public function mount() {
        $this->date = Carbon::parse('today');
        $this->collection = collect([8, 9, 10]);
        $this->custom = new CustomCastObject('26e72cac-9dcf-4b30-b7f9-78b14632cbe7');
    }

    public function runValidation()
    {
        $this->validate([
            'foo' => 'required',
            'bar' => 'required',
        ]);
    }

    public function runValidationWithMultiWordRule()
    {
        $this->validate([
            'foo' => 'alpha_dash',
        ]);
    }

    public function runValidationOnly($field)
    {
        $this->validateOnly($field, [
            'foo' => 'required',
            'bar' => 'required',
        ]);
    }

    public function runDeeplyNestedValidationOnly($field)
    {
        $this->validateOnly($field, [
            'items' => ['required', 'array'],
            'items.*' => 'array',
            'items.*.foo' => ['required', 'string'],
            'items.*.baz' => ['required', 'string'],
        ]);
    }

    public function runValidationWithCustomMessage()
    {
        $this->validate([
            'bar' => 'required',
        ], ['required' => 'Custom Message']);
    }

    public function runValidationWithCustomAttribute()
    {
        $this->validate([
            'bar' => 'required',
        ], [], ['bar' => 'foobar']);
    }

    public function runValidationWithDateCastAttribute()
    {
        $this->validate([
            'date' => 'required|before:today',
        ]);
    }

    public function runValidationWithCollectionCastAttribute()
    {
        $this->validate([
            'collection' => 'required|array|size:4',
        ]);
    }

    public function runValidationWithCustomCastAttribute()
    {
        $this->validate([
            'custom' => 'required|uuid',
        ]);
    }

    public function runNestedValidation()
    {
        $this->validate([
            'emails.*' => 'email',
        ]);
    }

    public function runDeeplyNestedValidation()
    {
        $this->validate([
            'items' => ['required', 'array'],
            'items.*' => 'array',
            'items.*.foo' => ['required', 'string'],
            'items.*.baz' => ['required', 'string'],
        ]);
    }

    public function render()
    {
        return app('view')->make('dump-errors');
    }
}
