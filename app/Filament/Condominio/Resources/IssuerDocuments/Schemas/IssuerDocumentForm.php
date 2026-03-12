<?php

namespace App\Filament\Condominio\Resources\IssuerDocuments\Schemas;

use App\Enums\IssuerDocumentTypeEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class IssuerDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_name')
                    ->label('Nome do Documento')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Nome que será exibido no sistema')
                    ->columnSpanFull(),

                Select::make('document_type')
                    ->label('Tipo de Documento')
                    ->required()
                    ->options(IssuerDocumentTypeEnum::getDocumentTypes())
                    ->columnSpan(1)
                    ->searchable(),

                TextInput::make('validate_at')
                    ->label('Válido até')
                    ->mask('99/99/9999')
                    ->maxLength(255)
                    ->helperText('Data de validade do documento (ex: 31/12/2024)    ')
                    ->columnSpan(1),

                FileUpload::make('file_path')
                    ->label('Arquivo do Documento')
                    ->required()
                    ->disk('local')
                    ->directory(function ($get) {
                        $issuer = currentIssuer();
                        if (!$issuer) {
                            return null;
                        }

                        return 'rag/' . $issuer->tenant_id . '/' . sanitize($issuer->cnpj) . '/documents';
                    })
                    ->visibility('private')
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    ])
                    ->maxSize(10240) // 10MB in KB
                    ->storeFileNamesIn('original_name')
                    ->preserveFilenames()
                    ->helperText('Formatos permitidos: PDF, DOC, DOCX. Tamanho máximo: 10MB')
                    ->columnSpanFull(),


            ]);
    }
}
