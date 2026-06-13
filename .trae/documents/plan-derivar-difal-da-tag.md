# Plano: Derivar DIFAL da CategoryTag (is_difal) no OrquestradorService

## Resumo
O campo `is_difal` da `CategoryTag` existe no banco e no formulário Filament, mas **não é utilizado** na geração do TXT Domínio. Atualmente, a flag `$debDifal` é hardcoded como `true` (linha 347 do `OrquestradorService.php`). O objetivo é derivar essa flag da categoria da etiqueta (`CategoryTag`) aplicada à NF.

---

## Análise do Estado Atual

### Arquitetura Relacionada
| Componente | Responsabilidade | Status `is_difal` |
|------------|------------------|-------------------|
| `CategoryTag` | Modelo com campo `is_difal` (boolean, default false) | ✅ Existe |
| `Tag` | Pertence a `CategoryTag` via `category_id` | ✅ Relacionamento `category()` |
| `Tagged` (pivot) | Liga `NotaFiscalEletronica` ↔ `Tag` com campo `value` (rateio) | ✅ Existe |
| `OrquestradorService` | Gera TXT Dominio, loop por tags, passa `$debDifal` para `CalculadorIcmsService` | ⚠️ TODO hardcoded |
| `CalculadorIcmsService::gerar1020Difal()` | Gera registro 1020 código 8 se CFOP ∈ {2556, 2551, 2406} E `$debDifal=true` | ✅ Funciona |

### Fluxo Atual no OrquestradorService (linhas 314-347)
```php
$tags = $notaFiscal->tagged ?? collect();

foreach ($tags as $tagged) {
    $tagId = $tagged->tag_id;
    $tagModel = $tagged->tag;           // Tag model
    // $tagModel->category → CategoryTag (eager loaded via Tag::$with = ['category'])
    
    $pct = ...; // rateio proporcional
    
    $credIcms = ! $this->resolvedorCfop->isZeraIcms($tagId);
    $credIpi = ! $this->resolvedorCfop->isZeraIpi($tagId);
    $credPiscof = ! $isSimples;
    $debDifal = true; // TODO: derivar da tag  ← PROBLEMA AQUI
    
    // ... processa itens, segmenta por CFOP
    
    // Chama CalculadorIcmsService passando $debDifal
    foreach ($this->calculadorIcms->gerar1020Difal($seg, $isSimples, $debDifal) as $l) {
        $linhas[] = $l;
    }
}
```

---

## Mudanças Propostas

### Arquivo: `/root/projetos/fiscaut-v4/app/Integrations/DominioSistemas/Services/OrquestradorService.php`

#### 1. Substituir a linha 347 (hardcoded) por derivação da CategoryTag
```php
// ANTES:
$debDifal = true; // TODO: derivar da tag

// DEPOIS:
$debDifal = $tagModel->category?->is_difal === true;
```

**Justificativa:**
- `$tagModel` já é a instância de `Tag` (linha 333)
- `Tag` tem `$with = ['category']` → eager loading automático
- `CategoryTag` tem cast `is_difal => 'boolean'`
- Null-safe operator `?->` previne erro se tag não tiver categoria

#### 2. (Opcional) Adicionar log de debug para auditoria
```php
Log::channel('dominio_log')->debug("DIFAL para tag {$tagModel->name} (id:{$tagId}): " . ($debDifal ? 'SIM' : 'NÃO') . " | categoria: " . ($tagModel->category?->name ?? 'sem categoria') . " | is_difal: " . ($tagModel->category?->is_difal ? 'true' : 'false'));
```

---

## Decisões e Suposições

| Decisão | Fundamentação |
|---------|---------------|
| Usar `$tagModel->category?->is_difal === true` | Tag já carrega category eager-loaded; cast boolean garante tipagem |
| Não alterar `CalculadorIcmsService` | A lógica de CFOP (2556, 2551, 2406) está correta; a flag apenas habilita/desabilita |
| Não alterar `ResolvedorCfopService` | Não é responsabilidade dele definir DIFAL por categoria de etiqueta |
| Manter `$debDifal` por tag (não global) | Uma NF pode ter múltiplas tags com categorias diferentes; cada bloco 1000/1020 é por tag |

---

## Passos de Verificação

### 1. Teste Unitário (sugerido)
```php
// tests/Feature/Integrations/DominioSistemas/OrquestradorServiceTest.php

/** @test */
public function deb_difal_deriva_da_category_tag_da_etiqueta()
{
    // Arrange
    $categoryDifal = CategoryTag::factory()->create(['is_difal' => true, 'is_enable' => true]);
    $categoryNaoDifal = CategoryTag::factory()->create(['is_difal' => false, 'is_enable' => true]);
    
    $tagDifal = Tag::factory()->create(['category_id' => $categoryDifal->id, 'is_enable' => true]);
    $tagNaoDifal = Tag::factory()->create(['category_id' => $categoryNaoDifal->id, 'is_enable' => true]);
    
    $nota = NotaFiscalEletronica::factory()->create();
    $nota->tag($tagDifal, 1000);      // tag com DIFAL
    $nota->tag($tagNaoDifal, 500);    // tag sem DIFAL
    
    // Act
    $service = new OrquestradorService($nota->issuer);
    $resultado = $service->gerarTxt(collect([$nota]));
    
    // Assert: verificar que 1020 código 8 aparece apenas para segmentos da tag com DIFAL
    // (precisa inspecionar $resultado['conteudo'] ou mockar CalculadorIcmsService)
}
```

### 2. Teste Manual via Filament
1. Acesse **Admin → Etiquetas de Categoria (Category Tags)**
2. Crie/edite uma categoria: marque **"Difal" = Sim**
3. Crie uma etiqueta (Tag) vinculada a essa categoria
4. Em uma NF-e de entrada interestadual (CFOP 6124 → 2124), aplique essa etiqueta
5. Gere o TXT Domínio
6. Verifique se o registro `1020|8|...` (DIFAL) é gerado
7. Repita com etiqueta de categoria **sem** Difal → NÃO deve gerar 1020|8

### 3. Logs de Auditoria
- Canal `dominio_log` (se adicionado log opcional) mostrará decisão por tag
- Canal `sieg_log` / `dominio_log` existentes já capturam erros

---

## Riscos e Mitigações

| Risco | Probabilidade | Mitigação |
|-------|---------------|-----------|
| Tag sem categoria (`category_id` null) | Baixa | Null-safe `?->` retorna `false`; comportamento seguro (sem DIFAL) |
| Categoria desabilitada (`is_enable=false`) | Média | Tag só é usada se `is_enable=true` (validado no TagObserver/Form) |
| Cache de tags desatualizado | Baixa | `Tag::getTagsUsedInNfe()` usa cache 1 dia; `TagObserver` limpa cache em save/delete |

---

## Arquivos Afetados

| Arquivo | Tipo de Mudança |
|---------|-----------------|
| `app/Integrations/DominioSistemas/Services/OrquestradorService.php` | **Único arquivo** - linha 347 + log opcional |

---

## Próximos Passos (após aprovação)
1. Aplicar a mudança na linha 347
2. (Opcional) Adicionar log de debug
3. Executar testes existentes: `vendor/bin/sail artisan test --filter=DominioSistemas`
4. Testar manualmente via Filament conforme seção "Teste Manual"