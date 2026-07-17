<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Facades\GenerateSignedUploadUrlFacade;
use Livewire\Facades\S3MultipartUploadFacade;
use Livewire\Livewire;
use Tests\FileUploadComponent;
use Tests\TestComponent;

class ChunkedUploadsUnitTest extends \Tests\TestCase
{
    public function test_planner_uses_form_strategy_for_small_files_on_local_disks()
    {
        $plan = app(UploadPlanner::class)->plan([
            ['name' => 'avatar.png', 'size' => 1024, 'type' => 'image/png'],
        ], false);

        $this->assertEquals('form', $plan['strategy']);
        $this->assertStringContainsString('signature=', $plan['url']);
    }

    public function test_planner_uses_chunked_strategy_when_a_file_exceeds_the_threshold()
    {
        config()->set('livewire.temporary_file_upload.chunk_size', 1024);

        $plan = app(UploadPlanner::class)->plan([
            ['name' => 'movie.mp4', 'size' => 3000, 'type' => 'video/mp4', 'lastModified' => 123],
        ], false);

        $this->assertEquals('chunked', $plan['strategy']);
        $this->assertEquals(1024, $plan['files'][0]['chunkSize']);
        $this->assertCount(1, $plan['files']);
        $this->assertEquals(3, $plan['files'][0]['totalChunks']);
        $this->assertEquals([], $plan['files'][0]['receivedChunks']);
        $this->assertStringContainsString('upload-chunk', $plan['url']);
    }

    public function test_planner_grows_the_chunk_size_so_no_file_needs_more_than_ten_thousand_chunks()
    {
        config()->set('livewire.temporary_file_upload.chunk_size', 1024);
        config()->set('livewire.temporary_file_upload.rules', ['required', 'file']);

        $plan = app(UploadPlanner::class)->plan([
            ['name' => 'huge.mp4', 'size' => 50 * 1024 * 1024, 'type' => 'video/mp4'],
        ], false);

        $this->assertEquals('chunked', $plan['strategy']);
        $this->assertEquals((int) ceil(50 * 1024 * 1024 / 10000), $plan['files'][0]['chunkSize']);
        $this->assertEquals(10000, $plan['files'][0]['totalChunks']);
    }

    public function test_planner_keeps_small_files_on_the_form_strategy_when_chunking_is_disabled()
    {
        config()->set('livewire.temporary_file_upload.chunking', false);
        config()->set('livewire.temporary_file_upload.chunk_size', 1024);

        $plan = app(UploadPlanner::class)->plan([
            ['name' => 'movie.mp4', 'size' => 3000, 'type' => 'video/mp4'],
        ], false);

        $this->assertEquals('form', $plan['strategy']);
    }

    public function test_planner_reports_already_received_chunks_for_resumability()
    {
        config()->set('livewire.temporary_file_upload.chunk_size', 1024);

        $info = ['name' => 'movie.mp4', 'size' => 3000, 'type' => 'video/mp4', 'lastModified' => 123];

        $id = ChunkedUpload::fingerprint($info, 1024);

        FileUploadConfiguration::storage()->put(ChunkedUpload::directory($id).'/0.part', str_repeat('a', 1024));
        FileUploadConfiguration::storage()->put(ChunkedUpload::directory($id).'/2.part', str_repeat('a', 952));

        $plan = app(UploadPlanner::class)->plan([$info], false);

        $this->assertEquals([0, 2], $plan['files'][0]['receivedChunks']);
    }

    public function test_chunk_fingerprints_are_deterministic_and_size_sensitive()
    {
        $info = ['name' => 'movie.mp4', 'size' => 3000, 'type' => 'video/mp4', 'lastModified' => 123];

        $this->assertEquals(
            ChunkedUpload::fingerprint($info, 1024),
            ChunkedUpload::fingerprint($info, 1024)
        );

        $this->assertNotEquals(
            ChunkedUpload::fingerprint($info, 1024),
            ChunkedUpload::fingerprint(array_merge($info, ['size' => 3001]), 1024)
        );
    }

