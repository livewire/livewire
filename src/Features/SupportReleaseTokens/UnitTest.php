<?php

namespace Livewire\Features\SupportReleaseTokens;

use Livewire\Component;
use Livewire\Exceptions\LivewireReleaseTokenMismatchException;
use Livewire\Livewire;
use Livewire\Mechanisms\HandleComponents\Checksum;

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

    public function test_release_token_is_verified_before_checksum_so_old_snapshots_get_proper_error()
    {
        // This test simulates a request from an old browser session (pre-v3.7.1) where:
        // 1. The snapshot contains memo.children (which old snapshots had)
        // 2. The snapshot does NOT contain memo.release (which didn't exist)
        // 3. The checksum was computed WITH memo.children (like old Livewire did)
        //
        // Since PR #9339, new Livewire strips memo.children before computing checksums,
        // so an old snapshot's checksum would fail verification. However, we want users
        // to see LivewireReleaseTokenMismatchException (prompting a refresh) instead of
        // CorruptComponentPayloadException (which is confusing).
        //
        // The fix moves release token verification to the checksum.verify hook, which
        // fires BEFORE the checksum comparison, ensuring the proper error is thrown...
        $this->expectException(LivewireReleaseTokenMismatchException::class);

        app('livewire')->component('component-with-release-token', ComponentWithReleaseToken::class);

        // Build a snapshot WITHOUT memo.release (simulating pre-v3.7.1)
        // but WITH memo.children (which old snapshots included)...
        $snapshot = [
            'memo' => [
                'name' => 'component-with-release-token',
                'id' => 'test-id',
                'children' => ['child-1' => ['id' => 'child-1', 'tag' => 'div']],
            ],
            'data' => [],
        ];

        // Generate checksum WITH children included (like old Livewire did before PR #9339)...
        $hashKey = app('encrypter')->getKey();
        $snapshot['checksum'] = hash_hmac('sha256', json_encode($snapshot), $hashKey);

        // When we verify this snapshot, the release token check should fire first
        // (via checksum.verify hook) and throw LivewireReleaseTokenMismatchException,
        // NOT CorruptComponentPayloadException from the checksum mismatch...
        Checksum::verify($snapshot);
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
