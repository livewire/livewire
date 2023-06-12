Livewire offers powerful support for uploading files within your components.

First, add the `WithFileUploads` trait to your component. Now you can use `wire:model` on file inputs as if they were any other input type, and Livewire will take care of the rest.

Here's an example of a simple component that handles uploading a photo:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Rule;

class UploadPhoto extends Component
{
    use WithFileUploads;

    #[Rule('image|max:1024')] // 1MB Max
    public $photo;

    public function save()
    {
        $this->photo->store('photos');
    }
}
```

```html
<form wire:submit="save">
    <input type="file" wire:model="photo">

    @error('photo') <span class="error">{{ $message }}</span> @enderror

    <button type="submit">Save photo</button>
</form>
```

From the developer's perspective, handling file inputs is no different than handling any other input type: Add `wire:model` to the `<input>` tag, and everything else is taken care of for you.

However, more is happening under the hood to make file uploads work in Livewire. Here's a glimpse at what goes on when a user selects a file to upload:

1. When a new file is selected, Livewire's JavaScript makes an initial request to the component on the server to get a temporary "signed" upload URL.
2. Once the URL is received, JavaScript does the actual "upload" to the signed URL, storing the upload in a temporary directory designated by Livewire and returning the new temporary file's unique hash ID.
3. Once the file is uploaded and the unique hash ID is generated, Livewire's JavaScript makes a final request to the component on the server, telling it to "set" the desired public property to the new temporary file.
4. Now, the public property (in this case, `$photo`) is set to the temporary file upload and is ready to be stored or validated at any point.

## Storing uploaded files

The previous example demonstrates the most basic storage scenario: Moving the temporarily uploaded file to the "photos" directory on the app's default filesystem disk.

However, you may want to customize the file name of the stored file or even specify a specific storage "disk" to keep the file on (maybe in an S3 bucket, for example).

Livewire honors the same API's Laravel uses for storing uploaded files, so feel free to browse [Laravel's file upload documentation](https://laravel.com/docs/filesystem#file-uploads). However, here are a few common storage scenarios for you:

```php
public function save()
{
    // Store the uploaded file in the "photos" directory of the default filesystem disk.
    $this->photo->store('photos');

    // Store in the "photos" directory in a configured "s3" bucket.
    $this->photo->store('photos', 's3');

    // Store in the "photos" directory with the filename "avatar.png".
    $this->photo->storeAs('photos', 'avatar');

    // Store in the "photos" directory in a configured "s3" bucket with the filename "avatar.png".
    $this->photo->storeAs('photos', 'avatar', 's3');

    // Store in the "photos" directory, with "public" visibility in a configured "s3" bucket.
    $this->photo->storePublicly('photos', 's3');

    // Store in the "photos" directory, with the name "avatar.png", with "public" visibility in a configured "s3" bucket.
    $this->photo->storePubliclyAs('photos', 'avatar', 's3');
}
```

The methods above should provide enough flexibility for storing the uploaded files exactly how you want to.

## Handling multiple files
Livewire handles multiple file uploads automatically by detecting the `multiple` attribute on the `<input>` tag.

For example, here's a component with an array property called `$photos`. By adding `multiple` to the form's file input, Livewire will automatically append new files to this array.

```php
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Rule;

class UploadPhotos extends Component
{
    use WithFileUploads;

    #[Rule(['photos.*' => 'image|max:1024'])]
    public $photos = [];

    public function save()
    {
        foreach ($this->photos as $photo) {
            $photo->store('photos');
        }
    }
}
```

```html
<form wire:submit="save">
    <input type="file" wire:model="photos" multiple>

    @error('photos.*') <span class="error">{{ $message }}</span> @enderror

    <button type="submit">Save photo</button>
</form>
```

## File validation

Like you've seen previously, validating file uploads with Livewire is the same as handling file uploads from a standard Laravel controller.

> [!warning] Make sure S3 is properly configured
> Many of the validation rules relating to files require access to the file. If you are [uploading directly to S3](#upload-to-s3) these validation rules will fail if the S3 file object is not publicly accessible.

For more information, visit [Laravel's file validation documentation](https://laravel.com/docs/validation#available-validation-rules).

## Temporary preview URLs

After a user chooses a file, you should show them a preview of that file before they submit the form and store the file.

Livewire makes this trivial with the `->temporaryUrl()` method on uploaded files.

> [!info] Temporary URLs are restricted to images
> For security reasons, temporary upload URLs are only supported on files with image mime-types.

Here's an example of a file upload with an image preview:

```php
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Rule;

class UploadPhoto extends Component
{
    use WithFileUploads;

    #[Rule('image|max:1024')]
    public $photo;

    // ...
}
```

```html
<form wire:submit="save">
    @if ($photo) <!-- [tl! highlight:2] -->
        <img src="{{ $photo->temporaryUrl() }}">
    @endif

    <input type="file" wire:model="photo">

    @error('photo') <span class="error">{{ $message }}</span> @enderror

    <button type="submit">Save photo</button>
</form>
```

As previously mentioned, Livewire stores temporary files in a non-public directory; therefore, there's no simple way to expose a temporary, public URL to your users for image previewing.

Livewire takes care of this complexity by providing a temporary, signed URL that pretends to be the uploaded image so your page can show something to your users.

This URL is protected against showing files in directories above the temporary directory. Because it's signed temporarily, users can't abuse this URL to preview other files on your system.

> [!tip] S3 temporary signed URLs
> If you've configured Livewire to use S3 for temporary file storage, calling `->temporaryUrl()` will generate a temporary, signed url from S3 directly so that you don't hit your Laravel app server for this preview at all.

## Testing file uploads

You can use Laravel's existing file upload testing helpers to test file uploads.

Here's a complete example of testing the `UploadPhoto` component with Livewire.

```php
<?php

