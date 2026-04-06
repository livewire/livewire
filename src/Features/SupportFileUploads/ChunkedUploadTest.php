<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class ChunkedUploadTest extends \Tests\TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Use the unit test temporary disk so we have a real local filesystem
        // to write chunks to (Storage::fake() also works because flock and
        // file paths still resolve to a real local directory).
        Storage::fake('tmp-for-tests');

        config()->set('livewire.temporary_file_upload.chunk_size', 1024); // 1KB for fast tests
        config()->set('livewire.temporary_file_upload.chunk_max_upload_time', 60);
        config()->set('livewire.temporary_file_upload.rules', ['required', 'file', 'max:100']); // 100KB max
    }

    public function tearDown(): void
    {
        // Each test runs many requests against the chunk endpoints. Without
        // clearing the rate limiter we'd pollute the global throttle:600,1
        // bucket and break other test files (like UnitTest's middleware tests)
        // when running the full suite.
        RateLimiter::clear('600|1|127.0.0.1');
        RateLimiter::clear('60|1|127.0.0.1');

        parent::tearDown();
    }

    /** Helper: get a signed init URL */
    protected function signedInitUrl(): string
    {
        return URL::temporarySignedRoute(
            'livewire.chunk-upload-init',
            now()->addMinutes(60),
        );
    }

    /** Helper: hit init and return decoded JSON */
    protected function initUpload(int $size, string $name = 'test.txt'): array
    {
        $response = $this->post($this->signedInitUrl(), [], [
            'Upload-Length' => $size,
            'Upload-Name' => base64_encode($name),
        ]);

        $response->assertOk();

        return $response->json();
    }

    /** Helper: send a chunk */
    protected function sendChunk(string $patchUrl, int $offset, string $content)
    {
        // The HTTP test framework needs the body sent as raw content.
        return $this->call(
            'PATCH',
            $patchUrl,
            [],
            [],
            [],
            ['HTTP_UPLOAD_OFFSET' => $offset, 'CONTENT_TYPE' => 'application/offset+octet-stream'],
            $content,
        );
    }

    public function test_init_returns_transfer_id_and_signed_urls()
    {
        $response = $this->post($this->signedInitUrl(), [], [
            'Upload-Length' => 5000,
            'Upload-Name' => base64_encode('photo.jpg'),
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['transferId', 'patchUrl', 'offsetUrl']);

        $body = $response->json();
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{40}$/', $body['transferId']);
        $this->assertStringContainsString('chunk-upload', $body['patchUrl']);
        $this->assertStringContainsString('chunk-upload', $body['offsetUrl']);
        $this->assertStringContainsString('signature=', $body['patchUrl']);
        $this->assertStringContainsString('signature=', $body['offsetUrl']);
    }

    public function test_init_rejects_unsigned_request()
    {
        $unsigned = route('livewire.chunk-upload-init');
        $response = $this->post($unsigned, [], ['Upload-Length' => 5000]);
        $response->assertStatus(401);
    }

    public function test_init_rejects_zero_or_negative_size()
    {
        $response = $this->post($this->signedInitUrl(), [], ['Upload-Length' => 0]);
        $response->assertStatus(400);
    }

    public function test_init_rejects_size_exceeding_configured_max()
    {
        // 100KB max → reject 200KB upload
        $response = $this->post($this->signedInitUrl(), [], [
            'Upload-Length' => 200 * 1024,
        ]);
        $response->assertStatus(413);
    }

    public function test_init_creates_manifest_file()
    {
        $body = $this->initUpload(5000, 'photo.jpg');

        $manifestPath = FileUploadConfiguration::path("chunks/{$body['transferId']}/manifest.json");
        $this->assertTrue(Storage::disk('tmp-for-tests')->exists($manifestPath));

        $manifest = json_decode(Storage::disk('tmp-for-tests')->get($manifestPath), true);
        $this->assertEquals(5000, $manifest['size']);
        $this->assertEquals(0, $manifest['offset']);
        $this->assertEquals('photo.jpg', $manifest['name']);
    }

    public function test_full_upload_flow_assembles_file_correctly()
    {
        // Create a 3KB body that we'll send as 3 × 1KB chunks
        $totalContent = str_repeat('A', 1024) . str_repeat('B', 1024) . str_repeat('C', 1024);
        $body = $this->initUpload(strlen($totalContent), 'test.txt');

        // Send chunk 1
        $response = $this->sendChunk($body['patchUrl'], 0, substr($totalContent, 0, 1024));
        $response->assertNoContent();
        $this->assertEquals(1024, $response->headers->get('Upload-Offset'));

        // Send chunk 2
        $response = $this->sendChunk($body['patchUrl'], 1024, substr($totalContent, 1024, 1024));
        $response->assertNoContent();
        $this->assertEquals(2048, $response->headers->get('Upload-Offset'));

        // Send chunk 3 (final)
        $response = $this->sendChunk($body['patchUrl'], 2048, substr($totalContent, 2048, 1024));
        $response->assertNoContent();
        $this->assertEquals(3072, $response->headers->get('Upload-Offset'));
        $this->assertEquals('true', $response->headers->get('Upload-Complete'));

        $signedPath = $response->headers->get('X-Signed-Path');
        $this->assertNotNull($signedPath);

        // Extract the actual filename from the signed path and verify the assembled file matches
        $tmpFilename = TemporaryUploadedFile::extractPathFromSignedPath($signedPath);
        $assembledPath = FileUploadConfiguration::path($tmpFilename);

        $this->assertTrue(Storage::disk('tmp-for-tests')->exists($assembledPath));
        $this->assertEquals($totalContent, Storage::disk('tmp-for-tests')->get($assembledPath));

        // Chunk directory should be cleaned up
        $chunkDir = FileUploadConfiguration::path("chunks/{$body['transferId']}");
        $this->assertFalse(Storage::disk('tmp-for-tests')->exists("{$chunkDir}/manifest.json"));
    }

    public function test_patch_rejects_unsigned_request()
    {
        $body = $this->initUpload(1024);
        $unsigned = route('livewire.chunk-upload-patch', ['transferId' => $body['transferId']]);

        $response = $this->call(
            'PATCH', $unsigned, [], [], [],
            ['HTTP_UPLOAD_OFFSET' => 0, 'CONTENT_TYPE' => 'application/offset+octet-stream'],
            'data',
        );
        $response->assertStatus(401);
    }

    public function test_patch_rejects_offset_mismatch()
    {
        $body = $this->initUpload(1024);

        // Wrong offset
        $response = $this->sendChunk($body['patchUrl'], 500, 'data');
        $response->assertStatus(409);
    }

    public function test_patch_rejects_chunk_larger_than_chunk_size()
    {
        // chunk_size is 1024 in setUp, send 2KB
        $body = $this->initUpload(5000);

        $response = $this->sendChunk($body['patchUrl'], 0, str_repeat('X', 2048));
        $response->assertStatus(413);
    }

    public function test_patch_rejects_chunk_that_would_exceed_declared_size()
    {
        // Declared size 1500, but we'd send 1024 + 1024 = 2048
        $body = $this->initUpload(1500);

        $this->sendChunk($body['patchUrl'], 0, str_repeat('X', 1024))->assertNoContent();
        // Second chunk would push us over 1500
        $this->sendChunk($body['patchUrl'], 1024, str_repeat('X', 1024))->assertStatus(413);
    }

    public function test_patch_rejects_invalid_transfer_id_format()
    {
        $signedUrl = URL::temporarySignedRoute(
            'livewire.chunk-upload-patch',
            now()->addMinutes(60),
            ['transferId' => 'short'],
        );

        $response = $this->call(
            'PATCH', $signedUrl, [], [], [],
            ['HTTP_UPLOAD_OFFSET' => 0, 'CONTENT_TYPE' => 'application/offset+octet-stream'],
            'data',
        );
        $response->assertStatus(400);
    }

    public function test_patch_returns_404_for_unknown_transfer_id()
    {
        $fakeId = str_repeat('a', 40);
        $signedUrl = URL::temporarySignedRoute(
            'livewire.chunk-upload-patch',
            now()->addMinutes(60),
            ['transferId' => $fakeId],
        );

        $response = $this->call(
            'PATCH', $signedUrl, [], [], [],
            ['HTTP_UPLOAD_OFFSET' => 0, 'CONTENT_TYPE' => 'application/offset+octet-stream'],
            'data',
        );
        $response->assertStatus(404);
    }

    public function test_offset_endpoint_returns_current_progress()
    {
        $body = $this->initUpload(3000);

        // Initially offset is 0
        $offsetResponse = $this->get($body['offsetUrl']);
        $offsetResponse->assertOk();
        $this->assertEquals(['offset' => 0, 'size' => 3000], $offsetResponse->json());

        // Send a 1KB chunk
        $this->sendChunk($body['patchUrl'], 0, str_repeat('X', 1024))->assertNoContent();

        // Offset should now be 1024
        $offsetResponse = $this->get($body['offsetUrl']);
        $this->assertEquals(['offset' => 1024, 'size' => 3000], $offsetResponse->json());
    }

    public function test_offset_endpoint_returns_no_store_cache_header()
    {
        $body = $this->initUpload(1024);
        $response = $this->get($body['offsetUrl']);

        // Laravel's session middleware may append `private` to cache-control,
        // so we just assert no-store is present (which is the directive that matters
        // for resume correctness — browsers must not serve stale offset values).
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_offset_endpoint_rejects_unsigned_request()
    {
        $body = $this->initUpload(1024);
        $unsigned = route('livewire.chunk-upload-offset', ['transferId' => $body['transferId']]);

        $this->get($unsigned)->assertStatus(401);
    }

    public function test_finalized_file_is_validated_against_global_rules()
    {
        // Require the file to be a JPEG image. The assembled file is plain text,
        // so it will pass the size check but fail the mime check at finalize time.
        config()->set('livewire.temporary_file_upload.rules', ['required', 'file', 'mimes:jpg', 'max:100']);
        config()->set('livewire.temporary_file_upload.chunk_size', 1024);

        $body = $this->initUpload(2048, 'big.txt');

        $this->sendChunk($body['patchUrl'], 0, str_repeat('X', 1024))->assertNoContent();
        $response = $this->sendChunk($body['patchUrl'], 1024, str_repeat('X', 1024));

        // Final chunk should have triggered validation, which should fail (text file != jpeg)
        $response->assertStatus(422);
        $this->assertArrayHasKey('errors', $response->json());

        // The assembled file should have been cleaned up
        $chunkDir = FileUploadConfiguration::path("chunks/{$body['transferId']}");
        $this->assertFalse(Storage::disk('tmp-for-tests')->exists("{$chunkDir}/manifest.json"));
    }

    public function test_finalized_file_validation_failure_cleans_up_assembled_file()
    {
        config()->set('livewire.temporary_file_upload.rules', ['required', 'file', 'mimes:jpg', 'max:100']);
        config()->set('livewire.temporary_file_upload.chunk_size', 1024);

        $body = $this->initUpload(1024, 'fake.txt');
        $response = $this->sendChunk($body['patchUrl'], 0, str_repeat('X', 1024));

        $response->assertStatus(422);

        // Verify nothing was left in livewire-tmp/ for this transfer
        $tmpFiles = Storage::disk('tmp-for-tests')->files(FileUploadConfiguration::path());
        // Filter out the chunks directory itself
        $tmpFiles = array_filter($tmpFiles, fn ($f) => ! str_contains($f, 'chunks/'));
        $this->assertEmpty($tmpFiles, 'Failed validation should leave no orphaned files in livewire-tmp');
    }

    public function test_finalized_file_appears_in_livewire_tmp_when_validation_passes()
    {
        $body = $this->initUpload(1024, 'small.txt');
        $response = $this->sendChunk($body['patchUrl'], 0, str_repeat('A', 1024));

        $response->assertNoContent();
        $signedPath = $response->headers->get('X-Signed-Path');

        $tmpFilename = TemporaryUploadedFile::extractPathFromSignedPath($signedPath);
        $this->assertTrue(Storage::disk('tmp-for-tests')->exists(FileUploadConfiguration::path($tmpFilename)));

        // Meta file with original name should also exist
        $this->assertTrue(Storage::disk('tmp-for-tests')->exists(FileUploadConfiguration::path($tmpFilename . '.json')));

        $meta = json_decode(Storage::disk('tmp-for-tests')->get(FileUploadConfiguration::path($tmpFilename . '.json')), true);
        $this->assertEquals('small.txt', $meta['name']);
    }

    public function test_resume_via_offset_endpoint_then_continue()
    {
        $body = $this->initUpload(3000);

        // Send first chunk (1024 bytes)
        $this->sendChunk($body['patchUrl'], 0, str_repeat('A', 1024))->assertNoContent();

        // Simulate failure: client doesn't know server's offset, queries it
        $offsetResp = $this->get($body['offsetUrl']);
        $serverOffset = $offsetResp->json('offset');
        $this->assertEquals(1024, $serverOffset);

        // Resume from the server's offset
        $this->sendChunk($body['patchUrl'], $serverOffset, str_repeat('B', 1024))->assertNoContent();

        // Final chunk
        $finalResp = $this->sendChunk($body['patchUrl'], 2048, str_repeat('C', 952));
        $finalResp->assertNoContent();
        $this->assertEquals('true', $finalResp->headers->get('Upload-Complete'));
    }

    public function test_filename_with_non_ascii_characters_is_decoded_correctly()
    {
        $unicodeName = 'café_文件_😀.txt';
        $body = $this->initUpload(1024, $unicodeName);

        $manifestPath = FileUploadConfiguration::path("chunks/{$body['transferId']}/manifest.json");
        $manifest = json_decode(Storage::disk('tmp-for-tests')->get($manifestPath), true);
        $this->assertEquals($unicodeName, $manifest['name']);
    }

    public function test_chunking_disabled_when_chunk_size_is_null()
    {
        config()->set('livewire.temporary_file_upload.chunk_size', null);

        $this->assertFalse(FileUploadConfiguration::isChunkingEnabled());
    }

    public function test_chunking_disabled_when_using_s3()
    {
        config()->set('livewire.temporary_file_upload.chunk_size', 1024);
        config()->set('livewire.temporary_file_upload.disk', 's3');
        config()->set('filesystems.disks.s3.driver', 's3');

        $this->assertFalse(FileUploadConfiguration::isChunkingEnabled());
    }

    public function test_chunk_routes_use_chunk_middleware()
    {
        config()->set('livewire.temporary_file_upload.chunk_middleware', 'throttle:custom-name');

        $middleware = collect(ChunkedUploadController::middleware())->map->middleware->all();

        $this->assertContains('throttle:custom-name', $middleware);
    }

    public function test_max_upload_size_is_extracted_from_rules()
    {
        config()->set('livewire.temporary_file_upload.rules', ['file', 'max:500']);
        $this->assertEquals(500 * 1024, FileUploadConfiguration::maxUploadSizeInBytes());

        config()->set('livewire.temporary_file_upload.rules', ['file']);
        $this->assertNull(FileUploadConfiguration::maxUploadSizeInBytes());
    }

    public function test_multiple_concurrent_uploads_dont_interfere()
    {
        $body1 = $this->initUpload(1024, 'file1.txt');
        $body2 = $this->initUpload(2048, 'file2.txt');

        $this->assertNotEquals($body1['transferId'], $body2['transferId']);

        // Send a chunk to upload 1
        $this->sendChunk($body1['patchUrl'], 0, str_repeat('A', 1024))->assertNoContent();

        // Upload 2 should still be at offset 0
        $this->assertEquals(0, $this->get($body2['offsetUrl'])->json('offset'));

        // Send chunks to upload 2
        $this->sendChunk($body2['patchUrl'], 0, str_repeat('B', 1024))->assertNoContent();
        $this->sendChunk($body2['patchUrl'], 1024, str_repeat('B', 1024))->assertNoContent();

        // Both should have ended up assembled correctly without crossing wires
    }

    public function test_start_upload_does_not_pass_chunk_config_for_small_files()
    {
        config()->set('livewire.temporary_file_upload.chunk_size', 10 * 1024 * 1024); // 10MB

        $component = \Livewire\Livewire::test(ChunkedUploadComponent::class);

        // Manually trigger _startUpload with a small file
        $component->call('_startUpload', 'photo', [['name' => 'tiny.txt', 'size' => 100, 'type' => 'text/plain']], false);

        $component->assertDispatched('upload:generatedSignedUrl', function ($event, $payload) {
            return $payload['chunkConfig'] === null;
        });
    }

    public function test_start_upload_passes_chunk_config_for_large_files()
    {
        config()->set('livewire.temporary_file_upload.chunk_size', 1024); // 1KB

        $component = \Livewire\Livewire::test(ChunkedUploadComponent::class);

        $component->call('_startUpload', 'photo', [['name' => 'big.bin', 'size' => 5 * 1024, 'type' => 'application/octet-stream']], false);

        $component->assertDispatched('upload:generatedSignedUrl', function ($event, $payload) {
            return is_array($payload['chunkConfig'])
                && $payload['chunkConfig']['chunkSize'] === 1024
                && isset($payload['chunkConfig']['initUrl'])
                && isset($payload['chunkConfig']['retryDelays']);
        });
    }

    public function test_start_upload_skips_chunking_when_disabled()
    {
        config()->set('livewire.temporary_file_upload.chunk_size', null);

        $component = \Livewire\Livewire::test(ChunkedUploadComponent::class);

        $component->call('_startUpload', 'photo', [['name' => 'big.bin', 'size' => 100 * 1024 * 1024, 'type' => 'application/octet-stream']], false);

        $component->assertDispatched('upload:generatedSignedUrl', function ($event, $payload) {
            return $payload['chunkConfig'] === null;
        });
    }
}

class ChunkedUploadComponent extends \Tests\TestComponent
{
    use \Livewire\WithFileUploads;

    public $photo;
}
