<?php

namespace Tests;

use LogicException;
use RuntimeException;
use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Http\UploadedFile;
use Livewire\FileUploadConfiguration;
use Illuminate\Support\Facades\Storage;
use Facades\Livewire\GenerateSignedUploadUrl;
use Livewire\Exceptions\MissingFileUploadsTraitException;
use Livewire\Exceptions\S3DoesntSupportMultipleFileUploads;

class FileUploadsTest extends TestCase
{
    /** @test */
    public function component_must_have_file_uploads_trait_to_accept_file_uploads()
    {
        $this->expectException(MissingFileUploadsTraitException::class);

        Livewire::test(NonFileUploadComponent::class)
            ->set('photo', UploadedFile::fake()->image('avatar.jpg'));
    }

    /** @test */
    public function s3_driver_only_supports_single_file_uploads()
    {
        config()->set('livewire.temporary_file_upload.disk', 's3');

        $this->expectException(S3DoesntSupportMultipleFileUploads::class);

        Livewire::test(FileUploadComponent::class)
            ->set('photos', [UploadedFile::fake()->image('avatar.jpg')]);
    }

    /** @test */
    public function can_set_a_file_as_a_property_and_store_it()
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.jpg');

        Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->call('upload', 'uploaded-avatar.png');

        Storage::disk('avatars')->assertExists('uploaded-avatar.png');
    }

    /** @test */
    public function can_remove_a_file_property()
    {
        $file = UploadedFile::fake()->image('avatar.jpg');

        $component = Livewire::test(FileUploadComponent::class)
            ->set('photo', $file);

        $tmpFilename = $component->viewData('photo')->getFilename();

        $component->call('removeUpload', 'photo', $tmpFilename)
            ->assertEmitted('upload:removed', 'photo', $tmpFilename)
            ->assertSet('photo', null);
    }

    /** @test */
    public function can_remove_a_file_from_an_array_of_files_property()
    {
        $file1 = UploadedFile::fake()->image('avatar1.jpg');
        $file2 = UploadedFile::fake()->image('avatar2.jpg');

        $component = Livewire::test(FileUploadComponent::class)
            ->set('photos', [$file1, $file2]);

        $tmpFiles = $component->viewData('photos');

        $component->call('removeUpload', 'photos', $tmpFiles[1]->getFilename())
            ->assertEmitted('upload:removed', 'photos', $tmpFiles[1]->getFilename());

        $tmpFiles = $component->call('$refresh')->viewData('photos');

        $this->assertCount(1, $tmpFiles);
    }

    /** @test */
    public function if_the_file_property_is_an_array_the_uploaded_file_will_append_to_the_array()
    {
        Storage::fake('avatars');

        $file1 = UploadedFile::fake()->image('avatar1.jpg');
        $file2 = UploadedFile::fake()->image('avatar2.jpg');

        Livewire::test(FileUploadComponent::class)
            ->set('photosArray', $file1)
            ->set('photosArray', $file2)
            ->call('uploadPhotosArray', 'uploaded-avatar');

        Storage::disk('avatars')->assertExists('uploaded-avatar1.png');
        Storage::disk('avatars')->assertExists('uploaded-avatar2.png');
    }

    /** @test */
    public function storing_a_file_returns_its_filename()
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $storedFilename = Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->call('uploadAndSetStoredFilename')
            ->get('storedFilename');

        Storage::disk('avatars')->assertExists($storedFilename);
    }

    /** @test */
    public function can_get_a_file_original_name()
    {
        $file = UploadedFile::fake()->image('avatar.jpg');

        $component = Livewire::test(FileUploadComponent::class)
            ->set('photo', $file);

        $tmpFile = $component->viewData('photo');

        $this->assertEquals('avatar.jpg', $tmpFile->getClientOriginalName());
    }

    /** @test */
    public function can_get_multiple_files_original_name()
    {
        $file1 = UploadedFile::fake()->image('avatar1.jpg');
        $file2 = UploadedFile::fake()->image('avatar2.jpg');

        $component = Livewire::test(FileUploadComponent::class)
            ->set('photos', [$file1, $file2]);

        $tmpFiles = $component->viewData('photos');

        $this->assertEquals('avatar1.jpg', $tmpFiles[0]->getClientOriginalName());
        $this->assertEquals('avatar2.jpg', $tmpFiles[1]->getClientOriginalName());
    }

    /** @test */
    public function can_set_a_file_as_a_property_using_the_s3_driver_and_store_it()
    {
        config()->set('livewire.temporary_file_upload.disk', 's3');

        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.jpg');

        Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->call('upload', 'uploaded-avatar.png');

        Storage::disk('avatars')->assertExists('uploaded-avatar.png');
    }

    /** @test */
    public function can_set_multiple_files_as_a_property_and_store_them()
    {
        Storage::fake('avatars');

        $file1 = UploadedFile::fake()->image('avatar1.jpg');
        $file2 = UploadedFile::fake()->image('avatar2.jpg');

        Livewire::test(FileUploadComponent::class)
            ->set('photos', [$file1, $file2])
            ->call('uploadMultiple', 'uploaded-avatar');

        Storage::disk('avatars')->assertExists('uploaded-avatar1.png');
        Storage::disk('avatars')->assertExists('uploaded-avatar2.png');
    }

    /** @test */
    public function a_file_cant_be_larger_than_12mb_or_the_global_livewire_uploader_will_fail()
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.jpg')->size(13000); // 13MB

        Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->assertHasErrors('photo');
    }

    /** @test */
    public function the_global_upload_validation_rules_can_be_configured_and_the_error_messages_show_as_normal_validation_errors_for_the_property()
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.jpg')->size(100); // 100KB

        config()->set('livewire.temporary_file_upload.rules', 'file|max:50');

        Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->assertHasErrors('photo');
    }

    /** @test */
    public function multiple_files_cant_be_larger_than_12mb_or_the_global_livewire_uploader_will_fail()
    {
        Storage::fake('avatars');

        $file1 = UploadedFile::fake()->image('avatar.jpg')->size(13000); // 13MB
        $file2 = UploadedFile::fake()->image('avatar.jpg')->size(13000); // 13MB

        Livewire::test(FileUploadComponent::class)
            ->set('photos', [$file1, $file2])
            ->assertHasErrors('photos.0')
            ->assertHasErrors('photos.1');
    }

    /** @test */
    public function an_uploaded_file_can_be_validated()
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.jpg')->size(200);

        Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->call('validateUpload')
            ->assertHasErrors(['photo' => 'max']);
    }

    /** @test */
    public function multiple_uploaded_files_can_be_validated()
    {
        Storage::fake('avatars');

        $file1 = UploadedFile::fake()->image('avatar.jpg');
        $file2 = UploadedFile::fake()->image('avatar.jpg')->size(200);

        Livewire::test(FileUploadComponent::class)
            ->set('photos', [$file1, $file2])
            ->call('validateMultipleUploads')
            ->assertHasErrors(['photos.1' => 'max']);
    }

    /** @test */
    public function a_file_can_be_valited_in_real_time()
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()->create('avatar.xls', 75);

        Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->assertHasErrors(['photo' => 'image']);
    }

    /** @test */
    public function multiple_files_can_be_valited_in_real_time()
    {
        Storage::fake('avatars');

        $file1 = UploadedFile::fake()->image('avatar.png');
        $file2 = UploadedFile::fake()->create('avatar.xls', 75);

        Livewire::test(FileUploadComponent::class)
            ->set('photos', [$file1, $file2])
            ->assertHasErrors(['photos.1' => 'image']);
    }

    /** @test */
    public function image_dimensions_can_be_validated()
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.png', 100, 200);

        Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->call('validateUploadWithDimensions')
            ->assertHasErrors(['photo' => 'dimensions']);

        Storage::disk('avatars')->assertMissing('uploaded-avatar.png');
    }

    /** @test */
    public function temporary_files_older_than_24_hours_are_cleaned_up_on_every_new_upload()
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.jpg');
        $file2 = UploadedFile::fake()->image('avatar.jpg');
        $file3 = UploadedFile::fake()->image('avatar.jpg');

        Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->call('upload', 'uploaded-avatar.png');

        Livewire::test(FileUploadComponent::class)
            ->set('photo', $file2)
            ->call('upload', 'uploaded-avatar2.png');

        $this->assertCount(2, FileUploadConfiguration::storage()->allFiles());

        // Make temporary files look 2 days old.
        foreach (FileUploadConfiguration::storage()->allFiles() as $fileShortPath) {
            touch(FileUploadConfiguration::storage()->path($fileShortPath), now()->subDays(2)->timestamp);
        }

        Livewire::test(FileUploadComponent::class)
            ->set('photo', $file3)
            ->call('upload', 'uploaded-avatar3.png');

        $this->assertCount(1, FileUploadConfiguration::storage()->allFiles());
    }

    /** @test */
    public function temporary_files_older_than_24_hours_are_not_cleaned_up_on_every_new_upload_when_using_S3()
    {
        config()->set('livewire.temporary_file_upload.disk', 's3');

        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.jpg');
        $file2 = UploadedFile::fake()->image('avatar.jpg');
        $file3 = UploadedFile::fake()->image('avatar.jpg');

        Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->call('upload', 'uploaded-avatar.png');

        Livewire::test(FileUploadComponent::class)
            ->set('photo', $file2)
            ->call('upload', 'uploaded-avatar2.png');

        $this->assertCount(2, FileUploadConfiguration::storage()->allFiles());

        // Make temporary files look 2 days old.
        foreach (FileUploadConfiguration::storage()->allFiles() as $fileShortPath) {
            touch(FileUploadConfiguration::storage()->path($fileShortPath), now()->subDays(2)->timestamp);
        }

        Livewire::test(FileUploadComponent::class)
            ->set('photo', $file3)
            ->call('upload', 'uploaded-avatar3.png');

        $this->assertCount(3, FileUploadConfiguration::storage()->allFiles());
    }

    /** @test */
    public function S3_can_be_configured_so_that_temporary_files_older_than_24_hours_are_cleaned_up_automatically()
    {
        $this->artisan('livewire:configure-s3-upload-cleanup');

        // Can't "really" test this without using a live S3 bucket.
        $this->assertTrue(true);
    }

    /** @test */
    public function the_global_upload_route_middleware_is_configurable()
    {
        config()->set('livewire.temporary_file_upload.middleware', 'Tests\DummyMiddleware');

        $url = GenerateSignedUploadUrl::forLocal();

        try {
            $this->withoutExceptionHandling()->post($url);
        } catch (\Throwable $th) {
            $this->assertEquals('Middleware was hit!', $th->getMessage());
        }
    }

    /** @test */
    public function can_preview_a_temporary_file_with_a_temporary_signed_url()
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $photo = Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->viewData('photo');

        ob_start();
        $this->get($photo->temporaryUrl())->sendContent();
        $rawFileContents = ob_get_clean();

        $this->assertEquals($file->get(), $rawFileContents);
    }

    /** @test */
    public function cant_preview_a_non_image_temporary_file_with_a_temporary_signed_url()
    {
        $this->expectException(RuntimeException::class);

        Storage::fake('avatars');

        $file = UploadedFile::fake()->create('avatar.pdf');

        $photo = Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->viewData('photo');

        $photo->temporaryUrl();
    }

    /** @test */
    public function public_temporary_file_url_must_have_valid_signature()
    {
        $photo = Livewire::test(FileUploadComponent::class)
            ->set('photo', UploadedFile::fake()->image('avatar.jpg'))
            ->viewData('photo');

        $this->get(Str::before($photo->temporaryUrl(), '&signature='))->assertStatus(401);
    }

    /** @test */
    public function file_paths_cant_include_slashes_which_would_allow_them_to_access_other_private_directories()
    {
        $this->expectException(LogicException::class);

        $file = UploadedFile::fake()->image('avatar.jpg');

        $component = Livewire::test(FileUploadComponent::class)
            ->set('photo', $file);

        // Try to hijack the photo property to a path outside the temporary livewire directory root.
        $component->set('photo', 'livewire-file:../dangerous.png')
            ->call('$refresh');
    }

    /** @test */
    public function can_preview_a_temporary_files_with_a_temporary_signed_url_from_s3()
    {
        config()->set('livewire.temporary_file_upload.disk', 's3');

        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $photo = Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->viewData('photo');

        // When testing, rather than trying to hit an s3 server, we just serve
        // the local driver preview URL.
        ob_start();
        $this->get($photo->temporaryUrl())->sendContent();
        $rawFileContents = ob_get_clean();

        $this->assertEquals($file->get(), $rawFileContents);
    }

    /** @test */
    public function removing_first_item_from_array_of_temporary_uploaded_files_serializes_correctly()
    {
        $file1 = UploadedFile::fake()->image('avatar1.jpg');
        $file2 = UploadedFile::fake()->image('avatar2.jpg');
        $file3 = UploadedFile::fake()->image('avatar3.jpg');
        $file4 = UploadedFile::fake()->image('avatar4.jpg');

        $component = Livewire::test(FileUploadComponent::class)
                             ->set('photos', [$file1, $file2, $file3, $file4]);

        $this->assertStringStartsWith('livewire-files:', $component->get('photos'));

        $component->call('removePhoto', 3);
        $this->assertStringStartsWith('livewire-files:', $component->get('photos'));

        $component->call('removePhoto', 0);
        $this->assertStringStartsWith('livewire-files:', $component->get('photos'));
    }
}

