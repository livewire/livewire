<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Http\UploadedFile;
use Livewire\Facades\GenerateSignedUploadUrlFacade;
use Livewire\Facades\S3MultipartUploadFacade;
use Symfony\Component\Mime\MimeTypes;

/**
 * Given the metadata of the files a user has selected, decide HOW they
 * should be uploaded and hand the frontend everything it needs to do so.
 *
 * Strategies:
 *   "form"    → POST all files to the signed upload endpoint (small files, non-S3 disks)
 *   "chunked" → slice each file and POST chunks to the chunk endpoint (large files, non-S3 disks)
 *   "s3"      → PUT each file to a presigned URL; large files use S3 multipart via per-part presigned URLs
 *   "reject"  → the declared file metadata already violates the configured rules, fail before any bytes move
 */
class UploadPlanner
{
    // Laravel's `image` rule content-sniffs the real file, and which extensions
    // it allows varies by framework version (svg is excluded by default since
    // Laravel 12) — preflight against the widest set any supported version
    // allows so a file is never rejected here that the authoritative
    // post-upload check would have accepted...
    protected $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'];

    public function plan($fileInfos, $isMultiple, $declaredRules = [])
    {
        $fileInfos = collect($fileInfos)->values()->map(fn ($info) => [
            'name' => is_string($info['name'] ?? null) ? $info['name'] : '',
            'size' => max(0, (int) ($info['size'] ?? 0)),
            'type' => is_string($info['type'] ?? null) ? $info['type'] : '',
            'lastModified' => (int) ($info['lastModified'] ?? 0),
        ]);

        $errors = array_merge_recursive(
            $this->declaredSizeViolations($fileInfos, $declaredRules['size'] ?? []) ?? [],
            $this->declaredTypeViolations($fileInfos, $declaredRules['types'] ?? []) ?? [],
        );

        if ($errors) {
            return ['strategy' => 'reject', 'errors' => json_encode(['errors' => $errors])];
        }

        if (FileUploadConfiguration::isUsingS3()) {
            // A missing Flysystem S3 adapter would otherwise surface as an
            // opaque fatal mid-request — fail fast with a pointer instead...
            FileUploadConfiguration::ensureS3AdapterIsInstalled();

            return $this->planForS3($fileInfos);
        }

        if ($this->shouldChunk($fileInfos)) {
            return $this->planForChunked($fileInfos);
        }

        return [
            'strategy' => 'form',
            'url' => GenerateSignedUploadUrlFacade::forLocal(),
        ];
    }

    protected function shouldChunk($fileInfos)
    {
        if (! FileUploadConfiguration::chunkingEnabled()) return false;

        return $fileInfos->contains(fn ($info) => $info['size'] > FileUploadConfiguration::chunkThreshold());
    }

    protected function planForChunked($fileInfos)
    {
        return [
            'strategy' => 'chunked',
            'url' => GenerateSignedUploadUrlFacade::forChunks(),
            'files' => $fileInfos->map(function ($info) {
                $chunkSize = $this->chunkSizeFor($info['size']);
                $totalChunks = ChunkedUpload::totalChunks($info['size'], $chunkSize);
                $fingerprint = ChunkedUpload::fingerprint($info, $chunkSize);

                return [
                    // A signed capability — the chunk endpoint takes the chunk
                    // count and size from here, not from request input...
                    'id' => ChunkedUpload::signCapability($fingerprint, $totalChunks, $chunkSize),
                    'chunkSize' => $chunkSize,
                    'totalChunks' => $totalChunks,
                    // Chunks that already made it to the server from a previous
                    // attempt — the frontend skips these so uploads are resumable...
                    'receivedChunks' => ChunkedUpload::receivedChunks($fingerprint),
                    // Already fully assembled on a previous attempt (a lost
                    // completion response before reload) — the frontend returns
                    // this straight away instead of re-uploading...
                    'completed' => ChunkedUpload::completedPath($fingerprint),
                ];
            })->all(),
        ];
    }

    protected function chunkSizeFor($size)
    {
        // Grow the chunk size for very large files so no upload ever needs
        // more than 10,000 chunks (also S3's limit on multipart parts)...
        return max(FileUploadConfiguration::chunkSize(), (int) ceil($size / ChunkedUpload::MAX_CHUNKS));
    }

    protected function planForS3($fileInfos)
    {
        $useMultipart = FileUploadConfiguration::chunkingEnabled();
        $threshold = FileUploadConfiguration::chunkThreshold();

        return [
            'strategy' => 's3',
            'files' => $fileInfos->map(function ($info) use ($useMultipart, $threshold) {
                if ($useMultipart && $info['size'] > $threshold) {
                    return ['multipart' => S3MultipartUploadFacade::plan($info)];
                }

                $file = UploadedFile::fake()->create($info['name'], $info['size'] / 1024, $info['type']);

                return GenerateSignedUploadUrlFacade::forS3($file);
            })->all(),
        ];
    }

    protected function declaredSizeViolations($fileInfos, $sizeRules = [])
    {
        $maxKilobytes = FileUploadConfiguration::maxDeclaredSizeInKilobytes();

        if (! $maxKilobytes && ! $sizeRules) return null;

        $errors = [];

        foreach ($fileInfos as $index => $info) {
            if ($message = $this->declaredSizeViolation($info['size'], $maxKilobytes, $sizeRules, 'files.'.$index)) {
                $errors['files.'.$index] = [$message];
            }
        }

        return $errors ?: null;
    }

