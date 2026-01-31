## Objetivo
- Criar a ação em lote de CT-e (download de XML e/ou PDF/DACTE em .zip) espelhando o padrão já usado na NF-e.

## Implementação (Arquivos Novos)
- Criar [DownloadXmlPdfCteEmLoteAction.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Actions/DownloadXmlPdfCteEmLoteAction.php) copiando a estrutura de [DownloadXmlPdfNfeEmLoteAction.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Actions/DownloadXmlPdfNfeEmLoteAction.php):
  - `BulkAction::make('download-xml-pdf-cte-em-lote')`
  - Mesmo modal (heading/description), 2 checkboxes (`download_xml`, `download_pdf`) com default `true`.
  - Mesma validação: exigir ao menos um checkbox marcado e exigir ao menos 1 registro com `xml`.
  - Disparar um job em background com `dispatch($records, $data, Auth::user()->id)`.

- Criar [DownloadXmlPdfCteEmLoteActionJob.php](file:///root/projetos/fiscaut-v4.1/app/Jobs/BulkAction/DownloadXmlPdfCteEmLoteActionJob.php) copiando [DownloadXmlPdfNfeEmLoteActionJob.php](file:///root/projetos/fiscaut-v4.1/app/Jobs/BulkAction/DownloadXmlPdfNfeEmLoteActionJob.php):
  - Mesmo caminho/estratégia do zip: `storage public` em `downloads/{m-Y}/{random}.zip` e criação via `ZipArchive`.
  - Para PDF, usar `NFePHP\DA\CTe\Dacte` (igual ao que já existe em [DownloadPdfCteAction.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Actions/DownloadPdfCteAction.php)).
  - Para XML, adicionar `{chave}.xml` com `gzuncompress($record->xml)`.
  - Enviar notificação ao banco com botão “Baixar arquivo” apontando para `/downloads/{filename}`.

## Integração no Resource (Uso)
- Atualizar [CteTomadasTable.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/CteTomadas/Tables/CteTomadasTable.php) para incluir `DownloadXmlPdfCteEmLoteAction::make()` dentro do `BulkActionGroup` (junto do `DeleteBulkAction`).

## Ajustes de Robustez (Compatíveis com o padrão atual)
- Inicializar `$erros = [];` no Job para evitar variável indefinida (mantendo a mesma lógica do job da NF-e).
- Opcional (se desejar manter 100% igual à NF-e, não aplico): despachar somente `$recordsWithXml` para o job, evitando exceções por registros sem XML.

## Verificação
- Rodar a suíte de testes/checagens PHP do projeto via Sail (para manter consistência do ambiente Docker).<mccoremem id="01KG5PCBPH5FWYH8HYAJV5GKSQ" />
- Validar manualmente no Filament: selecionar múltiplos CT-es na listagem, marcar XML/PDF, confirmar, e checar a notificação com link e o zip gerado em `public/downloads/{m-Y}`.
