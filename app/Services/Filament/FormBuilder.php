<?php

namespace App\Services\Filament;

use App\Services\Filament\Concerns\InteractsWithContainers;
use App\Services\Filament\Concerns\InteractsWithSteps;
use App\Services\Filament\Contracts\FormFieldInterface;
use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;

class FormBuilder
{
    use InteractsWithContainers;
    use InteractsWithSteps;

    protected string $sort = '';

    protected string $direction = '';

    protected ?Closure $callableRequiredConditionClosure = null;

    protected array $conditionalColumns = [];

    /**
     * Set the form order.
     */
    public function sort(string $sort, string $direction): static
    {
        if (strpos($sort, '.')) {
            throw new Exception('Relacionamentos não são aceitos para ordenação!');
        }

        $this->sort = $sort;

        $this->direction = $direction;

        return $this;
    }

    public function getSort(): string
    {
        return $this->sort;
    }

    public function getDirection(): string
    {
        return $this->direction;
    }

    /**
     * Get the form group
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * The query.
     */
    protected ?EloquentBuilder $query = null;

    /**
     * The readonly status.
     */
    protected bool $readonly = false;

    /**
     * Create a new form builder instance.
     */
    public static function make(): static
    {
        return new self();
    }

    /**
     * Set the model.
     */
    public function model(string|Closure|FormFieldInterface $model): static
    {
        $queryModel = null;

        if ($model instanceof FormFieldInterface) {
            $queryModel = $model;
        }

        if (new $model() instanceof FormFieldInterface) {
            $queryModel = new $model();
        }

        if ($queryModel instanceof Model) {
            $this->query = $queryModel::query();

            return $this;
        }

        throw new Exception('The model must be an instance of FormFieldInterface.');
    }

    /**
     * Modify the query using the given callback.
     */
    public function modifyQueryUsing(Closure $callback): static
    {
        // Modifica a query existente usando o callback
        $this->query = $callback($this->query);

        // Retorna a instância para permitir chaining
        return $this;
    }

    /**
     * Get the query.
     */
    public function getQuery(): EloquentBuilder
    {
        return $this->query;
    }

    /**
     * Determine if the form is readonly.
     */
    public function readonly(bool $readonly = true): static
    {
        $this->readonly = $readonly;

        return $this;
    }

    /**
     * Determine if the form is readonly.
     */
    public function isReadonly(): bool
    {
        return $this->readonly;
    }

    public function requiredCondition(Closure $callback): static
    {
        $this->callableRequiredConditionClosure = $callback;

        return $this;
    }

    public function evaluateRequiredCondition(FormFieldInterface $field): bool
    {
        if (isset($this->callableRequiredConditionClosure) && is_callable($this->callableRequiredConditionClosure)) {
            return ($this->callableRequiredConditionClosure)($field);
        }

        return false;
    }
}

