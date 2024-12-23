<?php

namespace Livewire\Features\SupportCookie;

use Illuminate\Support\Facades\Cookie as CookieFacade;
use Livewire\Component;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class BaseCookie extends LivewireAttribute
{
    /**
     * @param string|null $key
     * @param int         $minutes
     * @param string|null $path
     * @param string|null $domain
     * @param bool|null   $secure
     * @param bool        $httpOnly
     * @param bool        $raw
     * @param string|null $sameSite
     */
    public function __construct(
        protected $key = null,
        protected $minutes = 60 * 24 * 365, // one year
        protected $path = null,
        protected $domain = null,
        protected $secure = null,
        protected $httpOnly = true,
        protected $raw = false,
        protected $sameSite = null,
    ) {
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
