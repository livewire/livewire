<?php

namespace Livewire\Features\SupportTesting;

use Livewire\Drawer\Utils;

class ComponentState
{
    function __construct(
        protected $component,
        protected $response,
        protected $view,
        protected $html,
        protected $snapshot,
        protected $effects,
    ) {}

    function getComponent() {
        return $this->component;
    }

    function getSnapshot()
    {
        return $this->snapshot;
    }

    function getSnapshotData()
    {
        return $this->untupleify($this->snapshot['data']);
    }

    function getEffects()
    {
        return $this->effects;
    }

    function getView()
    {
        return $this->view;
    }

    function getResponse()
    {
        return $this->response;
    }

    function untupleify($payload) {
        $value = Utils::isSyntheticTuple($payload) ? $payload[0] : $payload;

        if (is_array($value)) {
            foreach ($value as $key => $child) {
                $value[$key] = $this->untupleify($child);
            }
        }

        return $value;
    }

    function getHtml($stripInitialData = false)
    {
        $html = $this->html;

        if ($stripInitialData) {
            $removeMe = (string) str($html)->betweenFirst(
                'wire:snapshot="', '"'
            );

            $html = str_replace($removeMe, '', $html);

            $removeMe = (string) str($html)->betweenFirst(
                'wire:effects="', '"'
            );

            $html = str_replace($removeMe, '', $html);
        }

        return $html;
    }
}
