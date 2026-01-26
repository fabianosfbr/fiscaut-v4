# XmlReaderService (XML → array)

O `XmlReaderService` padroniza a leitura de XMLs fiscais (NF-e, CT-e e eventos) e retorna um **array associativo** estruturado, pronto para ser consumido por serviços/jobs de persistência.

## Assinatura

```php
use App\Services\Xml\XmlReaderService;

$reader = app(XmlReaderService::class)->read($xmlString);
```

- Retorno: `array` associativo.
- Erros: lança exceção em XML malformado/ilegível.

## Estrutura do retorno (convenções)

O retorno é uma árvore baseada nos elementos do XML, com as seguintes regras:

- **Chave raiz**: o nome do elemento raiz do XML (ex.: `nfeProc`, `cteProc`, `procEventoNFe`, `procEventoCTe`, `resNFe`, `resEvento`, `retDistDFeInt`).
- **Elemento folha sem atributos**: `string` (sem cast numérico).
- **Elemento com filhos**: `array` associativo, com as chaves sendo os nomes dos elementos filhos.
- **Repetição de tags**: vira `array[]` (lista).
- **Atributos**: armazenados em `@attributes`:
  - `['@attributes' => ['Id' => '...', 'NSU' => '...']]`
- **Texto + atributos e/ou texto + filhos**: o conteúdo textual fica em `@content`:
  - `['@attributes' => [...], '@content' => '...']`

### Exemplo de tipos (ilustrativo)

O array retornado contém todos os dados necessários para a camada de persistência extrair e salvar. Exemplo de estrutura (ilustrativa):

```php
[
  'nfeProc' => [
    'NFe' => [
      'infNFe' => [
        'ide' => [
          'nNF' => '123',
        ],
        'emit' => [
          'xNome' => '...',
          'CNPJ' => '...',
        ],
        'det' => [
          [
            'prod' => [ 'cProd' => '...', 'xProd' => '...' ],
            'imposto' => [ 'ICMS' => [ /* ... */ ] ],
          ],
        ],
      ],
    ],
    'protNFe' => [
      'infProt' => [
        'chNFe' => '...',
        'cStat' => '100',
      ],
    ],
  ],
]
```

## Exemplos de uso (nova sintaxe por array)

### 1) Acesso a elementos aninhados

```php
$numero = $xml['nfeProc']['NFe']['infNFe']['ide']['nNF'] ?? null;
$chave = $xml['nfeProc']['protNFe']['infProt']['chNFe'] ?? null;
```

### 2) Acesso a listas (repetição de tags)

```php
$itens = $xml['nfeProc']['NFe']['infNFe']['det'] ?? [];

foreach ($itens as $det) {
    $codigo = $det['prod']['cProd'] ?? null;
    $descricao = $det['prod']['xProd'] ?? null;
}
```

### 3) Elementos opcionais

```php
$ie = $xml['nfeProc']['NFe']['infNFe']['emit']['IE'] ?? null;
$im = $xml['nfeProc']['NFe']['infNFe']['emit']['IM'] ?? null;
```

### 4) Atributos + conteúdo (docZip da SEFAZ)

O `docZip` costuma vir como lista com `@attributes` e `@content`:

```php
$docZipList = $xml['retDistDFeInt']['loteDistDFeInt']['docZip'] ?? [];

foreach ($docZipList as $docZip) {
    $nsu = $docZip['@attributes']['NSU'] ?? null;
    $base64Gzip = $docZip['@content'] ?? '';
}
```

## Guia de migração (antigo → novo)

### Exemplo solicitado

- Antes:

```php
$reader->value('nfeProc.retEvento')->get();
```

- Depois:

```php
$reader['nfeProc']['retEvento'];
```

### Padrões comuns

- **Leitura simples (um valor)**  
  - Antes: `$reader->value('protNFe.infProt.chNFe')->sole()`  
  - Depois: `$xml['nfeProc']['protNFe']['infProt']['chNFe'] ?? null`

- **Leitura de lista**  
  - Antes: `$reader->value('NFe.infNFe.det')->get()`  
  - Depois: `$xml['nfeProc']['NFe']['infNFe']['det'] ?? []`

- **Checagem de existência**  
  - Antes: `if ($reader->value('procEventoNFe')->get())`  
  - Depois: `if (!empty($xml['procEventoNFe'] ?? null))`

## Referência de API

### `XmlReaderService::read(string $xml): array`

- Faz o parse do XML e retorna o array estruturado conforme as convenções acima.
- Lança exceção quando o XML estiver malformado, vazio ou não puder ser parseado.

