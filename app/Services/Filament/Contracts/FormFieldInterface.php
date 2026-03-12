<?php

namespace App\Services\Filament\Contracts;

use App\Enums\FieldAttributesEnum;
use App\Enums\FieldTypesEnum;
use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

interface FormFieldInterface
{
    /**
     * Get the field name.
     */
    public function getName(): string;

    /**
     * Get the field label.
     */
    public function getLabel(): string;

    /**
     * Get the field type.
     */
    public function getType(): FieldTypesEnum|string;

    /**
     * Get the field attribute.
     */
    public function getFieldAttribute(): null|string|FieldAttributesEnum;

    /**
     * Get the field group.
     */
    public function getDefaultGroup(): ?string;

    /**
     * Get the group from the relationship.
     */
    public function getGroupFromRelationship(string $group): string;

    /**
     * Get if the field is required.
     */
    public function getRequired(): bool;

    /**
     * Get the field options.
     */
    public function options(): Collection|Closure|HasMany|HasManyThrough|array;
}