namespace Tests\Feature\Livewire;

use Illuminate\Support\Facades\Storage;
use App\Http\Livewire\UploadPhoto;
use Livewire\Livewire;
use Tests\TestCase;

class UploadPhotoTest extends TestCase
{
    /** @test */
    public function can_upload_photo()
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

Here's a snippet of the `UploadPhoto` component required to make the previous test pass:

```php
use Livewire\Component;
use Livewire\WithFileUploads;

class UploadPhoto extends Component
{
    use WithFileUploads;

    public $photo;

    public function upload($name)
    {
        $this->photo->storeAs('/', $name, disk: 'avatars');
    }

    // ...
}
```

For more specifics on testing file uploads, reference [Laravel's file upload testing documentation](https://laravel.com/docs/http-tests#testing-file-uploads).

## Uploading directly to Amazon S3

As previously mentioned, Livewire stores all file uploads in a temporary directory until the developer permanently stores the file.

By default, Livewire uses the default filesystem disk configuration (usually `local`) and stores the files under a folder called `livewire-tmp/`.

This means that file uploads are always hitting your server, even if you choose to store them in an S3 bucket later.

If you wish to bypass this system and instead store Livewire's temporary uploads in an S3 bucket, you can configure that behavior like so:

In your `config/livewire.php` file, set `livewire.temporary_file_upload.disk` to `s3` (or another custom disk that uses the `s3` driver):

```php
return [
    // ...
    'temporary_file_upload' => [
        'disk' => 's3',
        // ...
    ],
];
```

Now, when a user uploads a file, the file will never actually hit your server. It will be uploaded directly to your S3 bucket under the sub-directory: `livewire-tmp/`.

> [!info] You must publish Livewire's config file if you haven't already
> Before customizing the file upload disk, you must first publish Livewire's configuration file to your application's `/config` directory by running the following command:
> ```shell
> php artisan livewire:publish --config
> ```

### Configuring automatic file cleanup

This temporary directory will fill up with files quickly; therefore, it's essential to configure S3 to clean up files older than 24 hours.

To configure this behavior, run the following artisan command from the environment with the S3 bucket configured.

```shell
php artisan livewire:configure-s3-upload-cleanup
```

Any temporary files older than 24 hours will be cleaned up by S3 automatically.

> [!info]
> If you are not using S3, Livewire will handle the file cleanup automatically. No need to run this command.

## Loading indicators

Although `wire:model` for file uploads works differently than other `wire:model` input types under the hood, the interface for showing loading indicators remains the same.

You can display a loading indicator scoped to the file upload like so:

```html
<input type="file" wire:model="photo">

<div wire:loading wire:target="photo">Uploading...</div>
```

Now, while the file is uploading, the "Uploading..." message will be shown and then hidden when the upload is finished.

For more information, reference [loading states in Livewire](/docs/loading).

## Progress indicators

Every file upload in Livewire dispatches JavaScript events on the `<input>` element for custom JavaScript to listen to.

Here are the dispatched events:

Event | Description
--- | ---
`livewire-upload-start` | Dispatched when the upload starts
`livewire-upload-finish` | Dispatches if the upload is successfully finished
`livewire-upload-error` | Dispatches if the upload fails in some way
`livewire-upload-progress` | Dispatches an event containing the upload progress percentage as the upload progresses

Here is an example of wrapping a Livewire file upload in an AlpineJS component to display a progress bar:

```html
<form wire:submit="save">
    <div
        x-data="{ uploading: false, progress: 0 }"
        x-on:livewire-upload-start="uploading = true"
        x-on:livewire-upload-finish="uploading = false"
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

## JavaScript upload API

Integrating with 3rd-party file-uploading libraries often requires finer-tuned control than a simple `<input type="file">` tag.

For these cases, Livewire exposes dedicated JavaScript functions.

The functions exist on the JavaScript component object, which can be accessed using the convenience Blade directive from within your Livewire component's template: `@this`.

```html
<script>
    let file = document.querySelector('input[type="file"]').files[0]

    // Upload a file:
    @this.upload('photo', file, (uploadedFilename) => {
        // Success callback.
    }, () => {
        // Error callback.
    }, (event) => {
        // Progress callback.
        // event.detail.progress contains a number between 1 and 100 as the upload progresses.
    })

    // Upload multiple files:
    @this.uploadMultiple('photos', [file], successCallback, errorCallback, progressCallback)

    // Remove single file from multiple uploaded files
    @this.removeUpload('photos', uploadedFilename, successCallback)
</script>
```

## Configuration

Because Livewire stores all file uploads temporarily before the developer can validate or store them, it assumes some default handling of all file uploads.

### Global validation

By default, Livewire will validate ALL temporary file uploads with the following rules: `file|max:12288` (Must be a file less than 12MB).

If you wish to customize this, you can configure exactly what validate rules should run on all temporary file uploads inside `config/livewire.php`:

```php
'temporary_file_upload' => [
    // ...
    'rules' => 'file|mimes:png,jpg,pdf|max:102400', // (100MB max, and only pngs, jpegs, and pdfs.)
],
```

### Global middleware

The temporary file upload endpoint has throttling middleware by default. You can customize exactly what middleware this endpoint uses with the following configuration variable:

```php
'temporary_file_upload' => [
    // ...
    'middleware' => 'throttle:5,1', // Only allow 5 uploads per user per minute.
],
```

### Temporary upload directory

Temporary files are uploaded to the specified disk's `livewire-tmp/` directory. You can customize this with the following configuration key:

```php
'temporary_file_upload' => [
    // ...
    'directory' => 'tmp',
],
```
