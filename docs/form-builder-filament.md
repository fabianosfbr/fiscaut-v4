# Form Builder Dinâmico (Filament)

> **Confidencial**: Fiscaut é um produto comercial proprietário. Este documento é interno.

Este guia descreve como usar o serviço de form builder dinâmico localizado em `app/Services/Filament` para renderizar formulários do Filament a partir de definições persistidas em banco de dados.

## Visão geral

O fluxo principal é:

1. Um **modelo de campos** implementa `FormFieldInterface`.
2. O serviço `FormBuilderRender` consulta esses campos e gera componentes Filament.
3. O resultado é usado diretamente no `->schema()` de um Form do Filament.

Componentes principais:

- `FormBuilderRender`: fachada simples para configurar o builder e renderizar.
- `FormBuilder`: configura query, agrupamento, ordenação e estado (readonly).
- `FormGeneratorService`: transforma os registros em `Field` do Filament.
- `FieldGeneratorService`: mapeia `FieldTypesEnum` e `FieldAttributesEnum` para componentes.

## Requisitos do modelo

O modelo que representa cada “campo” precisa implementar `App\Services\Filament\Contracts\FormFieldInterface`.

Campos mínimos esperados pelo serviço:

- `getName()`: nome do campo (chave de estado do Filament).
- `getLabel()`: rótulo exibido.
- `getType()`: `FieldTypesEnum::Input` ou `FieldTypesEnum::Select`.
- `getFieldAttribute()`: `FieldAttributesEnum` (ex.: `Text`, `Number`, `Checkbox`, `Radio`, `Multiple`).
- `getDefaultGroup()`: grupo padrão usado para separar seções/steps.
- `getGroupFromRelationship($group)`: resolve o grupo via relacionamento quando `group()` é usado.
- `options()`: coleção/array usado como `datalist` para inputs e base para selects.

Interfaces opcionais que ativam comportamentos extras:

- `HasOptions`: obrigatório para campos do tipo `Select`.
- `HasAcceptedFileTypes`: permite definir `acceptedFileTypes()` para `FileUpload`.
- `HasFileUploadOptions`: permite configurar `directory()`, `disk()`, `maxSize()` e `preserveFilenames()` para `FileUpload`. `directory()` aceita `string` ou `Closure`.
- `HasInputOptions`: permite configurar `placeholder()` e `mask()` para `TextInput`.
- `HasRepeaterSchema`: permite definir a `schema` do `Repeater` via JSON.
- `HasDependantFields`: permite campos dependentes (container + lista de fields).
- `CanHandleFieldState`: fornece `afterStateUpdated()`.
- `CanDehydrateState`: fornece `dehydrateStateUsing()`.

## Tipos e atributos suportados

Tipos (`FieldTypesEnum`):

- `Input`
- `Select`
- `Repeater`

Atributos (`FieldAttributesEnum`) usados no mapeamento:

- Para `Input`: `Text` (padrão), `Number`, `Checkbox`, `File`
- Para `Select`: `Radio`, `Multiple`, padrão simples (select único)

## Uso básico (sem container)

```php
use App\Services\Filament\FormBuilderRender;
use App\Models\IssuerControlField;

// Dentro do schema do Form do Filament
FormBuilderRender::make()
    ->form(IssuerControlField::class)
    ->render()
```

Resultado: um array de `Field` pronto para ser usado no `->schema()`.

## Uso com agrupamento e container (Wizard/Section/etc.)

Quando há agrupamento, o serviço agrupa os campos pelo relacionamento informado em `group()` e cria steps dentro do container.

```php
use App\Services\Filament\FormBuilderRender;
use App\Models\IssuerControlField;
use Filament\Schemas\Components\Wizard;

FormBuilderRender::make()
    ->form(IssuerControlField::class)
    ->group('groupControl.name')
    ->sort('order', 'asc')
    ->container(Wizard::class)
    ->defaultStepClass(\\Filament\\Schemas\\Components\\Wizard\\Step::class)
    ->render()
```

Observações importantes:

