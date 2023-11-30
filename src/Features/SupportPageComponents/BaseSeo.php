<?php

namespace Livewire\Features\SupportPageComponents;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute]
class BaseSeo extends LivewireAttribute
{
    function __construct(
        public $title,
        public $description = null,
        public $keywords = null,
        public $robots = 'index, follow',
        public $author = null,
        public $ogType = 'website',
        public $ogSitename = null,
        public $ogTitle = null,
        public $ogDescription = null,
        public $ogImage = null,
        public $ogLocale = null,
        public $ogUrl = null,
        public $twitterCard = null,
        public $twitterSite = null,
        public $twitterTitle = null,
        public $twitterDescription = null,
        public $twitterImage = null,
        public $twitterAuthor = null,
        public $twitterCreator = null,
        public $twitterUrl = null
    ) {
        $this->ogType = $ogType ?? 'website';
        $this->ogTitle = $ogTitle ?? $title;
        $this->ogDescription = $ogDescription ?? $this->description;
        $this->ogLocale = $ogLocale ?? str(app()->getLocale())->replace('_', '-');
        $this->ogUrl = $ogUrl ?? url()->current();
        $this->twitterCard = $twitterCard ?? 'summary';
        $this->twitterTitle = $twitterTitle ?? $ogTitle;
        $this->twitterDescription = $twitterDescription ?? $ogDescription;
        $this->twitterImage = $this->ogImage;
        $this->twitterUrl = $this->twitterUrl ?? $this->ogUrl;
    }
}
