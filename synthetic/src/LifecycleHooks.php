<?php

namespace Synthetic;

class LifecycleHooks
{
    static function booted($target) {
        if (! is_object($target)) return;

        if (method_exists($target, 'booted')) $target->booted();
    }

    static function updating($target, $key, $value) {
        if (! is_object($target)) return;

        if (method_exists($target, 'updating')) $target->updating($key, $value);
    }

    static function updatingSelf($target, $key, $value) {
        if (! is_object($target)) return;

        if (method_exists($target, 'updatingSelf')) $target->updatingSelf($key, $value);
    }

    static function updated($target, $key, $value) {
        if (! is_object($target)) return;

        if (method_exists($target, 'updated')) $target->updated($key, $value);
    }

    static function updatedSelf($target, $key, $value) {
        if (! is_object($target)) return;

        if (method_exists($target, 'updatedSelf')) $target->updatedSelf($key, $value);
    }
}
