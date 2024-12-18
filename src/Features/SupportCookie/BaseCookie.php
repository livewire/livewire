<?php

namespace Livewire\Features\SupportCookie;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Config;
use Attribute;

#[Attribute(\Attribute::TARGET_PROPERTY)]
class BaseCookie extends LivewireAttribute
{
    /**
     * BaseCookie allows Livewire properties to be automatically persisted in cookies.
     * This is useful for storing user preferences, authentication tokens, or any stateful data.
     *
     * By default, this class respects Laravel's session configuration to ensure secure and consistent cookie handling.
     *
     * @param string|null $key       The unique name of the cookie. If null, Livewire will generate a default key.
     * @param int         $minutes   Cookie expiration time in minutes (default: 30 days).
     * @param string|null $path      The path where the cookie is available (default: "/").
     * @param string|null $domain    The domain that the cookie is available to. Uses `config('session.domain')` by default.
     * @param bool|null   $secure    Whether the cookie should only be sent over HTTPS. Defaults to `config('session.secure')`.
     * @param bool        $httpOnly  If true, the cookie will be inaccessible to JavaScript (default: true).
     * @param bool        $raw       If true, the cookie is sent without URL encoding (default: false).
     * @param string|null $sameSite  Controls how cookies are sent with cross-site requests. Defaults to `config('session.same_site', 'lax')`.
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
        $this->domain = $this->domain ?? Config::get('session.domain', null);
        $this->secure = $this->secure ?? Config::get('session.secure', true);
        $this->sameSite = $this->sameSite ?? Config::get('session.same_site', 'lax');
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
        return Cookie::has($this->key());
    }

    protected function read()
    {
        return Cookie::get($this->key());
    }

    protected function write()
    {
        Cookie::queue(
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
