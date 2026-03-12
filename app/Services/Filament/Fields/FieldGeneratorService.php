<?php

namespace App\Services\Filament\Fields;

use App\Enums\FieldAttributesEnum;
use App\Enums\FieldTypesEnum;
use App\Services\Filament\Contracts\CanDehydrateState;
use App\Services\Filament\Contracts\CanHandleFieldState;
use App\Services\Filament\Contracts\FormFieldInterface;
use App\Services\Filament\Contracts\HasAcceptedFileTypes;
use App\Services\Filament\Contracts\HasFileUploadOptions;
use App\Services\Filament\Contracts\HasInputOptions;
use App\Services\Filament\Contracts\HasDependantFields;
use App\Services\Filament\Contracts\HasOptions;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class FieldGeneratorService
{
    /**
     * Generate the field.
     */
    public static function make(
        FormFieldInterface $field,
        bool $readonly = false,
        bool $required = false
    ): Field {
        $component = self::proccessField(
            field: $field
        );

        if ($readonly) {
            $component->disabled();
        }

        if ($required) {
            $component->required()
                ->validationMessages([
                    'required' => 'O campo :Attribute é obrigatório',
                ]); // @phpstan-ignore-line
        }

        return $component;
    }

    /**
     * Proccess the field.
     */
    protected static function proccessField(FormFieldInterface $field): Field
    {
        return match ($field->getType()) { // @phpstan-ignore-line
            FieldTypesEnum::Input => self::mountInputField($field),
            FieldTypesEnum::Select => self::mountSelectField($field),
        };
    }

    /**
     * Get the input field.
     */
    protected static function mountInputField(FormFieldInterface $field): Field
    {
        if ($field instanceof HasDependantFields && $field->hasDependantFields()) {

            $fieldWithDependants = null;

            if ($field->getDependantFieldsContainer()) {

                $fieldWithDependants = $field->getDependantFieldsContainer()
                    ->schema([
                        self::getInputField($field)
                            ->live(onBlur: true)
                            ->reactive()
                            ->columnSpanFull(),
                        ...$field->getDependantFields(),
                    ]);
            }

            if ($fieldWithDependants == null) {
                throw new \Exception('The field must have dependant fields.');
            }

            return $fieldWithDependants;
        }

        return self::getInputField($field)
            ->live(onBlur: true)
            ->reactive();
    }

    /**
     * Get the input field.
     */
    protected static function getInputField(FormFieldInterface $field): Field
    {
        $name = $field->getName();

        $component = match ($field->getFieldAttribute()) {

            FieldAttributesEnum::Checkbox => Toggle::make($name)
                ->label($field->getLabel())
                ->live(onBlur: true)
                ->reactive()
                ->inline(false),

            FieldAttributesEnum::File => self::mountFileUpload($field),

            FieldAttributesEnum::Number => TextInput::make($name)
                ->numeric()
                ->live(onBlur: true)
                ->reactive()
                ->label($field->getLabel())
                ->placeholder('Digite um número')
                ->datalist($field->options()->pluck('label')->toArray()),

            default => TextInput::make($name)
                ->autocapitalize('words')
                ->label($field->getLabel())
                ->live(onBlur: true)
                ->reactive()
                ->placeholder('Digite aqui o valor.')
                ->datalist($field->options()->pluck('label')->toArray()),
        };

        if ($field instanceof CanHandleFieldState) {
            $component
                ->afterStateUpdated($field->afterStateUpdated());
        }

        if ($field instanceof CanDehydrateState) {
            $component->dehydrateStateUsing($field->dehydrateStateUsing());
        }

        if ($component instanceof TextInput && $field instanceof HasInputOptions) {
            $placeholder = $field->getInputPlaceholder();
            if ($placeholder) {
                $component->placeholder($placeholder);
            }

            $mask = $field->getInputMask();
            if ($mask) {
                $component->mask($mask);
            }
        }

        return $component;
    }

    protected static function mountFileUpload(FormFieldInterface $field): Field
    {
        $component = FileUpload::make($field->getName())
            ->label($field->getLabel())
            ->live(onBlur: true)
            ->reactive();

        if ($field instanceof HasAcceptedFileTypes) {
            $acceptedTypes = $field->getAcceptedFileTypes();
            if (! empty($acceptedTypes)) {
                $component->acceptedFileTypes($acceptedTypes);
            }
        }

        if ($field instanceof HasFileUploadOptions) {
            $directory = $field->getFileDirectory();
            if ($directory instanceof \Closure) {
                $directory = $directory();
            }

            if (is_string($directory)) {
                $directory = self::resolveDirectoryPlaceholders($directory);
            }

            if ($directory) {
                $component->directory($directory);
            }

            if ($field->getFileDisk()) {
                $component->disk($field->getFileDisk());
            }

            if ($field->getFileMaxSize()) {
                $component->maxSize($field->getFileMaxSize());
            }

            if ($field->shouldPreserveFilenames()) {
                $component->preserveFilenames();
            }
        }

        return $component;
    }

    protected static function resolveDirectoryPlaceholders(string $directory): string
    {
        $issuer = currentIssuer();

        if (! $issuer) {
            return $directory;
        }

        $replacements = [
            '{tenant_id}' => (string) $issuer->tenant_id,
            '{issuer_id}' => (string) $issuer->id,
            '{cnpj}' => (string) $issuer->cnpj,
            '{cnpj_sanitized}' => (string) sanitize($issuer->cnpj),
        ];

        return strtr($directory, $replacements);
    }

    /**
     *  Get the select field.
     */
    protected static function mountSelectField(FormFieldInterface $field): Field
    {
        if (!$field instanceof HasOptions) {
            throw new \Exception('The field must implement the HasOptions interface.');
        }

        $options = collect($field->getOptions())->filter(fn($option) => $option)->toArray();
        $label = $field->getLabel();
        $name = $field->getName();
        $fieldAttribute = $field->getFieldAttribute();

        if (empty($options)) {
            $options = [];
        }

        $component = match ($fieldAttribute) {

            FieldAttributesEnum::Radio => Radio::make($name)
                ->label($label)
                ->live(onBlur: true)
                ->reactive()
                ->options($options),

            FieldAttributesEnum::Multiple => Select::make($name)
                ->label($label)
                ->options($options)
                ->live(onBlur: true)
                ->reactive()
                ->multiple()
                ->preload()
                ->searchable(),

            default => Select::make($name)
                ->label($label)
                ->searchable()
                ->live(onBlur: true)
                ->reactive()
                ->options($options)
                ->preload(),
        };

        return $component;
    }
}
