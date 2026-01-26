## Diagnóstico
- O trecho alvo é o campo [UploadFileManagerForm.php:L96-L101](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/UploadFileManagers/Schemas/UploadFileManagerForm.php#L96-L101): `TextInput::make('valor')` dentro do `Repeater::make('tags')`.
- Pelo contexto do form (prefixo `R$`, soma em `doc_value_create` e persistência como `tagged.value` decimal), o tipo correto é **moeda BRL**.
- Já existe padrão no projeto para máscara monetária com Filament v5 usando `mask(RawJs::make('$money(...)'))` + `dehydrateStateUsing(...)` em [CnaeForm.php:L36-L42](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Cnaes/Schemas/CnaeForm.php#L36-L42).

## Mudanças no formulário
- Ajustar apenas o campo `valor` (linhas 96–101) para:
  - Remover a duplicidade de `->prefix('R$')`.
  - Adicionar `->placeholder('0,00')` (exemplo do formato esperado).
  - Aplicar máscara monetária nativa do Filament:
    - `->mask(RawJs::make('$money($input, ",", ".", 2)'))` para formatação em tempo real enquanto digita (inclui colagem/backspace com comportamento estável).
  - Garantir envio “limpo” ao backend:
    - `->dehydrateStateUsing(...)` convertendo `1.234,56` → `1234.56` (float) para gravar/usar corretamente no Laravel e na persistência `decimal`.
  - Manter `->required()` e **manter `->numeric()`** (a validação ocorrerá no valor desidratado/normalizado no submit, alinhando com a regra de “valor puro”).
  - Opcional (se fizer sentido manter consistência visual ao re-hidratar valores numéricos): `->formatStateUsing(...)` para exibir `1234.56` como `1.234,56`.

## Verificação (funcional e UX)
- Validar no browser:
  - Digitação progressiva: `1`, `12`, `1234` → `1.234,00`/`1.234,xx` conforme a máscara.
  - Copiar/colar: `1234,56` e `1234.56` (normalizar se necessário) e observar resultado.
  - Backspace no meio do número e no final (cursor estável).
- Validar payload no backend:
  - Confirmar que em `CreateUploadFileManager::taggedFile()` o `$tag_apply['valor']` chega como **float** (sem `.` de milhar e sem `,`).

## Arquivo impactado
- Alteração pontual em [UploadFileManagerForm.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/UploadFileManagers/Schemas/UploadFileManagerForm.php#L96-L101).

Se você confirmar este plano, eu aplico a alteração no arquivo e em seguida rodo uma verificação rápida no fluxo de criação para garantir que o valor chega desformatado no backend.