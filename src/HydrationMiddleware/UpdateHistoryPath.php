<?php

namespace Livewire\HydrationMiddleware;

use Illuminate\Support\Str;
use Livewire\Exceptions\CannotCombineHistoryPathAndQueryStringException;
use Livewire\Exceptions\InvalidHistoryPathException;

class UpdateHistoryPath implements HydrationMiddleware
{
    public static function hydrate($instance, $request)
    {
        //
    }

    public static function dehydrate($instance, $response)
    {
        if (! empty($url = $instance->mapStateToUrl())) {
        	$root = url()->to('/');
	        
        	throw_if(isset($response->updatesQueryString), new CannotCombineHistoryPathAndQueryStringException($instance->getName()));
        	throw_unless(Str::startsWith($url, $root), new InvalidHistoryPathException($instance->getName()));
        	
            $response->historyPath = Str::after($url, $root);
        }
    }
}
