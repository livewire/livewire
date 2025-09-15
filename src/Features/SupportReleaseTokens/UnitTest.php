<?php

namespace Livewire\Features\SupportReleaseTokens;

use Livewire\Component;
use Livewire\Exceptions\LivewireReleaseTokenMismatchException;
use Livewire\Livewire;

class UnitTest extends \Tests\TestCase
{
    public function test_release_token_is_added_to_the_snapshot()
    {
        ReleaseToken::$LIVEWIRE_RELEASE_TOKEN = 'foo';
        app('config')->set('livewire.release_token', 'bar');

        $component = Livewire::test(ComponentWithReleaseToken::class);

        $this->assertEquals('foo-bar-baz', $component->snapshot['memo']['release']);
    }

    public function test_release_token_is_checked_on_subsequent_requests_and_passes_if_the_release_token_matches()
    {
        $this->withoutExceptionHandling();

        ReleaseToken::$LIVEWIRE_RELEASE_TOKEN = 'foo';
        app('config')->set('livewire.release_token', 'bar');

        $component = Livewire::test(ComponentWithReleaseToken::class);

        $component->refresh()
            ->assertSuccessful();
    }

    public function test_release_token_is_checked_on_subsequent_requests_and_fails_if_the_internal_livewire_release_token_does_not_match()
    {
        $this->withoutExceptionHandling();
        $this->expectException(LivewireReleaseTokenMismatchException::class);

        ReleaseToken::$LIVEWIRE_RELEASE_TOKEN = 'foo';
        app('config')->set('livewire.release_token', 'bar');

        $component = Livewire::test(ComponentWithReleaseToken::class);

        ReleaseToken::$LIVEWIRE_RELEASE_TOKEN = 'bob';

        $component->refresh()
            ->assertStatus(419);
    }

    public function test_release_token_is_checked_on_subsequent_requests_and_fails_if_the_application_release_token_does_not_match()
    {
        $this->withoutExceptionHandling();
        $this->expectException(LivewireReleaseTokenMismatchException::class);

        ReleaseToken::$LIVEWIRE_RELEASE_TOKEN = 'foo';
        app('config')->set('livewire.release_token', 'bar');

        $component = Livewire::test(ComponentWithReleaseToken::class);

        ComponentWithReleaseToken::$releaseToken = 'bob';

        $component->refresh()
            ->assertStatus(419);
    }
}

class ComponentWithReleaseToken extends Component
{
    public static $releaseToken = 'baz';

    public static function releaseToken(): string
    {
        return static::$releaseToken;
    }

    public function render()
    {
        return '<div></div>';
    }
}