    public function test_chunk_fingerprints_are_scoped_to_the_session()
    {
        $info = ['name' => 'movie.mp4', 'size' => 3000, 'type' => 'video/mp4', 'lastModified' => 123];

        session()->setId(str_repeat('a', 40));
        $first = ChunkedUpload::fingerprint($info, 1024);

        // Another user uploading a file with identical metadata must never
        // land on (or be able to reference) the same chunk directory...
        session()->setId(str_repeat('b', 40));
        $second = ChunkedUpload::fingerprint($info, 1024);

        $this->assertNotEquals($first, $second);
    }

    public function test_planner_generates_a_presigned_url_per_file_on_s3()
    {
        config()->set('livewire.temporary_file_upload.disk', 's3');

        GenerateSignedUploadUrlFacade::swap(new class extends GenerateSignedUploadUrl {
            public function forS3($file, $visibility = '') {
                return ['path' => 'signed-path-for-'.$file->getClientOriginalName(), 'url' => 'https://s3.example.com/put', 'headers' => []];
            }
        });

        $plan = app(UploadPlanner::class)->plan([
            ['name' => 'one.png', 'size' => 1024, 'type' => 'image/png'],
            ['name' => 'two.png', 'size' => 2048, 'type' => 'image/png'],
        ], true);

        $this->assertEquals('s3', $plan['strategy']);
        $this->assertCount(2, $plan['files']);
        $this->assertEquals('signed-path-for-one.png', $plan['files'][0]['path']);
        $this->assertEquals('signed-path-for-two.png', $plan['files'][1]['path']);
    }

    public function test_planner_uses_s3_multipart_for_files_over_the_threshold()
    {
        config()->set('livewire.temporary_file_upload.disk', 's3');

        S3MultipartUploadFacade::swap(new class extends S3MultipartUpload {
            public function plan($fileInfo) {
                return ['ref' => 'signed-ref', 'partSize' => 5242880, 'totalParts' => 3, 'uploadedParts' => [], 'parts' => [], 'completeUrl' => 'https://example.com/multipart'];
            }
        });

        $plan = app(UploadPlanner::class)->plan([
            ['name' => 'movie.mp4', 'size' => 8 * 1024 * 1024, 'type' => 'video/mp4'],
        ], false);

        $this->assertEquals('s3', $plan['strategy']);
        $this->assertEquals('signed-ref', $plan['files'][0]['multipart']['ref']);
    }

    public function test_planner_rejects_files_whose_declared_size_violates_the_configured_max_rule()
    {
        $plan = app(UploadPlanner::class)->plan([
            ['name' => 'huge.mp4', 'size' => 13 * 1024 * 1024, 'type' => 'video/mp4'],
        ], false);

        $this->assertEquals('reject', $plan['strategy']);
        $this->assertArrayHasKey('files.0', json_decode($plan['errors'], true)['errors']);
    }

    public function test_chunks_are_stored_and_assembled_into_a_temporary_upload()
    {
        config()->set('livewire.temporary_file_upload.chunk_size', 1024);

        $info = ['name' => 'novel.txt', 'size' => 2048, 'type' => 'text/plain', 'lastModified' => 5];

        $id = ChunkedUpload::fingerprint($info, 1024);
        $signedId = ChunkedUpload::signCapability($id, 2, 1024);
        $url = GenerateSignedUploadUrlFacade::forChunks();

        $first = $this->post($url, array_merge($this->chunkPayload($signedId, 0, $info), [
            'chunk' => UploadedFile::fake()->createWithContent('novel.txt.part', str_repeat('A', 1024)),
        ]));

        $first->assertOk();
        $this->assertFalse($first->json('complete'));
        $this->assertEquals([0], $first->json('received'));

        $second = $this->post($url, array_merge($this->chunkPayload($signedId, 1, $info), [
            'chunk' => UploadedFile::fake()->createWithContent('novel.txt.part', str_repeat('B', 1024)),
        ]));

        $second->assertOk();
        $this->assertTrue($second->json('complete'));

        $path = TemporaryUploadedFile::extractPathFromSignedPath($second->json('paths.0'));

        $storage = FileUploadConfiguration::storage();

        $this->assertEquals(
            str_repeat('A', 1024).str_repeat('B', 1024),
            $storage->get(FileUploadConfiguration::path($path, false))
        );

        // The metadata sidecar records the original client name...
        $meta = json_decode($storage->get(FileUploadConfiguration::path($path.'.json', false)), true);
        $this->assertEquals('novel.txt', $meta['name']);

        // The chunk directory is cleaned up after assembly...
        $this->assertEmpty($storage->files(ChunkedUpload::directory($id)));
    }

