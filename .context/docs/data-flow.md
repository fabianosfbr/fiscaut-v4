# Data Flow (Fiscaut v4.1)

Este documento descreve como os dados circulam no ecossistema do **Fiscaut v4.1**, cobrindo:

- Ciclo reativo entre **frontend (Filament/Alpine)** e **backend (Laravel/Livewire)**
- Escopo e isolamento de dados (**tenant** e **issuer**)
- Fluxos fiscais principais (NF-e/CT-e): **Distribuição DF-e (NSU/docZip)**, **manifestação**, **importação manual**
- Filas, jobs e persistência
- Pontos de observabilidade e tratamento de erros

---

## 1) Visão geral da arquitetura (TALL + Filament)

O Fiscaut segue o padrão **TALL Stack**:

1. **Tailwind CSS**: estilo e layout.
2. **Alpine.js**: estado local no navegador e interações leves.
3. **Laravel**: regras de negócio, autenticação, autorização e persistência.
4. **Livewire**: ponte reativa (AJAX) entre UI e backend.
5. **Filament** (sobre Livewire): componentes prontos (forms, tables, pages, widgets) e convenções para recursos administrativos.

Em termos de fluxo, o “centro” é o **componente Livewire**: ele mantém estado, executa ações, valida e re-renderiza a UI de forma incremental (DOM diff).

---

## 2) O loop reativo (Livewire Request/Response)

O ciclo padrão de atualização de dados funciona assim:

### 2.1 Captura no cliente (Filament + Alpine)
- O usuário interage com um componente (ex.: `TextInput`, `Toggle`, `TagsInput`, `Textarea`, etc.).
- Eventos do DOM são processados e o estado do componente é atualizado no lado do cliente.

### 2.2 Sincronização com o componente Livewire
- O Livewire intercepta as mudanças e envia um request assíncrono ao servidor com o “delta” do estado.
- Componentes mais complexos usam utilitários para encontrar o componente Livewire “pai” antes de disparar atualizações (ex.: `findClosestLivewireComponent` em `vendor/filament/support/resources/js/partials.js`).

### 2.3 Processamento no servidor (Laravel)
O backend recebe o estado e executa, tipicamente, nesta ordem:

1. **Autorização**: policies/guards verificam se o usuário pode criar/editar/consultar.
2. **Validação**: regras definidas no Resource/Page/Component (Filament) são aplicadas.
3. **Hooks de lifecycle** (quando existentes): por exemplo, mutações antes de persistir (como `mutateFormDataBeforeCreate()` / `mutateFormDataBeforeSave()` em Resources/Pages Filament).

### 2.4 Resposta e DOM diff
- O servidor responde com um payload (incluindo HTML e estado).
- O Livewire aplica **DOM diff**, atualizando apenas os trechos necessários.
- Feedback ao usuário (ex.: notificações) pode ser emitido nesse estágio.

Exemplo (notificação disparada no front):

```js
new Notification()
  .title('Saved successfully')
  .success()
  .send()
```

---

## 3) Escopo e isolamento de dados

O Fiscaut aplica isolamento em múltiplas camadas para garantir que os dados corretos sejam lidos e modificados.

### 3.1 Multi-tenancy (`tenant_id`)
- A maioria das entidades é vinculada a um `tenant_id`.
- Comumente isso é aplicado via **Global Scope** (Eloquent), impedindo que consultas tragam dados de outro tenant.

**Objetivo:** garantir isolamento organizacional.

### 3.2 Contexto fiscal por empresa (`issuer_id`)
Dentro de um tenant, um usuário pode operar múltiplas empresas emissoras (“Issuers”).

- Existe um **Active Issuer** (normalmente em sessão).
- Tabelas e recursos Filament filtram dados por `issuer_id` (ex.: listagens e relatórios).

**Objetivo:** separar dados fiscais por CNPJ/empresa emissora.

---

## 4) Ciclo de vida de documentos fiscais (NF-e / CT-e)

Os documentos fiscais têm particularidades:

- Assinatura e comunicação exigem **certificado digital**.
- A integração com SEFAZ pode ser lenta/intermitente.
- Processamentos são frequentemente assíncronos (**jobs e filas**).