- `group('relacionamento.campo')` faz `with()` automaticamente no relacionamento.
- `sort()` **não** aceita relacionamento (ex.: `relation.field`) e lança exceção.
- Para respeitar a ordem dos grupos (`issuer_group_controls.order`), use `modifyQueryUsing()` com `join` e `orderBy` do grupo antes do `order` dos campos.
- `container()` exige um componente Filament que implemente `CanBeContained` e possua método `make()`.
- Se o container for `Wizard`, o resultado é marcado como `skippable()`.
- `defaultStepClass()` permite trocar a classe de Step (precisa estender `Wizard\\Step`).

## Required dinâmico

A obrigatoriedade é definida por um callback no builder:

```php
FormBuilderRender::make()
    ->form(IssuerControlField::class)
    ->requiredCondition(function ($field) {
        return $field->getRequired();
    })
    ->render();
```

Nota: o método `getRequired()` existe na interface, mas só é aplicado via `requiredCondition()`.

## Readonly

O modo readonly desabilita todos os campos.

```php
FormBuilderRender::make()
    ->form(IssuerControlField::class)
    ->readonly()
    ->render();
```

## Exemplo prático: controles por Issuer

A ideia é criar “blocos” de campos por assunto (seguro, AVCB, para-raios) e associá-los ao Issuer atual.
Você pode estruturar seus registros como um conjunto de campos com o grupo adequado.

Exemplo de grupos sugeridos:

- `seguro`
- `avcb`
- `pararaios`

### Exemplo de dados (sintético)

```text
seguro:
- numero_apolice (Input/Text)
- nome_seguradora (Input/Text)
- bonus (Input/Text)
- nome_corretora (Input/Text)
- vigencia (Input/Text)
- valor_premio (Input/Number)

avcb:
- documento (Input/File)
- vigencia (Input/Text)
- projeto (Input/Text)
- responsavel (Input/Text)

pararaios:
- vigencia (Input/Text)
- projeto (Input/Text)
- responsavel (Input/Text)
```

### Renderização sugerida no Form do Issuer

```php
use App\Services\Filament\FormBuilderRender;
use App\Models\IssuerControlField;
use Filament\Schemas\Components\Wizard;

FormBuilderRender::make()
    ->form(IssuerControlField::class)
    ->group('groupControl.name')
    ->sort('order', 'asc')
    ->container(Wizard::class)
    ->defaultStepClass(\\Filament\\Schemas\\Components\\Wizard\\Step::class)
    ->requiredCondition(fn ($field) => $field->getRequired())
    ->render()
```

## Pontos de atenção

- O modelo **deve** implementar `FormFieldInterface`.
- Campos do tipo `Select` **devem** implementar `HasOptions`.
- `options()` e `getOptions()` são tratados como listas simples de `label => value`.
- Campos dependentes exigem `HasDependantFields` e um container do tipo `Field`.
- O agrupamento determina os steps quando `container()` é usado.
- `attribute = file` renderiza `FileUpload` com configuração padrão.
- Se o modelo implementar `HasAcceptedFileTypes`, o serviço aplica `acceptedFileTypes($accepted_types)`.
- Se o modelo implementar `HasFileUploadOptions`, o serviço aplica `directory()`, `disk()`, `maxSize()` e `preserveFilenames()`. O `directory()` pode ser uma `Closure` para diretórios dinâmicos.
- Se o modelo implementar `HasInputOptions`, o serviço aplica `placeholder()` e `mask()` ao `TextInput`.
- Se o modelo implementar `HasRepeaterSchema`, o serviço renderiza um `Repeater` com a `schema` definida.

Exemplo de `directory()` dinâmico:

```php
public function getFileDirectory(): string|Closure|null
{
    return function () {
        $issuer = currentIssuer();
        if (! $issuer) {
            return null;
        }

        return 'rag/' . $issuer->tenant_id . '/' . sanitize($issuer->cnpj) . '/documents';
    };
}
```

