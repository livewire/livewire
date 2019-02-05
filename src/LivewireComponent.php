<?php

namespace Livewire;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Fluent;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Opis\Closure\SerializableClosure;

abstract class LivewireComponent
{
    protected $hashes = [];
    protected $exemptFromHashDiffing = [];
    protected $connection;
    protected $component;
    protected $forms;

    protected $propertiesExemptFromHashing = [
        'hashes', 'exemptFromHashDiffing', 'connection',
        'component', 'forms',
    ];

    public function __construct($connection, $component)
    {
        $this->connection = $connection;
        $this->component = $component;
        $this->forms = new LivewireFormCollection;
    }

    abstract public function render();

    public function mounted()
    {
        //
    }

    public function sync($model, $value)
    {
        if (method_exists($this, 'onSync' . studly_case($model))) {
            $this->{'onSync' . studly_case($model)}($value);
        }

        $this->exemptFromHashDiffing[] = $model;

        $this->{$model} = $value;
    }

    public function makeSerializable($callback)
    {
        return new SerializableClosure($callback);
    }

    public function formInput($form, $input, $value)
    {
        throw_unless($this->forms[$form], new \Exception('register form: '.$form));

        $this->forms[$form]->updateValue($input, $value);
    }

    public function formsThatNeedInputRefreshing()
    {
        return array_filter(
            array_map(function ($form, $name) {
                return $form->needsInputRefresh() ? $name : false;
            }, $this->forms->toArray(), array_keys($this->forms->toArray()))
        );
    }

    public function clearFormRefreshes()
    {
        foreach ($this->forms->toArray() as $form) {
            $form->refreshed();
        }
    }

    public function onRequest()
    {
        $this->createHashesForDiffing();
    }

    public function createHashesForDiffing()
    {
        $properties = array_map(function ($prop) {
            return $prop->getName();
        }, (new \ReflectionClass($this))->getProperties());

        $properties = array_diff($properties, $this->propertiesExemptFromHashing);

        foreach ($properties as $property) {
            // For now only has strings and numbers to not be too slow.
            if (is_null($this->{$property}) || is_string($this->{$property}) || is_numeric($this->{$property})) {
                $this->hashes[$property] = crc32($this->{$property});
            }
        }
    }

    public function dirtySyncs()
    {
        $pile = [];
        foreach ($this->hashes as $prop => $hash) {
            if (in_array($prop, $this->exemptFromHashDiffing)) {
                continue;
            }
            // For now only has strings and numbers to not be too slow.
            if (crc32($this->{$prop}) !== $hash) {
                $pile[] = $prop;
            }
        }

        return $pile;
    }

    public function clearSyncRefreshes()
    {
        $this->hashes = [];
        $this->exemptFromHashDiffing = [];
    }

    public function refresh()
    {
        $this->connection->send(json_encode([
            'component' => $this->component,
            'dom' => $this->render()->render(),
        ]));
    }

    public function view($errors = null)
    {
        $errors = $errors ? (new ViewErrorBag)->put('default', $errors) : new ViewErrorBag;

        return $this->render()->with('forms', $this->forms)->with('errors', $errors);
    }

    public function __toString()
    {
        return $this->view();
    }
}
