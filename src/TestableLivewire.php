<?php

namespace Livewire;

use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Assert as PHPUnit;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Validation\ValidationException;
use Livewire\Connection\TestConnectionHandler;

class TestableLivewire
{
    protected $prefix;
    protected $component;
    protected $inputs = [];
    public $rawDom;

    public function __construct($component, $prefix)
    {
        [$dom, $id, $serialized] = app('livewire')->mount($component);

        $this->prefix = $prefix;
        $this->serialized = $serialized;

        $this->crawler = new Crawler(
            $this->rawDom = $this->convertColonsToDoubleDashes($dom)
        );
    }

    public function dump()
    {
        echo $this->rawDom;

        return $this;
    }

    public function type($selector, $text)
    {
        $node = $this->crawler->filter($this->formatSelector($selector));

        throw_unless($node->count(), new \Exception('Can\'t find element with selector: [' . $selector . ']'));

        if (($dataName = $node->attr("{$this->prefix}--colon--model")) || ($dataName = $node->attr("{$this->prefix}--colon--model--dot--lazy"))) {
            $this->syncInput($dataName, $text);
        }

        if ($inputName = $node->attr('name')) {
           $this->inputs[$inputName] = $text;
        }

        return $this;
    }

    public function click($selector)
    {
        $node = $this->crawler->filter($this->formatSelector($selector));

        throw_unless($node->count(), new \Exception('Can\'t find element with selector: [' . $selector . ']'));


        $methodName = $node->attr("{$this->prefix}--colon--click");

        throw_unless($methodName, new \Exception("Cannot find value for [{$this->prefix}:click] on element: [" . $selector . ']'));

        if (str_contains($methodName, '(')) {
            preg_match('/\((.*)\)/', $methodName, $match);
            $parameters = explode(', ', $match[1]);
            $methodName = str_before($methodName, '(');
        } else {
            $parameters = [];
        }

        $result = (new TestConnectionHandler)->handle('fireMethod', [
            'method' => $methodName,
            'params' => $parameters,
            'ref' => null,
        ], $this->serialized);

        $this->serialized = $result['serialized'];
        $this->crawler = new Crawler(
            $this->rawDom = $this->convertColonsToDoubleDashes($result['dom'])
        );

        return $this;
    }

    public function press($selector)
    {
        $button = $this->crawler->filter($this->formatSelector($selector));

        $form = $button->parents()->filter("[{$this->prefix}--submit]");

        $this->component->fireMethod($form->attr("{$this->prefix}--submit"), $this->inputs);
        $this->resetDom();

        return $this;
    }

    public function key($selector, $key)
    {
        throw_unless($key === '{enter}', new \Exception('I havent implemented any other key besides enter yet, sorry'));

        $node = $this->crawler->filter($this->formatSelector($selector));

        $methodName = $node->attr("{$this->prefix}--colon--keydown--dot--enter");

        $this->fireMethod($methodName);

        return $this;
    }

    public function assertDontSeeIn($selector, $text)
    {
        return $this->assertSeeIn($selector, $text, $negate = true);
    }

    public function assertSeeIn($selector, $text, $negate = false)
    {
        $source = $this->crawler->filter($this->formatSelector($selector))->text();

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

    public function assertVisible($selector)
    {
        $nodes = $this->crawler->filter($this->formatSelector($selector));

        PHPUnit::assertGreaterThan(0, $nodes->count());

        return $this;
    }

    public function fromView($nameOfViewVariable, $callback)
    {
        $callback($this->component->view()->{$nameOfViewVariable});

        return $this;
    }

    public function convertColonsToDoubleDashes($input)
    {
        preg_match_all('/(wire:.*)="/', $input, $matches);
        $rawDirectives = $matches[1];

        foreach ($rawDirectives as $rawDirective) {
            $replacedColons = str_replace(':', '--colon--', $rawDirective);
            $replacedPeriods = str_replace('.', '--dot--', $replacedColons);
            $input = str_replace($rawDirective, $replacedPeriods, $input);
        }

        return $input;
    }

    public function formatSelector($selector)
    {
        if (strpos($selector, '@') === 0) {
            $selector = '[wire:ref="'.substr($selector, 1, strlen($selector)).'"]';
        }

        return $this->convertColonsToDoubleDashes($selector);
    }

    public function __call($method, $params)
    {
        $return = $this->component->fireMethod($method, $params);

        $this->resetDom();

        return $return;
    }

    public function __get($property)
    {
        return $this->component->wrapped->getPropertyValue($property);
    }

    public function resetDom()
    {
        try {
            $this->crawler = new Crawler(
                $this->rawDom = $this->convertColonsToDoubleDashes($this->component->output())
            );
        } catch (ValidationException $th) {
            dd('hye');
        }
    }

    public function tap($callback)
    {
        $callback($this);

        return $this;
    }

    public function toHtml($node)
    {
        return $node->getNode(0)->ownerDocument->saveXML($node->getNode(0));
    }

    public function fireMethod($method, $parameters = [])
    {
        $result = (new TestConnectionHandler)->handle('fireMethod', [
            'method' => $method,
            'params' => $parameters,
            'ref' => null,
        ], $this->serialized);

        $this->serialized = $result['serialized'];
        $this->crawler = new Crawler(
            $this->rawDom = $this->convertColonsToDoubleDashes($result['dom'])
        );
    }

    public function syncInput($name, $value)
    {
        $result = (new TestConnectionHandler)->handle('syncInput', [
            'name' => $name,
            'value' => $value,
        ], $this->serialized);

        $this->serialized = $result['serialized'];
        $this->crawler = new Crawler(
            $this->rawDom = $this->convertColonsToDoubleDashes($result['dom'])
        );
    }
}
