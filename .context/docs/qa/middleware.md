# Middleware (QA) — Fiscaut v4.1 (Laravel + Filament)

Middleware é a “camada de passagem” entre **requisição** e **resposta**. No Fiscaut v4.1 (Laravel + Filament), ela é essencial para garantir **segurança**, **inicialização do painel**, **consistência de dados** e **controle de acesso** — principalmente no fluxo do **Admin Panel (Filament)**.

Este documento descreve como o projeto organiza middleware, como registrar middleware no painel Filament e como validar/depurar em contexto de QA.

---

## O que middleware faz neste projeto

### 1) Autenticação e segurança
- Garante que usuários estejam autenticados antes de acessar rotas protegidas.
- Aplica proteções padrão do Laravel, como **CSRF**.
- Pode impor regras adicionais (ex.: “perfil completo”, “papel X”, “permissão Y”).

### 2) Inicialização do Filament (Admin)
O Filament precisa de um stack específico para:
- Sessão e cookies
- Disponibilização de erros de validação
- Bindings de rotas
- Disparo de eventos internos que registram assets e navegação (ex.: `DispatchServingFilamentEvent`)

### 3) Consistência de dados
Normalizações comuns:
- `TrimStrings`: remove espaços em branco
- `ConvertEmptyStringsToNull`: converte `""` em `null` antes de chegar no controller/Livewire

### 4) Autorização (RBAC/Policies)
Garante que o usuário tenha permissão para acessar determinados endpoints/recursos do painel.

---

## Stacks de middleware (visão geral)

### A) Global Middleware (executa em todas as requisições)
Configurado no `app/Http/Kernel.php` (padrão Laravel). Normalmente inclui:
- `TrustProxies`: cabeçalhos de proxy / SSL termination
- `ValidatePostSize`: limite para upload
- `TrimStrings` e `ConvertEmptyStringsToNull`: sanitização de input

Use este nível para regras **universais** (infra e higiene de dados).

---

### B) Stack do Filament Admin Panel
O Filament define middleware do painel no **Panel Provider**, por exemplo:

- `app/Providers/Filament/AdminPanelProvider.php`

Um exemplo típico de stack do painel:

```php
->middleware([
    EncryptCookies::class,
    AddQueuedCookiesToResponse::class,
    StartSession::class,
    AuthenticateSession::class,
    ShareErrorsFromSession::class,
    VerifyCsrfToken::class,
    SubstituteBindings::class,
    DisableBladeIconComponents::class,
    DispatchServingFilamentEvent::class, // essencial no Filament
])
```

**Notas importantes:**
- **Sessão** (`StartSession`) e **cookies** são críticos para autenticação, flashes e o funcionamento do Filament.
- `DispatchServingFilamentEvent` é um dos “pontos vitais”: sem ele, o Filament pode não registrar corretamente scripts/estilos/navegação.

---

### C) Middleware de autenticação (rotas protegidas)
Normalmente aplicado como “auth middleware” do painel:

- `Authenticate`: redireciona visitantes (guest) para login
- `Verified` (opcional): exige e-mail verificado

No Filament, essa camada costuma ser aplicada via `->authMiddleware([...])`.

---

## Criando middleware customizado (ex.: validação QA)

### 1) Gerar a classe
```bash
php artisan make:middleware EnsureProfileIsComplete
```

### 2) Implementar a regra
`app/Http/Middleware/EnsureProfileIsComplete.php`:

```php
public function handle(Request $request, Closure $next): Response
{
    if (auth()->check() && !auth()->user()->is_profile_complete) {
        return redirect()->route('profile.edit')
            ->with('warning', 'Please complete your profile first.');
    }

    return $next($request);
}
```

### 3) Aplicar no Filament (Admin Panel)
No `app/Providers/Filament/AdminPanelProvider.php`, adicione no `authMiddleware`:

```php
public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->authMiddleware([
            Authenticate::class,
            \App\Http\Middleware\EnsureProfileIsComplete::class,
        ]);
}
```

**Quando usar `middleware()` vs `authMiddleware()` no Filament**
- `middleware([...])`: stack geral do painel (sessão, CSRF, bindings, eventos do Filament).
- `authMiddleware([...])`: regras de autenticação/autorização antes de entrar no painel (bloqueio/redirect).

---

## QA: como identificar quais middleware estão ativos

Para inspecionar o stack aplicado em rotas específicas (por exemplo, Admin):

```bash
php artisan route:list --path=admin
```

Isso ajuda a confirmar:
- se a rota está no grupo `web`
- se o painel Filament anexou middleware próprios
- se o `authMiddleware()` está sendo aplicado como esperado

---

## QA: estratégias de teste

### A) Ignorar middleware em feature tests (quando não é o foco)
Útil para testar lógica de recursos/controllers sem depender de autenticação/sessão:

```php
public function test_can_view_dashboard_stats()
{
    $response = $this->withoutMiddleware()
                     ->get('/admin');

    $response->assertStatus(200);
}
```

**Cuidado:** isso pode mascarar problemas reais de segurança/controle de acesso. Use apenas quando o objetivo do teste não for middleware.

---

### B) Testar a lógica do middleware (bloqueia/permite)
Crie testes dedicados para confirmar:
- redirecionamentos (302)
- bloqueios (403)
- permissões/roles/policies

Exemplo:

```php
public function test_unauthorized_user_is_blocked_from_qa_tools()
{
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
         ->get('/admin/qa-dashboard')
         ->assertStatus(403);
}
```

---

## Referência rápida de middleware (relevantes no projeto)

| Middleware | Para que serve | Observações |
|---|---|---|
| `DispatchServingFilamentEvent` | Dispara evento para o Filament registrar assets/navegação | Importante para o painel funcionar corretamente |
| `SubstituteBindings` | Resolve models via route model binding | Ex.: `/users/{user}` vira `User $user` |
| `VerifyCsrfToken` | Protege contra CSRF | Verifique exclusões (webhooks/APIs) se existirem |
| `Authenticate` | Bloqueia guests e força login | No Filament, é a “porta de entrada” do Admin |
| `ShareErrorsFromSession` | Disponibiliza `$errors` em views | Necessário para exibir validações em formulários |

---

## Arquivos relacionados (para consulta)
- `app/Http/Kernel.php` — definição dos stacks globais e grupos (`web`, `api`)
- `app/Providers/Filament/AdminPanelProvider.php` — middleware do painel Filament (`middleware()` e `authMiddleware()`)
- `app/Http/Middleware/*` — middleware customizado do projeto

---
