<?php

namespace Synthetic;

function wrap($subject) {
    return new Wrapped($subject);
}

function trigger($name, &...$params) {
    return app('synthetic')->trigger($name, ...$params);
}

function on($name, $callback) {
    return app('synthetic')->on($name, $callback);
}

function before($name, $callback) {
    return app('synthetic')->before($name, $callback);
}

function after($name, $callback) {
    return app('synthetic')->after($name, $callback);
}



