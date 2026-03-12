<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum FieldTypesEnum: string implements HasLabel
{
    case Input = 'input';

    case Select = 'select';

    case Repeater = 'repeater';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Input => 'Input',
            self::Select => 'Select',
            self::Repeater => 'Repeater',
        };
    }

    public static function toArray()
    {
        $statuses = [];
        foreach (self::cases() as $status) {
            $statuses[$status->value] = $status->getLabel();
        }

        return $statuses;
    }

    public function attributes()
    {
        return match ($this) {
            self::Input => [
                FieldAttributesEnum::Text->value => FieldAttributesEnum::Text->getLabel(),
                FieldAttributesEnum::Checkbox->value => FieldAttributesEnum::Checkbox->getLabel(),
                FieldAttributesEnum::File->value => FieldAttributesEnum::File->getLabel(),
                FieldAttributesEnum::Number->value => FieldAttributesEnum::Number->getLabel(),
            ],
            self::Select => [
                FieldAttributesEnum::Radio->value => FieldAttributesEnum::Radio->getLabel(),
                FieldAttributesEnum::Multiple->value => FieldAttributesEnum::Multiple->getLabel(),
            ],
            self::Repeater => [],
        };
    }
}
