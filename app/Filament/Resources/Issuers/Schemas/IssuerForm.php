<?php

namespace App\Filament\Resources\Issuers\Schemas;

use App\Enums\AtividadesEmpresariaisEnum;
use App\Enums\IssuerTypeEnum;
use App\Enums\RegimesEmpresariaisEnum;
use App\Models\Municipio;
use App\Services\CertificateService;
use App\Services\CnpjJaService;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\HtmlString;

class IssuerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make('Dados da Empresa')
                            ->schema([
                                Section::make('Dados da Empresa')
                                    ->description('Informações básicas da empresa que será cadastrada')
                                    ->schema([
                                        TextInput::make('razao_social')
                                            ->label('Razão Social')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpan(3)
                                            ->placeholder('Digite a razão social da empresa'),

                                        TextInput::make('cnpj')
                                            ->label('CNPJ')
                                            ->mask('99.999.999/9999-99')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->suffixAction(
                                                Action::make('buscar_cnpj')
                                                    ->label('Buscar CNPJ')
                                                    ->icon('heroicon-m-magnifying-glass')
                                                    ->action(function (Get $get, Set $set): void {
                                                        static::fillCompanyDataFromCnpj($get, $set);
                                                    })
                                            )
                                            ->validationMessages([
                                                'required' => 'O CNPJ é obrigatório',
                                                'unique' => 'Este CNPJ já está cadastrado',
                                            ])
                                            ->columnSpan(3)
                                            ->placeholder('00.000.000/0000-00'),

                                        TextInput::make('inscricao_estadual')
                                            ->label('Inscrição Estadual')
                                            ->maxLength(255)
                                            ->columnSpan(3)
                                            ->placeholder('Inscrição Estadual (opcional)'),

                                        TextInput::make('inscricao_municipal')
                                            ->label('Inscrição Municipal')
                                            ->maxLength(255)
                                            ->columnSpan(3)
                                            ->placeholder('Inscrição Municipal (opcional)'),

                                        Select::make('cod_municipio_ibge')
                                            ->label('Município')
                                            ->searchable()
                                            ->required()
                                            ->options(function () {
                                                return Cache::remember('municipios_sp', now()->addDays(1), function () {

                                                    $municipios = Municipio::get()
                                                        ->map(function ($municipio) {
                                                            $municipio->nome = $municipio->nome . ' | ' . $municipio->sigla;

                                                            return $municipio;
                                                        })
                                                        ->pluck('nome', 'id');

                                                    return $municipios;
                                                });
                                            })
                                            ->columnSpan(2),

                                        Select::make('regime')
                                            ->required()
                                            ->options(RegimesEmpresariaisEnum::class)
                                            ->columnSpan(1),

                                        Select::make('issuer_type')
                                            ->label('Tipo da Empresa')
                                            ->required()
                                            ->live()
                                            ->default(IssuerTypeEnum::PADRAO->value)
                                            ->options(IssuerTypeEnum::class)
                                            ->columnSpan(1),

                                        Select::make('classificacao_tributaria')
                                            ->label('Classificação Tributária')
                                            ->required()
                                            ->default('99')
                                            ->options([
                                                '01' => '01 - Empresa enquadrada no regime de tributação Simples Nacional com tributação previdenciária substituída',
                                                '02' => '02 - Empresa enquadrada no regime de tributação Simples Nacional com tributação previdenciária não substituída',
                                                '03' => '03 - Empresa enquadrada no regime de tributação Simples Nacional com tributação previdenciária substituída e não substituída',
                                                '04' => '04 - MEI - Micro Empreendedor Individual',
                                                '06' => '06 - Agroindústria',
                                                '07' => '07 - Produtor Rural Pessoa Jurídica',
                                                '08' => '08 - Consórcio Simplificado de Produtores Rurais',
                                                '09' => '09 - Órgão Gestor de Mão de Obra',
                                                '10' => '10 - Entidade Sindical a que se refere a Lei 12.023/2009',
                                                '11' => '11 - Associação Desportiva que mantém Clube de Futebol Profissional',
                                                '13' => '13 - Banco, caixa econômica, sociedade de crédito, financiamento e investimento e demais empresas relacionadas no parágrafo 1º do art. 22 da Lei 8.212./91',
                                                '14' => '14 - Sindicatos em geral, exceto aquele classificado no código [10]',
                                                '21' => '21 - Pessoa Física, exceto Segurado Especial',
                                                '22' => '22 - Segurado Especial',
                                                '60' => '60 - Missão Diplomática ou Repartição Consular de carreira estrangeira',
                                                '70' => '70 - Empresa de que trata o Decreto 5.436/2005',
                                                '80' => '80 - Entidade Beneficente de Assistência Social isenta de contribuições sociais',
                                                '85' => '85 - Ente Federativo, Órgãos da União, Autarquias e Fundações Públicas',
                                                '99' => '99 - Pessoas Jurídicas em Geral',
                                            ])
                                            ->columnSpan(2),

                                        Fieldset::make('Contrato / Detalhes Adicionais')
                                            ->visible(fn(Get $get): bool => in_array($get('issuer_type')?->value ?? $get('issuer_type'), [
                                                IssuerTypeEnum::CONDOMINIO->value,
                                                IssuerTypeEnum::ASSOCIACAO->value,
                                            ]))
                                            ->columns([
                                                'default' => 1,
                                                'md' => 2,
                                                'xl' => 3,
                                            ])                                            
                                            ->schema([
                                                TextInput::make('contract_number')
                                                    ->label('Número do Contrato')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->visible(fn(Get $get): bool => in_array($get('issuer_type')?->value ?? $get('issuer_type'), [
                                                        IssuerTypeEnum::CONDOMINIO->value,
                                                        IssuerTypeEnum::ASSOCIACAO->value,
                                                    ]))
                                                    ->columnSpan(1)
                                                    ->placeholder('Digite o número do contrato'),

                                                TextInput::make('contract_start_date')
                                                    ->label('Data de Início do Contrato')
                                                    ->required()
                                                    ->mask('99/99/9999')
                                                    ->formatStateUsing(fn($state) => static::formatDateForDisplay($state))
                                                    ->visible(fn(Get $get): bool => in_array($get('issuer_type')?->value ?? $get('issuer_type'), [
                                                        IssuerTypeEnum::CONDOMINIO->value,
                                                        IssuerTypeEnum::ASSOCIACAO->value,
                                                    ]))
                                                    ->rules([
                                                        'date_format:d/m/Y',
                                                        'before_or_equal:today',
                                                    ])
                                                    ->validationMessages([
                                                        'before_or_equal' => 'A data de início do contrato não pode ser futura.',
                                                    ])
                                                    ->columnSpan(1)
                                                    ->placeholder('DD/MM/AAAA'),
                                            ])
                                            ->columnSpan(4),





                                        Select::make('atividade')
                                            ->label('Atividade')
                                            ->required()
                                            ->multiple()
                                            ->options(AtividadesEmpresariaisEnum::class)
                                            ->columnSpan(2),

                                        Radio::make('contribuinte_icms')
                                            ->label('Contribuinte ICMS?')
                                            ->boolean(trueLabel: 'Sim', falseLabel: 'Não')
                                            ->default(false)
                                            ->columnSpan(2),

                                        TextEntry::make('placeholder_is_enabled')
                                            ->label('Status no sistema')
                                            ->state(function (Get $get): ?HtmlString {
                                                return new HtmlString('<div class="text-sm text-gray-500">Caso esteja desabilitado, a empresa não será executada em nenhum serviço do sistema.</div>');
                                            })
                                            ->columnSpanFull(),

                                        Toggle::make('is_enabled')
                                            ->label(function ($state) {
                                                return $state ? 'Habilitado' : 'Desabilitado';
                                            })
                                            ->live()
                                            ->default(true)
                                            ->columnSpanFull(),
                                     
                                    ])
                                    ->columnSpanFull()
                                    ->columns(6),
                            ]),
                        Tab::make('Detalhes da Empresa')
                            ->schema([
                                Section::make('Demais informações da Empresa')
                                    ->schema([
                                        TextInput::make('data_abertura')
                                            ->label('Data de Abertura')
                                            ->mask('99/99/9999')
                                            ->formatStateUsing(fn($state) => static::formatDateForDisplay($state))
                                            ->readOnly()
                                            ->placeholder('DD/MM/AAAA')
                                            ->columnSpan(3),

                                        TextInput::make('telefone')
                                            ->label('Telefone')
                                            ->tel()
                                            ->readOnly()
                                            ->mask('(99) 9999-9999')
                                            ->columnSpan(3)
                                            ->placeholder('(00) 0000-0000'),

                                        TextInput::make('email')
                                            ->label('E-mail')
                                            ->email()
                                            ->maxLength(255)
                                            ->readOnly()
                                            ->columnSpan(3)
                                            ->placeholder('email@empresa.com.br'),

                                        TextInput::make('logradouro')
                                            ->label('Logradouro')
                                            ->maxLength(255)
                                            ->readOnly()
                                            ->columnSpan(4)
                                            ->placeholder('Rua, Avenida, etc.'),

                                        TextInput::make('numero')
                                            ->label('Número')
                                            ->maxLength(20)
                                            ->readOnly()
                                            ->columnSpan(2)
                                            ->placeholder('Número'),

                                        TextInput::make('complemento')
                                            ->label('Complemento')
                                            ->maxLength(255)
                                            ->readOnly()
                                            ->columnSpan(2)
                                            ->placeholder('Complemento (opcional)'),

                                        TextInput::make('bairro')
                                            ->label('Bairro')
                                            ->maxLength(255)
                                            ->readOnly()
                                            ->columnSpan(2)
                                            ->placeholder('Bairro'),

                                        TextInput::make('cidade')
                                            ->label('Cidade')
                                            ->maxLength(255)
                                            ->readOnly()
                                            ->columnSpan(2)
                                            ->placeholder('Digite a cidade'),

                                        TextInput::make('cep')
                                            ->label('CEP')
                                            ->mask('99999-999')
                                            ->readOnly()
                                            ->columnSpan(2)
                                            ->placeholder('00000-000'),

                                        TextInput::make('situacao_cadastral')
                                            ->label('Situação Cadastral')
                                            ->maxLength(50)
                                            ->readOnly()
                                            ->columnSpan(2)
                                            ->placeholder('Ex: ATIVA'),

                                        TextInput::make('data_situacao_cadastral')
                                            ->label('Data da Situação Cadastral')
                                            ->mask('99/99/9999')
                                            ->formatStateUsing(fn($state) => static::formatDateForDisplay($state))
                                            ->readOnly()
                                            ->placeholder('DD/MM/AAAA')
                                            ->columnSpan(2),

                                        TextInput::make('natureza_operacao_id')
                                            ->label('ID Natureza Operação')
                                            ->maxLength(50)
                                            ->readOnly()
                                            ->columnSpan(2),

                                        TextInput::make('natureza_operacao')
                                            ->label('Natureza Operação')
                                            ->maxLength(50)
                                            ->readOnly()
                                            ->columnSpan(2),


                                        Section::make('Atividades Econômicas')
                                            ->schema([
                                                Section::make('Atividade Principal')
                                                    ->schema([
                                                        TextInput::make('main_activity.id')
                                                            ->label('Código')
                                                            ->numeric()
                                                            ->readOnly()
                                                            ->columnSpan(2),

                                                        TextInput::make('main_activity.text')
                                                            ->label('Descrição')
                                                            ->maxLength(255)
                                                            ->readOnly()
                                                            ->columnSpan(4),
                                                    ])
                                                    ->columns(6)
                                                    ->columnSpanFull()
                                                    ->collapsible(),

                                                Section::make('Atividades Secundárias')
                                                    ->visible(function ($record, Get $get) {
                                                        return count($record?->side_activities ?? $get('side_activities') ?? []) > 0;
                                                    })
                                                    ->schema([
                                                        Repeater::make('side_activities')
                                                            ->hiddenLabel(true)
                                                            ->disabled()
                                                            ->table([
                                                                TableColumn::make('Código')
                                                                    ->width('200px')
                                                                    ->hiddenHeaderLabel(),
                                                                TableColumn::make('Descrição')
                                                                    ->hiddenHeaderLabel(),
                                                            ])
                                                            ->schema([
                                                                TextInput::make('id')
                                                                    ->label('Código')
                                                                    ->numeric()
                                                                    ->columnSpan(2),

                                                                TextInput::make('text')
                                                                    ->label('Descrição')
                                                                    ->maxLength(255)
                                                                    ->columnSpan(4),
                                                            ])
                                                            ->columns(6)
                                                            ->collapsible()
                                                            ->itemLabel(
                                                                fn(array $state): ?string => isset($state['id'], $state['text'])
                                                                    ? "#{$state['id']} - {$state['text']}"
                                                                    : null
                                                            ),
                                                    ])
                                                    ->columnSpanFull()
                                                    ->collapsible(),
                                            ])
                                            ->columns(6)
                                            ->columnSpanFull(),

                                    ])
                                    ->columnSpanFull()
                                    ->columns(6),
                            ]),
                        Tab::make('Certificado Digital')
                            ->schema([
                                Section::make('Certificado Digital A1')
                                    ->description('Upload e validação do certificado digital para emissão de documentos fiscais')
                                    ->schema([
                     
                                        // Mostrar certificado atual quando em edição
                                        TextEntry::make('certificado_atual_info')
                                            ->label('Certificado Digital Atual')
                                            ->state(function (Get $get, $record): ?HtmlString {
                                                // Verificar se estamos editando (tem validade_certificado preenchida)

                                                $validadeCertificado = $record->validade_certificado;
                                                $contentCertificado = $record->certificado_content;
                                                $razaoSocial = $record->razao_social;

                                                if (! $validadeCertificado && !$contentCertificado) {
                                                    return null; // Não mostrar se não tem certificado
                                                }

                                                try {
                                                    $dataVencimento = Carbon::parse($validadeCertificado);
                                                    $vencimentoFormatado = $dataVencimento->format('d/m/Y');
                                                    $hoje = Carbon::now();
                                                    $diasRestantes = (int) $hoje->diffInDays($dataVencimento, false);

                                               
                                                    // Determinar cor e ícone baseado na validade
                                                    if ($diasRestantes < 0) {
                                                        $diasVencidos = abs($diasRestantes);
                                                        $corSituacao = '#dc2626';
                                                        $iconeSituacao = '❌';
                                                        $textoSituacao = "Vencido há {$diasVencidos} " . ($diasVencidos === 1 ? 'dia' : 'dias');
                                                        $corBorda = '#dc2626';
                                                        $corFundo = '#fef2f2';
                                                    } elseif ($diasRestantes <= 30) {
                                                        $corSituacao = '#f59e0b';
                                                        $iconeSituacao = '⚠️';
                                                        $textoSituacao = "Vence em {$diasRestantes} " . ($diasRestantes === 1 ? 'dia' : 'dias');
                                                        $corBorda = '#f59e0b';
                                                        $corFundo = '#fffbeb';
                                                    } else {
                                                        $corSituacao = '#059669';
                                                        $iconeSituacao = '✅';
                                                        $textoSituacao = "Válido por mais {$diasRestantes} " . ($diasRestantes === 1 ? 'dia' : 'dias');
                                                        $corBorda = '#059669';
                                                        $corFundo = '#f0fdf4';
                                                    }

                                                    return new HtmlString("
                                        <div style='background: {$corFundo}; border: 2px solid {$corBorda}; border-radius: 12px; padding: 20px; margin-bottom: 16px;'>
                                            <div style='display: flex; align-items: center; gap: 12px; margin-bottom: 12px;'>
                                                <div style='width: 40px; height: 40px; background-color: {$corBorda}; border-radius: 50%; display: flex; align-items: center; justify-content: center;'>
                                                    <span style='color: white; font-size: 18px;'>🛡️</span>
                                                </div>
                                                <div>
                                                    <h3 style='margin: 0; color: {$corSituacao}; font-size: 16px; font-weight: 600;'>Certificado Digital Cadastrado</h3>
                                                    <p style='margin: 0; color: {$corSituacao}; font-size: 14px;'>Certificado A1 em uso para esta empresa</p>
                                                </div>
                                            </div>
                                            
                                            <div style='display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 12px;'>
                                                <div style='background-color: white; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;'>
                                                    <label style='display: block; font-size: 12px; color: #64748b; font-weight: 500; margin-bottom: 4px;'>VALIDADE</label>
                                                    <div style='font-size: 14px; color: #1e293b; font-weight: 600;'>{$vencimentoFormatado}</div>
                                                </div>
                                                <div style='background-color: white; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;'>
                                                    <label style='display: block; font-size: 12px; color: #64748b; font-weight: 500; margin-bottom: 4px;'>SITUAÇÃO</label>
                                                    <div style='font-size: 14px; color: {$corSituacao}; font-weight: 600; display: flex; align-items: center; gap: 6px;'>
                                                        <span>{$iconeSituacao}</span>
                                                        <span>{$textoSituacao}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div style='background-color: white; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;'>
                                                <label style='display: block; font-size: 12px; color: #64748b; font-weight: 500; margin-bottom: 4px;'>EMPRESA VINCULADA</label>
                                                <div style='font-size: 14px; color: #1e293b; font-weight: 500;'>" . htmlspecialchars($razaoSocial ?? 'Não informado') . "</div>
                                            </div>
                                            
                                            <div style='margin-top: 16px; padding: 12px; background-color: rgba(255,255,255,0.7); border-radius: 8px; border: 1px dashed {$corBorda};'>
                                                <p style='margin: 0; font-size: 13px; color: {$corSituacao}; text-align: center;'>
                                                    💡 <strong>Para substituir o certificado:</strong> Faça upload de um novo arquivo abaixo
                                                </p>
                                            </div>
                                        </div>
                                    ");
                                                } catch (Exception $e) {
                                                    return new HtmlString('
                                        <div style="padding: 16px; border: 1px solid #f87171; background-color: #fef2f2; color: #dc2626; border-radius: 8px; margin-bottom: 16px;">
                                            <strong>❌ Erro ao processar certificado atual:</strong> ' . htmlspecialchars($e->getMessage()) . '
                                        </div>
                                    ');
                                                }
                                            })
                                            ->visible(function($record){


                                                return isset($record->validade_certificado) && isset($record->certificado_content);
                                            })
                                            ->columnSpanFull(),                                            

                                        TextEntry::make('certificado_info_upload')
                                            ->hiddenLabel()
                                            ->state(new HtmlString('
                                <div style="padding: 12px; background-color: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 8px; margin-bottom: 16px;">
                                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                        <svg style="width: 20px; height: 20px; color: #0ea5e9;" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span style="font-weight: 600; color: #0c4a6e;">Upload de Novo Certificado Digital</span>
                                    </div>
                                    <ul style="margin: 0; padding-left: 20px; color: #0c4a6e; font-size: 14px;">
                                        <li>Faça upload do arquivo do certificado digital A1 (.pfx ou .p12)</li>
                                        <li>O certificado deve estar válido (não vencido)</li>
                                        <li>Tenha a senha do certificado em mãos</li>
                                        <li>Após o upload, digite a senha para validação automática</li>
                                        <li>O novo certificado substituirá o certificado atual</li>
                                    </ul>
                                </div>
                            '))
                                            ->columnSpanFull(),

                                        FileUpload::make('path_certificado')
                                            ->label(function (Get $get) {
                                                return filled($get('validade_certificado'))
                                                    ? 'Substituir Certificado Digital (Opcional)'
                                                    : 'Arquivo do Certificado Digital';
                                            })
                                            // ->acceptedFileTypes(['.pfx', '.p12'])
                                            ->maxSize(2048)
                                            ->directory('certificados')
                                            ->visibility('private')
                                            ->helperText(function (Get $get) {
                                                return filled($get('validade_certificado'))
                                                    ? '⚠️ Deixe em branco para manter o certificado atual. Faça upload apenas se desejar substituir por um novo certificado.'
                                                    : 'Selecione o arquivo .pfx ou .p12 do seu certificado digital A1 (máximo 2MB)';
                                            })
                                            // ->required(fn(Get $get): bool => empty($get('validade_certificado'))) // Obrigatório apenas se não tem certificado
                                            ->columnSpanFull()
                                            ->live()
                                            ->afterStateUpdated(function ($state, Set $set) {
                                                // Reset campos quando arquivo é alterado
                                                if ($state) {
                                                    $set('certificado_verificado', false);
                                                    $set('data_inicio_certificado', null);
                                                    $set('validade_certificado', null);
                                                    $set('certificado_content', null);
                                                } else {
                                                    // Limpa todos os campos se arquivo foi removido
                                                    $set('senha_certificado', null);
                                                    $set('certificado_verificado', false);
                                                    $set('data_inicio_certificado', null);
                                                    $set('validade_certificado', null);
                                                    $set('certificado_content', null);
                                                }
                                            }),

                                        TextInput::make('senha_certificado')
                                            ->label('Senha do Certificado')
                                            ->password()
                                            ->revealable()
                                            ->required(fn(Get $get): bool => filled($get('path_certificado')))
                                            ->visible(fn(Get $get): bool => filled($get('path_certificado')))
                                            ->helperText('Digite a senha e clique no ícone de validação para confirmar.')
                                            ->prefixAction(
                                                Action::make('validar_certificado')
                                                    ->icon('heroicon-m-check-circle')
                                                    ->color(function (Get $get) {
                                                        return $get('certificado_verificado') === true ? 'success' : 'danger';
                                                    })
                                                    ->tooltip('Validar senha e extrair dados')
                                                    ->action(function (Get $get, Set $set) {

                                                        $password = $get('senha_certificado');
                                                        $certificadoPathArray = $get('path_certificado');

                                                        if (! $password || ! $certificadoPathArray) {
                                                            $set('data_inicio_certificado', null);
                                                            $set('validade_certificado', null);
                                                            $set('certificado_content', null);
                                                            $set('certificado_verificado', false);

                                                            Notification::make()
                                                                ->title('Dados incompletos')
                                                                ->body('Por favor, certifique-se de que o arquivo e a senha foram informados.')
                                                                ->warning()
                                                                ->send();

                                                            return;
                                                        }

                                                        try {
                                                            $pfx = null;

                                                            // Obter conteúdo do arquivo de certificado
                                                            if (is_array($certificadoPathArray)) {
                                                                foreach ($certificadoPathArray as $pathObj) {
                                                                    if ($pathObj instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                                                                        $pfx = $pathObj->get();
                                                                        break;
                                                                    }
                                                                }
                                                            } elseif ($certificadoPathArray instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                                                                $pfx = $certificadoPathArray->get();
                                                            }

                                                            if (! $pfx) {
                                                                Notification::make()
                                                                    ->title('Erro ao ler arquivo')
                                                                    ->body('O arquivo do certificado não foi encontrado ou está inválido.')
                                                                    ->warning()
                                                                    ->send();

                                                                return;
                                                                throw new Exception('Arquivo do certificado não encontrado ou inválido.');
                                                            }

                                                            // Usar o service para validar e extrair informações
                                                            $certificateService = new CertificateService;
                                                            try {
                                                                $certificateData = $certificateService->validateAndExtractCertificateInfo($pfx, $password);
                                                            } catch (Exception $e) {
                                                                Notification::make()
                                                                    ->title('Erro na validação do certificado')
                                                                    ->body($e->getMessage())
                                                                    ->warning()
                                                                    ->send();

                                                                return;
                                                            }

                                                            // Atualizar campos do formulário com os dados extraídos
                                                            $set('razao_social', $certificateData['razao_social']);
                                                            $set('cnpj', $certificateData['cnpj']);
                                                            $set('data_inicio_certificado', $certificateData['data_inicio']);
                                                            $set('validade_certificado', $certificateData['data_fim']);
                                                            $set('certificado_content', $certificateData['certificado_content']);
                                                            $set('certificado_verificado', true);

                                                            // Notificação de sucesso
                                                            $mensagem = 'Certificado validado com sucesso!';
                                                            if ($certificateData['is_expiring_soon']) {
                                                                $mensagem .= " ⚠️ Atenção: Vence em {$certificateData['dias_restantes']} dia(s).";
                                                            }

                                                            Notification::make()
                                                                ->title('Certificado Válido!')
                                                                ->body($mensagem)
                                                                ->success()
                                                                ->duration(5000)
                                                                ->send();
                                                        } catch (Exception $e) {
                                                            // Limpar campos em caso de erro
                                                            $set('data_inicio_certificado', null);
                                                            $set('validade_certificado', null);
                                                            $set('certificado_content', null);
                                                            $set('certificado_verificado', false);

                                                            Notification::make()
                                                                ->title('Erro na Validação do Certificado')
                                                                ->body($e->getMessage())
                                                                ->danger()
                                                                ->duration(8000)
                                                                ->send();
                                                        }
                                                    })
                                            )
                                            ->columnSpanFull(),

                                        TextEntry::make('info_certificado_display')
                                            ->label('Informações do Novo Certificado Validado')
                                            ->state(function (Get $get): ?HtmlString {
                                                $razaoSocialCertificado = $get('razao_social');
                                                $dataInicioStr = $get('data_inicio_certificado');
                                                $dataFimStr = $get('validade_certificado');
                                                $verificado = $get('certificado_verificado');

                                                if (! $verificado || ! $dataInicioStr || ! $dataFimStr) {
                                                    return new HtmlString('
                                        <div style="padding: 16px; border: 2px dashed #d1d5db; background-color: #f9f9f9; border-radius: 8px; text-align: center;">
                                            <div style="color: #6b7280; font-size: 14px;">
                                                📄 As informações do certificado aparecerão aqui após a validação
                                            </div>
                                        </div>
                                    ');
                                                }

                                                try {
                                                    $dataInicio = Carbon::parse($dataInicioStr);
                                                    $dataFim = Carbon::parse($dataFimStr);

                                                    $inicioFormatado = $dataInicio->format('d/m/Y');
                                                    $fimFormatado = $dataFim->format('d/m/Y');

                                                    $hoje = Carbon::now();
                                                    $diasRestantes = (int) $hoje->diffInDays($dataFim, false);

                                                    // Determinar cor e ícone baseado na validade
                                                    $corSituacao = '';
                                                    $iconeSituacao = '';
                                                    $textoSituacao = '';

                                                    if ($diasRestantes < 0) {
                                                        $diasVencidos = abs($diasRestantes);
                                                        $corSituacao = '#dc2626';
                                                        $iconeSituacao = '❌';
                                                        $textoSituacao = "Vencido há {$diasVencidos} " . ($diasVencidos === 1 ? 'dia' : 'dias');
                                                    } elseif ($diasRestantes <= 30) {
                                                        $corSituacao = '#f59e0b';
                                                        $iconeSituacao = '⚠️';
                                                        $textoSituacao = "Vence em {$diasRestantes} " . ($diasRestantes === 1 ? 'dia' : 'dias');
                                                    } else {
                                                        $corSituacao = '#059669';
                                                        $iconeSituacao = '✅';
                                                        $textoSituacao = "Válido por mais {$diasRestantes} " . ($diasRestantes === 1 ? 'dia' : 'dias');
                                                    }

                                                    return new HtmlString("
                                        <div style='background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border: 1px solid #0ea5e9; border-radius: 12px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);'>
                                            <div style='display: flex; align-items: center; gap: 12px; margin-bottom: 16px;'>
                                                <div style='width: 40px; height: 40px; background-color: #0ea5e9; border-radius: 50%; display: flex; align-items: center; justify-content: center;'>
                                                    <span style='color: white; font-size: 18px;'>🏢</span>
                                                </div>
                                                <div>
                                                    <h3 style='margin: 0; color: #0c4a6e; font-size: 16px; font-weight: 600;'>Certificado Validado</h3>
                                                    <p style='margin: 0; color: #0c4a6e; font-size: 14px;'>Informações extraídas do certificado digital</p>
                                                </div>
                                            </div>
                                            
                                            <div style='display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px;'>
                                                <div style='background-color: white; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;'>
                                                    <label style='display: block; font-size: 12px; color: #64748b; font-weight: 500; margin-bottom: 4px;'>VIGÊNCIA INICIAL</label>
                                                    <div style='font-size: 14px; color: #1e293b; font-weight: 600;'>{$inicioFormatado}</div>
                                                </div>
                                                <div style='background-color: white; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;'>
                                                    <label style='display: block; font-size: 12px; color: #64748b; font-weight: 500; margin-bottom: 4px;'>VIGÊNCIA FINAL</label>
                                                    <div style='font-size: 14px; color: #1e293b; font-weight: 600;'>{$fimFormatado}</div>
                                                </div>
                                                <div style='background-color: white; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;'>
                                                    <label style='display: block; font-size: 12px; color: #64748b; font-weight: 500; margin-bottom: 4px;'>SITUAÇÃO</label>
                                                    <div style='font-size: 14px; color: {$corSituacao}; font-weight: 600; display: flex; align-items: center; gap: 6px;'>
                                                        <span>{$iconeSituacao}</span>
                                                        <span>{$textoSituacao}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div style='background-color: white; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;'>
                                                <label style='display: block; font-size: 12px; color: #64748b; font-weight: 500; margin-bottom: 4px;'>RAZÃO SOCIAL</label>
                                                <div style='font-size: 14px; color: #1e293b; font-weight: 500;'>" . htmlspecialchars($razaoSocialCertificado) . '</div>
                                            </div>
                                        </div>
                                    ');
                                                } catch (\Carbon\Exceptions\InvalidFormatException $e) {
                                                    return new HtmlString('
                                        <div style="padding: 16px; border: 1px solid #f87171; background-color: #fef2f2; color: #dc2626; border-radius: 8px;">
                                            <strong>❌ Erro ao processar datas:</strong> Não foi possível interpretar as datas do certificado.
                                        </div>
                                    ');
                                                } catch (Exception $e) {
                                                    return new HtmlString('
                                        <div style="padding: 16px; border: 1px solid #f87171; background-color: #fef2f2; color: #dc2626; border-radius: 8px;">
                                            <strong>❌ Erro inesperado:</strong> ' . htmlspecialchars($e->getMessage()) . '
                                        </div>
                                    ');
                                                }
                                            })
                                            ->visible(fn(Get $get): bool => filled($get('path_certificado')))
                                            ->columnSpanFull(),

                                        // Campos ocultos para armazenar dados do certificado
                                        Hidden::make('data_inicio_certificado'),
                                        Hidden::make('certificado_content'),
                                        Hidden::make('validade_certificado'),
                                        Hidden::make('certificado_verificado'),
                                    ])
                                    ->columnSpanFull(),
                            ]),
                    ])->columnSpanFull(),

            ]);
    }

    private static function fillCompanyDataFromCnpj(Get $get, Set $set): void
    {
        $cnpj = sanitize((string) $get('cnpj'));

        if (strlen($cnpj) !== 14) {
            Notification::make()
                ->title('CNPJ inválido')
                ->body('Informe um CNPJ com 14 dígitos para realizar a busca.')
                ->warning()
                ->send();

            return;
        }

        try {
            $cnpjDetails = CnpjJaService::getCnpjDetails($cnpj);

            ds($cnpjDetails);
        } catch (Exception $e) {
            Notification::make()
                ->title('Erro ao consultar CNPJ')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return;
        }

        if ($cnpjDetails === []) {
            Notification::make()
                ->title('Nenhum dado encontrado')
                ->body('Não foi possível obter dados para este CNPJ.')
                ->warning()
                ->send();

            return;
        }

        $phoneArea = (string) data_get($cnpjDetails, 'phones.0.area', '');
        $phoneNumber = (string) data_get($cnpjDetails, 'phones.0.number', '');
        $cidade = (string) data_get($cnpjDetails, 'address.city', '');
        $uf = (string) data_get($cnpjDetails, 'address.state', '');

        $set('razao_social', data_get($cnpjDetails, 'company.name'));
        $set('inscricao_estadual', data_get($cnpjDetails, 'registrations.0.number'));
        $set('inscricao_municipal', data_get($cnpjDetails, 'municipalRegistration'));
        $set('data_abertura', static::formatDateForDisplay(data_get($cnpjDetails, 'founded')));
        $set('email', data_get($cnpjDetails, 'emails.0.address'));
        $set('telefone', $phoneArea . $phoneNumber);
        $set('logradouro', data_get($cnpjDetails, 'address.street'));
        $set('numero', data_get($cnpjDetails, 'address.number'));
        $set('complemento', data_get($cnpjDetails, 'address.details'));
        $set('bairro', data_get($cnpjDetails, 'address.district'));
        $set('cidade', $cidade);
        $set('uf', $uf);
        $set('cep', data_get($cnpjDetails, 'address.zip'));
        $set('situacao_cadastral', data_get($cnpjDetails, 'status.text'));
        $set('data_situacao_cadastral', static::formatDateForDisplay(data_get($cnpjDetails, 'statusDate')));
        $naturezaOperacaoId = (string) data_get($cnpjDetails, 'company.nature.id', '');
        $set('natureza_operacao_id', $naturezaOperacaoId !== '' ? (int) $naturezaOperacaoId : null);
        $set('natureza_operacao', data_get($cnpjDetails, 'company.nature.text'));
        $set('main_activity', data_get($cnpjDetails, 'mainActivity'));
        $set('side_activities', data_get($cnpjDetails, 'sideActivities', []));

        if ($naturezaOperacaoId === '3085') {
            $set('issuer_type', IssuerTypeEnum::CONDOMINIO->value);
        } elseif ($naturezaOperacaoId === '3999') {
            $set('issuer_type', IssuerTypeEnum::ASSOCIACAO->value);
        }

        $municipioId = static::resolveMunicipioId($cidade, $uf);
        if ($municipioId !== null) {
            $set('cod_municipio_ibge', $municipioId);
        }

        Notification::make()
            ->title('Dados preenchidos')
            ->body('Dados da empresa carregados a partir do CNPJ.')
            ->success()
            ->send();
    }

    private static function resolveMunicipioId(string $cidade, string $uf): ?int
    {
        if ($cidade === '' || $uf === '') {
            return null;
        }

        return Municipio::query()
            ->whereRaw('LOWER(nome) = ?', [mb_strtolower($cidade)])
            ->where('uf', mb_strtoupper($uf))
            ->value('id');
    }

    private static function formatDateForDisplay(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse((string) $value)->format('d/m/Y');
        } catch (Exception) {
            return null;
        }
    }
}
