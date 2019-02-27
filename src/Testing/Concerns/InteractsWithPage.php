<?php

namespace Livewire\Testing\Concerns;

use Livewire\Testing\TestConnectionHandler;
use Symfony\Component\DomCrawler\Crawler;

trait InteractsWithPage
{
    public function type($selector, $text)
    {
        $node = $this->querySelector($selector);

        if ($dataName = $node->attr("{$this->prefix}--colon--model")) {
            $this->syncInput($dataName, $text);
        }

        if ($inputName = $node->attr('name')) {
           $this->formInputs[$inputName] = $text;
        }

        return $this;
    }

    public function click($selector)
    {
        $node = $this->querySelector($selector);

        $rawMethod = $node->attr("{$this->prefix}--colon--click");

        [$method, $params] = $this->extractMethodAndParameters($rawMethod);

        return $this->callMethod($method, $params);
    }

    public function press($selector)
    {
        $button = $this->querySelector($selector);
        $form = $button->parents()->filter("[{$this->prefix}--submit]");
        $rawMethod = $form->attr("{$this->prefix}--submit");
        [$method, $params] = $this->extractMethodAndParameters($rawMethod);

        return $this->callMethod($method, $params + $this->formInputs);
    }

    public function key($selector, $key)
    {
        $node = $this->querySelector($selector);

        $rawAcceptableKeys = $node->attr("{$this->prefix}--colon--keydown-modifiers");
        $acceptableKeys = explode('.', $rawAcceptableKeys);

        $key = rtrim(ltrim($key, '{'), '}');

        if (in_array($key, $acceptableKeys)) {
            $rawMethod = $node->attr("{$this->prefix}--colon--keydown");
            [$method, $params] = $this->extractMethodAndParameters($rawMethod);

            return $this->callMethod($method, $params);
        }

        return $this;
    }

    protected function extractMethodAndParameters($method)
    {
        if (str_contains($method, '(')) {
            preg_match('/\((.*)\)/', $method, $match);
            $params = explode(', ', $match[1]);
            $method = str_before($method, '(');
        } else {
            $params = [];
        }

        return [$method, $params];
    }
}
