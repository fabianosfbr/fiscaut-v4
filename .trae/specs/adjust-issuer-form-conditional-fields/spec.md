# Ajuste de Campos Condicionais no Formulário de Empresa (Issuer)

## Por que
Atualmente, os campos "Atividade" e "Contribuinte ICMS" são exibidos para todos os tipos de empresa. No entanto, esses campos são relevantes e devem ser obrigatórios apenas para empresas do tipo "Padrão". Para outros tipos, como "Condomínio" ou "Associação", esses campos não devem ser exibidos nem exigidos.

## O que muda
- O campo `atividade` (Atividade) passará a ser visível e obrigatório apenas quando o `issuer_type` (Tipo da Empresa) for igual a `PADRAO`.
- O campo `contribuinte_icms` (Contribuinte ICMS?) passará a ser visível e obrigatório apenas quando o `issuer_type` (Tipo da Empresa) for igual a `PADRAO`.

## Impacto
- **Arquivos afetados**: `app/Filament/Resources/Issuers/Schemas/IssuerForm.php`
- **Sistemas afetados**: Cadastro e edição de empresas no painel administrativo (Filament).

## Requisitos ADICIONADOS
### Requisito: Exibição Condicional de Campos Fiscais
O sistema DEVE ocultar os campos "Atividade" e "Contribuinte ICMS" se o tipo de empresa não for "Padrão".

#### Cenário: Sucesso - Empresa Tipo Padrão
- **QUANDO** o usuário seleciona "Padrão" no campo "Tipo da Empresa"
- **ENTÃO** os campos "Atividade" e "Contribuinte ICMS" devem aparecer e ser marcados como obrigatórios.

#### Cenário: Sucesso - Empresa Tipo Condomínio/Associação
- **QUANDO** o usuário seleciona "Condomínio" ou "Associação" no campo "Tipo da Empresa"
- **ENTÃO** os campos "Atividade" e "Contribuinte ICMS" devem ser ocultados e não devem impedir o salvamento do formulário.

## Requisitos MODIFICADOS
### Requisito: Obrigatoriedade de Atividade e Contribuinte ICMS
**Original**: Campos sempre visíveis e `atividade` sempre obrigatório.
**Modificado**: Campos visíveis e obrigatórios apenas condicionalmente ao tipo de empresa "Padrão".
