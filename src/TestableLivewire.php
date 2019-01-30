<?php

namespace Livewire;

use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Assert as PHPUnit;
use Symfony\Component\DomCrawler\Crawler;

class TestableLivewire
{
    protected $component;
    protected $rawDom;

    public function __construct($component)
    {
        $this->component = $component;
        $this->resetDom();
    }

    public function type($selector, $text)
    {
        $node = $this->crawler->filter($selector);

        throw_unless($node->count(), new \Exception('Can\'t find element with selector: [' . $selector . ']'));

        if ($dataName = $node->attr('livewire--sync')) {
            $this->component->sync($dataName, $text);
            $this->resetDom();
        }

        if ($inputName = $node->attr('name')) {
           $this->inputs[$inputName] = $text;
        }

        return $this;
    }

    public function click($selector)
    {
        $node = $this->crawler->filter($selector);

        throw_unless($node->count(), new \Exception('Can\'t find element with selector: [' . $selector . ']'));

        $methodName = $node->attr('livewire--click');

        throw_unless($methodName, new \Exception('Cannot find value for [livewire:click] on element: [' . $selector . ']'));

        if (str_contains($methodName, '(')) {
            preg_match('/\((.*)\)/', $methodName, $match);
            $parameters = explode(', ', $match[1]);
            $methodName = str_before($methodName, '(');
        } else {
            $parameters = [];
        }

        $this->component->{$methodName}(...$parameters);
        $this->resetDom();

        return $this;
    }

    public function press($selector)
    {
        $button = $this->crawler->filter($selector);

        $form = $button->parents()->filter('[livewire--submit]');

        $this->component->{$form->attr('livewire--submit')}($this->inputs);
        $this->resetDom();

        return $this;
    }

    public function key($selector, $key)
    {
        throw_unless($key === '{enter}', new \Exception('I havent implemented any other key besides enter yet, sorry'));

        $node = $this->crawler->filter($selector);

        $methodName = $node->attr('livewire--keydown--enter');
        $this->component->{$methodName}();

        $this->resetDom();

        return $this;
    }

    public function assertDontSeeIn($selector, $text)
    {
        return $this->assertSeeIn($selector, $text, $negate = true);
    }

    public function assertSeeIn($selector, $text, $negate = false)
    {
        $source = $this->crawler->filter($selector)->text();

        $method = $negate ? 'assertNotContains' : 'assertContains';
        PHPUnit::{$method}((string)$text, strip_tags($source));

        return $this;
    }

    public function assertDontSee($text)
    {
        return $this->assertSee($text, $negate = true);
    }

    public function assertSee($text, $negate = false)
    {
        $source = $this->crawler->text();

        $method = $negate ? 'assertNotContains' : 'assertContains';
        PHPUnit::{$method}((string)$text, strip_tags($source));

        return $this;
    }

    public function fromView($nameOfViewVariable, $callback)
    {
        $callback($this->component->render()->{$nameOfViewVariable});

        return $this;
    }

    public function convertColonsToDoubleDashes($input)
    {
        return
            str_replace('livewire--keydown.enter', 'livewire--keydown--enter',
                str_replace('livewire:', 'livewire--',
                    str_replace('wire:', 'wire--',
                        $input
                    )
                )
            );
    }

    public function resetDom()
    {
        $this->crawler = new Crawler(
            $this->rawDom = $this->convertColonsToDoubleDashes($this->component->render()->render())
        );
    }
}
