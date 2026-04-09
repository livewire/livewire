Livewire offers powerful support for uploading files within your components.

First, add the `WithFileUploads` trait to your component. Once this trait has been added to your component, you can use `wire:model` on file inputs as if they were any other input type and Livewire will take care of the rest.

Here's an example of a simple component that handles uploading a photo:

```php
<?php // resources/views/components/⚡upload-photo.blade.php

use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use Livewire\Component;

new class extends Component {
    use WithFileUploads;

    #[Validate('image|max:1024')] // 1MB Max
    public $photo;

    public function save()
    {
        $this->photo->store(path: 'photos');
    }
};
```

```blade
<form wire:submit="save">
    <input type="file" wire:model="photo">

    @error('photo') <span class="error">{{ $message }}</span> @enderror

    <button type="submit">Save photo</button>
</form>
```

> [!warning] The "upload" method is reserved
> Notice the above example uses a "save" method instead of an "upload" method. This is a common "gotcha". The term "upload" is reserved by Livewire. You cannot use it as a method or property name in your component.

From the developer's perspective, handling file inputs is no different than handling any other input type: Add `wire:model` to the `<input>` tag and everything else is taken care of for you.

However, more is happening under the hood to make file uploads work in Livewire. Here's a glimpse at what goes on when a user selects a file to upload:

1. When a new file is selected, Livewire's JavaScript makes an initial request to the component on the server to get a temporary "signed" upload URL.
2. Once the URL is received, JavaScript does the actual "upload" to the signed URL, storing the upload in a temporary directory designated by Livewire and returning the new temporary file's unique hash ID.
3. Once the file is uploaded and the unique hash ID is generated, Livewire's JavaScript makes a final request to the component on the server, telling it to "set" the desired public property to the new temporary file.
4. Now, the public property (in this case, `$photo`) is set to the temporary file upload and is ready to be stored or validated at any point.

## Storing uploaded files

The previous example demonstrates the most basic storage scenario: moving the temporarily uploaded file to the "photos" directory on the application's default filesystem disk.

However, you may want to customize the file name of the stored file or even specify a specific storage "disk" to keep the file on (such as S3).

> [!tip] Original file names
> You can access the original file name of a temporary upload, by calling its `->getClientOriginalName()` method.

