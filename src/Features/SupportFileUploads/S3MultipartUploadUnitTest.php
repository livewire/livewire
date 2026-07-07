<?php

namespace Livewire\Features\SupportFileUploads;

/**
 * Exercises the real S3MultipartUpload logic (create → resume → complete →
 * abort, ListParts pagination, the completeness guard, and the mapping
 * self-heal) against a hand-rolled fake S3 client — no AWS SDK required.
 */
class S3MultipartUploadUnitTest extends \Tests\TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config()->set('livewire.temporary_file_upload.disk', 's3');
        config()->set('livewire.temporary_file_upload.chunk_size', 5 * 1024 * 1024);
    }

    protected function subject(FakeS3Client $client)
    {
        return new class($client) extends S3MultipartUpload {
            public function __construct(public $fake) {}
            protected function s3Client() { return $this->fake; }
            protected function s3Bucket() { return 'test-bucket'; }
            protected function finalizeSignedUri($uri) { return (string) $uri; }
        };
    }

    public function test_plan_creates_a_multipart_upload_and_presigns_every_part()
    {
        $client = new FakeS3Client;
        $subject = $this->subject($client);

        $info = ['name' => 'movie.mp4', 'size' => 12 * 1024 * 1024, 'type' => 'video/mp4', 'lastModified' => 1];

        $plan = $subject->plan($info);

        // 12MB / 5MB parts = 3 parts, none uploaded yet...
        $this->assertEquals(3, $plan['totalParts']);
        $this->assertCount(3, $plan['parts']);
        $this->assertEquals([1, 2, 3], collect($plan['parts'])->pluck('partNumber')->all());
        $this->assertEquals([], $plan['uploadedParts']);
        $this->assertEquals(1, $client->created);

        // The fingerprint → UploadId mapping was persisted for resumability...
        $fingerprint = ChunkedUpload::fingerprint($info, $plan['partSize']);
        $this->assertNotNull(FileUploadConfiguration::storage()->get(
            FileUploadConfiguration::path('multipart/'.$fingerprint.'.json', false)
        ));
    }

    public function test_plan_resumes_by_only_presigning_the_missing_parts()
    {
        $client = new FakeS3Client;
        $subject = $this->subject($client);

        $info = ['name' => 'movie.mp4', 'size' => 12 * 1024 * 1024, 'type' => 'video/mp4', 'lastModified' => 1];

        $first = $subject->plan($info);

        // Pretend parts 1 and 2 landed on S3 out of band...
        $client->parts = [
            ['PartNumber' => 1, 'Size' => 5 * 1024 * 1024, 'ETag' => 'etag-1'],
            ['PartNumber' => 2, 'Size' => 5 * 1024 * 1024, 'ETag' => 'etag-2'],
        ];

        $resumed = $this->subject($client)->plan($info);

        // The existing upload is reused (no second create), and only part 3 is presigned...
        $this->assertEquals(1, $client->created);
        $this->assertCount(1, $resumed['parts']);
        $this->assertEquals(3, $resumed['parts'][0]['partNumber']);
        $this->assertArrayHasKey(1, $resumed['uploadedParts']);
        $this->assertArrayHasKey(2, $resumed['uploadedParts']);
    }

    public function test_complete_refuses_to_finalize_a_truncated_upload()
    {
        $client = new FakeS3Client;
        $subject = $this->subject($client);

        $info = ['name' => 'movie.mp4', 'size' => 12 * 1024 * 1024, 'type' => 'video/mp4', 'lastModified' => 1];

        $plan = $subject->plan($info);
        $fingerprint = ChunkedUpload::fingerprint($info, $plan['partSize']);

        // Only 2 of the 3 expected parts are present — completing would produce
        // a truncated object, so it must abort with a 422...
        $client->parts = [
            ['PartNumber' => 1, 'Size' => 5 * 1024 * 1024, 'ETag' => 'etag-1'],
            ['PartNumber' => 2, 'Size' => 5 * 1024 * 1024, 'ETag' => 'etag-2'],
        ];

        try {
            $subject->complete($fingerprint);
            $this->fail('Expected an incomplete-upload abort.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(422, $e->getStatusCode());
        }

        $this->assertFalse($client->completed);
    }

    public function test_complete_finalizes_when_every_part_is_present()
    {
        $client = new FakeS3Client;
        $subject = $this->subject($client);

        $info = ['name' => 'movie.mp4', 'size' => 12 * 1024 * 1024, 'type' => 'video/mp4', 'lastModified' => 1];

        $plan = $subject->plan($info);
        $fingerprint = ChunkedUpload::fingerprint($info, $plan['partSize']);

        $client->parts = [
            ['PartNumber' => 1, 'Size' => 5 * 1024 * 1024, 'ETag' => 'etag-1'],
            ['PartNumber' => 2, 'Size' => 5 * 1024 * 1024, 'ETag' => 'etag-2'],
            ['PartNumber' => 3, 'Size' => 2 * 1024 * 1024, 'ETag' => 'etag-3'],
        ];

        $signedPath = $subject->complete($fingerprint);

        $this->assertTrue($client->completed);
        // ETags are collected server-side from ListParts, in ascending order...
        $this->assertEquals(
            [['PartNumber' => 1, 'ETag' => 'etag-1'], ['PartNumber' => 2, 'ETag' => 'etag-2'], ['PartNumber' => 3, 'ETag' => 'etag-3']],
            $client->completedParts
        );
        $this->assertIsString(TemporaryUploadedFile::extractPathFromSignedPath($signedPath));

        // The mapping is cleaned up after a successful completion...
        $this->assertNull(FileUploadConfiguration::storage()->get(
            FileUploadConfiguration::path('multipart/'.$fingerprint.'.json', false)
        ));
    }

    public function test_list_parts_paginates_beyond_a_single_page()
    {
        $client = new FakeS3Client;
        $client->pageSize = 1000;
        $client->parts = collect(range(1, 2500))->map(fn ($n) => [
            'PartNumber' => $n, 'Size' => 5 * 1024 * 1024, 'ETag' => 'etag-'.$n,
        ])->all();

        $subject = $this->subject($client);

        $info = ['name' => 'huge.mp4', 'size' => 2500 * 5 * 1024 * 1024, 'type' => 'video/mp4', 'lastModified' => 1];

        $plan = $subject->plan($info);

        // All 2500 already-uploaded parts are seen across 3 ListParts pages...
        $this->assertCount(2500, $plan['uploadedParts']);
        $this->assertGreaterThanOrEqual(3, $client->listPartsCalls);
    }

    public function test_a_stale_mapping_self_heals_only_on_a_missing_upload_error()
    {
        $info = ['name' => 'movie.mp4', 'size' => 12 * 1024 * 1024, 'type' => 'video/mp4', 'lastModified' => 1];
        $partSize = 5 * 1024 * 1024;
        $fingerprint = ChunkedUpload::fingerprint($info, $partSize);

        // A genuine "upload is gone" error discards the stale mapping and
        // transparently starts a fresh multipart upload...
        $missing = new FakeS3Client;
        $subject = $this->subject($missing);

        FileUploadConfiguration::storage()->put(
            FileUploadConfiguration::path('multipart/'.$fingerprint.'.json', false),
            json_encode(['filename' => 'x', 'key' => 'k', 'uploadId' => 'dead', 'partSize' => $partSize, 'name' => 'movie.mp4', 'size' => 12 * 1024 * 1024])
        );

        // Only the resumability probe throws — the freshly created upload lists fine...
        $missing->throwOnListOnce = new FakeAwsException('NoSuchUpload');

        $subject->plan($info);

        $this->assertEquals(1, $missing->created);

        // A transient error must NOT delete the mapping (it would orphan a live upload)...
        $transient = new FakeS3Client;
        $transient->throwOnListOnce = new FakeAwsException('SlowDown');
        $subject = $this->subject($transient);

        FileUploadConfiguration::storage()->put(
            FileUploadConfiguration::path('multipart/'.$fingerprint.'.json', false),
            json_encode(['filename' => 'x', 'key' => 'k', 'uploadId' => 'live', 'partSize' => $partSize, 'name' => 'movie.mp4', 'size' => 12 * 1024 * 1024])
        );

        try {
            $subject->plan($info);
            $this->fail('Expected the transient S3 error to propagate.');
        } catch (FakeAwsException $e) {
            $this->assertEquals('SlowDown', $e->getAwsErrorCode());
        }

        // The mapping survived — the live upload was not orphaned...
        $this->assertNotNull(FileUploadConfiguration::storage()->get(
            FileUploadConfiguration::path('multipart/'.$fingerprint.'.json', false)
        ));
        $this->assertEquals(0, $transient->created);
    }

    public function test_abort_removes_the_mapping_and_aborts_on_s3()
    {
        $client = new FakeS3Client;
        $subject = $this->subject($client);

        $info = ['name' => 'movie.mp4', 'size' => 12 * 1024 * 1024, 'type' => 'video/mp4', 'lastModified' => 1];

        $plan = $subject->plan($info);
        $fingerprint = ChunkedUpload::fingerprint($info, $plan['partSize']);

        $subject->abort($fingerprint);

        $this->assertTrue($client->aborted);
        $this->assertNull(FileUploadConfiguration::storage()->get(
            FileUploadConfiguration::path('multipart/'.$fingerprint.'.json', false)
        ));
    }
}