Observação sobre o formulário do `FieldsRelationManager`:
o campo **Diretório do arquivo** salva apenas **string** no banco.
Closures **não** podem ser persistidas via form. Para diretório dinâmico:
1) implemente `getFileDirectory()` no model retornando uma `Closure`, ou
2) use um template de string no banco e resolva via código (ex.: `{tenant_id}`).

### Resolver de placeholders (FileUpload)

Quando `file_directory` é uma string, o serviço substitui placeholders automaticamente:

- `{tenant_id}`: ID do tenant atual.
- `{issuer_id}`: ID do issuer atual.
- `{cnpj}`: CNPJ original do issuer.
- `{cnpj_sanitized}`: CNPJ do issuer sanitizado.

Exemplo:

```text
rag/{tenant_id}/{cnpj_sanitized}/documents
```

## Modelo de dados real (implementado)

Este projeto possui os modelos reais `IssuerGroupControl` e `IssuerControlField` com migrations próprias.

### Migration (real)

```php
Schema::create('issuer_group_controls', function (Blueprint $table) {
    $table->id();
    $table->foreignId('issuer_id')->constrained()->cascadeOnDelete();
    $table->string('name'); // ex: seguro, avcb, para-raios
    $table->unsignedInteger('order')->default(0);
    $table->text('description')->nullable();
    $table->timestamps();

    $table->unique(['issuer_id', 'name']);
});

Schema::create('issuer_control_fields', function (Blueprint $table) {
    $table->id();
    $table->foreignId('issuer_id')->constrained()->cascadeOnDelete();
    $table->foreignId('issuer_group_control_id')->nullable()->constrained('issuer_group_controls')->nullOnDelete();

    $table->string('key'); // ex: numero_apolice
    $table->string('label'); // ex: Número da apólice
    $table->string('type'); // FieldTypesEnum
    $table->string('attribute')->nullable(); // FieldAttributesEnum
    $table->text('description')->nullable();
    $table->boolean('required')->default(false);
    $table->unsignedInteger('order')->default(0);

    // Armazena opções para select/radio/multiple (label => value)
    $table->json('options')->nullable();
    // MIME types aceitos para FileUpload
    $table->json('accepted_types')->nullable();
    // Opções adicionais de FileUpload
    $table->string('file_directory')->nullable();
    $table->string('file_disk')->nullable();
    $table->unsignedInteger('file_max_size')->nullable();
    $table->boolean('preserve_filenames')->default(false);
    $table->string('input_placeholder')->nullable();
    $table->string('input_mask')->nullable();
    $table->json('repeater_schema')->nullable();

    $table->timestamps();

    $table->unique(['issuer_id', 'key']);
    $table->index(['issuer_id', 'issuer_group_control_id']);
});
```

### Model (real)

```php
use App\\Enums\\FieldAttributesEnum;
use App\\Enums\\FieldTypesEnum;
use App\\Services\\Filament\\Contracts\\FormFieldInterface;
use App\\Services\\Filament\\Contracts\\HasAcceptedFileTypes;
use App\\Services\\Filament\\Contracts\\HasFileUploadOptions;
use App\\Services\\Filament\\Contracts\\HasInputOptions;
use App\\Services\\Filament\\Contracts\\HasOptions;
use App\\Services\\Filament\\Contracts\\HasRepeaterSchema;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class IssuerControlField extends Model implements FormFieldInterface, HasOptions, HasAcceptedFileTypes, HasFileUploadOptions, HasInputOptions, HasRepeaterSchema
{
    protected $casts = [
        'options' => 'array',
        'accepted_types' => 'array',
        'preserve_filenames' => 'boolean',
        'required' => 'boolean',
    ];

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(Issuer::class);
    }

    public function groupControl(): BelongsTo
    {
        return $this->belongsTo(IssuerGroupControl::class, 'issuer_group_control_id');
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
        return $this->attribute ? FieldAttributesEnum::tryFrom($this->attribute) ?? $this->attribute : null;
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

    public function options()
    {
        return collect($this->options ?? []);
    }

    public function getOptions(): array
    {
        return $this->options ?? [];
    }

    public function getAcceptedFileTypes(): ?array
    {
        return $this->accepted_types ?? null;
    }

    public function getFileDirectory(): string|Closure|null
    {
        return $this->file_directory ?: null;
    }

    public function getFileMaxSize(): ?int
    {
        return $this->file_max_size ?: null;
    }

    public function getFileDisk(): ?string
    {
        return $this->file_disk ?: null;
    }

    public function shouldPreserveFilenames(): bool
    {
        return (bool) ($this->preserve_filenames ?? false);
    }

    public function getInputMask(): ?string
    {
        return $this->input_mask ?: null;
    }

    public function getInputPlaceholder(): ?string
    {
        return $this->input_placeholder ?: null;
    }

    public function getRepeaterSchema(): array
    {
        return $this->repeater_schema ?? [];
    }
}
```