Livewire honors the same APIs Laravel uses for storing uploaded files, so feel free to consult [Laravel's file upload documentation](https://laravel.com/docs/filesystem#file-uploads). However, below are a few common storage scenarios and examples:

```php
public function save()
{
    // Store the file in the "photos" directory of the default filesystem disk
    $this->photo->store(path: 'photos');

    // Store the file in the "photos" directory in a configured "s3" disk
    $this->photo->store(path: 'photos', options: 's3');

    // Store the file in the "photos" directory with the filename "avatar.png"
    $this->photo->storeAs(path: 'photos', name: 'avatar');

    // Store the file in the "photos" directory in a configured "s3" disk with the filename "avatar.png"
    $this->photo->storeAs(path: 'photos', name: 'avatar', options: 's3');

    // Store the file in the "photos" directory, with "public" visibility in a configured "s3" disk
    $this->photo->storePublicly(path: 'photos', options: 's3');

    // Store the file in the "photos" directory, with the name "avatar.png", with "public" visibility in a configured "s3" disk
    $this->photo->storePubliclyAs(path: 'photos', name: 'avatar', options: 's3');
}
```

## Handling multiple files

Livewire automatically handles multiple file uploads by detecting the `multiple` attribute on the `<input>` tag.

For example, below is a component with an array property named `$photos`. By adding `multiple` to the form's file input, Livewire will automatically append new files to this array:

```php
<?php // resources/views/components/⚡upload-photos.blade.php

use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use Livewire\Component;

new class extends Component {
    use WithFileUploads;

    #[Validate(['photos.*' => 'image|max:1024'])]
    public $photos = [];

    public function save()
    {
        foreach ($this->photos as $photo) {
            $photo->store(path: 'photos');
        }
    }
};
```

```blade
<form wire:submit="save">
    <input type="file" wire:model="photos" multiple>

    @error('photos.*') <span class="error">{{ $message }}</span> @enderror

    <button type="submit">Save photo</button>
</form>
```

## File validation

Like we've discussed, validating file uploads with Livewire is the same as handling file uploads from a standard Laravel controller.

> [!warning] Ensure S3 is properly configured
> Many of the validation rules relating to files require access to the file. When [uploading directly to S3](#uploading-directly-to-amazon-s3), these validation rules will fail if the S3 file object is not publicly accessible.

For more information on file validation, consult [Laravel's file validation documentation](https://laravel.com/docs/validation#available-validation-rules).

## Temporary preview URLs

After a user chooses a file, you should typically show them a preview of that file before they submit the form and store the file.

Livewire makes this trivial by using the `->temporaryUrl()` method on uploaded files.

> [!info] Temporary URLs are restricted to images
> For security reasons, temporary preview URLs are only supported on files with image MIME types.

Let's explore an example of a file upload with an image preview:

```php
<?php // resources/views/components/⚡upload-photo.blade.php

use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use Livewire\Component;

new class extends Component {
    use WithFileUploads;

    #[Validate('image|max:1024')]
    public $photo;

    // ...
};
```

```blade
<form wire:submit="save">
    @if ($photo) <!-- [tl! highlight:2] -->
        <img src="{{ $photo->temporaryUrl() }}">
    @endif

    <input type="file" wire:model="photo">

    @error('photo') <span class="error">{{ $message }}</span> @enderror

    <button type="submit">Save photo</button>
</form>
```

As previously discussed, Livewire stores temporary files in a non-public directory; therefore, typically there's no simple way to expose a temporary, public URL to your users for image previewing.

However, Livewire solves this issue by providing a temporary, signed URL that pretends to be the uploaded image so your page can show an image preview to your users.

This URL is protected against showing files in directories above the temporary directory. And, because it's signed, users can't abuse this URL to preview other files on your system.

> [!tip] S3 temporary signed URLs
> If you've configured Livewire to use S3 for temporary file storage, calling `->temporaryUrl()` will generate a temporary, signed URL to S3 directly so that image previews aren't loaded from your Laravel application server.

## Testing file uploads

You can use Laravel's existing file upload testing helpers to test file uploads.

Below is a complete example of testing the `UploadPhoto` component with Livewire:

```php
<?php

namespace Tests\Feature\Livewire;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Livewire\UploadPhoto;
use Livewire\Livewire;
use Tests\TestCase;

class UploadPhotoTest extends TestCase
{
    public function test_can_upload_photo()
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.png');

        Livewire::test(UploadPhoto::class)
            ->set('photo', $file)
            ->call('upload', 'uploaded-avatar.png');

        Storage::disk('avatars')->assertExists('uploaded-avatar.png');
    }
}
```

Below is an example of the `upload-photo` component required to make the previous test pass:

```php
<?php // resources/views/components/⚡upload-photo.blade.php

use Livewire\WithFileUploads;
use Livewire\Component;

new class extends Component {
    use WithFileUploads;

    public $photo;

    public function upload($name)
    {
        $this->photo->storeAs('/', $name, disk: 'avatars');
    }

    // ...
};
```

For more information on testing file uploads, please consult [Laravel's file upload testing documentation](https://laravel.com/docs/http-tests#testing-file-uploads).

## Uploading directly to Amazon S3

As previously discussed, Livewire stores all file uploads in a temporary directory until the developer permanently stores the file.

By default, Livewire uses the default filesystem disk configuration (usually `local`) and stores the files within a `livewire-tmp/` directory.

Consequently, file uploads are always utilizing your application server, even if you choose to store the uploaded files in an S3 bucket later.

If you wish to bypass your application server and instead store Livewire's temporary uploads in an S3 bucket, set the `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK` environment variable in your `.env` file to `s3` (or another custom disk that uses the `s3` driver):

```env
LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=s3
```

Now, when a user uploads a file, the file will never actually be stored on your server. Instead, it will be uploaded directly to your S3 bucket within the `livewire-tmp/` sub-directory.

> [!tip]
> Alternatively, you can publish Livewire's configuration file with `php artisan livewire:config` for full control over the `temporary_file_upload` config.

### Configuring automatic file cleanup

Livewire's temporary upload directory will fill up with files quickly; therefore, it's essential to configure S3 to clean up files older than 24 hours.

To configure this behavior, run the following Artisan command from the environment that is utilizing an S3 bucket for file uploads:

```shell
php artisan livewire:configure-s3-upload-cleanup
```

Now, any temporary files older than 24 hours will be cleaned up by S3 automatically.

> [!info]
> If you are not using S3 for file storage, Livewire will handle file cleanup automatically and there is no need to run the command above.

## Loading indicators

Although `wire:model` for file uploads works differently than other `wire:model` input types under the hood, the interface for showing loading indicators remains the same.

You can display a loading indicator scoped to the file upload using `wire:loading`:

```blade
<input type="file" wire:model="photo">

<div wire:loading wire:target="photo">Uploading...</div>
```

Or more simply using Livewire's automatic `data-loading` attribute:

```blade
<div>
    <input type="file" wire:model="photo">

    <div class="not-data-loading:hidden">Uploading...</div>
</div>
```

Now, while the file is uploading, the "Uploading..." message will be shown and then hidden when the upload is finished.

[Learn more about loading states →](/docs/4.x/loading-states)

## Progress indicators

Every Livewire file upload operation dispatches JavaScript events on the corresponding `<input>` element, allowing custom JavaScript to intercept the events:

Event | Description
--- | ---
`livewire-upload-start` | Dispatched when the upload starts
`livewire-upload-finish` | Dispatched if the upload is successfully finished
`livewire-upload-cancel` | Dispatched if the upload was cancelled prematurely
`livewire-upload-error` | Dispatched if the upload fails
`livewire-upload-progress` | An event containing the upload progress percentage as the upload progresses

Below is an example of wrapping a Livewire file upload in an Alpine component to display an upload progress bar:

```blade
<form wire:submit="save">
    <div
        x-data="{ uploading: false, progress: 0 }"
        x-on:livewire-upload-start="uploading = true"
        x-on:livewire-upload-finish="uploading = false"
        x-on:livewire-upload-cancel="uploading = false"
        x-on:livewire-upload-error="uploading = false"
        x-on:livewire-upload-progress="progress = $event.detail.progress"
    >
        <!-- File Input -->
        <input type="file" wire:model="photo">

        <!-- Progress Bar -->
        <div x-show="uploading">
            <progress max="100" x-bind:value="progress"></progress>
        </div>
    </div>

    <!-- ... -->
</form>
```

## Cancelling an upload

If an upload is taking a long time, a user may want to cancel it. You can provide this functionality with Livewire's `$cancelUpload()` function in JavaScript.

Here's an example of creating a "Cancel Upload" button in a Livewire component using `wire:click` to handle the click event:

```blade
<form wire:submit="save">
    <!-- File Input -->
    <input type="file" wire:model="photo">

    <!-- Cancel upload button -->
    <button type="button" wire:click="$cancelUpload('photo')">Cancel Upload</button>

    <!-- ... -->
</form>
```

When "Cancel upload" is pressed, the file upload will request will be aborted and the file input will be cleared. The user can now attempt another upload with a different file.

Alternatively, you can call `cancelUpload(...)` from Alpine like so:

```blade
<button type="button" x-on:click="$wire.cancelUpload('photo')">Cancel Upload</button>
```

## JavaScript upload API

Integrating with third-party file-uploading libraries often requires more control than a simple `<input type="file" wire:model="...">` element.

For these scenarios, Livewire exposes dedicated JavaScript functions.

These functions exist on a JavaScript component object, which can be accessed using Livewire's convenient `$wire` object from within your Livewire component's template:

```blade
<script>
    let file = $wire.el.querySelector('input[type="file"]').files[0]

    // Upload a file...
    $wire.upload('photo', file, (uploadedFilename) => {
        // Success callback...
    }, () => {
        // Error callback...
    }, (event) => {
        // Progress callback...
        // event.detail.progress contains a number between 1 and 100 as the upload progresses
    }, () => {
        // Cancelled callback...
    })

    // Upload multiple files...
    $wire.uploadMultiple('photos', [file], successCallback, errorCallback, progressCallback, cancelledCallback)

    // Remove single file from multiple uploaded files...
    $wire.removeUpload('photos', uploadedFilename, successCallback)

    // Cancel an upload...
    $wire.cancelUpload('photos')
</script>
```

## Configuration

Because Livewire stores all file uploads temporarily before the developer can validate or store them, it assumes some default handling behavior for all file uploads.

### Global validation

By default, Livewire will validate all temporary file uploads with the following rules: `file|max:12288` (Must be a file less than 12MB).

If you wish to customize these rules, you can do so inside your application's `config/livewire.php` file:

```php
'temporary_file_upload' => [
    // ...
    'rules' => 'file|mimes:png,jpg,pdf|max:102400', // (100MB max, and only accept PNGs, JPEGs, and PDFs)
],
```

### Global middleware

The temporary file upload endpoint is assigned a throttling middleware by default. You can customize exactly what middleware this endpoint uses via the following configuration option:

```php
'temporary_file_upload' => [
    // ...
    'middleware' => 'throttle:5,1', // Only allow 5 uploads per user per minute
],
```

### Temporary upload directory

Temporary files are uploaded to the specified disk's `livewire-tmp/` directory. You can customize this directory via the following configuration option:

```php
'temporary_file_upload' => [
    // ...
    'directory' => 'tmp',
],
```

## Chunked uploads

For large files, Livewire can split uploads into smaller chunks that are sent sequentially. This avoids hitting PHP's `upload_max_filesize` and `post_max_size` limits (as well as third-party limits like Cloudflare's 100MB request body cap), reduces memory usage, and lets failed chunks retry without restarting the whole upload.

Chunked uploads are **opt-in** and disabled by default. Enable them by setting `chunk.enabled` to `true` in your config:

```php
'temporary_file_upload' => [
    // ...
    'chunk' => [
        'enabled' => true,
    ],
],
```

Once enabled, Livewire automatically chunks any uploaded file larger than `chunk.size` (1 MB by default). Smaller files continue to use the normal single-request upload path. From your component's perspective, nothing changes — you still get a `TemporaryUploadedFile` on your property:

```php
new class extends Component {
    use WithFileUploads;

    #[Validate('file|max:512000')] // 500MB
    public $video;

    public function save()
    {
        $this->video->store(path: 'videos');
    }
};
```

> [!warning] Local disk only
> Chunked uploads currently only work when `temporary_file_upload.disk` is a local filesystem. If you enable chunking while using the S3 disk, Livewire will throw an `S3DoesntSupportChunkedUploads` exception when an upload is attempted — set `chunk.enabled` to `false` or switch to a local disk. Native S3 multipart upload support is planned for a future release.

> [!info] Bump your global validation rule
> Remember that the global `temporary_file_upload.rules` validation still applies to the assembled file. The default `max:12288` (12MB) will reject anything larger. If you're enabling chunked uploads to support large files, bump `max` accordingly.

> [!warning] Don't point the temp upload disk at a publicly served directory
> The `temporary_file_upload.disk` setting (and the `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK` env var) controls where in-flight chunks and assembled tmp files live. **Never set this to a disk whose root is under your web document root** (the `public` disk in a default Laravel app, or any custom disk that maps to `public_path()`). Doing so makes the assembled tmp files reachable via HTTP, and in some configurations the web server will execute them as scripts. The default `local` disk (root: `storage/app/private`) is safe and is what you should be using.

### Chunked upload configuration

```php
'temporary_file_upload' => [
    // ...
    'chunk' => [
        'enabled' => false,                       // Set to true to enable chunked uploads.
        'size' => 1024 * 1024,                    // Bytes per chunk. Defaults to 1MB — see tuning notes below.
        'retry_delays' => [500, 1000, 3000],      // Backoff between failed chunk retries (ms).
        'max_upload_time' => 60 * 24,             // Max minutes for an entire chunked upload to complete (24h default).
        'middleware' => null,                     // Middleware applied to chunk endpoints. Defaults to throttle:600,1.
        'absolute_max_bytes' => 5 * 1024 * 1024 * 1024, // Hard ceiling on Upload-Length when no `max:` rule is set (defaults to 5GB).
    ],
],
```

#### Tuning `chunk.size`

The default chunk size is 1 MB because that matches the default `client_max_body_size` in nginx, which is what Laravel Forge and Laravel Cloud both ship with out of the box. Leaving the default means chunked uploads work immediately on a fresh deploy — no server tweaks required.

**For better throughput on large files, you can bump the chunk size** — but you need to bump the server's body limit to match.

| Chunk size | Requests per 1 GB file | Server tweak required? |
|---|---|---|
| 1 MB (default) | ~1,024 | none |
| 5 MB | ~205 | nginx `client_max_body_size 6M` |
| 10 MB | ~103 | nginx `client_max_body_size 11M` |

**On Laravel Forge**, the easiest way is the "Max File Upload Size" field in the site's "Meta" tab — it sets nginx's `client_max_body_size` and PHP's `post_max_size`/`upload_max_filesize` together. Set it to a value slightly larger than your chunk size (e.g. `6` for 5MB chunks).

**On Laravel Cloud**, edit your site's nginx configuration via the dashboard to set `client_max_body_size` higher than your chunk size, and adjust `post_max_size` in your PHP config.

**On a custom nginx**, add `client_max_body_size 6M;` to the relevant `server { ... }` block and reload nginx.

Don't forget to set `chunk.size` in your Livewire config to match after bumping the server limit.

`chunk.max_upload_time` controls how long the signed chunk URLs stay valid. If an upload takes longer than this, the URLs expire and in-flight chunks start failing. The default of 24 hours comfortably handles multi-GB uploads on slow connections (e.g. 10GB at 5Mbps takes about 4.5 hours), and aligns with Livewire's existing 24-hour temporary-file cleanup window.

`chunk.absolute_max_bytes` only matters when there is no `max:` rule in `temporary_file_upload.rules`. In that case it acts as a hard ceiling so a missing rule can't become an unbounded upload claim. If you set a `max:` rule (recommended), Livewire enforces that instead and `chunk.absolute_max_bytes` is unused.

`chunk.middleware` defaults to `throttle:600,1` — looser than the legacy `throttle:60,1` for single-request uploads, because chunking inherently makes many small requests per file. **If your upload component is reachable by anonymous or unauthenticated visitors, tighten this** to limit how quickly an attacker can fill the disk with abandoned chunks:

```php
'chunk' => [
    // ...
    'middleware' => 'throttle:60,1',
],
```

The throttle counter is per-IP per-minute, so an attacker uploading 5MB chunks at the default `600/1` could write up to ~3GB/minute of trash data per IP before being blocked. The cleanup logic only removes abandoned chunks after 24 hours, so the disk pressure persists. For public-facing apps, tightening to `60/1` (~300MB/min/IP) is much safer.

### How it works

When the user selects a file larger than `chunk.size`, Livewire's JavaScript:

1. POSTs to a chunk-init endpoint with the total file size, getting back a transfer ID and signed URLs for the chunk endpoints.
2. Slices the file into `chunk.size` pieces and PATCHes each one to the chunk endpoint with an `Upload-Offset` header.
3. On the final chunk, the server assembles the file, validates it against the configured rules, and returns a signed path.
4. Livewire's JavaScript hands that signed path to the same `_finishUpload` flow used by non-chunked uploads, and your component property is set to a `TemporaryUploadedFile`.

If a chunk fails, the client retries with backoff, querying the server for the authoritative offset before resuming. Validation errors at finalize time (e.g. mime mismatch) bubble up the same way they would for a non-chunked upload, so `@error('photo')` works as expected.

> [!info] PATCH method support
> Chunked uploads use HTTP PATCH requests. If your infrastructure (WAF, reverse proxy, etc.) blocks PATCH by default, either configure it to allow PATCH on the chunk endpoint, or leave `chunk.enabled` at `false`.

## See also

- **[Forms](/docs/4.x/forms)** — Handle file uploads in forms
- **[Validation](/docs/4.x/validation)** — Validate uploaded files
- **[Loading States](/docs/4.x/loading-states)** — Show upload progress indicators
- **[wire:model](/docs/4.x/wire-model)** — Bind file inputs to properties