    protected function declaredSizeViolation($bytes, $globalMaxKilobytes, $sizeRules, $attribute)
    {
        // These comparisons mirror Laravel's file size validation exactly
        // (kilobytes, boundary-inclusive) so a file the preflight lets through
        // never fails the same rule after upload — and vice versa...
        if ($globalMaxKilobytes && $bytes > $globalMaxKilobytes * 1024) {
            return trans('validation.max.file', ['attribute' => $attribute, 'max' => $globalMaxKilobytes]);
        }

        if (isset($sizeRules['max']) && $bytes > $sizeRules['max'] * 1024) {
            return trans('validation.max.file', ['attribute' => $attribute, 'max' => $sizeRules['max']]);
        }

        if (isset($sizeRules['min']) && $bytes < $sizeRules['min'] * 1024) {
            return trans('validation.min.file', ['attribute' => $attribute, 'min' => $sizeRules['min']]);
        }

        if (isset($sizeRules['size']) && $bytes !== $sizeRules['size'] * 1024) {
            return trans('validation.size.file', ['attribute' => $attribute, 'size' => $sizeRules['size']]);
        }

        if (isset($sizeRules['between']) && ($bytes < $sizeRules['between'][0] * 1024 || $bytes > $sizeRules['between'][1] * 1024)) {
            return trans('validation.between.file', ['attribute' => $attribute, 'min' => $sizeRules['between'][0], 'max' => $sizeRules['between'][1]]);
        }

        return null;
    }

    protected function declaredTypeViolations($fileInfos, $typeRules)
    {
        if (! $typeRules) return null;

        $errors = [];

        foreach ($fileInfos as $index => $info) {
            foreach ($typeRules as $constraint) {
                if ($message = $this->declaredTypeViolation($info, $constraint, 'files.'.$index)) {
                    $errors['files.'.$index][] = $message;
                }
            }
        }

        return $errors ?: null;
    }

    protected function declaredTypeViolation($info, $constraint, $attribute)
    {
        $extension = strtolower(pathinfo($info['name'], PATHINFO_EXTENSION));
        $mime = strtolower($info['type']);
        $parameters = $constraint['parameters'];

        switch ($constraint['rule']) {
            case 'extensions':
                // Laravel checks the client-supplied filename's extension —
                // exactly what we have, so this preflight is an exact mirror...
                if (! in_array($extension, $parameters)) {
                    return trans('validation.extensions', ['attribute' => $attribute, 'values' => implode(', ', $parameters)]);
                }

                return null;

            case 'image':
                if ($this->declaredTypeMatches($extension, $mime, $this->imageExtensions)) return null;

                return trans('validation.image', ['attribute' => $attribute]);

            case 'mimes':
                $allowed = $parameters;

                // Laravel treats jpg and jpeg as interchangeable...
                if (in_array('jpg', $allowed) || in_array('jpeg', $allowed)) {
                    $allowed = array_unique(array_merge($allowed, ['jpg', 'jpeg']));
                }

                if ($this->declaredTypeMatches($extension, $mime, $allowed)) return null;

                return trans('validation.mimes', ['attribute' => $attribute, 'values' => implode(', ', $parameters)]);

            case 'mimetypes':
                if ($this->declaredMimetypeMatches($extension, $mime, $parameters)) return null;

                return trans('validation.mimetypes', ['attribute' => $attribute, 'values' => implode(', ', $parameters)]);
        }

        return null;
    }

    // The authoritative `image`/`mimes` check sniffs the uploaded file's
    // contents — all we have here is the client-declared filename and MIME
    // type. Only reject when every declared signal contradicts the allowed
    // set; missing or unrecognized signals let the file through to the real
    // server-side validation...
    protected function declaredTypeMatches($extension, $mime, $allowedExtensions)
    {
        if ($extension === '' && $mime === '') return true;

        if ($extension !== '' && in_array($extension, $allowedExtensions)) return true;

        if ($mime !== '') {
            $mimeTypes = MimeTypes::getDefault();

            $extensionsForMime = array_map('strtolower', $mimeTypes->getExtensions($mime));

            if (array_intersect($extensionsForMime, $allowedExtensions)) return true;

            foreach ($allowedExtensions as $allowed) {
                if (in_array($mime, array_map('strtolower', $mimeTypes->getMimeTypes($allowed)))) return true;
            }

            // A MIME type nothing recognizes can't prove a violation on its own...
            if ($extension === '' && ! $extensionsForMime) return true;
        }

        return false;
    }

    protected function declaredMimetypeMatches($extension, $mime, $allowedMimetypes)
    {
        // Mirror Laravel's validateMimetypes, including `image/*`-style
        // wildcard families...
        $matches = fn ($candidate) => in_array($candidate, $allowedMimetypes)
            || in_array(explode('/', $candidate)[0].'/*', $allowedMimetypes);

        if ($extension === '' && $mime === '') return true;

        if ($mime !== '' && $matches($mime)) return true;

        if ($extension !== '') {
            $mimesForExtension = array_map('strtolower', MimeTypes::getDefault()->getMimeTypes($extension));

            foreach ($mimesForExtension as $candidate) {
                if ($matches($candidate)) return true;
            }

            // An extension nothing recognizes can't prove a violation on its own...
            if ($mime === '' && ! $mimesForExtension) return true;
        }

        return false;
    }
}
