# Manual de Instruções - Comando Laravel para Geração de Arquivo TXT Domínio Sistemas

## Descrição
Este comando Laravel permite gerar arquivos TXT conforme o layout da Domínio Sistemas a partir de uma Collection de dados. O comando utiliza a infraestrutura de classes desenvolvida para representar os diferentes tipos de registros do layout.

## Como Usar

### Sintaxe Básica
```bash
php artisan dominio-sistemas:gerar-txt {collection} {--output=} {--empresa=}
```

### Parâmetros

#### collection (obrigatório)
- **Descrição**: Coleção de dados para gerar o arquivo TXT
- **Formato**: Pode ser uma string JSON contendo os registros ou o caminho para um arquivo JSON
- **Exemplo**: `'{"registros": [{"tipo_registro": "0000", "inscricao_empresa": "12345678901234"}]}'`

#### Opções

##### --output
- **Descrição**: Caminho do arquivo de saída
- **Padrão**: `storage/app/dominio_saida.txt`
- **Exemplo**: `--output=/caminho/personalizado/arquivo.txt`

##### --empresa
- **Descrição**: Inscrição da empresa (CNPJ/CPF) para o registro 0000
- **Exemplo**: `--empresa=12345678901234`

## Formato dos Dados

Cada registro na Collection deve seguir o seguinte formato:

```json
{
  "tipo_registro": "0000",
  "outros_campos": "valores"
}
```

### Tipos de Registros Suportados

#### Registro 0000 - Identificação da Empresa
```json
{
  "tipo_registro": "0000",
  "inscricao_empresa": "12345678901234"
}
```

#### Registro 0010 - Cadastro de Clientes
```json
{
  "tipo_registro": "0010",
  "inscricao": "12345678901234",
  "razao_social": "Nome do Cliente",
  "uf": "SP",
  "cep": "12345678"
}
```

#### Registro 0020 - Cadastro de Fornecedores
```json
{
  "tipo_registro": "0020",
  "inscricao": "12345678901234",
  "razao_social": "Nome do Fornecedor",
  "uf": "RJ",
  "cep": "98765432"
}
```

#### Registro 0100 - Cadastro de Produtos
```json
{
  "tipo_registro": "0100",
  "codigo_produto": "PROD001",
  "descricao_produto": "Descrição do Produto",
  "codigo_ncm": "12345678",
  "codigo_barras": "1234567890123",
  "valor_unitario": 10.50
}
```

#### Registro 0135 - Produtos - Valor Unitário
```json
{
  "tipo_registro": "0135",
  "codigo_produto": "PROD001",
  "data": "2023-01-15",
  "valor_unitario": 10.50
}
```

#### Registro 1000 - Notas Fiscais de Entrada
```json
{
  "tipo_registro": "1000",
  "codigo_especie": "55",
  "inscricao_fornecedor": "55566677788899",
  "cfop": "5101",
  "numero_documento": 12345,
  "data_entrada": "2023-01-15",
  "data_emissao": "2023-01-14",
  "valor_contabil": 100.50,
  "valor_produtos": 100.50,
  "municipio_origem": "3550308",
  "situacao_nota": 0
}
```

## Exemplos de Uso

### Exemplo 1: Usando dados JSON inline
```bash
php artisan dominio-sistemas:gerar-txt '[{"tipo_registro":"0000","inscricao_empresa":"12345678901234"},{"tipo_registro":"0100","codigo_produto":"PROD001","descricao_produto":"Produto Exemplo","valor_unitario":10.5}]' --output=/tmp/meu_arquivo.txt --empresa=12345678901234
```

### Exemplo 2: Usando arquivo JSON
```bash
# Primeiro, crie um arquivo JSON com os dados:
# dados.json
[
  {
    "tipo_registro": "0000",
    "inscricao_empresa": "12345678901234"
  },
  {
    "tipo_registro": "0100",
    "codigo_produto": "PROD001",
    "descricao_produto": "Produto Exemplo",
    "valor_unitario": 10.50
  }
]

# Depois execute o comando:
php artisan dominio-sistemas:gerar-txt dados.json --output=/tmp/saida.txt
```

### Exemplo 3: Apenas com registros de produtos
```bash
php artisan dominio-sistemas:gerar-txt '[{"tipo_registro":"0100","codigo_produto":"PROD001","descricao_produto":"Produto 1","valor_unitario":15.75},{"tipo_registro":"0100","codigo_produto":"PROD002","descricao_produto":"Produto 2","valor_unitario":22.30}]' --output=/tmp/produtos.txt --empresa=98765432109876
```

## Formato de Saída

O arquivo gerado seguirá o layout da Domínio Sistemas com cada registro em uma linha, começando e terminando com o caractere pipe (|):

```
|0000|12345678901234|
|0100|PROD001|Produto Exemplo||12345678||1234567890123|||||||||||10.5||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
```

## Tratamento de Erros

- Se a Collection estiver vazia, o comando retornará um erro
- Se algum registro não tiver o campo `tipo_registro`, o comando retornará um erro
- Se os dados fornecidos forem inválidos para o tipo de registro, o comando retornará um erro
- Se não for possível criar o arquivo de saída, o comando retornará um erro

## Observações

- Todos os campos são automaticamente formatados de acordo com as regras do layout da Domínio Sistemas
- As datas são convertidas para o formato dd/mm/aaaa
- Os valores decimais usam vírgula como separador decimal
- O encoding do arquivo de saída é Windows-1252 conforme exigido pelo layout