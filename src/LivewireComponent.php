<?php

namespace Livewire;

use Illuminate\Support\Fluent;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Opis\Closure\SerializableClosure;

abstract class LivewireComponent
{
    protected $connection;
    protected $component;
    protected $forms;

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
