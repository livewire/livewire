<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Livewire\WithFileUploads;
use Tests\TestComponent;

class ChunkUnitTest extends \Tests\TestCase
{
    protected function addChunkedUploadToSession($uploadId, $fileName = 'file.txt', $fileSize = 1024, $fileMimeType = 'text/plain')
    {
        $uploads = session()->get('livewire_chunked_uploads', []);
        $uploads[$uploadId] = [
            'fileName' => $fileName,
            'fileSize' => $fileSize,
            'fileMimeType' => $fileMimeType,
        ];
        session()->put('livewire_chunked_uploads', $uploads);
    }

    public function test_start_chunked_upload_dispatches_signed_chunk_url_event()
    {
        $component = Livewire::test(ChunkedUploadComponent::class)
            ->call('_startChunkedUpload', 'photo', [['name' => 'video.mp4', 'size' => 10485760, 'type' => 'video/mp4']], false);

        $component->assertDispatched('upload:generatedSignedChunkUrl');
    }

    public function test_start_chunked_upload_stores_upload_metadata_in_session()
    {
        Livewire::test(ChunkedUploadComponent::class)
            ->call('_startChunkedUpload', 'photo', [['name' => 'video.mp4', 'size' => 10485760, 'type' => 'video/mp4']], false);

        $uploads = session()->get('livewire_chunked_uploads');
        $this->assertNotEmpty($uploads);

        $metadata = array_values($uploads)[0];
        $this->assertEquals('video.mp4', $metadata['fileName']);
        $this->assertEquals(10485760, $metadata['fileSize']);
        $this->assertEquals('video/mp4', $metadata['fileMimeType']);
    }

    public function test_chunked_upload_throws_for_s3()
    {
        config()->set('livewire.temporary_file_upload.disk', 's3');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Chunked uploads are not supported with S3 storage.');

        Livewire::test(ChunkedUploadComponent::class)
            ->call('_startChunkedUpload', 'photo', [['name' => 'video.mp4', 'size' => 10485760, 'type' => 'video/mp4']], false);
    }

    public function test_component_must_have_file_uploads_trait_for_chunked_uploads()
    {
        $this->expectException(MissingFileUploadsTraitException::class);

        Livewire::test(NonChunkedUploadComponent::class)
            ->call('_startChunkedUpload', 'photo', [['name' => 'video.mp4', 'size' => 10485760, 'type' => 'video/mp4']], false);
    }

    public function test_chunk_upload_controller_stores_individual_chunks()
    {
        $storage = FileUploadConfiguration::storage();
        $uploadId = (string) Str::uuid();
        $this->addChunkedUploadToSession($uploadId, 'video.mp4', 3072, 'video/mp4');

        $url = URL::temporarySignedRoute('livewire.upload-chunk', now()->addMinutes(5), ['uploadId' => $uploadId]);

        $chunk = UploadedFile::fake()->createWithContent('video.mp4', str_repeat('A', 1024));

        $response = $this->post($url, [
            'chunk' => $chunk,
            'uploadId' => $uploadId,
            'chunkIndex' => 0,
            'totalChunks' => 3,
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'partial', 'index' => 0]);

        $chunkDir = FileUploadConfiguration::path('chunks/' . $uploadId);
        $this->assertTrue($storage->exists($chunkDir . '/000000'));
    }

