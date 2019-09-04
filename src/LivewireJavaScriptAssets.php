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
        $lastModified = filemtime($file);
        $expires = strtotime('+1 year');

        return response()->file($file, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'ETag' => $this->etag($file),
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

    protected function httpDate($timestamp)
    {
        return sprintf('%s GMT', gmdate('D, d M Y H:i:s', $timestamp));
    }
}
