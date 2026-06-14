# Plano: Adicionar suporte a DataUpload vs DataEmissao no SiegConnect

## Resumo
O job `SiegConnect` atualmente usa apenas **`DataEmissaoInicio`/`DataEmissaoFim`** na consulta à API SIEG. O objetivo é adicionar um parâmetro para permitir escolher entre:
- **Data de Emissão** (atual): `DataEmissaoInicio` / `DataEmissaoFim`
- **Data de Upload/Recebimento**: `DataUploadInicio` / `DataUploadFim`

---

## Análise do Estado Atual

### Fluxo de Dados
```
[Console Commands / Filament Page] 
    → despacha SiegConnect(dataInicial, dataFinal, tipoDocumento, tipoCnpj, issuerId, importJobId, event)
    → SiegConnect::handle()
        → monta payload com DataEmissaoInicio/DataEmissaoFim
        → POST https://api.sieg.com/BaixarXmlsV2?api_key=...
        → processa resultados e despacha ProcessDocument*Jobs
```

### Locais de Despacho (5 pontos)

| Tipo | Arquivo | Parâmetros passados |
|------|---------|---------------------|
| Console | `app/Console/Commands/Sieg/SyncNfe.php` | tipoDocumento=1, dataInicial, dataFinal, tipoCnpj, issuerId, importJobId, event |
| Console | `app/Console/Commands/Sieg/SyncCte.php` | tipoDocumento=2, dataInicial, dataFinal, tipoCnpj, issuerId, importJobId, event |
| Console | `app/Console/Commands/Sieg/SyncNfce.php` | tipoDocumento=4, dataInicial, dataFinal, tipoCnpj, issuerId, importJobId, event |
| Console | `app/Console/Commands/Sieg/SyncNfse.php` | tipoDocumento=3, dataInicial, dataFinal, tipoCnpj, issuerId, importJobId, event |
| Filament | `app/Filament/Pages/Importar/SiegImport.php` | tipoDocumento[], dataInicial, dataFinal, tipoCnpj[], issuerId, importJobId, event=true/false |

### Payload Atual (linhas 106-114)
```php
$payload = [
    'XmlType' => (int) $this->tipoDocumento,
    $this->tipoCnpj => $cnpj,
    'Take' => $this->take,
    'Skip' => $this->skip,
    'DataEmissaoInicio' => $this->dataInicial,   // SEMPRE emissão
    'DataEmissaoFim' => $this->dataFinal,        // SEMPRE emissão
    'Downloadevent' => $this->event,
];
```

---

## Mudanças Propostas

### 1. SiegConnect.php — Novo parâmetro `tipoData`

**Arquivo:** `app/Jobs/Sieg/SiegConnect.php`

#### Construtor (adicionar parâmetro)
```php
// Adicionar após $event = true:
protected string $tipoData = 'emissao',  // 'emissao' | 'upload'
```

#### Property
```php
protected string $tipoData = 'emissao';
```

#### No handle() — payload condicional
```php
$payload = [
    'XmlType' => (int) $this->tipoDocumento,
    $this->tipoCnpj => $cnpj,
    'Take' => $this->take,
    'Skip' => $this->skip,
    'Downloadevent' => $this->event,
];

// Campos de data baseados no tipo
if ($this->tipoData === 'upload') {
    $payload['DataUploadInicio'] = $this->dataInicial;
    $payload['DataUploadFim'] = $this->dataFinal;
} else {
    $payload['DataEmissaoInicio'] = $this->dataInicial;
    $payload['DataEmissaoFim'] = $this->dataFinal;
}
```

---

### 2. Atualizar Console Commands (4 arquivos)

**Padrão para cada comando:** Adicionar option `--tipo-data` (default: emissao)

#### Exemplo: SyncNfe.php
```php
// Adicionar option no signature()
protected $signature = 'app:sync-nfe-sieg 
    {--data-inicial=} 
    {--data-final=} 
    {--tipo-cnpj=} 
    {--event=} 
    {--tipo-data=emissao}';  // ← NOVO

// No handle()
$tipoData = $this->option('tipo-data') ?? 'emissao';
// validar: in_array($tipoData, ['emissao', 'upload'])

SiegConnect::dispatch(
    ...
    tipoData: $tipoData,  // ← NOVO
);
```

**Mesma alteração para:** SyncCte.php, SyncNfce.php, SyncNfse.php

---

### 3. Atualizar Filament Page (SiegImport.php)

**Arquivo:** `app/Filament/Pages/Importar/SiegImport.php`

#### Adicionar campo no formulário
```php
// No schema do form (após tipoCnpj)
Select::make('tipoData')
    ->label('Tipo de Data')
    ->options([
        'emissao' => 'Data de Emissão',
        'upload' => 'Data de Upload/Recebimento',
    ])
    ->default('emissao')
    ->required(),
```

#### No dispatch (linha ~150)
```php
SiegConnect::dispatch(
    ...
    tipoData: $this->tipoData,  // ← NOVO
);
```

---

## Decisões e Suposições

| Decisão | Fundamentação |
|---------|---------------|
| Valor default `'emissao'` | Mantém compatibilidade com comportamento atual |
| String `'emissao'` | `'upload'` | Mais legível que boolean; extensível se houver mais tipos |
| Não validar no job | Validação nos pontos de entrada (commands/form); job assume valor válido |
| Manter `$this->dataInicial` / `$this->dataFinal` | Nomes genéricos; o job decide qual campo da API preencher |
| `tipoData` por job (não global) | Permite consultas mistas no mesmo importJob (ex: NFe por emissão, CTe por upload) |

---

## Verificação

### Testes Manuais

1. **Console Command:**
```bash
# Data de emissão (padrão)
vendor/bin/sail artisan app:sync-nfe-sieg --data-inicial=2025-01-01 --data-final=2025-01-31 --tipo-data=emissao

# Data de upload
vendor/bin/sail artisan app:sync-nfe-sieg --data-inicial=2025-01-01 --data-final=2025-01-31 --tipo-data=upload
```

2. **Filament:**
- Acessar `/admin/importar/sieg`
- Selecionar "Data de Upload/Recebimento" no dropdown
- Executar importação
- Verificar logs: deve aparecer `DataUploadInicio`/`DataUploadFim` no payload (se houver log de debug)

### Logs de Auditoria
- Canal `sieg_log` já captura erros
- Opcional: adicionar log do tipo de data usado no início do `handle()`

---

## Arquivos Afetados

| Arquivo | Tipo |
|---------|------|
| `app/Jobs/Sieg/SiegConnect.php` | Core — parâmetro + lógica condicional |
| `app/Console/Commands/Sieg/SyncNfe.php` | Console — option `--tipo-data` |
| `app/Console/Commands/Sieg/SyncCte.php` | Console — option `--tipo-data` |
| `app/Console/Commands/Sieg/SyncNfce.php` | Console — option `--tipo-data` |
| `app/Console/Commands/Sieg/SyncNfse.php` | Console — option `--tipo-data` |
| `app/Filament/Pages/Importar/SiegImport.php` | Filament — campo select + dispatch |

---

## Próximos Passos (após aprovação)

1. Implementar `SiegConnect.php` (parâmetro + payload condicional)
2. Atualizar 4 console commands
3. Atualizar `SiegImport.php`
4. Testar via console e Filament
5. Executar lint: `vendor/bin/sail bin pint --dirty`