    public function test_chunk_endpoint_rejects_tampered_upload_ids()
    {
        $url = GenerateSignedUploadUrlFacade::forChunks();

        $this->post($url, array_merge($this->chunkPayload('bad-token:'.sha1('malicious').'|2|1024', 0, ['name' => 'x.txt', 'size' => 10, 'type' => 'text/plain']), [
            'chunk' => UploadedFile::fake()->createWithContent('x.txt.part', 'hello'),
        ]))->assertForbidden();
    }

    public function test_chunk_endpoint_rejects_ids_with_a_tampered_chunk_count_or_size()
    {
        $url = GenerateSignedUploadUrlFacade::forChunks();
        $signedId = ChunkedUpload::signCapability(sha1('some-file'), 2, 1024);

        // Inflating the signed chunk count or chunk size breaks the signature...
        $inflated = str_replace('|2|1024', '|10000|1024', $signedId);

        $this->post($url, array_merge($this->chunkPayload($inflated, 0, ['name' => 'x.txt', 'size' => 10, 'type' => 'text/plain']), [
            'chunk' => UploadedFile::fake()->createWithContent('x.txt.part', 'hello'),
        ]))->assertForbidden();

        // A bare signed fingerprint without the capability payload is also rejected...
        $this->post($url, array_merge($this->chunkPayload(TemporaryUploadedFile::signPath(sha1('some-file')), 0, ['name' => 'x.txt', 'size' => 10, 'type' => 'text/plain']), [
            'chunk' => UploadedFile::fake()->createWithContent('x.txt.part', 'hello'),
        ]))->assertForbidden();
    }

    public function test_chunk_endpoint_bounds_the_index_and_chunk_size_by_the_signed_capability()
    {
        $url = GenerateSignedUploadUrlFacade::forChunks();
        $signedId = ChunkedUpload::signCapability(sha1('some-file'), 2, 1024);
        $headers = ['Accept' => 'application/json'];

        // An index at or past the signed chunk count is rejected...
        $this->post($url, array_merge($this->chunkPayload($signedId, 2, ['name' => 'x.txt', 'size' => 10, 'type' => 'text/plain']), [
            'chunk' => UploadedFile::fake()->createWithContent('x.txt.part', 'hello'),
        ]), $headers)->assertStatus(422);

        // A chunk meaningfully larger than the signed chunk size is rejected...
        $this->post($url, array_merge($this->chunkPayload($signedId, 0, ['name' => 'x.txt', 'size' => 10, 'type' => 'text/plain']), [
            'chunk' => UploadedFile::fake()->createWithContent('x.txt.part', str_repeat('A', 5 * 1024)),
        ]), $headers)->assertStatus(422);
    }

    public function test_chunk_endpoint_requires_a_valid_signature()
    {
        $this->post(route('livewire.upload-chunk'), $this->chunkPayload(ChunkedUpload::signCapability(sha1('x'), 1, 1024), 0, ['name' => 'x.txt', 'size' => 10, 'type' => 'text/plain']))
            ->assertUnauthorized();
    }

    public function test_assembled_chunked_files_must_pass_the_configured_upload_rules()
    {
        config()->set('livewire.temporary_file_upload.chunk_size', 1024);
        config()->set('livewire.temporary_file_upload.rules', ['required', 'file', 'mimes:png']);

        $info = ['name' => 'novel.txt', 'size' => 1500, 'type' => 'text/plain', 'lastModified' => 5];

        $id = ChunkedUpload::fingerprint($info, 1024);
        $signedId = ChunkedUpload::signCapability($id, 2, 1024);
        $url = GenerateSignedUploadUrlFacade::forChunks();

        $this->post($url, array_merge($this->chunkPayload($signedId, 0, $info), [
            'chunk' => UploadedFile::fake()->createWithContent('novel.txt.part', str_repeat('A', 1024)),
        ]))->assertOk();

        $response = $this->post($url, array_merge($this->chunkPayload($signedId, 1, $info), [
            'chunk' => UploadedFile::fake()->createWithContent('novel.txt.part', str_repeat('B', 476)),
        ]), ['Accept' => 'application/json']);

        $response->assertStatus(422);
        $this->assertArrayHasKey('files.0', $response->json('errors'));

        // A failed assembly should not leave chunks behind — the upload must start over...
        $this->assertEmpty(FileUploadConfiguration::storage()->files(ChunkedUpload::directory($id)));
    }