### 4.1 Certificados digitais
Fluxo típico:

1. **Armazenamento**: conteúdo `.pfx` e senha ficam vinculados ao `Issuer` (idealmente criptografados em repouso).
2. **Uso**: em runtime, o serviço carrega certificado/senha e cria uma instância de `NFePHP\Common\Certificate` (em memória).
3. **Comunicação SEFAZ**: requisições autenticadas e assinadas via certificado.

**Ponto importante:** nunca persista certificado “em claro” em logs; evite dump de config em produção.

### 4.2 Processamento assíncrono (filas)
Operações mais pesadas são offloaded:

- A UI aciona uma sincronização (ex.: “Buscar documentos”).
- O backend despacha jobs em filas (prioridades como `high`/`low`).
- A UI pode:
  - Fazer polling de status, ou
  - Receber eventos (ex.: websocket, quando implementado).

---

## 5) Leitura via SEFAZ (Distribuição DF-e por NSU)

A distribuição DF-e é dividida em duas etapas:

1. **Consulta do lote** (por NSU)
2. **Processamento de cada `docZip`**

Em ambos os casos (NF-e e CT-e), o padrão é: consultar, obter `docZip`, decodificar, persistir metadados/documentos e atualizar checkpoints por issuer.

---

### 5.1 NF-e (modelo 55)

**Serviço:** `app/Services/Sefaz/NfeService.php`

#### Checkpoints por Issuer
- `issuers.ult_nsu_nfe`: último NSU processado
- `issuers.ultima_consulta_nfe`: timestamp da última consulta

#### Execução (alto nível)
1. Monta config (ambiente, UF, CNPJ, versões/schemes).
2. Chama `NFePHP\NFe\Tools->sefazDistDFe($ultNSU, 0)`.
3. Se houver `docZip`:
   - Despacha `ProcessResponseNfeSefazJob` (fila `high`)
   - Para cada `docZip`, despacha `ProcessXmlResponseNfeSefazJob` (fila `low`)
4. Decodificação típica do `docZip`:
   - `base64_decode` + `gzdecode` → XML original

#### Persistência
- Resumo (`resNFe`): `LogSefazResumoNfe::updateOrCreate(...)`
  - Armazena metadados e o XML (string)
- Documento completo (`nfeProc`): `NotaFiscalEletronica::updateOrCreate(...)`
  - Armazena XML **comprimido** (`gzcompress`) + metadados extraídos do `nfeProc`

#### Eventos
- `resEvento`, `procEventoNFe` e `evento` vão para rotinas de log.
- Por padrão, não alteram diretamente o status do documento (a regra pode variar conforme implementação).

#### Manifestação
- `NfeService::manifestaCienciaDaOperacao()` usa `Tools->sefazManifesta()`
- Registra `LogSefazManifestoEvent`
- Marca `LogSefazResumoNfe.is_ciente_operacao`

---

### 5.2 CT-e (Distribuição DF-e)

**Serviço:** `app/Services/Sefaz/CteService.php`

#### Checkpoints por Issuer
- `issuers.ult_nsu_cte`
- `issuers.ultima_consulta_cte`

#### Execução (alto nível)
1. Monta config e chama `NFePHP\CTe\Tools->sefazDistDFe($ultNSU, 0)`.
2. Despacha `ProcessResponseCteSefazJob` (fila `high`).
3. Processa `docZip` em `ProcessXmlResponseCteSefazJob` (fila `low`).
4. Decodificação:
   - `base64_decode` + `gzdecode`

#### Persistência
- Documento completo (`cteProc`): `ConhecimentoTransporteEletronico::updateOrCreate(...)`
  - XML comprimido (`gzcompress`) + metadados extraídos

#### Enriquecimento (NF-e vinculadas)
- Se o CT-e contiver chaves de NF-e referenciadas, despacha `CheckNfeData` (fila `low`) para enriquecer/associar informações.

#### Eventos
- `procEventoCTe`, `eventoCTe` e `evento` seguem para rotinas de log.

---

