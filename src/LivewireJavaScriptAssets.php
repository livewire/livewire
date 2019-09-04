<?php

namespace Livewire;

class LivewireJavaScriptAssets
{
    public function unminified()
    {
        return $this->pretendResponseIsFile(__DIR__.'/../dist/livewire.js');
    }

    public function minified()
    {
        return $this->pretendResponseIsFile(__DIR__.'/../dist/livewire.min.js');
    }

    public function pretendResponseIsFile($file)
    {
        $etag = $this->etag($file);
        $expires = strtotime('+1 year');
        $lastModified = filemtime($file);

        if ($this->matchesCache($etag, $lastModified)) {
            return response()->noContent(304, [
                'ETag' => $etag,
                'Last-Modified' => $this->httpDate($lastModified),
            ]);
        }

        return response()->file($file, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'ETag' => $etag,
            'Cache-Control' => 'public, max-age=31536000',
            'Expires' => $this->httpDate($expires),
            'Last-Modified' => $this->httpDate($lastModified),
        ]);
    }

    protected function etag($file)
    {
        $manifest = json_decode(file_get_contents(__DIR__.'/../dist/mix-manifest.json'), true);
        $versioned = $manifest['/' . basename($file)];

        parse_str(parse_url($versioned, PHP_URL_QUERY), $query);

        return $query['id'];
    }

    protected function matchesCache($etag, $lastModified)
    {
        $ifNoneMatch = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';
        $ifModifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '';

        return trim($ifNoneMatch, ' "') === $etag
            || @strtotime($ifModifiedSince) === $lastModified;
    }

    protected function httpDate($timestamp)
    {
        return sprintf('%s GMT', gmdate('D, d M Y H:i:s', $timestamp));
    }
}
