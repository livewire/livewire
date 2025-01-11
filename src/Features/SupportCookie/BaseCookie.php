<?php

namespace Livewire\Features\SupportCookie;

use Illuminate\Support\Facades\Cookie as CookieFacade;
use Livewire\Component;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class BaseCookie extends LivewireAttribute
{
    /**
     * @param string|null $key       The name of the cookie.
     * @param int         $minutes   Expiration time in minutes (default: 30 days).
     * @param string|null $path      The path on the server where the cookie is available.
     * @param string|null $domain    The domain that the cookie is available to.
     * @param bool|null   $secure    Whether the cookie should only be sent over HTTPS (default: true).
     * @param bool        $httpOnly  Whether the cookie is inaccessible to JavaScript (default: true).
     * @param bool        $raw       Whether to send the cookie without URL encoding (default: false).
     * @param string|null $sameSite  Cross-site policy for the cookie (default: 'Lax').
     */
    public function __construct(
        protected $key = null,
        protected $minutes = 60 * 24 * 30, // 30 days
        protected $path = '/',
        protected $domain = null,
        protected $secure = true, // Default to HTTPS only
        protected $httpOnly = true, // Default to HttpOnly
        protected $raw = false,
        protected $sameSite = 'Lax', // Default to Lax for cross-site protection
    ) {
        if ($secure === null) {
            // Automatically detect HTTPS
            $this->secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        }
    }

    public function mount()
    {
        if (!$this->exists()) {
            return;
        }

        $fromCookie = $this->read();

        $this->setValue($fromCookie);
    }

    public function dehydrate()
    {
        $this->write();
    }

    protected function exists()
    {
        return CookieFacade::has($this->key());
    }

    protected function read()
    {
        return CookieFacade::get($this->key());
    }

    protected function write()
    {
        CookieFacade::queue(
            name: $this->key(),
            value: $this->getValue(),
            minutes: $this->minutes,
            path: $this->path,
            domain: $this->domain,
            secure: $this->secure,
            httpOnly: $this->httpOnly,
            raw: $this->raw,
            sameSite: $this->sameSite,
        );
    }

    protected function key()
    {
        if (! $this->key) {
            return 'lw'.crc32($this->component->getName().$this->getName());
        }

        return self::replaceDynamicPlaceholders($this->key, $this->component);
    }

    public static function replaceDynamicPlaceholders($key, $component): string
    {
        return preg_replace_callback('/\{(.*)\}/U', function ($matches) use ($component) {
            return data_get($component, $matches[1], function () use ($matches): void {
                throw new \Exception('Unable to evaluate dynamic cookie key placeholder: '.$matches[0]);
            });
        }, $key);
    }
}
