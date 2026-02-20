# XML Reader Services (NF-e e CT-e)

O projeto utiliza serviços dedicados para leitura, identificação, parsing e persistência de XMLs fiscais, substituindo a implementação anterior mais genérica. Esses serviços padronizam o fluxo de importação (carregar XML → identificar/parsear → salvar), integram com logs/eventos do sistema e acionam rotinas complementares (jobs) quando necessário.

---

## Visão geral

### Serviços disponíveis

- **`XmlNfeReaderService`**
  - Leitura de **NF-e** (documento completo), **resumos** e **eventos** (ex.: cancelamento, carta de correção).
  - Extrai dados fiscais, integra com logs/eventos e persiste no modelo relacionado.

- **`XmlCteReaderService`**
  - Leitura de **CT-e** (documento completo) e **eventos** (ex.: cancelamento).
  - Pode associar automaticamente NF-es referenciadas, integra com logs/eventos e persiste no modelo relacionado.

### Dependências e integração (alto nível)

Os leitores especializados se apoiam em serviços de infraestrutura:

- **`XmlReaderService`**: parsing básico do XML em estrutura associativa (arrays).
- **`XmlIdentifierService`**: identificação do tipo de XML (NF-e, evento, resumo, CT-e etc.).
- Jobs/rotinas complementares:
  - Ex.: `CheckNfeData` (processamento adicional pós-importação).
- Modelos impactados:
  - **`NotaFiscalEletronica`**
  - **`ConhecimentoTransporteEletronico`**

> Observação: as antigas `NfeService` e `CteService` foram descontinuadas em favor desses leitores especializados.

---

## Fluxo padrão de uso

Ambos os serviços seguem o mesmo padrão encadeável (fluent interface):

1. **`loadXml($xmlString)`**: injeta o XML bruto (string).
2. **`setOrigem('...')`**: define a origem do XML.
3. **`setIssuer($issuer)`**: vincula o emissor/entidade no contexto do sistema.
4. **`parse()`**: identifica o tipo do XML e faz o parsing/normalização.
5. **`save()`**: persiste/atualiza dados no banco e executa integrações necessárias.

---

## XmlNfeReaderService

### Quando usar

Use quando você precisa importar:

- NF-e completa (`nfeProc`, `NFe`)
- Resumo de NF-e
- Eventos de NF-e (`procEventoNFe`, `evento`), como:
  - cancelamento
  - carta de correção

### Exemplo de uso

```php
use App\Services\Xml\XmlNfeReaderService;

$service = app(XmlNfeReaderService::class)
    ->loadXml($xmlString)
    ->setOrigem('IMPORTADO')  // IMPORTADO, SEFAZ ou SIEG
    ->setIssuer($issuer)
    ->parse()
    ->save();
```

### O que esperar do `save()`

Em termos práticos, o `save()`:

- persiste/atualiza a NF-e e estruturas relacionadas
- registra logs/eventos internos do sistema
- pode disparar jobs para validação/processamento adicional (ex.: `CheckNfeData`)

---

## XmlCteReaderService

### Quando usar

Use quando você precisa importar:

- CT-e completo (`cteProc`, `CTe`)
- Eventos de CT-e (ex.: cancelamento)

### Exemplo de uso

```php
use App\Services\Xml\XmlCteReaderService;

$service = app(XmlCteReaderService::class)
    ->loadXml($xmlString)
    ->setOrigem('IMPORTADO')  // IMPORTADO, SEFAZ ou SIEG
    ->setIssuer($issuer)
    ->parse()
    ->save();
```

### Particularidades

- Pode realizar **associação automática** com NF-es referenciadas (quando aplicável).
- Integra com o sistema de logs/eventos assim como o leitor de NF-e.

---

## Tipos de XML suportados

Os serviços identificam automaticamente o tipo do XML (não é necessário informar manualmente). Tipos suportados:

- `TIPO_NFE`: NF-e completa
- `TIPO_NFE_RESUMO`: Resumo de NF-e
- `TIPO_EVENTO_NFE`: Evento de NF-e (cancelamento, CCe etc.)
- `TIPO_CTE`: CT-e completo
- `TIPO_EVENTO_CTE`: Evento de CT-e (cancelamento)

---

## Estrutura do retorno (parsing)

Após o `parse()`, a estrutura gerada segue convenções para facilitar acesso:

- **Chave raiz**: baseada no documento original (ex.: `nfeProc`, `cteProc`).
- **Elementos repetitivos**: normalizados como **arrays**.
- **Atributos XML**: expostos via `@attributes` quando necessário.

Exemplo conceitual (formato ilustrativo):

```php
[
  'nfeProc' => [
    '@attributes' => [
      'versao' => '4.00',
    ],
    'NFe' => [
      'infNFe' => [
        // ...
      ],
    ],
    'protNFe' => [
      // ...
    ],
  ],
]
```

---

## Origem do XML (`setOrigem`)

A origem é usada para rastreabilidade e regras internas de processamento.

Valores documentados:

- `IMPORTADO`
- `SEFAZ`
- `SIEG`

Exemplo:

```php
->setOrigem('SEFAZ')
```

---

## Boas práticas

- **Sempre defina o `issuer`**: isso garante vínculo correto com a entidade/empresa emissora no sistema.
- **Use `parse()` antes de `save()`**: o `save()` pressupõe que o tipo do XML já foi identificado e os dados normalizados.
- **Reaproveite os leitores**: evite reimplementar parsing/identificação manual — os serviços já encapsulam essas regras.

---

## Migração (legado)

Se você possui trechos utilizando a implementação antiga:

- `NfeService` → migrar para **`XmlNfeReaderService`**
- `CteService` → migrar para **`XmlCteReaderService`**

O novo padrão melhora manutenção, tipagem do fluxo de importação e separa responsabilidades (identificação/parsing vs. persistência vs. integrações).

---

## Referências relacionadas (código)

- `App\Services\Xml\XmlNfeReaderService`
- `App\Services\Xml\XmlCteReaderService`
- `App\Services\Xml\XmlReaderService`
- `App\Services\Xml\XmlIdentifierService`
- Job: `CheckNfeData`
- Modelos:
  - `NotaFiscalEletronica`
  - `ConhecimentoTransporteEletronico`
