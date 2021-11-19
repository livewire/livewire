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
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            $this->markTestSkipped('Typed Property Initialization not supported prior to PHP 7.4');
        }

        $uploader = SupportFileUploads::init();
        $inputValue = 'File Upload';
        $outputValue = $uploader->dehydratePropertyFromWithFileUploads($inputValue);
        $this->assertTrue($inputValue === $outputValue);
    }

    /** @test */
    public function an_image_should_return_a_serialized_version_of_itself()
    {
        Storage::fake('tmp-for-tests');

        if (version_compare(PHP_VERSION, '7.4', '<')) {
            $this->markTestSkipped('Typed Property Initialization not supported prior to PHP 7.4');
        }

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

        if (version_compare(PHP_VERSION, '7.4', '<')) {
            $this->markTestSkipped('Typed Property Initialization not supported prior to PHP 7.4');
        }

        $file1 = UploadedFile::fake()->image('avatar.jpg');
        $file2 = UploadedFile::fake()->image('avatar.jpg');

        $uploader = SupportFileUploads::init();
        $tmpFileArray = [
            TemporaryUploadedFile::createFromLivewire($file1->path()),
            TemporaryUploadedFile::createFromLivewire($file2->path())
        ];

        $outputFileStr = $uploader->dehydratePropertyFromWithFileUploads($tmpFileArray);
        $imageListArr = explode(',', str_replace(array('livewire-files:','[', ']'), '', $outputFileStr));

        $this->assertTrue(str_contains($outputFileStr, 'livewire-files:'));
        $this->assertTrue(count($imageListArr) === count($tmpFileArray));
    }

    /** @test */
    public function a_wireable_object_serialize_all_images_within_it()
    {
        Storage::fake('tmp-for-tests');

        if (version_compare(PHP_VERSION, '7.4', '<')) {
            $this->markTestSkipped('Typed Property Initialization not supported prior to PHP 7.4');
        }

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
        $imageListArr = explode(',', str_replace(array('livewire-files:','[', ']'), '', $wireableOutput->imageList));


        $this->assertTrue(str_contains($wireableOutput->image, 'livewire-file:'));
        $this->assertTrue(str_contains($wireableOutput->imageList, 'livewire-files:'));
        $this->assertTrue(count($imageListArr) === count($tmpFileArray));
        $this->assertTrue($wireableOutput->text === 'test string');
        $this->assertTrue($wireableOutput->number === 1);
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