class FakeAwsException extends \RuntimeException
{
    public function __construct(protected string $awsCode)
    {
        parent::__construct($awsCode);
    }

    public function getAwsErrorCode()
    {
        return $this->awsCode;
    }
}

class FakeS3Client
{
    public int $created = 0;
    public bool $completed = false;
    public array $completedParts = [];
    public bool $aborted = false;
    public array $parts = [];
    public int $pageSize = 1000;
    public int $listPartsCalls = 0;
    public $throwOnList = null;
    public $throwOnListOnce = null;

    public function createMultipartUpload($args)
    {
        $this->created++;

        return ['UploadId' => 'upload-'.$this->created];
    }

    public function listParts($args)
    {
        $this->listPartsCalls++;

        if ($this->throwOnList) throw $this->throwOnList;

        if ($this->throwOnListOnce) {
            $error = $this->throwOnListOnce;
            $this->throwOnListOnce = null;
            throw $error;
        }

        $marker = (int) ($args['PartNumberMarker'] ?? 0);
        $limit = $args['MaxParts'] ?? $this->pageSize;

        $page = collect($this->parts)
            ->filter(fn ($part) => $part['PartNumber'] > $marker)
            ->take($limit)
            ->values();

        $last = $page->last();
        $truncated = $last && collect($this->parts)->contains(fn ($p) => $p['PartNumber'] > $last['PartNumber']);

        return [
            'Parts' => $page->all(),
            'IsTruncated' => (bool) $truncated,
            'NextPartNumberMarker' => $last['PartNumber'] ?? null,
        ];
    }

    public function completeMultipartUpload($args)
    {
        $this->completed = true;
        $this->completedParts = $args['MultipartUpload']['Parts'];
    }

    public function abortMultipartUpload($args)
    {
        $this->aborted = true;
    }

    public function getCommand($name, $args = [])
    {
        return ['name' => $name, 'args' => $args];
    }

    public function createPresignedRequest($command, $expires)
    {
        return new class($command) {
            public function __construct(public $command) {}
            public function getUri() { return 'https://s3.example.com/'.($this->command['args']['PartNumber'] ?? 'x'); }
        };
    }
}