### Seeder (sintético)

```php
IssuerGroupControl::insert([
    ['issuer_id' => 1, 'name' => 'seguro', 'order' => 1],
    ['issuer_id' => 1, 'name' => 'avcb', 'order' => 2],
    ['issuer_id' => 1, 'name' => 'pararaios', 'order' => 3],
]);

IssuerControlField::insert([
    // Seguro
    ['issuer_id' => 1, 'issuer_group_control_id' => 1, 'key' => 'numero_apolice', 'label' => 'Número da apólice', 'type' => 'input', 'attribute' => 'text', 'required' => true, 'order' => 1, 'input_placeholder' => 'Digite o número da apólice'],
    ['issuer_id' => 1, 'issuer_group_control_id' => 1, 'key' => 'nome_seguradora', 'label' => 'Seguradora', 'type' => 'input', 'attribute' => 'text', 'required' => true, 'order' => 2],
    ['issuer_id' => 1, 'issuer_group_control_id' => 1, 'key' => 'bonus', 'label' => 'Bônus', 'type' => 'input', 'attribute' => 'text', 'required' => false, 'order' => 3],
    ['issuer_id' => 1, 'issuer_group_control_id' => 1, 'key' => 'nome_corretora', 'label' => 'Corretora', 'type' => 'input', 'attribute' => 'text', 'required' => false, 'order' => 4],
    ['issuer_id' => 1, 'issuer_group_control_id' => 1, 'key' => 'vigencia', 'label' => 'Vigência', 'type' => 'input', 'attribute' => 'text', 'required' => true, 'order' => 5, 'input_mask' => '99/99/9999', 'input_placeholder' => 'DD/MM/AAAA'],
    ['issuer_id' => 1, 'issuer_group_control_id' => 1, 'key' => 'valor_premio', 'label' => 'Valor do prêmio', 'type' => 'input', 'attribute' => 'number', 'required' => false, 'order' => 6],

    // AVCB
    ['issuer_id' => 1, 'issuer_group_control_id' => 2, 'key' => 'documento_avcb', 'label' => 'Documento', 'type' => 'input', 'attribute' => 'file', 'required' => true, 'order' => 1, 'accepted_types' => ['application/pdf'], 'file_directory' => 'issuer/controls'],
    ['issuer_id' => 1, 'issuer_group_control_id' => 2, 'key' => 'vigencia_avcb', 'label' => 'Vigência', 'type' => 'input', 'attribute' => 'text', 'required' => true, 'order' => 2],
    ['issuer_id' => 1, 'issuer_group_control_id' => 2, 'key' => 'projeto_avcb', 'label' => 'Projeto', 'type' => 'input', 'attribute' => 'text', 'required' => false, 'order' => 3],
    ['issuer_id' => 1, 'issuer_group_control_id' => 2, 'key' => 'responsavel_avcb', 'label' => 'Responsável', 'type' => 'input', 'attribute' => 'text', 'required' => false, 'order' => 4],

    // Para-raios
    ['issuer_id' => 1, 'issuer_group_control_id' => 3, 'key' => 'vigencia_pararaios', 'label' => 'Vigência', 'type' => 'input', 'attribute' => 'text', 'required' => true, 'order' => 1],
    ['issuer_id' => 1, 'issuer_group_control_id' => 3, 'key' => 'projeto_pararaios', 'label' => 'Projeto', 'type' => 'input', 'attribute' => 'text', 'required' => false, 'order' => 2],
    ['issuer_id' => 1, 'issuer_group_control_id' => 3, 'key' => 'responsavel_pararaios', 'label' => 'Responsável', 'type' => 'input', 'attribute' => 'text', 'required' => false, 'order' => 3],
]);
```

