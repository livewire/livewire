<?php

namespace Livewire\Features\SupportFileUploads;

use App\Livewire\UploadFile;
use Carbon\Carbon;
use Illuminate\Filesystem\FilesystemAdapter;
use Livewire\WithFileUploads;
use Livewire\Livewire;
use Livewire\Features\SupportDisablingBackButtonCache\SupportDisablingBackButtonCache;
use League\Flysystem\PathTraversalDetected;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Facades\Livewire\Features\SupportFileUploads\GenerateSignedUploadUrl;
use Illuminate\Http\Testing\FileFactory;
use Illuminate\Support\Arr;
use Tests\TestComponent;

class UnitTest extends \Tests\TestCase
{
    public function test_component_must_have_file_uploads_trait_to_accept_file_uploads()
    {
        $this->markTestSkipped(); // @todo: need to implement this properly...

        $this->expectException(MissingFileUploadsTraitException::class);

        Livewire::test(NonFileUploadComponent::class)
            ->set('photo', UploadedFile::fake()->image('avatar.jpg'));
    }

    public function test_s3_driver_only_supports_single_file_uploads()
    {
        config()->set('livewire.temporary_file_upload.disk', 's3');

        $this->expectException(S3DoesntSupportMultipleFileUploads::class);

        Livewire::test(FileUploadComponent::class)
            ->set('photos', [UploadedFile::fake()->image('avatar.jpg')]);
    }

    public function test_can_set_a_file_as_a_property_and_store_it()
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.jpg');

        Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->call('upload', 'uploaded-avatar.png');

