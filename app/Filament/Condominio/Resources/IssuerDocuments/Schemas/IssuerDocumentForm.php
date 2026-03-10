<?php

namespace App\Filament\Condominio\Resources\IssuerDocuments\Schemas;

use App\Enums\IssuerDocumentTypeEnum;
use App\Models\Issuer;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class IssuerDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('issuer_id')
                    ->label('Empresa')
                    ->required()
                    ->options(function () {
                        return Issuer::where('tenant_id', currentIssuer()->tenant_id)
                            ->pluck('razao_social', 'id');
                    })
                    ->searchable()
                    ->preload(),

                Select::make('document_type')
                    ->label('Tipo de Documento')
                    ->required()
                    ->options(IssuerDocumentTypeEnum::getDocumentTypes())
                    ->searchable(),

                TextInput::make('user_name')
                    ->label('Nome do Documento')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Nome que será exibido no sistema'),

                FileUpload::make('file_path')
                    ->label('Arquivo do Documento')
                    ->required()
                    ->disk('local')
                    ->directory(function ($get) {
                        $issuer = Issuer::find($get('issuer_id'));
                        if (!$issuer) {
                            return null;
                        }
                        return 'private/rag/' . $issuer->tenant_id . '/' . $issuer->cnpj . '/documents';
                    })
                    ->visibility('private')
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                    ])
                    ->maxSize(10240) // 10MB in KB
                    ->storeFileNamesIn('original_name')
                    ->preserveFilenames()
                    ->afterStateUpdated(function ($state, $set) {
                        if ($state) {
                            $file = $state;
                            $set('original_name', $file->getClientOriginalName());
                            $set('format', $file->getClientOriginalExtension());
                            $set('extension', $file->getClientOriginalExtension());
                            $set('file_size', $file->getSize());
                            $set('mime_type', $file->getMimeType());
                        }
                    })
                    ->helperText('Formatos permitidos: PDF, DOC, DOCX. Tamanho máximo: 10MB'),

                TextInput::make('original_name')
                    ->label('Nome Original do Arquivo')
                    ->disabled()
                    ->dehydrated(),

                TextInput::make('format')
                    ->label('Formato')
                    ->disabled()
                    ->dehydrated(),

                TextInput::make('extension')
                    ->label('Extensão')
                    ->disabled()
                    ->dehydrated(),

                TextInput::make('file_size')
                    ->label('Tamanho do Arquivo (bytes)')
                    ->disabled()
                    ->dehydrated(),

                TextInput::make('mime_type')
                    ->label('Tipo MIME')
                    ->disabled()
                    ->dehydrated(),
            ]);
    }
}