class DummyMiddleware
{
    public function handle($request, $next)
    {
        throw new \Exception('Middleware was hit!');
    }
}

class NonFileUploadComponent extends Component
{
    public $photo;

    public function render() { return app('view')->make('null-view'); }
}

class FileUploadComponent extends Component
{
    use WithFileUploads;

    public $photo;
    public $photos;
    public $photosArray = [];
    public $storedFilename;

    public function updatedPhoto()
    {
        $this->validate(['photo' => 'image|max:300']);
    }

    public function updatedPhotos()
    {
        $this->validate(['photos.*' => 'image|max:300']);
    }

    public function upload($name)
    {
        $this->photo->storeAs('/', $name, $disk = 'avatars');
    }

    public function uploadMultiple($baseName)
    {
        $number = 1;

        foreach ($this->photos as $photo) {
            $photo->storeAs('/', $baseName.$number++.'.png', $disk = 'avatars');
        }
    }

    public function uploadPhotosArray($baseName)
    {
        $number = 1;

        foreach ($this->photosArray as $photo) {
            $photo->storeAs('/', $baseName.$number++.'.png', $disk = 'avatars');
        }
    }

    public function uploadAndSetStoredFilename()
    {
        $this->storedFilename = $this->photo->store('/', $disk = 'avatars');
    }

    public function uploadDangerous()
    {
        $this->photo->store();
    }

    public function validateUpload()
    {
        $this->validate(['photo' => 'file|max:100']);
    }

    public function validateMultipleUploads()
    {
        $this->validate(['photos.*' => 'file|max:100']);
    }

    public function validateUploadWithDimensions()
    {
        $this->validate([
            'photo' => Rule::dimensions()->maxWidth(100)->maxHeight(100),
        ]);
    }

    public function removePhoto($key) {
        unset($this->photos[$key]);
    }

    public function render() { return app('view')->make('null-view'); }
}