        Storage::disk('avatars')->assertExists('uploaded-avatar.png');
    }

    public function test_can_remove_a_file_property()
    {
        $file = UploadedFile::fake()->image('avatar.jpg');

        $component = Livewire::test(FileUploadComponent::class)
            ->set('photo', $file);

        $tmpFilename = $component->viewData('photo')->getFilename();

        $component->call('_removeUpload', 'photo', $tmpFilename)
            ->assertDispatched('upload:removed', name: 'photo', tmpFilename: $tmpFilename)
            ->assertSetStrict('photo', null);
    }

    public function test_cant_remove_a_file_property_with_mismatched_filename_provided()
    {

        $file = UploadedFile::fake()->image('avatar.jpg');

        $component = Livewire::test(FileUploadComponent::class)
            ->set('photo', $file);

        $component->call('_removeUpload', 'photo', 'mismatched-filename.png')
            ->assertNotDispatched('upload:removed', name: 'photo', tmpFilename: 'mismatched-filename.png')
            ->assertNotSet('photo', null);

    }

    public function test_can_remove_a_file_from_an_array_of_files_property()
    {
        $file1 = UploadedFile::fake()->image('avatar1.jpg');
        $file2 = UploadedFile::fake()->image('avatar2.jpg');

        $component = Livewire::test(FileUploadComponent::class)
            ->set('photos', [$file1, $file2]);

        $tmpFiles = $component->viewData('photos');

        $component->call('_removeUpload', 'photos', $tmpFiles[1]->getFilename())
            ->assertDispatched('upload:removed', name: 'photos', tmpFilename: $tmpFiles[1]->getFilename());

        $tmpFiles = $component->call('$refresh')->viewData('photos');

        $this->assertCount(1, $tmpFiles);
    }

    public function test_if_the_file_property_is_an_array_the_uploaded_file_will_append_to_the_array()
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

    public function test_storing_a_file_returns_its_filename()
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $storedFilename = Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->call('uploadAndSetStoredFilename')
            ->get('storedFilename');

        Storage::disk('avatars')->assertExists($storedFilename);
    }

    public function test_storing_a_file_uses_uploaded_file_hashname()
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.jpg');

        Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->call('uploadAndSetStoredFilename');

        Storage::disk('avatars')->assertExists($file->hashName());
    }

    public function test_can_get_a_file_original_name()
    {
        $file = UploadedFile::fake()->image('avatar.jpg');

        $component = Livewire::test(FileUploadComponent::class)
            ->set('photo', $file);

        $tmpFile = $component->viewData('photo');

        $this->assertEquals('avatar.jpg', $tmpFile->getClientOriginalName());
    }

    public function test_can_get_multiple_files_original_name()
    {
        $file1 = UploadedFile::fake()->image('avatar1.jpg');
        $file2 = UploadedFile::fake()->image('avatar2.jpg');

        $component = Livewire::test(FileUploadComponent::class)
            ->set('photos', [$file1, $file2]);

        $tmpFiles = $component->viewData('photos');

        $this->assertEquals('avatar1.jpg', $tmpFiles[0]->getClientOriginalName());
        $this->assertEquals('avatar2.jpg', $tmpFiles[1]->getClientOriginalName());
    }

    public function test_can_set_a_file_as_a_property_using_the_s3_driver_and_store_it()
    {
        config()->set('livewire.temporary_file_upload.disk', 's3');

        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.jpg');

        Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->call('upload', 'uploaded-avatar.png');

        Storage::disk('avatars')->assertExists('uploaded-avatar.png');
    }

    public function test_can_set_multiple_files_as_a_property_and_store_them()
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

    public function test_a_file_cant_be_larger_than_12mb_or_the_global_livewire_uploader_will_fail()
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.jpg')->size(13000); // 13MB

        Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->assertHasErrors('photo');
    }

    public function test_the_global_upload_validation_rules_can_be_configured_and_the_error_messages_show_as_normal_validation_errors_for_the_property()
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.jpg')->size(100); // 100KB

        config()->set('livewire.temporary_file_upload.rules', 'file|max:50');

        Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->assertHasErrors('photo');
    }

    public function test_multiple_files_cant_be_larger_than_12mb_or_the_global_livewire_uploader_will_fail()
    {
        Storage::fake('avatars');

        $file1 = UploadedFile::fake()->image('avatar.jpg')->size(13000); // 13MB
        $file2 = UploadedFile::fake()->image('avatar.jpg')->size(13000); // 13MB

        Livewire::test(FileUploadComponent::class)
            ->set('photos', [$file1, $file2])
            ->assertHasErrors('photos.0')
            ->assertHasErrors('photos.1');
    }

    public function test_an_uploaded_file_can_be_validated()
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.jpg')->size(200);

        Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->call('validateUpload')
            ->assertHasErrors(['photo' => 'max']);
    }

    public function test_multiple_uploaded_files_can_be_validated()
    {
        Storage::fake('avatars');

        $file1 = UploadedFile::fake()->image('avatar.jpg');
        $file2 = UploadedFile::fake()->image('avatar.jpg')->size(200);

        Livewire::test(FileUploadComponent::class)
            ->set('photos', [$file1, $file2])
            ->call('validateMultipleUploads')
            ->assertHasErrors(['photos.1' => 'max']);
    }

    public function test_a_file_can_be_validated_in_real_time()
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()->create('avatar.xls', 75);

        Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->assertHasErrors(['photo' => 'image']);
    }

    public function test_multiple_files_can_be_validated_in_real_time()
    {
        Storage::fake('avatars');

        $file1 = UploadedFile::fake()->image('avatar.png');
        $file2 = UploadedFile::fake()->create('avatar.xls', 75);

        Livewire::test(FileUploadComponent::class)
            ->set('photos', [$file1, $file2])
            ->assertHasErrors(['photos.1' => 'image']);
    }

    public function test_file_upload_global_validation_can_be_translated()
    {
        Storage::fake('avatars');

        $translator = app()->make('translator');
        $translator->addLines([
            'validation.uploaded' => 'The :attribute failed to upload.',
            'validation.attributes.file' => 'upload'
        ], 'en');

        $file = UploadedFile::fake()->create('upload.xls', 100);

        $test = Livewire::test(FileUploadComponent::class)
            ->set('file', $file)
            ->call('uploadError', 'file')
            ->assertHasErrors(['file']);

        $this->assertEquals('The upload failed to upload.', $test->errors()->get('file')[0]);
    }

    public function test_image_dimensions_can_be_validated()
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.png', 100, 200);

        Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->call('validateUploadWithDimensions')
            ->assertHasErrors(['photo' => 'dimensions']);

        Storage::disk('avatars')->assertMissing('uploaded-avatar.png');
    }

    public function test_invalid_file_extension_can_validate_dimensions()
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()
            ->create('not-a-png-image.pdf', 512, 512);

        Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->call('validateUploadWithDimensions')
            ->assertHasErrors(['photo' => 'dimensions']);

        Storage::disk('avatars')->assertMissing('uploaded-not-a-png-image.png');
    }

    public function test_temporary_files_older_than_24_hours_are_cleaned_up_on_every_new_upload()
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

    public function test_temporary_files_older_than_24_hours_are_not_cleaned_up_if_configuration_specifies()
    {
        config()->set('livewire.temporary_file_upload.cleanup', false);

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

    public function test_temporary_files_older_than_24_hours_are_not_cleaned_up_on_every_new_upload_when_using_S3()
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

    public function test_S3_can_be_configured_so_that_temporary_files_older_than_24_hours_are_cleaned_up_automatically()
    {
        $this->artisan('livewire:configure-s3-upload-cleanup');

        // Can't "really" test this without using a live S3 bucket.
        $this->assertTrue(true);
    }

    public function test_the_global_upload_route_middleware_is_configurable()
    {
        config()->set('livewire.temporary_file_upload.middleware', DummyMiddleware::class);

        $url = GenerateSignedUploadUrl::forLocal();

        try {
            $this->withoutExceptionHandling()->post($url);
        } catch (\Throwable $th) {
            $this->assertStringContainsString(DummyMiddleware::class, $th->getMessage());
        }
    }

    public function test_the_global_upload_route_middleware_supports_multiple_middleware()
    {
        config()->set('livewire.temporary_file_upload.middleware', ['throttle:60,1', DummyMiddleware::class]);

        $url = GenerateSignedUploadUrl::forLocal();

        try {
            $this->withoutExceptionHandling()->post($url);
        } catch (\Throwable $th) {
            $this->assertStringContainsString('throttle:60,1', $th->getMessage());
            $this->assertStringContainsString(DummyMiddleware::class, $th->getMessage());
        }
    }

    public function test_can_preview_a_temporary_file_with_a_temporary_signed_url()
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $photo = Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->viewData('photo');

        // Due to Livewire object still being in memory, we need to
        // reset the "shouldDisableBackButtonCache" property back to its default
        // which is false to ensure it's not applied to the below route
        \Livewire\Features\SupportDisablingBackButtonCache\SupportDisablingBackButtonCache::$disableBackButtonCache = false;

        ob_start();
        $this->get($photo->temporaryUrl())->sendContent();
        $rawFileContents = ob_get_clean();

        $this->assertEquals($file->get(), $rawFileContents);

        $this->assertTrue($photo->isPreviewable());
    }

    public function test_file_is_not_sent_on_cache_hit()
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $photo = Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->viewData('photo');

        ob_start();
        $response = $this->get($photo->temporaryUrl());
        $response->sendContent();
        $rawFileContents = ob_get_clean();
        $this->assertEquals($file->get(), $rawFileContents);

        ob_start();
        $cachedResponse = $this->get($photo->temporaryUrl(), [
            'If-Modified-Since' => $response->headers->get('last-modified'),
        ]);
        $cachedResponse->sendContent();
        $this->assertEquals(304, $cachedResponse->getStatusCode());
        $cachedFileContents = ob_get_clean();

        $this->assertEquals('', $cachedFileContents);
    }

    public function test_can_preview_a_temporary_file_on_a_remote_storage()
    {
        $disk = Storage::fake('tmp-for-tests');

        // A remote storage will always return the short path when calling $disk->path(). To simulate a remote
        // storage, the fake storage will be recreated with an empty prefix option in order to get the short path even
        // if it's a local filesystem.
        Storage::set('tmp-for-tests', new FilesystemAdapter($disk->getDriver(), $disk->getAdapter(), ['prefix' => '']));

        $file = UploadedFile::fake()->image('avatar.jpg');

        $photo = Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->viewData('photo');

        // Due to Livewire object still being in memory, we need to
        // reset the "shouldDisableBackButtonCache" property back to it's default
        // which is false to ensure it's not applied to the below route
        \Livewire\Features\SupportDisablingBackButtonCache\SupportDisablingBackButtonCache::$disableBackButtonCache = false;

        ob_start();
        $this->get($photo->temporaryUrl())->sendContent();
        $rawFileContents = ob_get_clean();

        $this->assertEquals($file->get(), $rawFileContents);

        $this->assertTrue($photo->isPreviewable());
    }

    public function test_cant_preview_a_non_image_temporary_file_with_a_temporary_signed_url()
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()->create('avatar.pdf');

        $photo = Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->viewData('photo');

        $this->expectException(FileNotPreviewableException::class);
        $photo->temporaryUrl();

        $this->assertFalse($photo->isPreviewable());
    }

    public function test_allows_setting_file_types_for_temporary_signed_urls_in_config()
    {
        config()->set('livewire.temporary_file_upload.preview_mimes', ['pdf']);

        Storage::fake('advatars');

        $file = UploadedFile::fake()->create('file.pdf');

        $photo = Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->viewData('photo');

        // Due to Livewire object still being in memory, we need to
        // reset the "shouldDisableBackButtonCache" property back to it's default
        // which is false to ensure it's not applied to the below route
        SupportDisablingBackButtonCache::$disableBackButtonCache = false;

        ob_start();
        $this->get($photo->temporaryUrl())->sendContent();
        $rawFileContents = ob_get_clean();

        $this->assertEquals($file->get(), $rawFileContents);
    }

    public function test_public_temporary_file_url_must_have_valid_signature()
    {
        $photo = Livewire::test(FileUploadComponent::class)
            ->set('photo', UploadedFile::fake()->image('avatar.jpg'))
            ->viewData('photo');

        $this->get(str($photo->temporaryUrl())->before('&signature='))->assertStatus(401);
    }

    public function test_file_paths_cant_include_slashes_which_would_allow_them_to_access_other_private_directories()
    {
        $this->expectException(PathTraversalDetected::class);

        $file = UploadedFile::fake()->image('avatar.jpg');

        $component = Livewire::test(FileUploadComponent::class)
            ->set('photo', $file);

        // Try to hijack the photo property to a path outside the temporary livewire directory root.
        $component->set('photo', 'livewire-file:../dangerous.png')
            ->call('$refresh');
    }

    public function test_can_preview_a_temporary_files_with_a_temporary_signed_url_from_s3()
    {
        config()->set('livewire.temporary_file_upload.disk', 's3');

        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $photo = Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->viewData('photo');

        // Due to Livewire object still being in memory, we need to
        // reset the "shouldDisableBackButtonCache" property back to it's default
        // which is false to ensure it's not applied to the below route
        SupportDisablingBackButtonCache::$disableBackButtonCache = false;

        // When testing, rather than trying to hit an s3 server, we just serve
        // the local driver preview URL.
        ob_start();
        $this->get($photo->temporaryUrl())->sendContent();
        $rawFileContents = ob_get_clean();

        $this->assertEquals($file->get(), $rawFileContents);
    }

    public function test_removing_first_item_from_array_of_temporary_uploaded_files_serializes_correctly()
    {
        $file1 = UploadedFile::fake()->image('avatar1.jpg');
        $file2 = UploadedFile::fake()->image('avatar2.jpg');
        $file3 = UploadedFile::fake()->image('avatar3.jpg');
        $file4 = UploadedFile::fake()->image('avatar4.jpg');

        $component = Livewire::test(FileUploadComponent::class)
                             ->set('photos', [$file1, $file2, $file3, $file4]);

        $this->assertCount(4, $component->snapshot['data']['photos'][0]);

        $component->call('removePhoto', 3);
        $this->assertCount(3, $component->snapshot['data']['photos'][0]);

        $component->call('removePhoto', 0);
        $this->assertCount(2, $component->snapshot['data']['photos'][0]);
    }

    public function test_removing_first_item_from_array_of_temporary_uploaded_files_serializes_correctly_with_in_array_public_property()
    {
        $file1 = UploadedFile::fake()->image('avatar1.jpg');
        $file2 = UploadedFile::fake()->image('avatar2.jpg');
        $file3 = UploadedFile::fake()->image('avatar3.jpg');
        $file4 = UploadedFile::fake()->image('avatar4.jpg');

        $component = Livewire::test(FileUploadInArrayComponent::class)
                             ->set('obj.file_uploads', [$file1, $file2, $file3, $file4])
                             ->set('obj.first_name', 'john')
                             ->set('obj.last_name', 'doe');

        $this->assertSame($component->get('obj.first_name'), 'john');

        $this->assertSame($component->get('obj.last_name'), 'doe');

        $this->assertCount(4, $component->snapshot['data']['obj'][0]['file_uploads'][0]);

        $component->call('removePhoto', 3);
        $this->assertCount(3, $component->snapshot['data']['obj'][0]['file_uploads'][0]);

        $component->call('removePhoto', 0);
        $this->assertCount(2, $component->snapshot['data']['obj'][0]['file_uploads'][0]);
    }

    public function test_it_can_upload_multiple_file_within_array_public_property()
    {
        $file1 = UploadedFile::fake()->image('avatar1.jpg');
        $file2 = UploadedFile::fake()->image('avatar2.jpg');
        $file3 = UploadedFile::fake()->image('avatar3.jpg');
        $file4 = UploadedFile::fake()->image('avatar4.jpg');

        $component = Livewire::test(FileUploadInArrayComponent::class)
                             ->set('obj.file_uploads', [$file1, $file2, $file3, $file4])
                             ->set('obj.first_name', 'john')
                             ->set('obj.last_name', 'doe');

        $tmpFiles = $component->viewData('obj')['file_uploads'];

        $this->assertSame($component->get('obj.first_name'), 'john');

        $this->assertSame($component->get('obj.last_name'), 'doe');

        $component->updateProperty('obj.first_number', 10);

        $this->assertSame($component->get('obj.first_number'), 10);

        $this->assertSame($component->get('obj.second_number'), 99);

        $this->assertStringStartsWith('livewire-file:', $component->snapshot['data']['obj'][0]['file_uploads'][0][0][0]);
        $this->assertStringStartsWith('livewire-file:', $component->snapshot['data']['obj'][0]['file_uploads'][0][1][0]);
        $this->assertStringStartsWith('livewire-file:', $component->snapshot['data']['obj'][0]['file_uploads'][0][2][0]);
        $this->assertStringStartsWith('livewire-file:', $component->snapshot['data']['obj'][0]['file_uploads'][0][3][0]);

        $this->assertCount(4, $tmpFiles);
    }

    public function test_it_can_upload_single_file_within_array_public_property()
    {
        $file1 = UploadedFile::fake()->image('avatar1.jpg');

        $component = Livewire::test(FileUploadInArrayComponent::class)
                             ->set('obj.file_uploads', $file1)
                             ->set('obj.first_name', 'john')
                             ->set('obj.last_name', 'doe');

        $this->assertSame($component->get('obj.first_name'), 'john');

        $this->assertSame($component->get('obj.last_name'), 'doe');

        $component->updateProperty('obj.first_number', 10);

        $this->assertSame($component->get('obj.first_number'), 10);

        $this->assertSame($component->get('obj.second_number'), 99);

        $this->assertStringStartsWith('livewire-file:', $component->snapshot['data']['obj'][0]['file_uploads'][0]);
    }

    public function test_it_returns_temporary_path_set_by_livewire()
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()->image($fileName = 'avatar.jpg');

        $photo = Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->call('upload', $fileName)
            ->viewData('photo');

        $this->assertEquals(
            FileUploadConfiguration::storage()->path(FileUploadConfiguration::directory()),
            $photo->getPath()
        );
    }

    public function test_preview_url_is_stable_over_some_time()
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $photo = Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->viewData('photo');

        Carbon::setTestNow(Carbon::today()->setTime(10, 01, 00));

        $first_url = $photo->temporaryUrl();

        Carbon::setTestNow(Carbon::today()->setTime(10, 05, 00));

        $second_url = $photo->temporaryUrl();

        $this->assertEquals($first_url, $second_url);
    }

    public function test_file_content_can_be_retrieved_from_temporary_uploaded_files()
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.jpg');

        Livewire::test(FileReadContentComponent::class)
            ->set('file', $file)
            ->assertSetStrict('content', $file->getContent());
    }

    public function test_validation_of_file_uploads_while_time_traveling()
    {
        Storage::fake('avatars');

        $this->travelTo(now()->addMonth());

        $file = UploadedFile::fake()->image('avatar.jpg');

        Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->call('upload', 'uploaded-avatar.png');

        Storage::disk('avatars')->assertExists('uploaded-avatar.png');
    }

    public function test_extension_validation_cant_be_spoofed_by_manipulating_the_mime_type()
    {
        Storage::fake('avatars');

        $file = (new \Illuminate\Http\Testing\FileFactory)->create('malicious.php', 0, 'image/png');

        Livewire::test(FileExtensionValidatorComponent::class)
            ->set('photo', $file)
            ->call('save')
            ->assertHasErrors('photo');

        Storage::disk('avatars')->assertMissing('malicious.php');
    }

    public function test_the_file_upload_controller_middleware_prepends_the_web_group()
    {
        config()->set('livewire.temporary_file_upload.middleware', ['throttle:60,1']);

        $middleware = Arr::pluck(FileUploadController::middleware(), 'middleware');

        $this->assertEquals(['web', 'throttle:60,1'], $middleware);
    }

    public function test_the_file_upload_controller_middleware_only_adds_the_web_group_if_absent()
    {
        config()->set('livewire.temporary_file_upload.middleware', ['throttle:60,1', 'web']);

        $middleware = Arr::pluck(FileUploadController::middleware(), 'middleware');

        $this->assertEquals(['throttle:60,1', 'web'], $middleware);
    }

    public function test_temporary_file_uploads_guess_correct_mime_during_testing()
    {
        Livewire::test(UseProvidedMimeTypeDuringTestingComponent::class)
            ->set('photo', UploadedFile::fake()->create('file.png', 1000, 'application/pdf'))
            ->call('save')
            ->assertHasErrors([
                'photo' => 'mimetypes',
            ]);
    }
}

