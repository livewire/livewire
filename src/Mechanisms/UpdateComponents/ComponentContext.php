<?php

namespace Livewire\Mechanisms\UpdateComponents;

use AllowDynamicProperties;
use Livewire\Drawer\Utils;

#[AllowDynamicProperties]
class ComponentContext
{
    public $target;
    public $component;
    public $effects = [];
    public $meta = [];
    public $initial;
    public $dataFromParent = [];

    public function __construct($target, $initial, $dataFromParent = [])
    {
        $this->target = $target;
        $this->component = $target;
        $this->initial = $initial;
        $this->dataFromParent = $dataFromParent;
    }

    public function addEffect($key, $value)
    {
        if (is_array($key)) {
            foreach ($key as $iKey => $iValue) $this->addEffect($iKey, $iValue);
            return;
        }

        $this->effects[$key] = $value;
    }

    public function pushEffect($key, $value, $iKey = null)
    {
        if (! isset($this->effects[$key])) $this->effects[$key] = [];

        if ($iKey) {
            $this->effects[$key][$iKey] = $value;
        } else {
            $this->effects[$key][] = $value;
        }
    }

    public function addMeta($key, $value)
    {
        $this->meta[$key] = $value;
    }

    public function pushMeta($key, $value, $iKey = null)
    {
        if (! isset($this->meta[$key])) $this->meta[$key] = [];

        if ($iKey) {
            $this->meta[$key][$iKey] = $value;
        } else {
            $this->meta[$key][] = $value;
        }
    }

    public function retrieve()
    {
        return [$this->meta, $this->effects];
    }
}
