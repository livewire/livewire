# Testing

Livewire supports 2 styles of testing it's components:
1. Unit testing
2. End-to-end testing (still in development)

For these demonstrations, we will be using a simple Counter component like the following:

**App\Http\Livewire\Counter.php**
```
class Counter extends LivewireComponent
{
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }

    public function decrement()
    {
        $this->count--;
    }

    public function render()
    {
        return view('livewire.counter');
    }
}
```

With a view that looks like:

**resources/views/livewire/counter.blade.php**
```
<div>
    {{ $count }}
    <button wire:click="increment">+</button>
    <button wire:click="decrement">-</button>
</div>
```

## Unit Testing

```php
class CounterTest extends TestCase
{
    /** @test */
    function can_increment()
    {
        $counter = Livewire::test(Counter::class);

        $this->assertEquals(1, $counter->count);

        $counter->increment();

        $this->assertEquals(2, $counter->count);

        $counter->decrement();

        $this->assertEquals(1, $counter->count);
    }
}
```

## End-to-end Testing

_Note: This style of testing is currently under development and likely won't work as expected._

```php
class CounterTest extends TestCase
{
    /** @test */
    function can_increment()
    {
        $counter = Livewire::test(Counter::class);

        $counter->assertSee(1)
            ->click('[wire:click="increment"]')
            ->assertSee(2)
            ->click('[wire:click="decrement"]')
            ->assertSee(1);
    }
}
```