    public function test_chunk_upload_reassembles_on_final_chunk()
    {
        $storage = FileUploadConfiguration::storage();
        $uploadId = (string) Str::uuid();

        $content1 = str_repeat('A', 1024);
        $content2 = str_repeat('B', 1024);
        $totalSize = strlen($content1) + strlen($content2);

        $this->addChunkedUploadToSession($uploadId, 'file.txt', $totalSize, 'text/plain');

        $url = URL::temporarySignedRoute('livewire.upload-chunk', now()->addMinutes(5), ['uploadId' => $uploadId]);

        // Send chunk 0
        $this->post($url, [
            'chunk' => UploadedFile::fake()->createWithContent('file.txt', $content1),
            'uploadId' => $uploadId,
            'chunkIndex' => 0,
            'totalChunks' => 2,
        ])->assertJson(['status' => 'partial']);

        // Send chunk 1 (final)
        $response = $this->post($url, [
            'chunk' => UploadedFile::fake()->createWithContent('file.txt', $content2),
            'uploadId' => $uploadId,
            'chunkIndex' => 1,
            'totalChunks' => 2,
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['path']);

        // Chunk directory should be cleaned up
        $chunkDir = FileUploadConfiguration::path('chunks/' . $uploadId);
        $this->assertEmpty($storage->files($chunkDir));
    }

    public function test_chunk_upload_rejects_invalid_upload_id()
    {
        $uploadId = (string) Str::uuid();
        // Note: NOT adding to session

        $url = URL::temporarySignedRoute('livewire.upload-chunk', now()->addMinutes(5), ['uploadId' => $uploadId]);

        $chunk = UploadedFile::fake()->createWithContent('file.txt', 'test');

        $response = $this->post($url, [
            'chunk' => $chunk,
            'uploadId' => $uploadId,
            'chunkIndex' => 0,
            'totalChunks' => 1,
        ]);

        $response->assertStatus(403);
    }

    public function test_chunk_upload_rejects_upload_id_mismatch_between_signature_and_payload()
    {
        $signedUploadId = (string) Str::uuid();
        $payloadUploadId = (string) Str::uuid();

        $this->addChunkedUploadToSession($signedUploadId);
        $this->addChunkedUploadToSession($payloadUploadId);

        $url = URL::temporarySignedRoute('livewire.upload-chunk', now()->addMinutes(5), ['uploadId' => $signedUploadId]);

        $chunk = UploadedFile::fake()->createWithContent('file.txt', 'test');

        $response = $this->post($url, [
            'chunk' => $chunk,
            'uploadId' => $payloadUploadId,
            'chunkIndex' => 0,
            'totalChunks' => 1,
        ]);

        $response->assertStatus(403);
    }

    public function test_chunk_upload_rejects_invalid_signature()
    {
        $uploadId = (string) Str::uuid();
        $this->addChunkedUploadToSession($uploadId);

        // Generate a URL without valid signature
        $url = route('livewire.upload-chunk', ['uploadId' => $uploadId]);

        $chunk = UploadedFile::fake()->createWithContent('file.txt', 'test');

        $response = $this->post($url, [
            'chunk' => $chunk,
            'uploadId' => $uploadId,
            'chunkIndex' => 0,
            'totalChunks' => 1,
        ]);

        $response->assertStatus(401);
    }

    public function test_chunk_exceeding_max_size_is_rejected()
    {
        config()->set('livewire.temporary_file_upload.max_chunk_size', 512);

        $uploadId = (string) Str::uuid();
        $this->addChunkedUploadToSession($uploadId, 'file.txt', 1024, 'text/plain');

        $url = URL::temporarySignedRoute('livewire.upload-chunk', now()->addMinutes(5), ['uploadId' => $uploadId]);

        $chunk = UploadedFile::fake()->createWithContent('file.txt', str_repeat('A', 1024));

        $response = $this->post($url, [
            'chunk' => $chunk,
            'uploadId' => $uploadId,
            'chunkIndex' => 0,
            'totalChunks' => 1,
        ]);

        $response->assertStatus(422);
    }

    public function test_reassembled_file_size_mismatch_is_rejected()
    {
        $uploadId = (string) Str::uuid();
        // Set fileSize to 9999 (intentionally wrong) in server-side metadata
        $this->addChunkedUploadToSession($uploadId, 'file.txt', 9999, 'text/plain');

        $url = URL::temporarySignedRoute('livewire.upload-chunk', now()->addMinutes(5), ['uploadId' => $uploadId]);

        $content = str_repeat('A', 1024);

        $response = $this->post($url, [
            'chunk' => UploadedFile::fake()->createWithContent('file.txt', $content),
            'uploadId' => $uploadId,
            'chunkIndex' => 0,
            'totalChunks' => 1,
        ]);

        $response->assertStatus(422);
    }

    public function test_cancel_chunked_upload_cleans_up_chunk_directory()
    {
        $storage = FileUploadConfiguration::storage();
        $uploadId = (string) Str::uuid();
        $this->addChunkedUploadToSession($uploadId);

        // Store a fake chunk
        $chunkDir = FileUploadConfiguration::path('chunks/' . $uploadId);
        $storage->put($chunkDir . '/000000', 'chunk-data');

        $this->assertTrue($storage->exists($chunkDir . '/000000'));

        Livewire::test(ChunkedUploadComponent::class)
            ->call('_cancelChunkedUpload', $uploadId);

        $this->assertEmpty($storage->files($chunkDir));
        $this->assertArrayNotHasKey($uploadId, session()->get('livewire_chunked_uploads', []));
    }

    public function test_cancel_chunked_upload_rejects_invalid_uuid()
    {
        $storage = FileUploadConfiguration::storage();

        // Create a directory that could be targeted
        $chunkDir = FileUploadConfiguration::path('chunks/not-a-uuid');
        $storage->put($chunkDir . '/000000', 'chunk-data');

        Livewire::test(ChunkedUploadComponent::class)
            ->call('_cancelChunkedUpload', 'not-a-uuid');

        // Directory should NOT be deleted because the uploadId is not a valid UUID
        $this->assertTrue($storage->exists($chunkDir . '/000000'));
    }

    public function test_cancel_chunked_upload_rejects_non_session_upload_id()
    {
        $storage = FileUploadConfiguration::storage();
        $uploadId = (string) Str::uuid();
        // Note: NOT adding to session

        $chunkDir = FileUploadConfiguration::path('chunks/' . $uploadId);
        $storage->put($chunkDir . '/000000', 'chunk-data');

        Livewire::test(ChunkedUploadComponent::class)
            ->call('_cancelChunkedUpload', $uploadId);

        // Directory should NOT be deleted because uploadId is not in session
        $this->assertTrue($storage->exists($chunkDir . '/000000'));
    }

    public function test_chunked_upload_produces_standard_temporary_uploaded_file()
    {
        $storage = FileUploadConfiguration::storage();
        $uploadId = (string) Str::uuid();

        $content1 = str_repeat('X', 2048);
        $content2 = str_repeat('Y', 2048);
        $totalSize = strlen($content1) + strlen($content2);

        $this->addChunkedUploadToSession($uploadId, 'document.pdf', $totalSize, 'application/pdf');

        $url = URL::temporarySignedRoute('livewire.upload-chunk', now()->addMinutes(5), ['uploadId' => $uploadId]);

        $this->post($url, [
            'chunk' => UploadedFile::fake()->createWithContent('document.pdf', $content1),
            'uploadId' => $uploadId,
            'chunkIndex' => 0,
            'totalChunks' => 2,
        ]);

        $response = $this->post($url, [
            'chunk' => UploadedFile::fake()->createWithContent('document.pdf', $content2),
            'uploadId' => $uploadId,
            'chunkIndex' => 1,
            'totalChunks' => 2,
        ]);

        $signedPath = $response->json('path');

        // Verify the signed path can be extracted
        $path = TemporaryUploadedFile::extractPathFromSignedPath($signedPath);
        $this->assertNotFalse($path);

        // Verify a TemporaryUploadedFile can be created from it
        $tmpFile = TemporaryUploadedFile::createFromLivewire($path);
        $this->assertInstanceOf(TemporaryUploadedFile::class, $tmpFile);
        $this->assertEquals($totalSize, $tmpFile->getSize());
    }

    public function test_chunk_middleware_is_configurable()
    {
        $this->assertEquals('throttle:300,1', FileUploadConfiguration::chunkMiddleware());

        config()->set('livewire.temporary_file_upload.chunk_middleware', 'throttle:500,1');
        $this->assertEquals('throttle:500,1', FileUploadConfiguration::chunkMiddleware());
    }

    public function test_max_chunk_size_is_configurable()
    {
        $this->assertEquals(3 * 1024 * 1024, FileUploadConfiguration::maxChunkSize());

        config()->set('livewire.temporary_file_upload.max_chunk_size', 5 * 1024 * 1024);
        $this->assertEquals(5 * 1024 * 1024, FileUploadConfiguration::maxChunkSize());
    }
}

class ChunkedUploadComponent extends TestComponent
{
    use WithFileUploads;

    public $photo;
    public $photos = [];

    public function upload($name)
    {
        $this->photo->storeAs('/', $name, 'avatars');
    }
}

class NonChunkedUploadComponent extends TestComponent
{
    public $photo;
}
