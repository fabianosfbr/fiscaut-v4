## Diagnóstico (estado atual)

* A view [CteTomadaInfolist.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/CteTomadas/Schemas/CteTomadaInfolist.php) consome vários atributos que não existem como colunas na tabela `ctes` e não estão hoje no model [ConhecimentoTransporteEletronico.php](file:///root/projetos/fiscaut-v4.1/app/Models/ConhecimentoTransporteEletronico.php).

* O model só expõe `cfop` via accessor e já possui rotina para ler XML possivelmente gz-comprimido (`extrairXmlComoString`).

* Os XMLs fornecidos (`xml-cte-1.xml`, `xml-cte-2.xml`, `xml-cte.xml`) confirmam a estrutura base `cteProc/CTe/infCte` e mostram variações relevantes:

  * `toma3/toma` presente (ex.: 3 no xml-cte-1, 0 no xml-cte-2)

  * ICMS pode vir como `ICMS00` (com `vBC/pICMS/vICMS`) ou `ICMSSN` (sem base/aliquota/valor).

## Ajustes no Model (principal)

* Refatorar o parsing do XML para acontecer uma única vez por request:

  * Criar um método privado `cteData()` que usa `XmlReaderService->read($xml)` e guarda o resultado em cache interno (propriedade privada), evitando reprocessar o XML em cada accessor.

  * Ajustar `getCfopAttribute()` para usar `cteData()` e corrigir o tipo de retorno (hoje está inconsistente: declara `int` mas retorna string vazia/null).

* Implementar accessors (no padrão do `getCfopAttribute`) para cobrir os campos usados pela view e que não existem na tabela:

  * Ide: `serie`, `tipo_tomador` (texto), e fallback de `tomador_razao_social`/`tomador_cnpj` via regra do `toma`.

  * Emitente: `emitente_logradouro`, `emitente_municipio`, `emitente_uf`, `emitente_cep`.

  * Remetente: `remetente_telefone`, `remetente_logradouro`, `remetente_municipio`, `remetente_uf`, `remetente_cep`.

  * Destinatário: `destinatario_telefone`, `destinatario_logradouro`, `destinatario_municipio`, `destinatario_uf`, `destinatario_cep`.

  * Expedidor/Recebedor (se existir no XML): `expedidor_*` e `recebedor_*` (nome, CNPJ/IE/xFant, telefone, logradouro/numero/complemento/bairro, municipio/uf/cep). Nos XMLs de validação esses nós não aparecem, então os accessors retornam `null`/string vazia e a view continua exibindo “Não informado”.

  * Impostos/valores:

    * `valor_servico` = `infCte/vPrest/vTPrest`

    * `valor_receber` = `infCte/vPrest/vRec`

    * `base_calculo_icms`, `aliquota_icms`, `valor_icms` = pegar o primeiro grupo válido em `infCte/imp/ICMS/*` (ex.: `ICMS00`, `ICMS20`, `ICMSSN`, etc.) e retornar `vBC/pICMS/vICMS` quando existirem.

* Estratégia de tolerância a variações do XML:

  * Telefones: buscar tanto no nó principal (ex.: `dest/fone`) quanto dentro do endereço (ex.: `emit/enderEmit/fone`), porque os XMLs têm formatos diferentes.

  * Tomador: mapear `toma` 0..3 para `rem/exped/receb/dest` e `toma=4` para `ide/toma4`.

## Ajustes pequenos na View (robustez)

* Onde há `formatStateUsing(fn (string $state) => ...)` (ex.: `tomador_cnpj`, `emitente_cnpj`, `remetente_cnpj`, `destinatario_cnpj`), trocar para aceitar `?string` e retornar “Não informado” quando `null`/vazio, evitando `TypeError` caso algum XML não tenha o dado.

## Validação com os XMLs fornecidos

* Criar testes (Pest) que instanciam `ConhecimentoTransporteEletronico` em memória, setam o atributo `xml` com o conteúdo dos arquivos e verificam:

  * `cfop` (5352/5353), `serie` (1/157), `nCTe` (se necessário via coluna existente), e `tipo_tomador` coerente com `toma`.

  * Endereços básicos de emitente/rem/dest batendo com os XMLs.

  * Impostos: no `xml-cte-2.xml` deve retornar `vBC=5.07`, `pICMS=12.00`, `vICMS=0.61`; no `xml-cte-1.xml` os campos devem ser `null` (pois é `ICMSSN`).

## Arquivos que serão alterados/criados

* Alterar: [ConhecimentoTransporteEletronico.php](file:///root/projetos/fiscaut-v4.1/app/Models/ConhecimentoTransporteEletronico.php)

* Alterar (robustez): [CteTomadaInfolist.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/CteTomadas/Schemas/CteTomadaInfolist.php)

* Criar: teste Pest para validar os accessors a partir de `xml-cte-1.xml`, `xml-cte-2.xml`, `xml-cte.xml`