    public function test_multipart_endpoint_completes_uploads_through_the_facade()
    {
        config()->set('livewire.temporary_file_upload.disk', 's3');

        S3MultipartUploadFacade::swap(new class extends S3MultipartUpload {
            public $completed = [];
            public function complete($fingerprint) {
                $this->completed[] = $fingerprint;

                return TemporaryUploadedFile::signPath('assembled-file.mp4');
            }
        });

        $fingerprint = sha1('some-file-identity');

        $response = $this->post(GenerateSignedUploadUrlFacade::forMultipart(), [
            'action' => 'complete',
            'ref' => TemporaryUploadedFile::signPath($fingerprint),
        ]);

        $response->assertOk();
        $this->assertEquals([$fingerprint], S3MultipartUploadFacade::getFacadeRoot()->completed);
        $this->assertEquals('assembled-file.mp4', TemporaryUploadedFile::extractPathFromSignedPath($response->json('paths.0')));
    }

    public function test_multipart_endpoint_rejects_tampered_refs_and_non_s3_disks()
    {
        config()->set('livewire.temporary_file_upload.disk', 's3');

        $this->post(GenerateSignedUploadUrlFacade::forMultipart(), [
            'action' => 'complete',
            'ref' => 'bad-token:'.sha1('malicious'),
        ])->assertForbidden();

        config()->set('livewire.temporary_file_upload.disk', null);
        config()->set('filesystems.default', 'local');

        $this->post(GenerateSignedUploadUrlFacade::forMultipart(), [
            'action' => 'complete',
            'ref' => TemporaryUploadedFile::signPath(sha1('x')),
        ])->assertNotFound();
    }

    public function test_a_resent_final_chunk_after_assembly_resolves_from_the_completion_tombstone()
    {
        config()->set('livewire.temporary_file_upload.chunk_size', 1024);

        $info = ['name' => 'novel.txt', 'size' => 2048, 'type' => 'text/plain', 'lastModified' => 5];

        $id = ChunkedUpload::fingerprint($info, 1024);
        $signedId = ChunkedUpload::signCapability($id, 2, 1024);
        $url = GenerateSignedUploadUrlFacade::forChunks();

        $this->post($url, array_merge($this->chunkPayload($signedId, 0, $info), [
            'chunk' => UploadedFile::fake()->createWithContent('novel.txt.part', str_repeat('A', 1024)),
        ]))->assertOk();

        $completed = $this->post($url, array_merge($this->chunkPayload($signedId, 1, $info), [
            'chunk' => UploadedFile::fake()->createWithContent('novel.txt.part', str_repeat('B', 1024)),
        ]));

        $completed->assertOk();
        $this->assertTrue($completed->json('complete'));
        $originalPath = $completed->json('paths.0');

        // The chunk directory is gone now, so a re-sent final chunk (a lost
        // completion response) has nothing to stitch — it must resolve from the
        // tombstone with the SAME path rather than re-uploading the file...
        $this->assertEmpty(FileUploadConfiguration::storage()->files(ChunkedUpload::directory($id)));

        $resent = $this->post($url, array_merge($this->chunkPayload($signedId, 1, $info), [
            'chunk' => UploadedFile::fake()->createWithContent('novel.txt.part', str_repeat('B', 1024)),
        ]));

        $resent->assertOk();
        $this->assertTrue($resent->json('complete'));
        $this->assertEquals($originalPath, $resent->json('paths.0'));

        // The re-send must NOT recreate a chunk directory to re-stitch from...
        $this->assertEmpty(FileUploadConfiguration::storage()->files(ChunkedUpload::directory($id)));
    }

    public function test_the_planner_reports_a_completed_upload_so_a_reload_skips_re_uploading()
    {
        config()->set('livewire.temporary_file_upload.chunk_size', 1024);

        $info = ['name' => 'novel.txt', 'size' => 2048, 'type' => 'text/plain', 'lastModified' => 5];

        $id = ChunkedUpload::fingerprint($info, 1024);
        $signedResult = TemporaryUploadedFile::signPath('assembled-novel.txt');

        // A prior attempt fully assembled the file and left a completion
        // tombstone (its chunk directory is long gone)...
        FileUploadConfiguration::storage()->put(ChunkedUpload::directory($id).'.done', $signedResult);

        // A fresh plan (as after a page reload) surfaces that result so the
        // frontend returns it instead of re-uploading everything...
        $plan = app(UploadPlanner::class)->plan([$info], false);

        $this->assertEquals('chunked', $plan['strategy']);
        $this->assertEquals($signedResult, $plan['files'][0]['completed']);
    }

