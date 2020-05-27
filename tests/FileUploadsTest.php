<?php

namespace Tests;

use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Http\Request;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Exceptions\MissingFileUploadsTraitException;
use Livewire\FileUploadConfiguration;

class FileUploadsTest extends TestCase
{
    /** @test */
    public function component_must_have_file_uploades_trait_to_accept_file_uploads()
    {
        $this->expectException(MissingFileUploadsTraitException::class);

        Livewire::test(NonFileUploadComponent::class)
            ->set('photo', UploadedFile::fake()->image('avatar.jpg'));
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
    public function a_file_cant_be_larger_than_12mb_or_the_global_livewire_uploader_will_fail()
    {
        $this->expectException(ValidationException::class);

        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.jpg')->size(13000); // 13MB

        Livewire::test(FileUploadComponent::class)
            ->set('photo', $file);

        Storage::disk('avatars')->assertMissing('uploaded-avatar.png');
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

        Storage::disk('avatars')->assertMissing('uploaded-avatar.png');
    }

    /** @test */
    public function a_file_can_be_valited_in_real_time()
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()->create('avatar.xls', 75);

        Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->assertHasErrors(['photo' => 'image']);

        Storage::disk('avatars')->assertMissing('uploaded-avatar.png');
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

    public function updatedPhoto()
    {
        $this->validate(['photo' => 'image|max:300']);
    }

    public function upload($name)
    {
        $this->photo->storeAs('/', $name, $disk = 'avatars');
    }

    public function validateUpload()
    {
        $this->validate(['photo' => 'file|max:100']);
    }

    public function validateUploadWithDimensions()
    {
        $this->validate([
            'photo' => Rule::dimensions()->maxWidth(100)->maxHeight(100),
        ]);
    }

    public function render() { return app('view')->make('null-view'); }
}
