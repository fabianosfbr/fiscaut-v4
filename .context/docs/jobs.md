# Background Jobs (Filas) — `docs/jobs.md`

Este documento descreve como o **Fiscaut** executa tarefas assíncronas usando o **Laravel Queue** (com **Redis**) e como as filas são gerenciadas via **Laravel Horizon**. Também apresenta os *jobs* mais importantes, seus papéis e como eles se conectam em pipelines (SEFAZ e SIEG), além de jobs usados em importações e ações em lote.

---

## Visão geral

Algumas operações do sistema são custosas (download/processamento de XMLs, geração de PDF, criação de ZIP, importações massivas). Para evitar travar a UI e para permitir escalabilidade, elas rodam em **background**:

- Usuário dispara uma ação (upload/importação/download em lote).
- O sistema **despacha** um Job para uma **fila**.
- Um ou mais **workers** (Horizon) consomem a fila e executam o trabalho.
- Progresso/resultado é persistido (ex.: `XmlImportJob`) e, quando aplicável, o usuário é **notificado**.

---

## Gerenciamento de filas com Laravel Horizon

O projeto utiliza **Laravel Horizon** para:

- monitorar filas Redis (dashboard);
- controlar supervisores/workers por ambiente (`local` vs `production`);
- balancear processos dinamicamente com estratégia `auto`.

### Filas (queues) utilizadas

A configuração separa cargas de trabalho por filas para isolamento e desempenho:

- **`sefaz`**: downloads/processamento do ambiente SEFAZ (NFe/CTe)  
- **`sieg`**: integrações com a API SIEG  
- **`default`**: tarefas gerais  
- **`low`**: tarefas longas (ETL, importações grandes, ações massivas)

> Para detalhes operacionais e ajustes em produção, consulte: **[Horizon em Produção](../../docs/horizon-producao.md)**.

---

## Padrões e conceitos usados nos Jobs

### 1) Padrão “Coordinator → Batch Manager → Worker”

Para operações com grande volume (SEFAZ/SIEG), os jobs tendem a ser organizados em três níveis:

1. **Coordinator (entrada)**: consulta fonte externa, cria “tracking” e inicia o lote.
2. **Batch Manager (orquestrador)**: cria um **Bus Batch** com vários workers e monitora conclusão.
3. **Worker (processador unitário)**: processa 1 documento por vez e persiste no banco/log.

### 2) Tracking de importação e progresso

Em vários fluxos, é criado/atualizado um registro de acompanhamento (ex.: **`XmlImportJob`**) para:

- contabilizar progresso (processados vs total);
- marcar status (ex.: `COMPLETED`);
- centralizar logs/observabilidade.

### 3) Parsing/Identificação de XML

Os pipelines reutilizam serviços (alto nível):

- `XmlExtractorService`: extrai conteúdo XML de arquivo (quando a origem é filesystem)
- `XmlIdentifierService`: identifica tipo do XML (NFe, CTe, Evento, Resumo)
- `XmlNfeReaderService` / `XmlCteReaderService`: faz parsing e persistência

---

## Core Jobs (Importação manual)

### `App\Jobs\ProcessXmlFile`

**Propósito:** processar um único XML importado via upload/arquivo.

**Fluxo:**
1. valida se arquivo existe;
2. extrai conteúdo com `XmlExtractorService`;
3. identifica tipo com `XmlIdentifierService`;
4. delega parsing (`XmlNfeReaderService` / `XmlCteReaderService`);
5. persiste no banco;
6. atualiza progresso em `XmlImportJob`.

**Resiliência (retry):**
- `tries = 3`
- `backoff = 60s`  
Adequado para falhas temporárias de filesystem ou leitura.

**Quando é usado:** uploads manuais/importações unitárias.

---

### `App\Jobs\ProcessXmlFileBatch`

**Propósito:** orquestrar a importação de múltiplos XMLs em lote.

**Fluxo:**
1. recebe lista de paths + import job/issuer;
2. despacha um `ProcessXmlFile` por arquivo em um **Bus Batch**;
3. monitora conclusão para atualizar status geral do `XmlImportJob`.

**Quando é usado:** importações em massa via interface administrativa.

---

## Jobs de Ações em Lote (Bulk Actions)

Esses jobs normalmente:
- recebem uma seleção de registros,
- geram arquivos (PDF/ZIP),
- e notificam o usuário quando estiver pronto.

### `App\Jobs\BulkAction\DownloadXmlPdfNfeEmLoteActionJob`

**Propósito:** gerar um ZIP com **XMLs e/ou PDFs (DANFE)** de uma lista de NFes.

**Destaques:**
- PDF gerado on-the-fly com `NFePHP\DA\NFe\Danfe`;
- suporta “crédito/rodapé” se configurado;
- notifica via **database notification** quando finalizado.

---

### `App\Jobs\BulkAction\DownloadXmlPdfCteEmLoteActionJob`

**Propósito:** gerar um ZIP com **XMLs e/ou PDFs (DACTE)** de uma lista de CTes.

**Destaques:**
- PDF gerado com `NFePHP\DA\CTe\Dacte`;
- suporta “crédito/rodapé” se configurado;
- notifica o usuário ao concluir.

---

### `App\Jobs\BulkAction\DownloadUploadFileBulkActionJob`

**Propósito:** criar um ZIP para downloads em lote de arquivos genéricos gerenciados pelo `UploadFileManager`.

**Destaques:**
- pode organizar a estrutura de pastas por tags ou tipos de documento;
- arquivos com múltiplas tags podem ir para uma pasta especial;
- notifica o usuário quando o ZIP estiver pronto.

