<?php

namespace Livewire\Testing;

use Symfony\Component\DomCrawler\Crawler;

class TestableLivewire
{
    protected $prefix;
    protected $rawHtml;
    protected $formInputs = [];

    use Concerns\MakesAssertions,
        Concerns\HasFunLittleUtilities,
        Concerns\MakesCallsToComponent,
        Concerns\InteractsWithPage;

    public function __construct($component, $prefix)
    {
        $this->prefix = $prefix;

        [$dom, $id, $serialized] = app('livewire')->mount($component);

        $this->updateComponent($dom, $serialized);
    }

    public function updateComponent($html, $serialized)
    {
        $this->serialized = $serialized;

        $this->crawler = new Crawler(
            $this->rawHtml = $this->replaceSpecialCharactersWithPlaceholders(
                $this->moveDirectiveModifiersToSeparateAttributes($html)
            )
        );
    }

    public function replaceSpecialCharactersWithPlaceholders($subject)
    {
        foreach ($this->getRawDirectives($subject) as $rawDirective) {
            $replacedColons = str_replace(':', '--colon--', $rawDirective);
            $replacedDots = str_replace('.', '--dot--', $replacedColons);
            $subject = str_replace($rawDirective, $replacedDots, $subject);
        }

        return $subject;
    }

    public function moveDirectiveModifiersToSeparateAttributes($subject)
    {
        // This craziness is because there is no good way in Symfony's DOMCrawler
        // to retreive the value of an attribute by a regex or something. Because of this,
        // attributes like "click.min.250ms" cannot be queried for unless we know the
        // exact, full directive. So instead, I'm ripping out the modifiers and placing
        // them in their own separate attribute that DOMCrawler will play nice with.
        foreach ($this->getRawDirectives($subject) as $rawDirective) {
            preg_match('/wire:(.*)="(.*)"/', $rawDirective, $matches);
            $results = explode('.', $matches[1]);
            $directive = $results[0];
            unset($results[0]);
            $modifiers = $results;

            $modifiersDirective = 'wire:' . $directive . '-modifiers="' . implode('.', $modifiers) . '" ';

            $directiveWithoutModifiersPrefixedByModifiersDirective
                = $modifiersDirective . ' wire:' . $directive;

            $subject = str_replace($rawDirective, $directiveWithoutModifiersPrefixedByModifiersDirective, $subject);
        }

        return $subject;
    }

    public function getRawDirectives($subject)
    {
        preg_match_all('/('.$this->prefix.':[.]*)="/', $subject, $matches);
        return $matches[1];
    }

    public function formatSelector($selector)
    {
        $hey = $this->replaceSpecialCharactersWithPlaceholders(
            $this->stripDirectiveModifiers(
                $this->interpretAtSymbolsAsRefShortcuts($selector)
            )
        );
    }

    public function stripDirectiveModifiers()
    {
        // TODO: get this style of testing working.
        // $matches = preg_match('/\[wire:(.*)=/', '[wire:click="yeah"]', $matches);
    }

    public function interpretAtSymbolsAsRefShortcuts($selector)
    {
        if (strpos($selector, '@') === 0) {
            return sprintf('[%s:ref="%s"]', $this->prefix, str_after($selector, '@'));
        }

        return $selector;
    }

    public function querySelector($selector = null)
    {
        $node = $selector
            ? $this->crawler->filter($this->formatSelector($selector))
            : $this->crawler;

        throw_unless($node->count(), new \Exception('Can\'t find element with selector: [' . $selector . ']'));

        return $node;
    }

    public function toHtml($node)
    {
        return $node->getNode(0)->ownerDocument->saveXML($node->getNode(0));
    }

    public function __get($property)
    {
        return decrypt($this->serialized)->{$property};
    }

    public function __call($method, $params)
    {
        return $this->callMethod($method, $params);
    }
}
