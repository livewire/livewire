<?php

namespace Tests;

use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;

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

    public function uploadErrorWithMalformedJson($name)
    {
        // Simulate malformed JSON without 'errors' key
        $malformedJson = '{"message":"Something went wrong"}';
        $this->_uploadErrored($name, $malformedJson, false);
    }
}