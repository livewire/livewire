<?php

namespace Livewire\Mechanisms\HandleComponents;

use AllowDynamicProperties;

#[AllowDynamicProperties]
class ComponentContext
{
    public $effects = [];
    public $memo = [];

    public function __construct(
        public $component,
        public $mounting = false,
    ) {}

    public function isMounting()
    {
        return $this->mounting;
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

    public function addMemo($key, $value)
    {
        $this->memo[$key] = $value;
    }

    public function pushMemo($key, $value, $iKey = null)
    {
        if (! isset($this->memo[$key])) $this->memo[$key] = [];

        if ($iKey) {
            $this->memo[$key][$iKey] = $value;
        } else {
            $this->memo[$key][] = $value;
        }
    }
}
