<?php

namespace Livewire\Features\SupportFileUploads;

use Livewire\Facades\S3MultipartUploadFacade;

class S3MultipartUploadController extends FileUploadController
{
    public function handleMultipart()
    {
        abort_unless(request()->hasValidSignature(), 401);

        abort_unless(FileUploadConfiguration::isUsingS3(), 404);

        $fingerprint = TemporaryUploadedFile::extractPathFromSignedPath((string) request('ref'));

        abort_if($fingerprint === false || ! preg_match('/^[a-f0-9]{40}$/', $fingerprint), 403, 'Invalid multipart upload reference.');

        switch (request('action')) {
            case 'complete':
                return ['paths' => [S3MultipartUploadFacade::complete($fingerprint)]];
            case 'abort':
                S3MultipartUploadFacade::abort($fingerprint);

                return ['aborted' => true];
            default:
                abort(422, 'Invalid multipart upload action.');
        }
    }
}
