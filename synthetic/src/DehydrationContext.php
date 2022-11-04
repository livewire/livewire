<?php

namespace Synthetic;

class DehydrationContext
{
    public $root;
    public $target;
    public $effects = [];
    public $meta = [];
    public $annotations;
    public $initial;
    public $path;

    public function __construct($root, $target, $initial, $annotationsFromParent, $path)
    {
        $this->root = $root;
        $this->target = $target;
        $this->initial = $initial;
        $this->annotations = Utils::getAnnotations($target);
        $this->annotationsFromParent = $annotationsFromParent;
        $this->path = $path;
    }

    public function addEffect($key, $value)
    {
        if (is_array($key)) {
            foreach ($key as $iKey => $iValue) $this->addEffect($iKey, $iValue);
        }

        $this->effects[$key] = $value;
    }

    public function addMeta($key, $value)
    {
        if (is_array($key)) {
            foreach ($key as $iKey => $iValue) $this->addEffect($iKey, $iValue);
        }

        $this->meta[$key] = $value;
    }

    public function annotations()
    {
        return $this->annotations;
    }

    public function annotationsFromParent()
    {
        return $this->annotationsFromParent;
    }

    public function retrieve()
    {
        return [$this->meta, $this->effects];
    }

    // function rules()
    // {

    // }
}
