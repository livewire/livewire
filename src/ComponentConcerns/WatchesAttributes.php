<?php

namespace Livewire\ComponentConcerns;

trait WatchesAttributes
{
    protected $watch = [];

    protected function getWatchers()
    {
        return $this->watch;
    }

    protected function watch($key, $value)
    {
        foreach($this->getWatchers() as $compare => $callback) {
            if (! $this->hasWatcher($key, $compare)) {
                continue;
            }

            if (is_callable($callback)) {
                $callback($key, $value);
            }

            if (is_string($callback) && method_exists($this, $callback)) {
                call_user_func_array([$this, $callback], [$key, $value]);
            }
        }
    }

    protected function hasWatcher($key, $compare)
    {
        $pattern = str_replace('\*', '[^\.]+', preg_quote($compare));

        preg_match('/^'.$pattern.'/', $key, $matches);

        return count($matches) > 0;
    }
}
