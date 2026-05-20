ID_CONDOMINIO_COND 80
*ID do condomínio

ST_NOME_CON Fornecedor
*Nome do Fornecedor

ID_CONTATO_CON 1507
*ID do Fornecedor ( Para conseguir esse valor use o endpoint Listar favorecidos)

ST_NOMERECEBEDOR_FAV Favorecido
Nome do Favorecido

ID_FAVORECIDO_CON 1507
ID do Favorecido ( Para conseguir esse valor use o endpoint Listar favorecidos)

DT_VENCIMENTOPRIMEIRAPARCELA 01/19/2023
*Data de vencimento

ID_FORMA_PAG 0
* ID Forma pagamento (0 Boleto | 1 Cheque | 2 Dinheiro | 3 Cartão de crédito | 4 Cartão de débito | 7 Débito automático | 8 Trans. bancária | 9 Doc/Ted | 10 Outros | 11 Tributo sem código de barras | 12 Pix | 13 DCTFWeb | 14 Pix copia e cola)

DADOS_PAGAMENTOS teste
Dados Pagamento (Para conseguir esse valor use o endpoint do Despesas -> Listar dados pagamento favorecido " DADOS_PAGAMENTOS = st_nomerecebedor") 

ID_FAVORECIDO_FAV 32
ID conta pagamento cadastrada (Este campo deve ser enviado caso o DADOS_PAGAMENTOS for passado, para conseguir esse valor use o endpoint Listar dados pagamento favorecido)

DT_DESPESA_DES 01/19/2023
Data do documento

ID_TIPO_DOC Tipo (1 Nota Fiscal | 2 Imposto | 3 Fatura | 4 Recibo | 5 Cupom Fiscal | 6 Outros | 7 Folha de pagamento)

ST_DOCUMENTO_DES Número documento

ST_SERIENOTA_DES Série da nota

APROPRIACAO[0][ST_CONTA_CONT] 2.1.6
*Conta (categoria) (Para conseguir esse valor use o endpoint Listar contas de um plano de contas específico)

APROPRIACAO[0][ST_DESCRICAO_CONT]  2.1.6 Adiantamento Décimo Terceiro Salário
*Conta (categoria) (Para conseguir esse valor use o endpoint Listar contas de um plano de contas específico)

APROPRIACAO[0][ST_COMPLEMENTO_APRO] Complemento

APROPRIACAO[0][VL_VALOR_PDES] 126
Valor

APROPRIACAO[0][ST_NOMEGRUPOSALDO_GS] Ordinário
Grupo de saldo padrão

APROPRIACAO[0][ID_GRUPOSALDO_GS] 1
ID grupo saldo

RETENCOES[0][ID_RV2_IMPOSTO_DES] ID Imposto (Para conseguir esse valor use o endpoint Listar impostos )

RETENCOES[0][DT_VENCIMENTO_PDES] Vencimento
RETENCOES[0][FL_RETERIMPOSTO_DES] 0
Reter imposto (0- Não | 1-Sim ) Se enviar 1, o valor da retenção será subtraído do valor total da despesa.

RETENCOES[0][ST_COMPLEMENTO_PDES] Complemento

RETENCOES[0][ST_CODIGOBARRAS_PDES] Código de Barras

CHECK_LIQUIDAR_TODOS_CH Liquidar (0 - Não| 1 - Sim )

DT_LIQUIDACAO_PDES Data liquidação (E necessario informar o valor para CHECK_LIQUIDAR_TODOS_CH = 1)

VL_DESCONTO_PDES Desconto (E necessario informar o valor para CHECK_LIQUIDAR_TODOS_CH = 1)

VL_MULTA_PDES Multa (E necessario informar o valor para CHECK_LIQUIDAR_TODOS_CH = 1)

VL_JUROS_PDES Juros (E necessario informar o valor para CHECK_LIQUIDAR_TODOS_CH = 1)

VL_PAGO Pago (E necessario informar o valor para CHECK_LIQUIDAR_TODOS_CH = 1)

FL_ACAO_IMPRESSAO 1
Ação (1 Apenas registrar | 2 Imprimir agora | 3 Imprimir depois em lote)

NM_NUMERO_CH Número do cheque

ID_CONTABANCO_CB 138
Conta bancária

ST_ENVELOPEETIQUETA_PDES Etiqueta Paybox

ARQUIVOS[0][ID_ARQUIVO_ARQ] 51
Arquivo vinculado a esta despesa

FL_RECORRENTE_DES -1
Despesa recorrente (-1 Auto (recomendado) | 0 Extraordinária | 2 Recorrente variável | 1 Recorrente fixa)

FL_RECORRENTEMANUAL_DES 1
*Para criar despesas recorrentes (fixas ou variáveis) é necessário informar este parâmetro.

