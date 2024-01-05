<?php

namespace Livewire\Features\SupportFileDownloads;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Livewire;
use PHPUnit\Framework\ExpectationFailedException;

class UnitTest extends \Tests\TestCase
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
    public function can_download_with_custom_japanese_filename()
    {
        Livewire::test(FileDownloadComponent::class)
                ->call('download', 'ダウンロード.csv')
                ->assertFileDownloaded('ダウンロード.csv', 'I\'m the file you should download.');
    }

    /** @test */
    public function can_download_a_file_as_stream_with_custom_japanese_filename()
    {
        Livewire::test(FileDownloadComponent::class)
                ->call('streamDownload', 'ダウンロード.csv')
                ->assertFileDownloaded('ダウンロード.csv', 'alpinejs');
    }

    /** @test */
    public function can_download_with_custom_japanese_filename_and_headers()
    {
        Livewire::test(FileDownloadComponent::class)
                ->call('download', 'ダウンロード.csv', ['Content-Type' => 'text/csv'])
                ->assertFileDownloaded('ダウンロード.csv', 'I\'m the file you should download.', 'text/csv');
    }

    /** @test */
    public function can_download_a_file_as_stream_with_custom_japanese_filename_and_headers()
    {
        Livewire::test(FileDownloadComponent::class)
                ->call('streamDownload', 'ダウンロード.csv', ['Content-Type' => 'text/csv'])
                ->assertFileDownloaded('ダウンロード.csv', 'alpinejs', 'text/csv');
    }

    /** @test */
    public function it_refreshes_html_after_download()
    {
        Livewire::test(FileDownloadComponent::class)
            ->call('download')
            ->assertFileDownloaded()
            ->assertSeeText('Thanks!');
    }

    /** @test */
    public function can_assert_that_nothing_was_downloaded()
    {
        Livewire::test(FileDownloadComponent::class)
            ->call('noDownload')
            ->assertNoFileDownloaded();
    }

    /** @test */
    public function can_fail_to_assert_that_nothing_was_downloaded()
    {
        $this->expectException(ExpectationFailedException::class);

        Livewire::test(FileDownloadComponent::class)
            ->call('download')
            ->assertNoFileDownloaded();
    }
}

class FileDownloadComponent extends Component
{
    public $downloaded = false;

    public function noDownload()
    {
        //
    }

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
        return <<<'HTML'
        <div>
            @if($downloaded)
                Thanks!
            @endif
        </div>
        HTML;
    }
}

class DownloadableResponse implements Responsable
{
    public function toResponse($request)
    {
        return  Storage::disk('unit-downloads')->download('download.txt');
    }
}