---

## Jobs Fiscais (SEFAZ)

O objetivo é baixar e processar documentos (ambiente nacional) em background, com alto volume e rastreabilidade.

### Pipeline de Download/Processamento de NFe

#### 1) `App\Jobs\Sefaz\SefazNfeDownloadAndProcessBatchJob` (Coordinator)

**Responsabilidade:** ponto de entrada.

**Fluxo:**
1. chama `SefazNfeDownloadService` para buscar um lote com base no último NSU;
2. cria tracking (`XmlImportJob`);
3. despacha `SefazNfeDownloadBatchJob` para processar os documentos encontrados.

---

#### 2) `App\Jobs\Sefaz\SefazNfeDownloadBatchJob` (Batch Manager)

**Responsabilidade:** gerenciar o **Bus Batch**.

**Fluxo:**
- recebe lista de documentos “brutos”;
- cria um job `SefazNfeProcessDocumentJob` por documento dentro de um batch;
- ao finalizar, atualiza `XmlImportJob` para `COMPLETED`.

---

#### 3) `App\Jobs\Sefaz\SefazNfeProcessDocumentJob` (Worker)

**Responsabilidade:** processar **um** documento.

**Fluxo:**
- recebe array bruto (NSU, conteúdo, tipo);
- lê/parsa com `XmlNfeReaderService`;
- persiste NFe / Resumo / Evento;
- registra conteúdo bruto em `LogSefazNfeContent`.

---

### Pipeline de Download/Processamento de CTe

Segue o mesmo padrão “Coordinator → Batch Manager → Worker”:

- **Coordinator:** `App\Jobs\Sefaz\SefazCteDownloadAndProcessBatchJob`
- **Batch Manager:** `App\Jobs\Sefaz\SefazCteDownloadBatchJob`
- **Worker:** `App\Jobs\Sefaz\SefazCteProcessDocumentJob`
  - parsing com `XmlCteReaderService`
  - log bruto em `LogSefazCteContent`

---

## Jobs de Integração (SIEG)

Integração com API SIEG para download em massa de XMLs, com paginação e controle de taxa.

### `App\Jobs\Sieg\SiegConnect` (Connector / Coordinator)

**Responsabilidade:** conectar e coordenar a coleta paginada.

**Fluxo:**
- chama endpoint `BaixarXmlsV2` usando a API key do tenant;
- pagina resultados com `Skip`/`Take`;
- cria tracking (`XmlImportJob`);
- despacha `ProcessXmlSiegBatch` com os XMLs baixados;
- aplica **rate limit** via `sleep(300ms)` entre requisições.

---

### `App\Jobs\Sieg\ProcessXmlSiegBatch` (Batch Manager)

**Responsabilidade:** orquestrar processamento em batch.

**Fluxo:**
- recebe XMLs em base64;
- “chunk” em grupos menores (tamanho 50);
- decodifica base64;
- despacha jobs `ProcessXmlSieg` (1 por XML) em **Bus Batch**;
- ao concluir: atualiza status do `XmlImportJob` e notifica usuário.

---

### `App\Jobs\Sieg\ProcessXmlSieg` (Worker)

**Responsabilidade:** processar um XML (origem SIEG).

**Fluxo:**
- identifica tipo via `XmlIdentifierService`;
- parsa via `XmlNfeReaderService` (NFe/Resumo/Evento) ou `XmlCteReaderService` (CTe/Evento);
- marca origem como `SIEG`;
- persiste no banco.

---

## Cross-referencing / Enriquecimento de dados

### `App\Jobs\Sefaz\CheckNfeData`

**Propósito:** vincular CTes às NFes referenciadas e sincronizar metadados.

**Fluxo:**
- lê `nfe_chave` no CTe;
- busca NFe correspondente no banco;
- se existir:
  - **herda tags** da NFe para o CTe;
  - sincroniza metadados (emitente/destinatário/valores) para relatórios.

---

## Exemplos práticos (alto nível)

### Despachando um job simples
```php
use App\Jobs\ProcessXmlFile;

ProcessXmlFile::dispatch($pathToXml, $xmlImportJobId, $issuerId)
    ->onQueue('default');
```

### Despachando um batch (conceito)
```php
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use App\Jobs\ProcessXmlFile;

$batch = Bus::batch([
    new ProcessXmlFile($file1, $importId, $issuerId),
    new ProcessXmlFile($file2, $importId, $issuerId),
])->onQueue('low')->dispatch();
```

> Os nomes/assinaturas exatas dos construtores podem variar; use este trecho como referência de padrão.

---

## Arquivos/áreas relacionadas

- **Jobs (PHP):** `App\Jobs\...` (núcleo do processamento assíncrono)
- **Horizon:** configuração e operação (ver `docs/horizon-producao.md`)
- **Serviços de XML:** `XmlExtractorService`, `XmlIdentifierService`, `XmlNfeReaderService`, `XmlCteReaderService`
- **Logs de conteúdo SEFAZ:** `LogSefazNfeContent`, `LogSefazCteContent`
- **Tracking:** `XmlImportJob`

---

## Operação e troubleshooting (dicas rápidas)

- Se a UI “não conclui” importações/downloads: verifique **Horizon dashboard**, filas e falhas.
- Se há lentidão em tarefas longas: confirme se estão indo para `low` e se o supervisor tem workers suficientes.
- Se SEFAZ/SIEG falham por intermitência: verifique **retries/backoff**, conectividade e limites de API.

---
