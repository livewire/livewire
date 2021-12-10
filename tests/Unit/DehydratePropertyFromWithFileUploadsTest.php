<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads;
use Illuminate\Http\UploadedFile;
use Livewire\TemporaryUploadedFile;
use Livewire\Wireable;

class DehydratePropertyFromWithFileUploadsTest extends TestCase
{
    /** @test */
    public function a_text_variable_should_return_with_no_changes()
    {
        $uploader = SupportFileUploads::init();
        $inputValue = 'File Upload';
        $outputValue = $uploader->dehydratePropertyFromWithFileUploads($inputValue);
        $this->assertTrue($inputValue === $outputValue);
    }

    /** @test */
    public function an_image_should_return_a_serialized_version_of_itself()
    {
        Storage::fake('tmp-for-tests');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $uploader = SupportFileUploads::init();
        $tempFile = TemporaryUploadedFile::createFromLivewire($file->path());
        $outputFile = $uploader->dehydratePropertyFromWithFileUploads($tempFile);
        $this->assertTrue(str_contains($outputFile, 'livewire-file:'));
    }

    /** @test */
    public function an_array_should_serialize_all_images_within_it()
    {
        Storage::fake('tmp-for-tests');

        $file1 = UploadedFile::fake()->image('avatar.jpg');
        $file2 = UploadedFile::fake()->image('avatar.jpg');

        $uploader = SupportFileUploads::init();
        $tmpFileArray = [
            TemporaryUploadedFile::createFromLivewire($file1->path()),
            TemporaryUploadedFile::createFromLivewire($file2->path())
        ];

        $outputFileStr = $uploader->dehydratePropertyFromWithFileUploads($tmpFileArray);
        $imageListArr = explode(',', str_replace(['livewire-files:', '[' , ']'],  '', $outputFileStr));

        $this->assertTrue(str_contains($outputFileStr, 'livewire-files:'));
        $this->assertTrue(count($imageListArr) === count($tmpFileArray));
    }

    /** @test */
    public function a_keyed_array_should_serialize_all_images_within_it_separately()
    {
        Storage::fake('tmp-for-tests');

        $file1 = UploadedFile::fake()->image('avatar.jpg');
        $file2 = UploadedFile::fake()->image('avatar.jpg');

        $uploader = SupportFileUploads::init();
        $tmpFileArray = [
            'file1' => TemporaryUploadedFile::createFromLivewire($file1->path()),
            'file2' => TemporaryUploadedFile::createFromLivewire($file2->path())
        ];

        $outputFileStr = $uploader->dehydratePropertyFromWithFileUploads($tmpFileArray);

        $this->assertIsArray($outputFileStr);
        $this->assertTrue(str_contains($outputFileStr['file1'], 'livewire-file:'));
        $this->assertTrue(str_contains($outputFileStr['file2'], 'livewire-file:'));
    }

    /** @test */
    public function a_wireable_object_serialize_all_images_within_it()
    {
        Storage::fake('tmp-for-tests');

        $file1 = UploadedFile::fake()->image('avatar.jpg');
        $file2 = UploadedFile::fake()->image('avatar.jpg');
        $file3 = UploadedFile::fake()->image('avatar.jpg');

        $uploader = SupportFileUploads::init();

        $tmpFile = TemporaryUploadedFile::createFromLivewire($file1->path());
        $tmpFileArray = [
            TemporaryUploadedFile::createFromLivewire($file2->path()),
            TemporaryUploadedFile::createFromLivewire($file3->path())
        ];

        $wireableInput = new DehydrateTestWireable($tmpFile, $tmpFileArray, 'test string', 1);

        $wireableOutput = $uploader->dehydratePropertyFromWithFileUploads($wireableInput);
        $imageListArr = explode(',', str_replace(['livewire-files:','[', ']'], '', $wireableOutput->imageList));

        $this->assertTrue(str_contains($wireableOutput->image, 'livewire-file:'));
        $this->assertTrue(str_contains($wireableOutput->imageList, 'livewire-files:'));
        $this->assertTrue(count($imageListArr) === count($tmpFileArray));
        $this->assertTrue($wireableOutput->text === 'test string');
        $this->assertTrue($wireableOutput->number === 1);
    }

    /** @test */
    public function serialize_mixed_arrays_with_uploaded_files()
    {
        Storage::fake('tmp-for-tests');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $uploadedFiles = [
          TemporaryUploadedFile::createFromLivewire($file->path()),
          TemporaryUploadedFile::createFromLivewire($file->path()),
          TemporaryUploadedFile::createFromLivewire($file->path()),
        ];

        $mixedUploadsWithImageFirst = [
          TemporaryUploadedFile::createFromLivewire($file->path()),
          [ 'id' => 1 ],
          [ 'id' => 2 ],
        ];

        $mixedUploadsWithImageNotFirst = [
          [ 'id' => 1 ],
          TemporaryUploadedFile::createFromLivewire($file->path()),
          [ 'id' => 2 ],
        ];

        $uploader = SupportFileUploads::init();

        // Test the uploaded files array.
        $outputUploadedFiles = $uploader->dehydratePropertyFromWithFileUploads($uploadedFiles);
        $uploadedFilesList = explode(',', str_replace(['livewire-files:', '[', ']'], '', $outputUploadedFiles));
        $this->assertTrue(str_contains($outputUploadedFiles, 'livewire-files:'));
        $this->assertTrue(count($uploadedFilesList) === count($uploadedFiles));

        // Test the mixed array with the image in the first position.
        $outputMixedImageFirst = $uploader->dehydratePropertyFromWithFileUploads($mixedUploadsWithImageFirst);
        $this->assertTrue(str_contains($outputMixedImageFirst[0], 'livewire-file:'));
        $this->assertTrue(is_array($outputMixedImageFirst[1]));
        $this->assertTrue(is_array($outputMixedImageFirst[2]));

        // Test the mixed array with the image not on the first position.
        $outputMixedImageNotFirst = $uploader->dehydratePropertyFromWithFileUploads($mixedUploadsWithImageNotFirst);
        $this->assertTrue(is_array($outputMixedImageNotFirst[0]));
        $this->assertTrue(str_contains($outputMixedImageNotFirst[1], 'livewire-file:'));
        $this->assertTrue(is_array($outputMixedImageNotFirst[2]));
    }
}

class DehydrateTestWireable implements Wireable
{
    public $image;
    public $imageList;
    public $text;
    public $number;

    public function __construct($image, $imageList, $text, $number)
    {
        $this->image = $image;
        $this->imageList = $imageList;
        $this->text = $text;
        $this->number = $number;
    }

    public function toLivewire()
    {
        return [
            'image' => $this->image,
            'imageList' => $this->imageList,
            'text' => $this->text,
            'number' => $this->number
        ];
    }

    public static function fromLivewire($value)
    {
        return new static($value['image'], $value['imageList'], $value['text'], $value['number']);
    }
}
