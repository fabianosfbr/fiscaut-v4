## Contexto
- O XML de referência [xml-nfse.xml](file:///root/projetos/fiscaut-v4.1/xml-nfse.xml) (layout SPED NFSe 1.01) traz, entre outros: `cNBS`, `xDescServ`, `xInfComp`, `vBC`, `pAliqAplic`, `vISSQN`, `dCompet`, `xLocIncid/cLocIncid`, e o endereço do prestador em `emit/enderNac`.
- Hoje, parte dos accessors do model usa `data_get()` com paths que não batem com esse XML (ex.: discriminação e alguns valores de ISS), e o Infolist tem um `dd()` que interrompe a tela.

## Ajustes no Model (NotaFiscalServico)
- Manter como padrão o mesmo estilo de [NotaFiscalServico.php:L109-L114](file:///root/projetos/fiscaut-v4.1/app/Models/NotaFiscalServico.php#L109-L114): pegar `$data = $this->nfseRoot()` e extrair via `searchValueInArray($data, '<CHAVE>')`.
- Refatorar os accessors abaixo para usar `searchValueInArray` e alinhar com o XML de verdade:
  - `getNfseDescricaoServicoExtraidaAttribute()`:
    - Priorizar `xDescServ` (descrição do serviço) e como fallback `xTribNac`.
    - Evitar retornar `cTribNac` como “descrição” (deixar apenas como último fallback se fizer sentido).
  - `getNfseDiscriminacaoExtraidaAttribute()`:
    - Incluir `xInfComp` (do `infoCompl`) como fonte principal para este XML.
    - Manter fallbacks existentes quando aparecerem em outros layouts.
  - `getNfseValorIssExtraidoAttribute()`:
    - Considerar `vISSQN` (presente em `infNFSe/valores`) além de chaves antigas.
  - `getNfseBaseCalculoIssExtraidaAttribute()`:
    - Extrair de `vBC` via `searchValueInArray`.
  - `getNfseAliquotaIssExtraidaAttribute()`:
    - Considerar `pAliqAplic` além de `pAliq`.
  - `getNfseIssRetidoExtraidoAttribute()`:
    - Permanecer com `vISSRet` como chave principal e fallbacks se necessário.

## Ajustes no Infolist (NfseEntradaInfolist)
- Remover o `dd($record->nfse_discriminacao_extraida);` em [NfseEntradaInfolist.php:L218-L233](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/NfseEntradas/Schemas/NfseEntradaInfolist.php#L218-L233) para a tela voltar a renderizar.
- Atualizar os paths de extração (funções `nfseStringFromRecord` / `nfseEnderecoFromRoot` / `nfseValoresFromRecord`) para refletirem o XML:
  - Competência: incluir `infNFSe.DPS.infDPS.dCompet` (no XML não há `ide.dCompet`).
  - Município de incidência: priorizar `infNFSe.xLocIncid` / `infNFSe.cLocIncid`.
  - Endereço do prestador: incluir caminho `infNFSe.emit` (especialmente `emit.enderNac`), pois no XML o prestador “fiscal” está em `emit`, não em `prest`.
  - Valores (vServ e outros): ampliar `nfseValoresFromRecord()` para também buscar `infNFSe.DPS.infDPS.valores` e permitir leitura de valores aninhados (ex.: `vServPrest.vServ`).

## Validação
- Criar/ajustar um teste automatizado (Pest) que:
  - Leia o conteúdo de `xml-nfse.xml`, alimente o `NotaFiscalServico` (ex.: via `xml_content`) e valide os accessors (`nfse_codigo_servico_extraido`, `nfse_descricao_servico_extraida`, `nfse_discriminacao_extraida`, `nfse_base_calculo_iss_extraida`, `nfse_valor_iss_extraido`, `nfse_aliquota_iss_extraida`) contra os valores do XML.
- Rodar a suíte de testes para garantir que não há regressões.

## Resultado esperado
- Os campos exibidos no Infolist passam a refletir corretamente o XML de referência e deixam de quebrar a UI.
- As extrações principais do model ficam mais resilientes a variações de layout por dependerem de `searchValueInArray`.
