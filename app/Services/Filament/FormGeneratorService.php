<?php

namespace App\Services\Filament;

use App\Services\Filament\Contracts\FormFieldInterface;
use App\Services\Filament\Fields\FieldGeneratorService;
use App\Services\Filament\FormBuilder;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class FormGeneratorService
{
/**
     * The filament fields.
     */
    protected static array $fields = [];

    private static ?FormBuilder $formBuilder = null;

    /**
     * Generate the form.
     */
    public static function make(FormBuilder $formBuilder): array|Component
    {
        self::$formBuilder = $formBuilder;

        self::setFields(
            query: $formBuilder->getQuery(),
            group: $formBuilder->getGroup(),
            sort: $formBuilder->getSort(),
            direction: $formBuilder->getDirection(),
            readonly: $formBuilder->isReadonly()
        );

        if ($formBuilder->isContained()) {
            return self::getContainedContent($formBuilder);
        }

        return array_map(fn ($field): Field => $field['field'], self::$fields);
    }

    /**
     * Set the fields.
     */
    protected static function setFields(
        Builder $query,
        ?string $group = null,
        ?string $sort = null,
        string $direction = 'asc',
        bool $readonly = false
    ): void {
        // Obter os campos da query
        $fields = $query->when($sort, function ($query) use ($sort, $direction) {
            $query->orderBy($sort, $direction);
        })->get();

        if ($fields->isEmpty()) {
            return;
        }

        // Processar os campos
        self::$fields = self::proccessFields(
            fields: $fields,
            group: $group,
            readonly: $readonly
        );
    }

    /**
     * Proccess the fields.
     */
    protected static function proccessFields(
        Collection|array $fields,
        ?string $group = null,
        bool $readonly = false
    ): array {
        // @phpstan-ignore-next-line
        return $fields->map(function ($field) use (
            $group,
            $readonly
        ) {
            return [
                'group' => $group ? $field->getGroupFromRelationship($group) : $field->getDefaultGroup(), // @phpstan-ignore-line
                'field' => self::getFieldFromModel(
                    field: $field,
                    readonly: $readonly
                ),
            ];
        })
            ->toArray();
    }

    /**
     * Get the field from the model.
     */
    protected static function getFieldFromModel(
        Model $field,
        bool $readonly = false
    ): Field {
        if (! $field instanceof FormFieldInterface) {
            throw new \Exception('The field must be an instance of FormFieldInterface.');
        }

        return FieldGeneratorService::make(
            field: $field,
            readonly: $readonly,
            required: self::verifyRequired($field)
        );
    }

    protected static function verifyRequired(FormFieldInterface $field): bool
    {
        return self::$formBuilder->evaluateRequiredCondition($field);
    }

    /**
     * Get the contained content.
     */
    protected static function getContainedContent(FormBuilder $formBuilder): array|Component
    {
        $container = $formBuilder->getContainer();

        if ($container instanceof Section) {
            if (empty(self::$fields)) {
                return [
                    Section::make('Sem campos')
                        ->schema([
                            Placeholder::make('Sem campos'),
                        ])
                        ->columns([
                            'sm' => 1,
                            'md' => 2,
                        ]),
                ];
            }

            $fieldsPerGroup = [];
            foreach (self::$fields as $field) {
                $fieldsPerGroup[$field['group']][] = $field['field'];
            }

            $sections = [];
            foreach ($fieldsPerGroup as $key => $fields) {
                $sections[] = Section::make($key)->schema($fields)->collapsible()->columnSpanFull();
            }

            return $sections;
        }

        $stepClass = $formBuilder->getStepClass();
        $steps = [];
        $fieldsPerGroup = [];

        if (empty(self::$fields)) {
            $component = $container::make()
                ->schema(
                    [
                        $stepClass::make('empty')
                            ->label('Sem campos')
                            ->icon('heroicon-o-x-mark')
                            ->schema([
                                Placeholder::make('Nenhnum campo foi encontrado'),
                            ]),
                    ]
                );

            if ($container instanceof Wizard) {
                $component->skippable();
            }

            return $component
                ->columns([
                    'sm' => 1,
                    'md' => 2,
                ]);
        }

        foreach (self::$fields as $field) {
            $fieldsPerGroup[$field['group']][] = $field['field'];
        }

        foreach ($fieldsPerGroup as $key => $fields) {
            $steps[] = $stepClass::make($key)->schema($fields);
        }

        if (! method_exists($container, 'make')) {
            throw new \Exception('The container must have a make method.');
        }

        $component = $container::make() // @phpstan-ignore-line
            ->schema($steps);

        if ($container instanceof Wizard) {
            $component->skippable();
        }

        return $component->columns([
            'sm' => 1,
            'md' => 2,
        ]);
    }
}
