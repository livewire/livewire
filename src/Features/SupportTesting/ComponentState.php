<?php

namespace Livewire\Features\SupportTesting;

use Livewire\Drawer\Utils;

class ComponentState
{
    public function __construct(
        protected $component,
        protected $response,
        protected $view,
        protected $html,
        protected $snapshot,
        protected $effects,
    ) {
    }

    public function getComponent()
    {
        return $this->component;
    }

    public function getSnapshot()
    {
        return $this->snapshot;
    }

    public function getSnapshotData()
    {
        return $this->untupleify($this->snapshot['data']);
    }

    public function getEffects()
    {
        return $this->effects;
    }

    public function getView()
    {
        return $this->view;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function untupleify($payload)
    {
        $value = Utils::isSyntheticTuple($payload) ? $payload[0] : $payload;

        if (is_array($value)) {
            foreach ($value as $key => $child) {
                $value[$key] = $this->untupleify($child);
            }
        }

        return $value;
    }

    public function getHtml($stripInitialData = false)
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
