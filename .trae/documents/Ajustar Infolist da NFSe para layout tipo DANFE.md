## Objetivo
- Reorganizar o Infolist de NFSe para ficar o mais parecido possível com o “espelho/DANFE” do PDF informado, mantendo a aba de XML para conferência.

## Levantamento do que já existe
- O Infolist atual já traz: número, emissão, status, chave/código verificação, prestador/tomador (nome, CNPJ/IM), discriminação, código/descrição do serviço, valores básicos de ISS e abas de XML/JSON. Tudo está em [NfseEntradaInfolist.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/NfseEntradas/Schemas/NfseEntradaInfolist.php).
- O model por trás é [NotaFiscalServico](file:///root/projetos/fiscaut-v4.1/app/Models/NotaFiscalServico.php), que já extrai e normaliza alguns campos do XML (`nfse_*_extraido` e `nfse_valores_xml`).

## Mudanças propostas no layout (tipo DANFE)
- **Renomear a aba “Dados” para “DANFE”** e transformar essa aba no “espelho” principal do documento.
- **Estruturar a aba DANFE em blocos (Sections) na ordem típica do DANFE**, usando `Section + Grid + Group` (padrão já usado em NF-e/CT-e):
  - **Cabeçalho/Identificação da NFS-e**: Número, Data/Hora Emissão, Código de Verificação, Chave (se houver), Município (código), Status.
  - **Prestador de Serviços**: Razão Social, CNPJ/CPF, IM e (quando disponível no XML) endereço completo em uma linha.
  - **Tomador de Serviços**: Razão Social, CNPJ/CPF, IM e (quando disponível no XML) endereço completo.
  - **Serviço**: Código do serviço, Descrição do serviço e uma caixa grande de “Discriminação do Serviço” (em `<pre>` como já existe).
  - **Valores / Tributação (ISS)**: Quadro em grid (semelhante aos “quadros” do DANFE) com Valor do Serviço, Base de Cálculo, Alíquota, ISS, ISS Retido e (quando existir no XML) campos adicionais de valores (deduções/descontos/outros) a partir do nó `infNFSe.valores`.
  - **Informações/Controle**: Data de Entrada, Apurada (badge), Origem, Link PDF, Cancelamento (data/motivo) em seção colapsável.

## Extração de campos “do DANFE” a partir do XML (sem alterar banco)
- Na própria schema do infolist, adicionar `state()` com leitura defensiva:
  - Tentar obter dados via `json_decode($record->nfse_xml_json, true)` e/ou via `json_decode($record->nfse_valores_xml, true)`.
  - Para cada campo, usar “primeiro valor não vazio” entre múltiplos caminhos (`data_get`) e, se nada existir, cair para `placeholder('Não informado')`.
- Assim, quando o provedor municipal variar a estrutura, o layout continua funcionando e só “enriquece” quando os dados existirem.

## Abas secundárias (manter para conferência)
- **Manter a aba XML** exatamente como está (XML em texto + JSON lido).
- **Manter as abas “Serviço” e “Valores / ISS”** (ou simplificar para apontarem para o mesmo conteúdo), mas o foco principal passa a ser a aba “DANFE” como visão consolidada.

## Validação
- Rodar lint/checagem PHP no arquivo alterado e abrir a página de visualização da NFSe no Filament para comparar visualmente com o PDF (ordem de blocos, rótulos e quadros de valores).

## Entregáveis
- Atualização de layout e campos em [NfseEntradaInfolist.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/NfseEntradas/Schemas/NfseEntradaInfolist.php) para visão “tipo DANFE”, mantendo a aba “XML” para conferência.