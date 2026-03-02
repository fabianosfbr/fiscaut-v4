<?php

namespace App\Filament\Resources\UploadFileManagers\Schemas;

use App\Filament\Forms\Components\DownloadDocumentFile;
use App\Filament\Forms\Components\SelectTagGrouped;
use App\Models\CategoryTag;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class UploadFileManagerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Demais documentos fiscais')
                    ->description('Detalhes e características do documento ')
                    ->schema([
                        TextEntry::make('empresa')
                            ->label('Empresa')
                            ->state(fn () => currentIssuer()->razao_social)
                            ->columnSpanFull(),
                        Select::make('doc_type')
                            ->label('Tipo de documento')
                            ->disabledOn('edit')
                            ->required()
                            ->options(config('admin.doc_types'))->columnSpan(1),

                        Select::make('periodo_exercicio')
                            ->label('Período de referência')
                            ->required()
                            ->options(getMesesAnterioresEPosteriores())
                            ->disabledOn('edit')
                            ->columnSpan(1),

                        TextInput::make('doc_value_create')
                            ->label('Valor Total Etiquetas')
                            ->prefix('R$')
                            ->disabled()
                            ->hiddenOn('edit')
                            ->columnSpan(1)
                            ->placeholder(function ($get, $set) {
                                $fields = $get('tags');
                                $sum = 0.0;
                                if (is_array($fields)) {
                                    foreach ($fields as $field) {
                                        if (isset($field['valor'])) {
                                            $valor = str_replace(',', '.', str_replace('.', '', $field['valor']));
                                            $sum += floatval($valor);
                                        }
                                    }
                                }
                                $set('doc_value', number_format($sum, 2, ',', '.'));

                                return number_format($sum, 2, ',', '.');
                            }),

                        TextEntry::make('etiquetas')
                            ->label('Etiquetas')
                            ->hiddenOn('create')
                            ->state(function (Model $record) {
                                $tags = $record->tagged ?? [];
                                $content = '<ul class="mt-2 pl-5 list-disc">';
                                foreach ($tags as $tagged) {
                                    $content .= '<li>'.$tagged->tag->code.' - '.$tagged->tag_name.' - '.'R$ '.number_format($tagged->value, 2, ',', '.').'</li>';
                                }
                                $content .= '</ul>';

                                return new HtmlString($content);
                            })
                            ->columnSpanFull(),

                        Repeater::make('tags')
                            ->label('Classificação')
                            ->hiddenOn('edit')
                            ->schema([
                                SelectTagGrouped::make('tag_id')
                                    ->label('Etiqueta')
                                    ->columnSpan(1)
                                    ->multiple(false)
                                    ->required()
                                    ->options(CategoryTag::getAllEnabled(currentIssuer()->id)),
                                TextInput::make('valor')
                                    ->prefix('R$')
                                    ->live(onBlur: true)
                                    ->required()
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                                    ->numeric(),
                            ])
                            ->addActionLabel('Adicionar etiqueta')
                            ->columns(2)
                            ->columnSpanFull(),
                        Textarea::make('title')
                            ->label('Descrição do documento')
                            ->required()
                            ->disabledOn('edit')
                            ->columnSpan('full')
                            ->columnSpan('full'),

                        FileUpload::make('arquivo')
                            ->label('Arquivo')
                            ->visibility('private')
                            ->maxSize(51200)
                            ->directory(function ($get) {
                                $currentIssuer = currentIssuer();
                                $periodo = explode('-', $get('periodo_exercicio'));

                                if (! $currentIssuer || ! $get('periodo_exercicio')) {
                                    return null;
                                }

                                return 'documentos/'.$currentIssuer->tenant_id.'/'.$currentIssuer->cnpj.'/docs-nao-fiscais/'.$periodo[0].'-'.$periodo[1];
                            })
                            ->hidden(function ($record) {
                                return isset($record->created_at) ? true : false;
                            })
                            ->required()
                            ->columnSpan('full'),

                        Toggle::make('processed')
                            ->label('Apurado')
                            ->visible(function () {
                                $user = Auth::user();
                                $role = $user->role;

                                return $role && $role->slug === 'admin';
                            })
                            ->inline(false)
                            ->columnSpan(1),

                        DownloadDocumentFile::make('id')
                            ->label('Arquivo enviado')
                            ->hiddenOn('create')
                            ->columnSpan(1),
                        Hidden::make('doc_value'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }
}
