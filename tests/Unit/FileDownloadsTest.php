<?php

namespace Tests\Unit;

use Illuminate\Contracts\Support\Responsable;
use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;

class FileDownloadsTest extends TestCase
{
    /** @test */
    public function can_download_a_file()
    {
        Livewire::test(FileDownloadComponent::class)
                ->call('download')
                ->assertFileDownloaded()
                ->assertFileDownloaded('download.txt', 'I\'m the file you should download.', 'text/plain');
    }

    /** @test */
    public function can_download_a_file_as_stream()
    {
        Livewire::test(FileDownloadComponent::class)
                ->call('streamDownload', 'download.txt')
                ->assertFileDownloaded('download.txt', 'alpinejs');
    }

    public function can_download_a_responsable(){
        Livewire::test(FileDownloadComponent::class)
                ->call('responsableDownload')
                ->assertFileDownloaded()
                ->assertFileDownloaded('download.txt', 'I\'m the file you should download.', 'text/plain');
    }

    /** @test */
    public function can_download_with_custom_filename()
    {
        Livewire::test(FileDownloadComponent::class)
                ->call('download', 'download.csv')
                ->assertFileDownloaded('download.csv', 'I\'m the file you should download.');
    }

    /** @test */
    public function can_download_a_file_as_stream_with_custom_filename()
    {
        Livewire::test(FileDownloadComponent::class)
                ->call('streamDownload', 'download.csv')
                ->assertFileDownloaded('download.csv', 'alpinejs');
    }

    /** @test */
    public function can_download_with_custom_filename_and_headers()
    {
        Livewire::test(FileDownloadComponent::class)
                ->call('download', 'download.csv', ['Content-Type' => 'text/csv'])
                ->assertFileDownloaded('download.csv', 'I\'m the file you should download.', 'text/csv');
    }

    /** @test */
    public function can_download_a_file_as_stream_with_custom_filename_and_headers()
    {
        Livewire::test(FileDownloadComponent::class)
                ->call('streamDownload', 'download.csv', ['Content-Type' => 'text/csv'])
                ->assertFileDownloaded('download.csv', 'alpinejs', 'text/csv');
    }

    /** @test */
    public function it_refreshes_html_after_download()
    {
        Livewire::test(FileDownloadComponent::class)
            ->call('download')
            ->assertFileDownloaded()
            ->assertSeeText('Thanks!');
    }
}

class FileDownloadComponent extends Component
{
    public $downloaded = false;

    public function download($filename = null, $headers = [])
    {
        $this->downloaded = true;
        return Storage::disk('unit-downloads')->download('download.txt', $filename, $headers);
    }

    public function streamDownload($filename = null, $headers = [])
    {
        $this->downloaded = true;
        return response()->streamDownload(function () {
            echo 'alpinejs';
        }, $filename, $headers);
    }

    public function responsableDownload()
    {
        return new DownloadableResponse();
    }


    public function render()
    {
        return app('view')->make('file-download-component');
    }
}

class DownloadableResponse implements Responsable
{
    public function toResponse($request)
    {
        return  Storage::disk('unit-downloads')->download('download.txt');
    }
}
