<?php

namespace App\Services\Filament\Concerns;

use Closure;
use Filament\Schemas\Components\Component;
use Filament\Support\Concerns\CanBeContained;

trait InteractsWithContainers
{
    /**
     * Specifies if the form is containered.
     */
    protected bool|Closure $isContained = false;

    /**
     * The form container.
     */
    protected ?Component $container = null;

    /**
     * Set the form container type
     *
     * @param  Component  $container
     */
    public function container(
        Component|string|Closure $container
    ): self {
        $formContainer = null;

        if ($container instanceof Component) {
            $formContainer = $container;
        }

        $reflection = new \ReflectionClass($container);

        if ($reflection->isInstantiable() && $reflection->isSubclassOf(Component::class)) {
            $formContainer = new $container;
        }

        if (! in_array(CanBeContained::class, class_uses($formContainer))) { // @phpstan-ignore-line
            throw new \Exception('The container must be a valid container.');
        }

        $this->container = $formContainer;

        $this->isContained = true;

        return $this;
    }

    /**
     * Get the form container.
     */
    public function getContainer(): ?Component
    {
        return $this->container;
    }

    /**
     * Specifies if the form is containered.
     */
    public function isContained(): bool
    {
        return $this->isContained;
    }
}
