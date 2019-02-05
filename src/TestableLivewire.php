<?php

namespace Livewire;

use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Assert as PHPUnit;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Validation\ValidationException;

class TestableLivewire
{
    protected $prefix;
    protected $component;
    protected $rawDom;

    public function __construct($component, $prefix)
    {
        $this->prefix = $prefix;
        $this->component = $component;
        $this->component->mounted();
        $this->resetDom();
    }

    public function type($selector, $text)
    {
        $node = $this->crawler->filter($selector);

        throw_unless($node->count(), new \Exception('Can\'t find element with selector: [' . $selector . ']'));

        if ($dataName = $node->attr("{$this->prefix}--form--sync")) {
            $form = $node->parents()->filter("[{$this->prefix}--form]");

            $formName = $form->attr("{$this->prefix}--form");
            $this->component->formInput($formName, $dataName, $text);
            $this->resetDom();
        } elseif ($dataName = $node->attr("{$this->prefix}--sync")) {
            $this->component->syncInput($dataName, $text);
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

        $methodName = $node->attr("{$this->prefix}--click");

        throw_unless($methodName, new \Exception("Cannot find value for [{$this->prefix}:click] on element: [" . $selector . ']'));

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

        $form = $button->parents()->filter("[{$this->prefix}--submit]");

        $this->component->{$form->attr("{$this->prefix}--submit")}($this->inputs);
        $this->resetDom();

        return $this;
    }

    public function key($selector, $key)
    {
        throw_unless($key === '{enter}', new \Exception('I havent implemented any other key besides enter yet, sorry'));

        $node = $this->crawler->filter($selector);

        $methodName = $node->attr("{$this->prefix}--keydown--enter");
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
        $callback($this->component->view()->{$nameOfViewVariable});

        return $this;
    }

    public function convertColonsToDoubleDashes($input)
    {
        return
            str_replace("{$this->prefix}--form.sync", "{$this->prefix}--form--sync",
                str_replace("{$this->prefix}--keydown.enter", "{$this->prefix}--keydown--enter",
                    str_replace("{$this->prefix}:", "{$this->prefix}--",
                        str_replace("{$this->prefix}:", "{$this->prefix}--",
                            $input
                        )
                    )
                )
            );
    }

    public function resetDom()
    {
        try {
            $this->crawler = new Crawler(
                $this->rawDom = $this->convertColonsToDoubleDashes($this->component->view()->render())
            );
        } catch (ValidationException $th) {
            dd('hye');
        }
    }
}