    public function test_the_reassembled_file_is_validated_against_the_max_rule_regardless_of_the_declared_size()
    {
        config()->set('livewire.temporary_file_upload.chunk_size', 1024);
        config()->set('livewire.temporary_file_upload.rules', ['required', 'file', 'max:1']); // 1KB

        // Declared size is a small lie (512 bytes), but the actual assembled
        // bytes exceed the max rule — assembly must re-validate and reject...
        $info = ['name' => 'novel.txt', 'size' => 512, 'type' => 'text/plain', 'lastModified' => 5];

        $id = ChunkedUpload::fingerprint($info, 1024);
        $signedId = ChunkedUpload::signCapability($id, 1, 1024);
        $url = GenerateSignedUploadUrlFacade::forChunks();

        $response = $this->post($url, array_merge($this->chunkPayload($signedId, 0, $info), [
            'chunk' => UploadedFile::fake()->createWithContent('novel.txt.part', str_repeat('A', 1536)),
        ]), ['Accept' => 'application/json']);

        $response->assertStatus(422);
        $this->assertArrayHasKey('files.0', $response->json('errors'));

        // A rejected assembly leaves nothing behind (no tombstone, no chunks)...
        $this->assertNull(ChunkedUpload::completedPath($id));
        $this->assertEmpty(FileUploadConfiguration::storage()->files(ChunkedUpload::directory($id)));
    }

    public function test_multipart_endpoint_aborts_uploads_and_rejects_unknown_actions()
    {
        config()->set('livewire.temporary_file_upload.disk', 's3');

        S3MultipartUploadFacade::swap(new class extends S3MultipartUpload {
            public $aborted = [];
            public function abort($fingerprint) { $this->aborted[] = $fingerprint; }
        });

        $fingerprint = sha1('some-file-identity');

        $aborted = $this->post(GenerateSignedUploadUrlFacade::forMultipart(), [
            'action' => 'abort',
            'ref' => TemporaryUploadedFile::signPath($fingerprint),
        ]);

        $aborted->assertOk();
        $this->assertTrue($aborted->json('aborted'));
        $this->assertEquals([$fingerprint], S3MultipartUploadFacade::getFacadeRoot()->aborted);

        // An unknown action is a 422, not a silent no-op...
        $this->post(GenerateSignedUploadUrlFacade::forMultipart(), [
            'action' => 'frobnicate',
            'ref' => TemporaryUploadedFile::signPath($fingerprint),
        ], ['Accept' => 'application/json'])->assertStatus(422);
    }

    public function test_empty_chunk_directories_are_swept_up_by_cleanup()
    {
        Storage::fake('avatars');

        $storage = FileUploadConfiguration::storage();

        // A leftover chunk directory from an abandoned upload, aged past 24h...
        $id = sha1('abandoned-upload');
        $storage->put(ChunkedUpload::directory($id).'/0.part', str_repeat('A', 128));
        touch($storage->path(ChunkedUpload::directory($id).'/0.part'), now()->subDays(2)->timestamp);

        $this->assertNotEmpty($storage->files(ChunkedUpload::directory($id)));

        // Any new upload triggers cleanupOldUploads()...
        Livewire::test(FileUploadComponent::class)
            ->set('photo', UploadedFile::fake()->image('avatar.jpg'))
            ->call('upload', 'uploaded-avatar.png');

        // The stale part is gone AND its now-empty directory is pruned...
        $this->assertEmpty($storage->files(ChunkedUpload::directory($id)));
        $this->assertNotContains(
            ChunkedUpload::directory($id),
            $storage->directories(FileUploadConfiguration::path('chunks'))
        );
    }

    protected function chunkPayload($signedId, $index, $info)
    {
        return [
            'id' => $signedId,
            'index' => $index,
            'name' => $info['name'],
            'type' => $info['type'],
        ];
    }
}