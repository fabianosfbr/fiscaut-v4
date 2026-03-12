<?php

namespace App\Services\Filament\Concerns;
use Filament\Schemas\Components\Wizard\Step;
use ReflectionClass;

trait InteractsWithSteps
{
    /**
     * The form group.
     */
    protected string $group = '';

    /**
     * The form has steps.
     */
    protected bool $hasSteps = false;

    /**
     * The form step class.
     */
    protected ?string $stepClass = null;

    /**
     * Set the form group.
     */
    public function group(string $group): static
    {
        $this->group = $group;

        $groupArray = explode('.', $group);

        if (count($groupArray) > 1) {
            unset($groupArray[count($groupArray) - 1]);

            $this->query->with(implode('.', $groupArray));
        }

        return $this;
    }

    /**
     * Get the form group
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * Set the form step class.
     */
    public function defaultStepClass(string $stepClass): static
    {
        $reflection = new ReflectionClass($stepClass);

        if ($reflection->isInstantiable() && $reflection->isSubclassOf(Step::class)) {
            $this->stepClass = $stepClass;

            return $this;
        }

        throw new \Exception('The step class must be a valid step class.');
    }

    /**
     * Set the form steps.
     */
    public function hasSteps(bool $hasSteps = true): static
    {
        if ($this->isContained) {
            $this->hasSteps = true;

            return $this;
        }

        $this->hasSteps = $hasSteps;

        return $this;
    }

    /**
     * Set the form steps.
     */
    public function getStepClass(): ?string
    {
        if (! $this->stepClass) {
            $this->stepClass = Step::class;
        }

        return $this->stepClass;
    }
}

