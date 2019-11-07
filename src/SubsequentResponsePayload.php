<?php

namespace Livewire;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

class SubsequentResponsePayload extends ResponsePayload implements Arrayable, Jsonable
{
    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'dom' => $this->injectComponentDataAsHtmlAttributesInRootElement(
                $this->dom, ['id' => $this->id]
            ),
            'data' => $this->data,
            'checksum' => $this->checksum,
            'children' => $this->children,
            'eventQueue' => $this->eventQueue,
            'redirectTo' => $this->redirectTo,
            'dirtyInputs' => $this->dirtyInputs,
            'events' => $this->events,
            'fromPrefetch' => $this->fromPrefetch,
            'gc' => $this->gc,
        ];
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
