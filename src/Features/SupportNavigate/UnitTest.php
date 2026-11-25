<?php

namespace Livewire\Features\SupportNavigate;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

class UnitTest extends TestCase
{
    public function test_vite_tags_are_tracked_by_their_build_version()
    {
        $this->useViteBuild('first-build', ['resources/js/app.js' => 'app-abc123.js', 'resources/css/app.css' => 'app-def456.css']);
        $firstBuild = $this->trackedVersions(Vite::withEntryPoints(['resources/js/app.js', 'resources/css/app.css'])->toHtml());

        $this->useViteBuild('next-build', ['resources/js/app.js' => 'app-ghi789.js', 'resources/css/app.css' => 'app-def456.css']);
        $nextBuild = $this->trackedVersions(Vite::withEntryPoints(['resources/js/app.js'])->toHtml());

        $this->assertCount(1, array_unique($firstBuild));
        $this->assertNotEquals($firstBuild[0], $nextBuild[0]);
    }

    protected function useViteBuild(string $buildDirectory, array $files)
    {
        $manifest = collect($files)->mapWithKeys(fn ($file, $src) => [$src => ['file' => 'assets/' . $file, 'src' => $src, 'isEntry' => true]]);

        File::ensureDirectoryExists(public_path($buildDirectory));
        File::put(public_path($buildDirectory . '/manifest.json'), $manifest->toJson());

        $this->beforeApplicationDestroyed(fn () => File::deleteDirectory(public_path($buildDirectory)));

        Vite::useBuildDirectory($buildDirectory);
    }

    protected function trackedVersions(string $html)
    {
        preg_match_all('/data-navigate-track="([^"]*)"/', $html, $matches);

        return $matches[1];
    }
}
