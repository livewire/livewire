<?php

namespace Synthetic;

trait SyntheticTesting {
    function test($target) {
        return new TestableSynthetic($target);
    }
}
