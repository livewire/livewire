<?php

if (! function_exists('synthetic')) {
    function synthetic($subject) {
        return app('synthetic')->synthesize($subject);
    }
}

