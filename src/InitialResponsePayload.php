<?php

namespace Livewire;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

class InitialResponsePayload extends ResponsePayload implements Htmlable, Arrayable, Jsonable
{
    public $instance;
    public $id;
    public $dom;
    public $data;
    public $name;
    public $checksum;
    public $children;
    public $events;

    public function __construct($data)
    {
        // "instance" is here because we need it for testing,
        // notice it's not included in the "toArray" method.
        $this->instance = $data['instance'];
        $this->id = $data['id'];
        $this->dom = $data['dom'];
        $this->data = $data['data'];
        $this->name = $data['name'];
        $this->checksum = $data['checksum'];
        $this->children = $data['children'];
        $this->events = $data['events'];
    }

    public function toHtml()
    {
        return $this->injectComponentDataAsHtmlAttributesInRootElement(
            $this->dom,
            $this->toArray()
        );
    }

    public function __toString()
    {
        return $this->toHtml();
    }

    public function toArray()
    {
        // Notice "dom" is missing from this array.
        return [
            'id' => $this->id,
            'data' => $this->data,
            'name' => $this->name,
            'checksum' => $this->checksum,
            'children' => $this->children,
            'events' => $this->events,
        ];
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    public function injectComponentDataAsHtmlAttributesInRootElement($dom, $data)
    {
        $prefix = app('livewire')->prefix();

        $attributesFormattedForHtmlElement = collect($data)
            ->mapWithKeys(function ($value, $key) use ($prefix) {
                return ["{$prefix}:{$key}" => $this->escapeStringForHtml($value)];
            })->map(function ($value, $key) {
                return sprintf('%s="%s"', $key, $value);
            })->implode(' ');

        preg_match('/<([a-zA-Z0-9\-]*)/', $dom, $matches, PREG_OFFSET_CAPTURE);
        $tagName = $matches[1][0];
        $lengthOfTagName = strlen($tagName);
        $positionOfFirstCharacterInTagName = $matches[1][1];

        return substr_replace(
            $dom,
            ' '.$attributesFormattedForHtmlElement,
            $positionOfFirstCharacterInTagName + $lengthOfTagName,
            0
        );
    }

    public function escapeStringForHtml($subject)
    {
        if (is_string($subject) || is_numeric($subject)) {
            return htmlspecialchars($subject);
        }

        return htmlspecialchars(json_encode($subject));
    }

    public function getRootElementTagName()
    {
        preg_match('/<([a-zA-Z0-9\-]*)/', $this->dom, $matches, PREG_OFFSET_CAPTURE);

        return $matches[1][0];
    }
}
