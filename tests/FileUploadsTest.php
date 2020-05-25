<?php

namespace Tests;

use Livewire\Livewire;
use Livewire\Component;
use Livewire\LivewireManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Exceptions\MissingWithFileUploadsTraitException;
use Livewire\WithFileUploads;

class FileUploadsTest extends TestCase
{
    /** @test */
    public function component_must_have_file_uploades_trait_to_accept_file_uploads()
    {
        $this->expectException(MissingWithFileUploadsTraitException::class);

        Livewire::test(FileUploadComponent::class)
            ->set('photo', UploadedFile::fake()->image('avatar.jpg'));
    }

    /** @test */
    public function can_simulate_a_file_upload_by_setting_a_file_as_a_property_and_storing_it()
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.jpg');

        Livewire::test(FileUploadComponent::class)
            ->set('photo', $file)
            ->call('upload');

        Storage::disk('avatars')->assertExists('uploaded-avatar.png');
    }

    /** @test */
    public function a_file_cant_be_larger_than_12mb_or_the_global_livewire_uploader_will_fail()
    {
        $this->expectException(ValidationException::class);

        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.jpg')->size(13000); // 33MB

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
            ->assertHasErrors('max');

        Storage::disk('avatars')->assertMissing('uploaded-avatar.png');
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

    public function upload()
    {
        $this->photo->storeAs('/', 'uploaded-avatar.png', $disk = 'avatars');
    }

    public function validateUpload()
    {
        $this->validate(['photo' => 'file|max:100']);
    }

    public function render() { return app('view')->make('null-view'); }
}
