## Mudança de diretriz (nova interface)
- O novo `XmlReaderService` passa a expor **um método principal que retorna um array associativo**, e o consumo no código será via **acesso por chaves** (ex.: `$reader['nfeProc']['retEvento']`).
- A sintaxe antiga baseada em métodos (`value()`, `get()`, `sole()`, `element()`, `getContent()`, `getAttributes()`) será tratada como **obsoleta** e removida da documentação e, no plano de refatoração, substituída no código.

## 1) Ajustes na documentação (obrigatório antes da implementação)
- Criar/atualizar um documento único de referência (ex.: `specs/xml-reader-service.md` ou equivalente no repositório) contendo:
  - **Seção “Retorno do Serviço”**: declarar explicitamente que `XmlReaderService::read(string $xml): array` retorna um **array estruturado**.
    - Especificar **estrutura base** (árvore do XML) e **convenções**:
      - Chave raiz: nome do elemento raiz (ex.: `nfeProc`, `cteProc`, `procEventoNFe`, `procEventoCTe`, `resNFe`, `retDistDFeInt`).
      - Repetição de tags: vira `array[]`.
      - Atributos: armazenar em `['@attributes' => ['NSU' => '...']]`.
      - Conteúdo textual quando há atributos/estrutura: armazenar em `['@content' => '...']`.
      - Folhas sem atributos: string.
    - Incluir um bloco com chaves “mapeadas para persistência” (exemplo de domínio, sem mudar regra de negócio):
      - `['numero' => string, 'chave' => string, 'emitente' => array, 'destinatario' => array, 'itens' => array[]]`.
      - Explicar que o array retornado contém os dados necessários para a camada de persistência extrair e salvar.
  - **Exemplos de uso**: revisar todos os exemplos para a nova sintaxe por chaves.
    - Exemplo equivalente ao solicitado:
      - Antes: `$reader->value('nfeProc.retEvento')->get()`
      - Depois: `$reader['nfeProc']['retEvento']`
    - Cobrir casos:
      - Elementos aninhados (`$xml['nfeProc']['NFe']['infNFe']['ide']['nNF']`).
      - Listas (`foreach ($xml['nfeProc']['NFe']['infNFe']['det'] as $det) { ... }`).
      - Elementos opcionais com `?? null`.
      - Atributos/contéudo (docZip):
        - `$docZip['@attributes']['NSU']` e `$docZip['@content']`.
  - **Guia de migração** (seção destacada): tabela “Antigo → Novo” com padrões comuns.
    - Incluir explicitamente o exemplo `$reader->value('nfeProc.retEvento')->get()` → `$reader['nfeProc']['retEvento']`.
  - **Referência de API**: documentar somente a interface nova (`read()` e, se necessário, helpers utilitários), removendo menções a `value()/get()`.

## 2) Implementação do `XmlReaderService` (SimpleXMLElement)
- Criar `App\Services\Xml\XmlReaderService` com:
  - `public function read(string $xml): array`
  - Parse com `simplexml_load_string` + tratamento de erro robusto (XML malformado, encoding, etc.).
  - Conversão recursiva para array preservando:
    - ordem e repetição;
    - namespaces (normalizar por `localName` para não depender de prefixos);
    - atributos em `@attributes`;
    - texto em `@content` quando necessário.
- Decidir e documentar “path resolution” na nova sintaxe:
  - Não haverá mais “path string”; a navegação será via chaves/arrays.
  - Para evitar `Undefined index`, padronizar o uso de `?? null` e/ou um helper opcional `xml_get(array $xml, array $path, mixed $default = null)` (a documentação continuará priorizando `[]`).

## 3) Refatoração do código para consumir array
- Atualizar `loadXmlReader()` em [helper.php](file:///root/projetos/fiscaut-v4.1/app/Helpers/helper.php#L1-L112) para retornar `array` (ex.: `app(XmlReaderService::class)->read($xml)`), e remover o import do pacote.
- Migrar todos os pontos que hoje usam a API do `XmlReader` (mapeados na análise) para acesso por array, incluindo:
  - Importação: [ProcessXmlFile](file:///root/projetos/fiscaut-v4.1/app/Jobs/ProcessXmlFile.php#L1-L153) e [ProcessDocumentXmlImportJob](file:///root/projetos/fiscaut-v4.1/app/Services/Sefaz/Process/ProcessDocumentXmlImportJob.php#L1-L53)
  - Jobs de resposta SEFAZ: [ProcessResponseNfeSefazJob](file:///root/projetos/fiscaut-v4.1/app/Services/Sefaz/Process/ProcessResponseNfeSefazJob.php#L1-L44), [ProcessXmlResponseNfeSefazJob](file:///root/projetos/fiscaut-v4.1/app/Services/Sefaz/Process/ProcessXmlResponseNfeSefazJob.php#L1-L54) e equivalentes de CTe
  - Regras de negócio/persistência: [HasNfe](file:///root/projetos/fiscaut-v4.1/app/Services/Sefaz/Traits/HasNfe.php#L1-L454), [HasCte](file:///root/projetos/fiscaut-v4.1/app/Services/Sefaz/Traits/HasCte.php#L1-L137), [HasLogSefaz](file:///root/projetos/fiscaut-v4.1/app/Services/Sefaz/Traits/HasLogSefaz.php#L1-L139)
  - PDF: [NfeHelper](file:///root/projetos/fiscaut-v4.1/app/Traits/NfeHelper.php#L1-L108) (substituir `values()` por uso direto do array retornado)
- Preservar a lógica de negócio (mesmos campos e mesma persistência), alterando apenas a forma de leitura.

## 4) Testes para garantir equivalência
- Adicionar fixtures XML (NFe, CTe, eventos e `retDistDFeInt` com `docZip`).
- Testes unitários do `XmlReaderService`:
  - atributos (`@attributes`) e conteúdo (`@content`);
  - repetição de tags e folhas;
  - namespaces (NFe/CTe reais usam default namespace).
- Testes de integração:
  - Executar jobs/serviços principais e validar que os registros persistidos têm os mesmos valores esperados.

## 5) Remoção da dependência
- Remover `saloonphp/xml-wrangler` do [composer.json](file:///root/projetos/fiscaut-v4.1/composer.json#L1-L100) e garantir que não existam mais imports/referências ao pacote.

## Critérios de conclusão
- Documentação declara retorno `array`, exemplos e API atualizados, e guia de migração incluído.
- Código usa a nova sintaxe por array em todos os pontos.
- Testes passam, e `saloonphp/xml-wrangler` removido do projeto.