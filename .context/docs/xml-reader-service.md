# XML Reader Services

O projeto agora utiliza serviços dedicados para a leitura de XMLs de NF-e e CT-e, substituindo a antiga implementação genérica.

## Serviços Disponíveis

### XmlNfeReaderService
Responsável pela leitura de XMLs de Nota Fiscal Eletrônica (NF-e).

**Assinatura:**
```php
use App\Services\Xml\XmlNfeReaderService;

$nfeData = app(XmlNfeReaderService::class)->read($xmlString);
```

### XmlCteReaderService
Responsável pela leitura de XMLs de Conhecimento de Transporte Eletrônico (CT-e).

**Assinatura:**
```php
use App\Services\Xml\XmlCteReaderService;

$cteData = app(XmlCteReaderService::class)->read($xmlString);
```

## Estrutura do Retorno
Ambos os serviços retornam um `array` associativo seguindo as convenções de estrutura do XML original.

- **Chave raiz**: Baseada no tipo de documento (ex: `nfeProc`, `cteProc`).
- **Listas**: Elementos repetitivos são normalizados para arrays.
- **Atributos**: Acessíveis via chave `@attributes` quando necessário.

## Migração
A antiga `NfeService` e `CteService` foram descontinuadas em favor destes leitores especializados que oferecem melhor tipagem e manutenção.
