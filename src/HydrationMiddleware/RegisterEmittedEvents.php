<?php

namespace Livewire\HydrationMiddleware;

use Livewire\Token;

class RegisterEmittedEvents implements HydrationMiddleware
{
    public static function hydrate($unHydratedInstance, $request)
    {
        //
    }

    public static function dehydrate($instance, $response)
    {
        $tokens = [];
        $response->eventQueue = static::extractTokens($instance->getEventQueue(), $tokens);
        $response->dispatchQueue = $instance->getDispatchQueue();
        $response->tokens = $tokens;
    }

    protected static function extractTokens($queue, &$tokens)
    {
        return collect($queue)->map(function ($event) use (&$tokens) {
            // Register tokens
            static::handleOutgoingEmits($event, $tokens);

            // Call back to those tokens
            static::handleOutgoingEmitTos($event);

            return $event;
        })->toArray();
    }

    protected static function handleOutgoingEmits(array &$event, array &$tokens)
    {
        $event['params'] = collect($event['params'])->map(function ($param) use (&$tokens) {
            // Any parameter that is a Token is converted
            // to its value, i.e the human-readable part
            if ($param instanceof Token) {
                $token = $param->value();
                $tokens[] = $token;
                return null;
            }
            return $param;
        })->filter()->values()->toArray();
    }

    protected static function handleOutgoingEmitTos(array &$event)
    {
        if (isset($event['to']) && $event['to'] instanceof Token) {
            // 'to' is the actual token instance
            $token = $event['to'];
            // replace it with its string notation
            $event['to'] = (string) $token;
            // unshift first param unto params array since
            // emitTo called with token doesn't require event
            // name.
            array_unshift($event['params'], $event['event']);
            // change event name to 'token' a special event
            $event['event'] = 'token';
        }
    }
}
