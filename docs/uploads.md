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
        $this->validate();

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

If you're storing files in S3 — or an S3-compatible service like Cloudflare R2 or DigitalOcean Spaces — the [Using S3](#using-s3) section below walks through the entire setup from scratch.

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
        $this->validate();

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

If a file input bound to an array property is missing the `multiple` attribute, Livewire still treats each upload as a multiple upload and appends it to the array — the property's shape wins. The `multiple` attribute's job is telling the browser to allow selecting more than one file at a time.

## File validation

Like we've discussed, validating file uploads with Livewire is the same as handling file uploads from a standard Laravel controller.

As a courtesy, Livewire fails fast when it can: size rules (`max`, `min`, `size`, `between`) and type rules (`image`, `mimes`, `mimetypes`, `extensions`) declared on a property are checked against the selected file's metadata _before_ the upload starts, so choosing a 200MB video against an `image|max:1024` rule shows a validation error instantly instead of after a long upload. This preflight only rejects files whose declared name or type provably violate the rule — the authoritative validation always runs server-side against the real file after upload.

> [!warning] Ensure S3 is properly configured
> Many of the validation rules relating to files require access to the file. When [storing temporary uploads directly in S3](#using-s3), these validation rules will fail if the S3 file object is not publicly accessible.

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

## Rich upload objects in JavaScript

File properties aren't just useful on the server — on the frontend, `$wire.photo` is a rich upload object rather than an opaque string. This unlocks instant, fully client-side previews and upload state without waiting on a server round trip:

```blade
<form wire:submit="save">
    <div x-show="$wire.photo"> <!-- [tl! highlight:5] -->
        <img x-bind:src="$wire.photo?.previewUrl">

        <progress max="100" x-bind:value="$wire.photo?.progress" x-show="$wire.photo?.isUploading"></progress>

        <button type="button" x-on:click="$wire.photo.remove()">Remove</button>
    </div>

    <input type="file" wire:model="photo">

    <button type="submit">Save photo</button>
</form>
```

The moment a user selects a file, the property optimistically holds a pending upload object — before any bytes reach the server. Its `previewUrl` is a local blob URL created from the file already in the browser, so previews appear instantly and never re-download the file, and its `progress` state updates reactively as the upload proceeds.

The upload object exposes:

Property | Description
--- | ---
`name` | The original filename from the user's machine
`extension` | The file extension, derived from `name`
`size` | The file's size in bytes
`sizeForHumans` | The size as a display string ("1.5 KB", "2 MB") — also available in PHP as `$file->sizeForHumans()`
`kind` | The coarse category of the file: `'image'`, `'audio'`, `'video'`, or `'file'` — from the browser's MIME type while uploading, the filename's extension after
`isImage` / `isAudio` / `isVideo` | Shorthand predicates over `kind`, e.g. `wire:show="photo.isImage"`
`filename` | The hashed temporary filename on the server (`null` while uploading)
`isUploading` | `true` while the upload is still in flight
`progress` | Upload progress from 0 to 100 (settles at 100)
`isPreviewable` | Whether a preview URL is available
`previewUrl` | The best available preview URL: a local blob URL when possible, otherwise the signed server URL
`temporaryUrl()` | The signed server-side preview URL (the JavaScript equivalent of PHP's `->temporaryUrl()`)
`remove()` | Remove this upload from its property, instantly — the property updates optimistically and the server confirms in the background (in-flight uploads are cancelled instead)

Properties holding multiple uploads hydrate into arrays of rich objects, so each file can be listed and removed individually:

```blade
<div>
    <input type="file" wire:model="photos" multiple>

    <template x-for="photo in $wire.photos" :key="photo.name">
        <div>
            <img x-bind:src="photo.previewUrl">

            <span x-text="photo.name"></span>

            <button type="button" x-on:click="photo.remove()">Remove</button>
        </div>
    </template>
</div>
```

> [!info] Blob URLs and Content Security Policies
> Local previews use `blob:` URLs. If your app enforces a Content Security Policy, make sure `img-src` includes `blob:`.

Rich upload objects are powered by [JavaScript synthesizers](/docs/synthesizers#javascript-synthesizers). When sent back to the server or stringified via `JSON.stringify()`, they degrade to their raw wire value automatically.

## Uploading beyond file inputs

Modern interfaces accept files from more than a file input: users paste screenshots into a message box, drag files onto the page, and click "Attach" buttons that open the system's file picker.

Livewire supports all of these through a single action: `$upload`. Use it inside any event listener — most commonly `wire:paste`, [`wire:drop`](/docs/wire-drop), and `wire:click`:

```blade
<textarea wire:paste="$upload('photos')"></textarea>

<div wire:drop.file="$upload('photos')">Drop files here</div>

<button type="button" wire:click="$upload('photos')">Attach files</button>
```

`$upload` follows one rule: **it takes files from the argument, else from the event, else from the user.**

* When the triggering event carries files — pasted screenshots, dropped files — those files upload into the property
* When the event *can* carry files but doesn't — a plain text paste — nothing happens, and the browser's default behavior proceeds untouched
* When the event *can't* carry files — a click — the browser's file picker opens and the user's selection uploads instead

Because `$upload` filters for itself, the listeners stay ordinary events you can use however you like. The `.file` modifier on `wire:drop` above is a listener-level filter — like `wire:keydown.enter` — that additionally scopes the *dropzone* to drags carrying files, so dragging selected text over it never engages it. More on that in the [`wire:drop` documentation](/docs/wire-drop).

`.file` works on any event listener carrying files — `wire:paste.file="handlePastedFiles"` only fires for pastes whose clipboard contains files, which is handy for custom handlers that would otherwise need their own guard.

Uploads started this way are ordinary Livewire uploads: they flow through the same temporary-upload pipeline as `wire:model` file inputs, respect your validation rules, and hydrate into [rich upload objects](#rich-upload-objects-in-javascript) for instant previews and progress.

Here's a complete chat-style message box that accepts attachments from all three gestures:

```php
<?php // resources/views/components/⚡message-box.blade.php

use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use Livewire\Component;

new class extends Component {
    use WithFileUploads;

    public $message = '';

    #[Validate(['attachments.*' => 'file|max:10240'])]
    public $attachments = [];

    public function send()
    {
        // ...
    }
};
```

```blade
<div class="relative" wire:drop.file.window="$upload('attachments')">
    {{-- A full-screen overlay, shown while files are dragged over the page... --}}
    <div class="hidden in-data-dragging:grid fixed inset-0 place-items-center bg-black/50 text-white">
        Drop files to attach
    </div>

    <form wire:submit="send">
        {{-- Pending and finished attachments, powered by rich upload objects... --}}
        <template wire:for="file in attachments" wire:for:key="file.name">
            <div>
                <img wire:show="file.isPreviewable" wire:bind:src="file.previewUrl">

                <span wire:text="file.name"></span>

                <progress wire:show="file.isUploading" wire:bind:value="file.progress" max="100"></progress>

                <button type="button" wire:click="file.remove()">&times;</button>
            </div>
        </template>

        <textarea wire:model="message" wire:paste="$upload('attachments')"></textarea>

        <button type="button" wire:click="$upload('attachments', { accept: 'image/*,.pdf' })">
            Add photos & files
        </button>

        <button type="submit" wire:bind:disabled="attachments.some(file => file.isUploading)">
            Send
        </button>
    </form>
</div>
```

A few things worth noticing:

* `wire:paste` sits on elements — paste events bubble, so placing it on the `<form>` would cover every input inside it
* `wire:drop`'s `.file` modifier scopes the dropzone to drags carrying files — dragging selected text across the page never flashes the overlay
* `.window` accepts drops anywhere on the page, and the `data-dragging` attribute it applies powers the overlay with plain CSS — no JavaScript required
* The "Add photos & files" button is a real `<button>`, so keyboards and screen readers work for free — no hidden `<input type="file">` hacks

### Options

`$upload` accepts an options object as its final argument:

```blade
<button type="button" wire:click="$upload('attachments', { accept: 'image/*', multiple: true })">
    Add photos
</button>
```

Option | Description
--- | ---
`accept` | Filter incoming files like a native file input's `accept` attribute — comma-separated mime types (with wildcards) or extensions. Applies to pasted and dropped files as well as the file picker
`multiple` | Whether to accept multiple files. Defaults to `true` when the property currently holds an array, `false` otherwise
`append` | Whether multiple uploads append to the property's existing files (`true`, the default) or replace them

> [!warning] Client-side filtering is a convenience, not a guard
> Like a native file input's `accept` attribute, `$upload`'s filtering only improves the experience for honest users. Always enforce file rules with [server-side validation](#file-validation).

### Explicit files and events

`$upload` also accepts files — or an event to pull files from — as its second argument, for when you're wiring things up manually:

```blade
<textarea x-on:paste="$wire.$upload('photos', $event)"></textarea>
```

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

## Using S3

Everything in this section applies equally to Amazon S3 and S3-compatible services like Cloudflare R2 and DigitalOcean Spaces.

Before configuring anything, it helps to know that every Livewire file upload makes two stops:

1. **Temporary storage** — the moment a user selects a file, Livewire uploads it to a temporary directory (`livewire-tmp/`) so it can be validated and previewed. This part belongs to Livewire.
2. **Permanent storage** — nothing is kept until your code calls `->store()`. Where that call puts the file is entirely up to you.

These two stops are configured independently, and "using S3" can mean either or both:

* If you just want your uploaded files to *end up* in S3, you only need steps 1 and 2.
* If you also want the uploads themselves to bypass your server and go straight to your bucket, continue to step 3.

### Step 1: Configure an S3 disk

Laravel ships with an `s3` disk in `config/filesystems.php` that's wired to environment variables, so you rarely need to touch the config file itself.

First, install the Flysystem S3 adapter — it isn't included with Laravel by default:

```shell
composer require league/flysystem-aws-s3-v3 "^3.0"
```

Then fill in the credentials in your `.env` file.

For Amazon S3:

```env
AWS_ACCESS_KEY_ID=your-key-id
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name
```

For an S3-compatible service, also set the service's `endpoint`. Cloudflare R2, for example:

```env
AWS_ACCESS_KEY_ID=your-r2-access-key-id
AWS_SECRET_ACCESS_KEY=your-r2-secret-key
AWS_DEFAULT_REGION=auto
AWS_BUCKET=your-bucket-name
AWS_ENDPOINT=https://<account-id>.r2.cloudflarestorage.com
```

Or DigitalOcean Spaces:

```env
AWS_ACCESS_KEY_ID=your-spaces-key
AWS_SECRET_ACCESS_KEY=your-spaces-secret
AWS_DEFAULT_REGION=nyc3
AWS_BUCKET=your-space-name
AWS_ENDPOINT=https://nyc3.digitaloceanspaces.com
```

> [!tip] Verify the disk before going further
> Run `php artisan tinker` and try writing a file:
>
> ```php
> Storage::disk('s3')->put('connectivity-test.txt', 'hello');
> ```
>
> If this returns `true`, your credentials work and everything below will too. Debugging a typo'd secret here takes seconds — debugging it through a failing file upload takes much longer.

### Step 2: Store uploaded files in S3

Where an upload ends up permanently is decided by your `->store()` call — not by any Livewire configuration. Pass the disk name to store the file in S3:

```php
public function save()
{
    $this->validate();

    $this->photo->store(path: 'photos', options: 's3');
}
```

Uploaded files now land in the `photos/` directory of your bucket. If that's all you were after, you're done.

At this point, temporary uploads — the hop between the user selecting a file and your `save()` method running — are still stored on your application server. That's perfectly fine for most applications. If you'd like uploads to skip your server entirely, continue to step 3.

### Step 3 (optional): Send temporary uploads directly to S3

By default, Livewire keeps temporary uploads on a local disk, which means every upload passes through your application server — even if it's permanently stored in S3 afterwards.

To bypass your server, point Livewire's temporary upload disk at S3 in your `.env` file:

```env
LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=s3
```

Now, when a user selects a file, the browser uploads it straight to the `livewire-tmp/` directory of your bucket using a pre-signed URL — the file never touches your server. Image previews via `->temporaryUrl()` are served directly from S3 as well, and large files automatically use native S3 multipart uploads (see [chunked and resumable uploads](#chunked-and-resumable-uploads)).

Because the browser now talks to your bucket directly, your bucket must allow cross-origin `PUT` requests from your application's domain. Add a CORS policy to the bucket:

```json
[
    {
        "AllowedOrigins": ["https://your-app.com"],
        "AllowedMethods": ["PUT", "GET"],
        "AllowedHeaders": ["*"],
        "MaxAgeSeconds": 3000
    }
]
```

On Amazon S3 this lives under the bucket's **Permissions → Cross-origin resource sharing (CORS)**; on Cloudflare R2 it's under **Settings → CORS policy**. Without it, uploads will fail in the browser with CORS errors even though everything on the server is configured correctly.

> [!tip]
> For full control over the temporary upload behavior — disk, directory, validation rules, and more — publish Livewire's config file with `php artisan livewire:config` and edit the `temporary_file_upload` section.

### Configuring automatic file cleanup

When temporary uploads are stored in S3, Livewire can't clean them up for you like it does locally, so your `livewire-tmp/` directory will fill up with files quickly. Instead, S3 itself should be configured to delete files older than 24 hours.

To set that up, run the following Artisan command from the environment that is using the S3 bucket for temporary uploads:

```shell
php artisan livewire:configure-s3-upload-cleanup
```

Now, any temporary files older than 24 hours will be cleaned up by S3 automatically.

> [!info]
> If you are not using S3 for temporary uploads, Livewire handles file cleanup automatically and there is no need to run the command above.

## Chunked and resumable uploads

Large files are uploaded in chunks automatically — no configuration or markup changes required.

When a selected file is bigger than the configured chunk threshold, Livewire slices it in the browser and uploads the pieces one at a time, then reassembles and validates the file on the server. On S3 disks, Livewire uses native [S3 multipart uploads](https://docs.aws.amazon.com/AmazonS3/latest/userguide/mpuoverview.html) instead, so large files still bypass your application server entirely.

Chunking solves two long-standing upload problems:

* **PHP's upload limits no longer apply.** Because each chunk is smaller than a stock `php.ini`'s `upload_max_filesize`, users can upload files far bigger than your PHP configuration would normally allow. Your Livewire validation rules (like `max:`) remain the authority on how big is too big.
* **Interrupted uploads are resumable.** If an upload is cancelled, interrupted, or the page is reloaded mid-flight, re-selecting the same file resumes from where it left off — Livewire fingerprints the file and only uploads the chunks the server doesn't already have.

Chunked uploads are locked down the same way regular temporary uploads are: every chunk request carries a cryptographically signed reference encoding the upload's identity, chunk count, and chunk size — none of which a client can tamper with — and fingerprints are scoped to the user's session, so one user can never touch another user's in-flight upload.

You can tune this behavior in the `temporary_file_upload` section of Livewire's config file:

```php
'temporary_file_upload' => [
    // ...
    'chunking' => true,        // Set to false to always upload files whole...
    'chunk_size' => null,      // Bytes per chunk | Default: 1MB (5MB on S3 — the multipart minimum)
    'chunk_threshold' => null, // Files larger than this are chunked | Default: chunk_size
],
```

> [!warning] Custom upload middleware and throttling
> A single chunked file is uploaded as many small requests, so the chunk endpoint uses a higher default throttle (`throttle:600,1`). If you set a custom `middleware` on `temporary_file_upload`, it governs the chunk endpoint too — a tight throttle like `throttle:60,1` will fail large uploads. Throttle generously, or disable `chunking` if you don't need it.

> [!info] Abandoned S3 multipart uploads
> Abandoned multipart uploads on S3 hold invisible storage until they are aborted. Add an [AbortIncompleteMultipartUpload lifecycle rule](https://docs.aws.amazon.com/AmazonS3/latest/userguide/mpu-abort-incomplete-mpu-lifecycle-config.html) to your bucket (one day is a good default) so they are cleaned up automatically. On non-S3 disks, Livewire cleans up stale chunks alongside other temporary uploads.

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
    // Open the file picker and upload the user's selection...
    await $wire.$upload('photos')

    // Upload specific File objects (or a FileList, or an array of Files)...
    await $wire.$upload('photos', files)

    // Pull files out of a paste, drop, or change event...
    await $wire.$upload('photos', event)

    // Pass picker and filtering options...
    await $wire.$upload('photos', { accept: 'image/*', multiple: true })

    // Remove single file from multiple uploaded files...
    $wire.$removeUpload('photos', uploadedFilename)

    // Cancel an upload...
    $wire.$cancelUpload('photos')
</script>
```

`$wire.$upload()` returns a promise that resolves with the [rich upload object](#rich-upload-objects-in-javascript) now living on the property — or an array of them when uploading multiple files (just the files from this upload, not the property's previously uploaded ones). If the picker is dismissed or the upload is cancelled it resolves with `null`, and if the upload fails it rejects:

```blade
<script>
    let photo = await $wire.$upload('photo')

    photo.previewUrl // Instant local preview...
</script>
```

The legacy callback signature from earlier versions of Livewire continues to work:

```blade
<script>
    $wire.$upload('photo', file, (uploadedFilename) => {
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
    $wire.$uploadMultiple('photos', [file], successCallback, errorCallback, progressCallback, cancelledCallback)
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

## See also

- **[Forms](/docs/4.x/forms)** — Handle file uploads in forms
- **[Validation](/docs/4.x/validation)** — Validate uploaded files
- **[Loading States](/docs/4.x/loading-states)** — Show upload progress indicators
- **[wire:model](/docs/4.x/wire-model)** — Bind file inputs to properties
