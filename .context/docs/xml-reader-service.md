# XML Reader Services

O projeto agora utiliza serviços dedicados para a leitura de XMLs de NF-e e CT-e, substituindo a antiga implementação genérica.

## Serviços Disponíveis

### XmlNfeReaderService
Responsável pela leitura de XMLs de Nota Fiscal Eletrônica (NF-e) e eventos relacionados.

**Funcionalidades principais:**
- Leitura e parsing de XMLs de NF-e completas
- Processamento de resumos de NF-e
- Tratamento de eventos de NF-e (cancelamentos, cartas de correção)
- Integração com o sistema de logs e eventos do sistema
- Extração e persistência de dados fiscais

**Assinatura:**
```php
use App\Services\Xml\XmlNfeReaderService;

$service = app(XmlNfeReaderService::class)
    ->loadXml($xmlString)
    ->setOrigem('IMPORTADO')  // IMPORTADO, SEFAZ ou SIEG
    ->setIssuer($issuer)
    ->parse()
    ->save();
```

### XmlCteReaderService
Responsável pela leitura de XMLs de Conhecimento de Transporte Eletrônico (CT-e) e eventos relacionados.

**Funcionalidades principais:**
- Leitura e parsing de XMLs de CT-e completas
- Processamento de eventos de CT-e (cancelamentos)
- Associação automática com NF-es referenciadas
- Integração com o sistema de logs e eventos do sistema

**Assinatura:**
```php
use App\Services\Xml\XmlCteReaderService;

$service = app(XmlCteReaderService::class)
    ->loadXml($xmlString)
    ->setOrigem('IMPORTADO')  // IMPORTADO, SEFAZ ou SIEG
    ->setIssuer($issuer)
    ->parse()
    ->save();
```

## Tipos de XML Suportados

Os serviços identificam automaticamente os seguintes tipos de XML:
- `TIPO_NFE`: Notas Fiscais Eletrônicas completas
- `TIPO_NFE_RESUMO`: Resumos de NF-e
- `TIPO_EVENTO_NFE`: Eventos de NF-e (cancelamentos, cartas de correção)
- `TIPO_CTE`: Conhecimentos de Transporte Eletrônicos completos
- `TIPO_EVENTO_CTE`: Eventos de CT-e (cancelamentos)

## Estrutura do Retorno
Ambos os serviços retornam um `array` associativo seguindo as convenções de estrutura do XML original.

- **Chave raiz**: Baseada no tipo de documento (ex: `nfeProc`, `cteProc`).
- **Listas**: Elementos repetitivos são normalizados para arrays.
- **Atributos**: Acessíveis via chave `@attributes` quando necessário.

## Integração com Outros Serviços
- Utilizam `XmlReaderService` para o parsing básico do XML
- Utilizam `XmlIdentifierService` para identificação do tipo de XML
- Disparam jobs como `CheckNfeData` para processamento adicional
- Atualizam modelos como `NotaFiscalEletronica` e `ConhecimentoTransporteEletronico`

## Migração
A antiga `NfeService` e `CteService` foram descontinuadas em favor destes leitores especializados que oferecem melhor tipagem e manutenção.