class DummyMiddleware
{
    public function handle($request, $next)
    {
        throw new \Exception(implode(',', $request->route()->computedMiddleware));
    }
}

class NonFileUploadComponent extends TestComponent
{
    public $photo;
}

class FileUploadComponent extends TestComponent
{
    use WithFileUploads;

    public $file;
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

    public function uploadError($name)
    {
        $this->_uploadErrored($name, null, false);
    }
}

class FileUploadInArrayComponent extends FileUploadComponent
{
    use WithFileUploads;

    public $obj = [
        'first_name' => null,
        'last_name' => null,
        'first_number' => 2,
        'second_number' => 99,
        'file_uploads' => null
    ];

    public function removePhoto($key) {
        unset($this->obj['file_uploads'][$key]);
    }
}

class FileReadContentComponent extends FileUploadComponent
{
    use WithFileUploads;

    public $file;
    public $content = '';

    public function updatedFile()
    {
        $this->content = $this->file->getContent();
    }
}

class FileExtensionValidatorComponent extends FileUploadComponent
{
    use WithFileUploads;

    public $photo;

    public function save()
    {
        $this->validate([
            'photo' => 'extensions:png',
        ]);

        $this->photo->storeAs('/', 'malicious.'.$this->photo->getClientOriginalExtension(), $disk = 'avatars');
    }
}

class UseProvidedMimeTypeDuringTestingComponent extends FileUploadComponent
{
    use WithFileUploads;

    public $photo;

    public function save()
    {
        $this->validate([
            'photo' => 'mimetypes:image/png',
        ]);
    }
}
