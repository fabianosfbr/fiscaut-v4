<?php

namespace App\Services\Filament;

use Closure;
use Filament\Schemas\Components\Component;

class FormBuilderRender
{
    protected FormBuilder $formBuilder;

    public static function make(): self
    {
        return new self;
    }

    public function form($type): self
    {
        $this->formBuilder = FormBuilder::make()
            ->model($type);

        return $this;
    }

    public function modifyQueryUsing(Closure $closure): self
    {
        $this->formBuilder->modifyQueryUsing($closure);

        return $this;
    }

    public function group(string $group): self
    {
        $this->formBuilder->group($group);

        return $this;
    }

    public function sort(string $sort, string $direction = 'asc'): self
    {
        $this->formBuilder->sort($sort, $direction);

        return $this;
    }

    public function readonly(bool $readonly = true): self
    {
        $this->formBuilder->readonly($readonly);

        return $this;
    }

    public function defaultStepClass(string $stepClass): self
    {
        $this->formBuilder->defaultStepClass($stepClass);

        return $this;
    }

    public function container(Component|string|Closure $container): self
    {
        $this->formBuilder->container($container);

        return $this;
    }

    public function requiredCondition(Closure $callback): self
    {
        $this->formBuilder->requiredCondition($callback);

        return $this;
    }

    public function render()
    {
        return FormGeneratorService::make(
            $this->formBuilder
        );
    }
}
