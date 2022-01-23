<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use DateTimeInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\Unit\Components\ComponentWithComplexTypedProperties;
use Tests\Unit\Models\ModelForSerialization;

class PublicTypedPropertyHydrationAndDehydrationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Schema::create('model_for_serializations', function ($table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->timestamps();
        });
    }

    public function test_date_hydrations()
    {
        if (version_compare(PHP_VERSION, '8.0', '<')) {
            return $this->markTestSkipped('Test requires PHP 8');
        }

        $date = Carbon::create(2022, 2, 15);
        $dateWithTz = Carbon::create(2019, 2, 1, 3, 45, 27, CarbonTimeZone::createFromHourOffset(1));

        Livewire::test(ComponentWithComplexTypedProperties::class)
            ->assertSet('date', null)
            ->set('date', $date)
            ->assertSet('date', $date)
            ->assertPayloadSet('date', $date->format(DateTimeInterface::ISO8601))
            ->assertSee("Date: $date")
            ->set('date', null)
            ->assertSet('date', null)
            ->assertPayloadSet('date', null)
            ->set('date', '2022-02-15')
            ->assertSet('date', $date)
            ->assertPayloadSet('date', $date->format(DateTimeInterface::ISO8601))
            ->set('date', '2019-02-01T03:45:27+01:00')
            ->assertSet('date', $dateWithTz)
            ->assertPayloadSet('date', $dateWithTz->format(DateTimeInterface::ISO8601))
            ->set('foo', '14:30, 02 Jan 2022')
            ->assertSet('foo', Carbon::create(2022, 1, 2, 14, 30))
            ->assertPayloadSet('foo', '14:30, 02 Jan 2022');
    }

    public function test_model_hydrations()
    {
        if (version_compare(PHP_VERSION, '8.0', '<')) {
            return $this->markTestSkipped('Test requires PHP 8');
        }

        $model = ModelForSerialization::create($attributes = ['id' => 1, 'title' => 'foo']);

        $attributedModel = $model;
        $attributedModel->wasRecentlyCreated = false;

        Livewire::test(ComponentWithComplexTypedProperties::class)
            ->assertSet('model', null)
            ->set('model', $model)
            ->assertSet('model', $model)
            ->assertPayloadSet('model', $attributes)
            ->set('model', null)
            ->assertSet('model', null)
            ->assertPayloadSet('model', null)
            ->set('attributedModel', 1)
            ->assertSet('attributedModel', $attributedModel)
            ->assertPayloadSet('attributedModel', $attributes)
            ->set('attributedModel', null)
            ->assertSet('attributedModel', null)
            ->assertPayloadSet('attributedModel', null)
            ->set('complexAttributedModel', 1)
            ->assertSet('complexAttributedModel', $attributedModel)
            ->assertPayloadSet('complexAttributedModel', 'foo')
            ->set('complexAttributedModel.title', 'bar')
            ->assertSet('complexAttributedModel.title', 'bar')
            ->assertPayloadSet('complexAttributedModel', 'bar');

        $this->expectException(ModelNotFoundException::class);

        Livewire::test(ComponentWithComplexTypedProperties::class)
            ->set('complexAttributedModel', 2);
    }
}
