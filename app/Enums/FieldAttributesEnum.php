<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
enum FieldAttributesEnum: string implements HasLabel
{
    case Text = 'text';
    case Checkbox = 'checkbox';
    case Number = 'number';
    case File = 'file';

    case Radio = 'radio';

    case Multiple = 'multiple';


    public function getLabel(): ?string
    {
        return match ($this) {
            self::Text => 'Texto',
            self::Checkbox => 'Checkbox',
            self::Number => 'Número',
            self::File => 'Arquivo',
            self::Radio => 'Radio',
            self::Multiple => 'Múltiplo',
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



}
