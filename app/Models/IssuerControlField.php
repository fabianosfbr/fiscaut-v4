<?php

namespace App\Models;

use App\Enums\FieldAttributesEnum;
use App\Enums\FieldTypesEnum;
use App\Services\Filament\Contracts\FormFieldInterface;
use App\Services\Filament\Contracts\HasAcceptedFileTypes;
use App\Services\Filament\Contracts\HasOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IssuerControlField extends Model implements FormFieldInterface, HasOptions, HasAcceptedFileTypes
{
    protected $guarded = ['id'];

    protected $casts = [
        'options' => 'array',
        'accepted_types' => 'array',
        'required' => 'boolean',
        'order' => 'integer',
    ];

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(Issuer::class);
    }

    public function groupControl(): BelongsTo
    {
        return $this->belongsTo(IssuerGroupControl::class, 'issuer_group_control_id');
    }

    public function controls(): HasMany
    {
        return $this->hasMany(IssuerControl::class, 'issuer_control_field_id');
    }

    public function getName(): string
    {
        return $this->key;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getType(): FieldTypesEnum|string
    {
        return FieldTypesEnum::tryFrom($this->type) ?? $this->type;
    }

    public function getFieldAttribute(): null|string|FieldAttributesEnum
    {
        if (! $this->attribute) {
            return null;
        }

        return FieldAttributesEnum::tryFrom($this->attribute) ?? $this->attribute;
    }

    public function getDefaultGroup(): ?string
    {
        return $this->groupControl?->name ?? 'default';
    }

    public function getGroupFromRelationship(string $group): string
    {
        return data_get($this, $group) ?? $this->getDefaultGroup() ?? 'default';
    }

    public function getRequired(): bool
    {
        return (bool) $this->required;
    }

    public function options(): Collection|array
    {
        return new Collection($this->options ?? []);
    }

    public function getOptions(): array
    {
        return $this->options ?? [];
    }

    public function getAcceptedFileTypes(): ?array
    {
        return $this->accepted_types ?? null;
    }
}