### 5.3 Restrições e throttling
Para evitar consumo indevido e respeitar limitações:

- Loop de consulta limitado (até **50 iterações**).
- `sleep(8)` entre consultas.
- Interrompe quando `cStat` indicar:
  - “sem documentos”
  - “consumo indevido” (ex.: `137`/`656`)
- Checkpoints (`ultNSU`) são persistidos no Issuer sempre que disponíveis.

---

## 6) Importação manual de XML/ZIP (NF-e e CT-e)

Além do fluxo automático por NSU, há importação via upload:

### 6.1 UI (Filament)
- Página: `app/Filament/Pages/Importar/XmlImport.php`
- A página:
  - Salva o arquivo no disk `local` (diretório `xml-imports`)
  - Cria um batch de processamento

### 6.2 Batch e Jobs
- `app/Jobs/ProcessXmlFileBatch.php`
  - Cria `Bus::batch([ProcessXmlFile...])` e acompanha status
- `app/Jobs/ProcessXmlFile.php`
  - Lê o arquivo do Storage
  - Extrai XML(s)
  - Identifica tipo e chama o serviço correspondente:
    - `NfeService->exec($xmlReader, $xml, 'Importacao')` ou
    - `CteService->exec($xmlReader, $xml, 'Importacao')`

### 6.3 Extração e identificação
- `app/Services/Xml/XmlExtractorService.php`
  - Extrai `.xml` diretamente e “varre” `.zip`
- `app/Services/Xml/XmlIdentifierService.php`
  - Inferência pelo elemento raiz: `nfeProc`, `resNFe`, `cteProc`, eventos etc.

**Resultado:** o pipeline de persistência/atualização é o mesmo (ou muito similar) ao usado no fluxo SEFAZ.

---

## 7) Integrações externas

| Integração | Referência | Finalidade |
|---|---|---|
| MySQL | `app/Models/` | Persistência relacional |
| SEFAZ APIs | `app/Services/Sefaz/NfeService.php`, `app/Services/Sefaz/CteService.php` | Distribuição DF-e (NSU/docZip) e manifestação |
| API de CNPJ | `CreateIssuer.php` | Busca automática de dados cadastrais |
| Filesystem | `config/filesystems.php` | Uploads e artefatos (XML/ZIP/PDF etc.) |

---

## 8) Tratamento de erros e observabilidade

### 8.1 Erros no frontend
- Problemas em Alpine/Filament aparecem no console do navegador.
- Utilitários do Filament (ex.: `vendor/filament/support/resources/js/utilities/select.js`) costumam ter checks para prevenir erros comuns de binding.

### 8.2 Erros no backend
- **Validação**: Filament/Livewire exibem inline errors automaticamente.
- **Exceções**: registradas em `storage/logs/laravel.log`.
- **Erros SEFAZ**: normalmente parseados e exibidos ao usuário via notificações de erro (“Danger”) para facilitar troubleshooting.

### 8.3 Observação importante (logs SEFAZ)
Os serviços/jobs de leitura chamam métodos como `registerLogNfeContent` e `registerLogCteEvent` via o trait:

- `App\Services\Sefaz\Traits\HasLogSefaz`

Se esse trait (ou métodos associados) não existir/estiver incompleto, a leitura pode falhar com:
- classe/trait não encontrado
- métodos inexistentes

**Ação recomendada:** garantir que o trait exista e esteja coberto por testes de integração (simulando respostas SEFAZ com `docZip`).

---

## 9) Referências relacionadas no projeto

- **Models**: `app/Models/` (mapeamento e scopes como `tenant_id`/`issuer_id`)
- **Resources/Pages Filament**: `app/Filament/Resources`, `app/Filament/Pages` (schemas, validação, hooks)
- **Policies**: `app/Policies` (autorização de leitura/escrita)
- **Frontend Filament (build vendor/public)**:
  - `public/js/filament/forms/components/*`
  - `public/js/filament/tables/components/columns/*`
  - `public/js/filament/schemas/components/*`

Essas referências ajudam a rastrear como a UI captura dados, sincroniza com Livewire e aciona ações no backend.