### Exemplo de `repeater_schema` (manutenções programadas)

```php
IssuerControlField::insert([
    [
        'issuer_id' => 1,
        'issuer_group_control_id' => 4,
        'key' => 'manutencoes_programadas',
        'label' => 'Manutenções programadas',
        'type' => 'repeater',
        'required' => false,
        'order' => 1,
        'repeater_schema' => [
            [
                'name' => 'tipo',
                'label' => 'Tipo de manutenção',
                'type' => 'select',
                'required' => true,
                'options' => [
                    "Caixa d'água" => "Caixa d'água",
                    'Extintor' => 'Extintor',
                    'Pressurização' => 'Pressurização',
                    'Caixa de gordura' => 'Caixa de gordura',
                    'Dedetização' => 'Dedetização',
                    'Teste de Mangueiras de incêndio' => 'Teste de Mangueiras de incêndio',
                    'Pluvial' => 'Pluvial',
                    'Calhas' => 'Calhas',
                    'Gerador' => 'Gerador',
                    'Teste de água (ph, etc)' => 'Teste de água (ph, etc)',
                    'Iluminação de emergência' => 'Iluminação de emergência',
                    'Teste alarme de incêndio / sprinter' => 'Teste alarme de incêndio / sprinter',
                    'Vistoria playground' => 'Vistoria playground',
                    'Manutenção periódica Elevador' => 'Manutenção periódica Elevador',
                    'Aquecedores' => 'Aquecedores',
                    'Manutenção de ar condicionado' => 'Manutenção de ar condicionado',
                    'Pintura fachada' => 'Pintura fachada',
                    'Hidrantes' => 'Hidrantes',
                    'Inventário – Ativo imobilizado' => 'Inventário – Ativo imobilizado',
                    'Outro' => 'Outro',
                ],
            ],
            [
                'name' => 'outro',
                'label' => 'Outro (se aplicável)',
                'type' => 'text',
                'required' => false,
                'placeholder' => 'Descreva a manutenção',
            ],
            [
                'name' => 'data_programada',
                'label' => 'Data de programação',
                'type' => 'text',
                'required' => false,
                'mask' => '99/99/9999',
                'placeholder' => 'DD/MM/AAAA',
            ],
        ],
    ],
]);
```

Exemplo de diretório dinâmico **via código** (não via banco):

```php
class IssuerControlField extends Model implements FormFieldInterface, HasOptions, HasAcceptedFileTypes, HasFileUploadOptions, HasInputOptions, HasRepeaterSchema
{
    public function getFileDirectory(): string|Closure|null
    {
        if ($this->attribute !== 'file') {
            return $this->file_directory ?: null;
        }

        return function () {
            $issuer = currentIssuer();
            if (! $issuer) {
                return null;
            }

            return 'rag/' . $issuer->tenant_id . '/' . sanitize($issuer->cnpj) . '/documents';
        };
    }
}
```
## Arquivos relacionados

- `app/Services/Filament/FormBuilderRender.php`
- `app/Services/Filament/FormBuilder.php`
- `app/Services/Filament/FormGeneratorService.php`
- `app/Services/Filament/Fields/FieldGeneratorService.php`
- `app/Services/Filament/Contracts/*.php`
- `app/Services/Filament/Contracts/HasAcceptedFileTypes.php`
- `app/Services/Filament/Contracts/HasFileUploadOptions.php`
- `app/Services/Filament/Contracts/HasInputOptions.php`
- `app/Services/Filament/Contracts/HasRepeaterSchema.php`
- `app/Enums/FieldTypesEnum.php`
- `app/Enums/FieldAttributesEnum.php`
- `app/Models/IssuerGroupControl.php`
- `app/Models/IssuerControlField.php`
