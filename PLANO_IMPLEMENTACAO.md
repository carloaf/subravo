# SUBRAVO — Plano de Implementação

> Sistema de Controle de Estoque e Empréstimo de Material de Intendência
> Baseado na arquitetura do sistema SAGA (Laravel 11 + PostgreSQL + Blade/Tailwind + Docker)

---

## Visão Geral

O **SUBRAVO** é um sistema web monolítico MVC para controle de estoque de material de intendência militar e gestão de empréstimos (cautelas). Login por número de identidade militar + senha local. Gera Cautelas/Termos de Responsabilidade em PDF, comprovantes de devolução, relatórios de movimentação/estoque e etiquetas com QR Code. Empréstimos podem ser feitos para indivíduos ou seções/subunidades. Estoque detalhado com: lote, validade, número de série, localização física, subunidade e data de entrada no SISCOFIS.

---

## Stack Tecnológica

| Camada            | Tecnologia                          |
| ----------------- | ----------------------------------- |
| Linguagem         | PHP ^8.3                            |
| Framework         | Laravel ^11.0                       |
| Banco de Dados    | PostgreSQL 16 (Alpine)              |
| Cache/Session     | Redis 7 (Alpine)                    |
| ORM               | Eloquent (built-in)                 |
| Frontend CSS      | Tailwind CSS ^3.x (CDN + Vite)     |
| Template Engine   | Blade (Laravel built-in)            |
| Build Tool        | Vite ^5.0                           |
| PDF               | barryvdh/laravel-dompdf ^2.0        |
| Excel             | maatwebsite/excel ^3.1              |
| QR Code           | simplesoftwareio/simple-qrcode      |
| Containerização   | Docker + Docker Compose             |
| Servidor Web      | Apache (php:8.4-apache)             |
| Node (build)      | Node.js 20.x                        |

---

## Legenda de Status

- ⬜ Não iniciado
- 🔧 Em andamento
- ✅ Concluído

---

## Passo 1 — Scaffolding do Projeto ✅

- [x] Criar projeto Laravel 11 no workspace
- [x] Configurar `config/app.php`: timezone `America/Sao_Paulo`, locale `pt_BR`
- [x] Criar `Dockerfile` multi-stage (Node → Composer → PHP 8.4-apache)
- [x] Criar `docker-compose.yml` (app:8000, postgres:5432, redis:6379, mailpit:8025)
- [x] Criar `.env.example` com variáveis: DB, Redis, APP_NAME=SUBRAVO
- [x] Instalar dependências Composer:
  - `barryvdh/laravel-dompdf` ^2.0 (v2.2.0)
  - `maatwebsite/excel` ^3.1 (v3.1.67)
  - `simplesoftwareio/simple-qrcode` ^4.2 (v4.2.0)
- [x] Configurar Vite + Tailwind CSS com paleta `subravo` (olive/militar)
- [x] Configurar `tailwind.config.js` com plugin `@tailwindcss/forms`
- [x] Configurar `config/database.php` default para `pgsql`
- [x] Criar `docker/apache/000-default.conf` (virtualhost Apache)

---

## Passo 2 — Banco de Dados (Migrations) ✅

### Tabela `ranks` — Postos e Graduações
| Coluna       | Tipo              |
| ------------ | ----------------- |
| id           | bigint PK         |
| name         | string (unique)   |
| abbreviation | string            |
| order        | integer           |
| timestamps   |                   |

### Tabela `organizations` — Organizações Militares
| Coluna       | Tipo              |
| ------------ | ----------------- |
| id           | bigint PK         |
| name         | string (unique)   |
| abbreviation | string            |
| is_host      | boolean           |
| timestamps   |                   |

### Tabela `users` — Usuários
| Coluna            | Tipo                                     |
| ----------------- | ---------------------------------------- |
| id                | bigint PK                                |
| identity_number   | string (unique) — nº identidade militar  |
| password          | string (hashed)                          |
| full_name         | string                                   |
| war_name          | string                                   |
| email             | string nullable                          |
| rank_id           | FK → ranks                               |
| organization_id   | FK → organizations                       |
| subunit           | string nullable                          |
| armed_force       | enum (EB, MB, FAB)                       |
| gender            | enum (M, F)                              |
| role              | enum (admin, almoxarife, solicitante, auditor) |
| is_active         | boolean default true                     |
| avatar_url        | string nullable                          |
| timestamps        |                                          |

### Tabela `categories` — Categorias de Material
| Coluna      | Tipo          |
| ----------- | ------------- |
| id          | bigint PK     |
| name        | string        |
| description | text nullable |
| timestamps  |               |

### Tabela `products` — Produtos / Material
| Coluna        | Tipo                                  |
| ------------- | ------------------------------------- |
| id            | bigint PK                             |
| name          | string                                |
| description   | text nullable                         |
| category_id   | FK → categories                       |
| unit          | string (un, pç, par, kg, etc.)        |
| minimum_stock | integer — alerta de estoque baixo     |
| is_serialized | boolean — controle por nº de série    |
| timestamps    |                                       |

### Tabela `stock_items` — Itens no Estoque (controle detalhado)
| Coluna              | Tipo                                              |
| ------------------- | ------------------------------------------------- |
| id                  | bigint PK                                         |
| product_id          | FK → products                                     |
| serial_number       | string nullable unique — nº série                 |
| batch               | string nullable — lote                            |
| expiration_date     | date nullable — validade                          |
| siscofis_entry_date | date — data entrada SISCOFIS                      |
| location            | string — prateleira/corredor/armário              |
| subunit             | string nullable — subunidade responsável          |
| quantity            | integer — para itens não serializados             |
| status              | enum (available, loaned, damaged, decommissioned) |
| notes               | text nullable                                     |
| timestamps          |                                                   |

### Tabela `loans` — Empréstimos / Cautelas
| Coluna                   | Tipo                                        |
| ------------------------ | ------------------------------------------- |
| id                       | bigint PK                                   |
| loan_number              | string unique — auto (CAUTELA-2026-000001)  |
| borrower_type            | enum (individual, section)                  |
| borrower_user_id         | FK → users nullable — quando individual     |
| borrower_section         | string nullable — quando seção/subunidade   |
| borrower_organization_id | FK → organizations nullable                 |
| loaned_by                | FK → users — almoxarife que emprestou       |
| loan_date                | datetime                                    |
| expected_return_date     | date nullable                               |
| actual_return_date       | datetime nullable                           |
| received_by              | FK → users nullable — quem recebeu devol.   |
| status                   | enum (active, returned, partial, overdue)   |
| notes                    | text nullable                               |
| timestamps               |                                             |

### Tabela `loan_items` — Itens do Empréstimo (pivot)
| Coluna            | Tipo                            |
| ----------------- | ------------------------------- |
| id                | bigint PK                       |
| loan_id           | FK → loans                      |
| stock_item_id     | FK → stock_items                |
| quantity          | integer                         |
| returned_quantity | integer default 0               |
| condition_out     | string — estado na saída        |
| condition_in      | string nullable — estado devol. |
| timestamps        |                                 |

### Tabela `stock_movements` — Log de Movimentações (auditoria)
| Coluna         | Tipo                                                       |
| -------------- | ---------------------------------------------------------- |
| id             | bigint PK                                                  |
| stock_item_id  | FK → stock_items                                           |
| movement_type  | enum (entry, exit, loan, return, adjustment, decommission) |
| quantity       | integer                                                    |
| reference_type | string nullable — 'loan', 'manual', etc.                  |
| reference_id   | bigint nullable — loan_id etc.                             |
| performed_by   | FK → users                                                 |
| notes          | text nullable                                              |
| timestamps     |                                                            |

- [x] Criar todas as migrations na ordem correta de dependência
- [x] Verificar com `php artisan migrate` que todas rodam sem erro

**Migrations criadas:**
- `0001_01_01_000000` — ranks, organizations, users, password_reset_tokens, sessions
- `0001_01_01_000001` — cache, cache_locks
- `0001_01_01_000002` — jobs, job_batches, failed_jobs
- `0001_01_01_000003` — categories, products
- `0001_01_01_000004` — stock_items
- `0001_01_01_000005` — loans
- `0001_01_01_000006` — loan_items
- `0001_01_01_000007` — stock_movements

**Nota:** Portas Docker ajustadas (postgres:5434, redis:6380) para evitar conflito com outros projetos.

---

## Passo 3 — Seeders ✅

- [x] `RankSeeder` — 16 postos/graduações com abreviações (Gen Ex → Sd)
- [x] `OrganizationSeeder` — 13 OMs com abreviações (11º D Sup como host)
- [x] `CategorySeeder` — 8 categorias: Fardamento, Equipamento Individual, Material de Camping, Cama e Mesa, Cozinha, Ferramentas, Material de Expediente, Outros
- [x] `AdminSeeder` — Usuário admin (identity_number: `000000000`, senha: `subravo2026`, role: admin)
- [x] `DatabaseSeeder` — Orquestra: Ranks → Organizations → Categories → Admin
- [x] Verificado com `php artisan migrate:fresh --seed` — 16 ranks, 13 orgs, 8 categorias, 1 admin

---

## Passo 4 — Models (Eloquent) ✅

Criados em `app/Models/`:

- [x] `User` — belongsTo Rank/Organization, hasMany borrowedLoans/issuedLoans/receivedReturns/stockMovements, role helpers (isAdmin, isAlmoxarife, etc.), getDisplayName()
- [x] `Rank` — hasMany User, scopeOrdered, getDisplayName()
- [x] `Organization` — hasMany User/Loan, scopeHost/scopeGuest, getDisplayName()
- [x] `Category` — hasMany Product, getProductCount()
- [x] `Product` — belongsTo Category, hasMany StockItem, getAvailableStock(), getLoanedStock(), isBelowMinimum(), scopeSerialized
- [x] `StockItem` — belongsTo Product, hasMany LoanItem/StockMovement, scopeAvailable/Loaned/ExpiringSoon, STATUSES const, isExpired(), isExpiringSoon()
- [x] `Loan` — hasMany LoanItem, belongsTo User (borrower/loaned_by/received_by), belongsTo Organization, STATUSES const, generateLoanNumber(), getBorrowerDisplayName(), scopeActive/Overdue
- [x] `LoanItem` — belongsTo Loan/StockItem, getPendingQuantity(), isFullyReturned(), isPartiallyReturned(), CONDITIONS const
- [x] `StockMovement` — belongsTo StockItem/User, TYPES const, record() factory method, scopeByType/ForDateRange

Validados via `artisan tinker`: relacionamentos, scopes, helpers e constantes OK.

---

## Passo 5 — Autenticação ✅

- [x] `AuthController` — login por `identity_number` + `password`, logout com invalidação de sessão
- [x] Guard `session` com Eloquent provider usando `identity_number` (override `getAuthIdentifierName()` no User)
- [x] View `auth/login.blade.php` — formulário identidade + senha, paleta militar, ícones SVG, responsivo
- [x] Dashboard provisório (`dashboard.blade.php`) — exibe perfil/org/identidade do usuário logado
- [x] Cadastro apenas via admin (sem auto-registro — sem rota de registro)
- [x] Reset de senha via admin (sem email nesta fase — sem rota de reset)
- [x] Autorização: coluna `role` com verificação inline (padrão SAGA)
- [x] Roles: `admin` (ativo), `almoxarife`, `solicitante`, `auditor` (preparados)
- [x] Middleware `EnsureUserIsActive` — desloga usuários desativados (alias `active`)
- [x] Middleware `CheckRole` — restrição por perfil (alias `role:admin,almoxarife,...`)
- [x] Middlewares registrados em `bootstrap/app.php` (padrão Laravel 11)
- [x] Rotas: `GET /` → redirect login, `GET/POST /login`, `POST /logout`, `GET /dashboard` (auth+active)

**Testes realizados:**
- `Auth::attempt` com credenciais corretas → SUCESSO (Cel ADMIN logado)
- `Auth::attempt` com senha errada → BLOQUEADO
- `Auth::attempt` com identidade inexistente → BLOQUEADO
- HTTP `GET /` → 302 redirect → `/login`
- HTTP `GET /login` → 200 (formulário com identity_number + SUBRAVO)
- HTTP `GET /dashboard` sem auth → 302 redirect → `/login`

---

## Passo 6 — Layout e Frontend Base ✅

- [x] `layouts/app.blade.php` — layout com sidebar colapsável (Alpine.js), navbar sticky, flash messages, footer
- [x] `layouts/partials/sidebar-nav.blade.php` — navegação: Dashboard, Produtos, Estoque, Categorias, Cautelas, Movimentações, Usuários, Relatórios
- [x] Sidebar condicional por role (`@if($user->isAdmin())` para seção Administração)
- [x] Sidebar responsiva: colapsável desktop + drawer mobile com overlay
- [x] Tailwind CSS paleta militar `subravo` (olive 50-950), custom utilities em `app.css`
- [x] Fonte Figtree via `fonts.bunny.net` + Vite (não CDN)
- [x] Alpine.js 3 via CDN para interatividade
- [x] Componentes Blade (9 componentes em `resources/views/components/`):
  - `<x-stat-card>` — card de indicador com ícone, valor e cor
  - `<x-card>` — container genérico com título, subtítulo e slot actions
  - `<x-btn>` — botão com variantes (primary/secondary/danger/success/outline), tamanhos e ícone
  - `<x-modal>` — modal Alpine.js com dispatch events
  - `<x-badge>` — etiqueta de status colorida
  - `<x-input>` — input com label, erro, hint
  - `<x-select>` — select com label e erro
  - `<x-table>` — wrapper responsivo de tabela com header/body slots
  - `<x-empty-state>` — estado vazio com ícone, título e botão de ação
- [x] Dashboard reescrito usando layout + componentes: 4 stat cards, 4 cards informativos (estoque baixo, validade, empréstimos, movimentações), info do usuário

**Validação:** `php artisan view:cache` — todas as 14 views Blade compilam sem erros (20KB HTML renderizado)

---

## Passo 7 — Controllers e Rotas ✅

### Controllers (`app/Http/Controllers/`)

- [x] **AuthController** — login, logout (Passo 5)
- [x] **DashboardController** — painel com resumos e alertas
- [x] **ProductController** — CRUD produtos + categorias (index, create, store, show, edit, update, destroy, categories, storeCategory, updateCategory, destroyCategory)
- [x] **StockController** — entrada, ajustes, visualização, movimentações (index, show, entry, storeEntry, adjust, storeAdjust, movements)
- [x] **LoanController** — cautela, devolução, listagem, PDF (index, create, store, show, returnForm, processReturn, cautelaPdf, returnReceiptPdf)
- [x] **AdminController** — CRUD usuários, toggle ativo (users, createUser, storeUser, editUser, updateUser, toggleUser)
- [x] **ReportController** — relatórios PDF/tela com 6 tipos (index, generate)

### Rotas (`routes/web.php`) — 42 rotas registradas

```
Guest:
  GET  /                              → login
  POST /login                         → AuthController@login
  POST /logout                        → AuthController@logout

Autenticado (middleware 'auth'):
  GET  /dashboard                     → DashboardController@index

  GET  /products                      → ProductController@index
  GET  /products/create               → ProductController@create
  POST /products                      → ProductController@store
  GET  /products/{id}/edit            → ProductController@edit
  PUT  /products/{id}                 → ProductController@update
  DELETE /products/{id}               → ProductController@destroy

  GET  /categories                    → ProductController@categories
  POST /categories                    → ProductController@storeCategory

  GET  /stock                         → StockController@index
  GET  /stock/entry                   → StockController@entry
  POST /stock/entry                   → StockController@storeEntry
  GET  /stock/{id}                    → StockController@show
  GET  /stock/{id}/adjust             → StockController@adjust
  POST /stock/{id}/adjust             → StockController@storeAdjust
  GET  /stock/movements               → StockController@movements

  GET  /loans                         → LoanController@index
  GET  /loans/create                  → LoanController@create
  POST /loans                         → LoanController@store
  GET  /loans/{id}                    → LoanController@show
  GET  /loans/{id}/return             → LoanController@returnForm
  POST /loans/{id}/return             → LoanController@processReturn
  GET  /loans/{id}/pdf                → LoanController@cautelaPdf
  GET  /loans/{id}/return-pdf         → LoanController@returnReceiptPdf

  Prefixo /admin (role: admin):
    GET   /admin/users                → AdminController@users
    GET   /admin/users/create         → AdminController@createUser
    POST  /admin/users                → AdminController@storeUser
    GET   /admin/users/{id}/edit      → AdminController@editUser
    PATCH /admin/users/{id}           → AdminController@updateUser

    GET  /admin/reports               → ReportController@index
    GET  /admin/reports/generate      → ReportController@generate
```

---

## Passo 8 — Views (Blade) ✅

- [x] `auth/login.blade.php` — criado no Passo 6
- [x] `dashboard.blade.php` — cards com indicadores + alertas (Passo 6/7)
- [x] `products/index.blade.php` — tabela com busca, filtro por categoria, paginação
- [x] `products/create.blade.php` — formulário completo com x-input/x-select
- [x] `products/edit.blade.php` — formulário com @method('PUT') + botão excluir
- [x] `products/show.blade.php` — grid info + stat-cards + tabela de itens de estoque
- [x] `products/categories.blade.php` — CRUD inline com Alpine.js
- [x] `stock/index.blade.php` — filtros (produto, status) + tabela com localização/validade
- [x] `stock/entry.blade.php` — formulário de entrada de material completo
- [x] `stock/show.blade.php` — detalhe do item + cards de quantidade + histórico de movimentações
- [x] `stock/adjust.blade.php` — info atual + formulário de ajuste com motivo obrigatório
- [x] `stock/movements.blade.php` — filtros (tipo, período) + tabela cronológica com ±coloração
- [x] `loans/index.blade.php` — busca + filtro status + tabela com ações (PDF, devolução)
- [x] `loans/create.blade.php` — formulário com Alpine.js: tipo mutuário, itens dinâmicos, condição saída
- [x] `loans/show.blade.php` — detalhes da cautela + mutuário + itens com condição saída/retorno
- [x] `loans/return.blade.php` — formulário de devolução parcial/total com condição de retorno
- [x] `loans/pdf/cautela.blade.php` — template DomPDF A4 com cabeçalho militar, tabela de itens, assinaturas
- [x] `loans/pdf/return-receipt.blade.php` — template DomPDF recibo de devolução com condições
- [x] `admin/users/index.blade.php` — tabela com busca, filtro perfil/status, toggle ativar/desativar
- [x] `admin/users/create.blade.php` — formulário completo: identidade, senha, dados pessoais, perfil
- [x] `admin/users/edit.blade.php` — formulário com senha opcional + info do registro
- [x] `admin/reports/index.blade.php` — central de relatórios com 6 cards tipo + filtros período/formato
- [x] `admin/reports/types/stock_summary.blade.php` — resumo do estoque com totais e status
- [x] `admin/reports/types/loans_active.blade.php` — cautelas ativas com alerta de atraso
- [x] `admin/reports/types/loans_history.blade.php` — histórico com filtro de período
- [x] `admin/reports/types/movements.blade.php` — movimentações com tipo e ±coloração
- [x] `admin/reports/types/low_stock.blade.php` — produtos abaixo do mínimo com déficit
- [x] `admin/reports/types/expiring.blade.php` — itens próximos da validade com destaque visual
- [ ] `stock/label.blade.php` — template etiqueta com QR Code (Passo 9)

---

## Passo 9 — Geração de Documentos ✅

### PDFs (barryvdh/laravel-dompdf)
- [x] **Cautela / Termo de Responsabilidade** — `loans/pdf/cautela.blade.php` (Passo 8) + `LoanController@cautelaPdf`
- [x] **Comprovante de Devolução** — `loans/pdf/return-receipt.blade.php` (Passo 8) + `LoanController@returnReceiptPdf`
- [x] **Layout PDF para relatórios** — `layouts/pdf.blade.php` — layout standalone com CSS embarcado, cabeçalho militar, badges, tabelas formatadas
- [x] **6 templates PDF de relatórios** — `admin/reports/pdf/{stock_summary,loans_active,loans_history,movements,low_stock,expiring}.blade.php`
- [x] **ReportController** — detecta formato e usa views PDF separadas para DomPDF (landscape A4)

### Excel (maatwebsite/excel)
- [x] `app/Exports/StockReportExport.php` — estoque atual com produto, categoria, disponível, emprestado, mínimo, status
- [x] `app/Exports/MovementReportExport.php` — movimentações por período com tipo, quantidade ±, responsável
- [x] `app/Exports/LoanReportExport.php` — cautelas ativas ou histórico com mutuário, datas, status (scope parametrizável)
- [x] `app/Exports/ExpirationAlertExport.php` — itens próximos da validade (60 dias) com dias restantes
- [x] Todos os exports: header estilizado (fundo olive #4a5d23), auto-size colunas, título de aba

### Etiquetas / QR Code (simplesoftwareio/simple-qrcode)
- [x] QR Code aponta para `/stock/{id}` — formato SVG 200px, error correction M
- [x] `stock/label.blade.php` + `stock/partials/label-single.blade.php` — template imprimível 80x50mm
- [x] Etiqueta: nome do produto, nº série, lote, validade, quantidade, localização, QR Code
- [x] `StockController@label` — etiqueta individual com print dialog
- [x] `StockController@labelsBatch` — etiquetas em lote via seleção POST
- [x] Botão "Etiqueta QR" na listagem de estoque e na página de detalhe do item

### Rotas adicionadas
- [x] `GET /stock/{id}/label` → `stock.label`
- [x] `POST /stock/labels-batch` → `stock.labelsBatch`

### Correções aplicadas
- [x] `loans/return.blade.php` — campos corrigidos (`items→returns`, `return_quantity→quantity`, `return_notes→notes`) para match com `processReturn` controller
- [x] `loans/show.blade.php` — route name corrigido (`loans.return.form` → `loans.return`)
- [x] `admin/reports/index.blade.php` — form method corrigido (GET → POST), formato Excel adicionado

---

## Passo 10 — Regras de Negócio ✅

### Empréstimos
- [x] Empréstimo só se `stock_item.status == 'available'` — verificado em `LoanController@store`
- [x] Impedir empréstimo de itens vencidos (`isExpired()`) — validação no controller + filtro no `create()` query
- [x] Ao criar empréstimo: item → `loaned`, registra `stock_movement` tipo `loan`
- [x] Nº cautela auto-gerado: `CAUTELA-{ANO}-{SEQUENCIAL:06d}` — `Loan::generateLoanNumber()`
- [x] Empréstimo individual: `borrower_user_id` com busca AJAX por identidade militar (`searchBorrower`)
- [x] Empréstimo seção: `borrower_section` + `borrower_organization_id`
- [x] Rota `GET /loans/search-borrower` → busca por `identity_number` ou `war_name` (debounce Alpine.js)

### Devoluções
- [x] Devolução parcial: atualiza `returned_quantity`, loan → `partial`
- [x] Devolução total: loan → `returned`, itens voltam a `available`, registra `actual_return_date`
- [x] Impedir devolução duplicada — guard `status === 'returned'` em `returnForm` e `processReturn`
- [x] Verificação cross-cautela — item deve pertencer à cautela correta

### Detecção de Atrasos
- [x] Artisan command `loans:check-overdue` — marca cautelas ativas vencidas como `overdue` no banco
- [x] Agendamento diário às 06:00 via `routes/console.php` (`Schedule::command`)
- [x] Scope `Loan::overdue()` ampliado — detecta tanto `status=overdue` quanto `active` + data vencida

### Dashboard / Alertas
- [x] Alerta no dashboard: validade < 30 dias, estoque < mínimo, empréstimos vencidos
- [x] Contador de alertas inclui empréstimos vencidos
- [x] Seção "Empréstimos Vencidos" com lista das cautelas em atraso (top 5)
- [x] Grid de alertas 3 colunas: estoque baixo, validade, vencidos

### Log e Movimentações
- [x] Log obrigatório em `stock_movements` para toda movimentação (entry, loan, return, adjustment)
- [x] `StockMovement::record()` — método estático padronizado com reference_type/id

### Proteções no Modelo
- [x] `StockItem::booted()` — impede `quantity < 0` via `saving` event (DomainException)
- [x] Scopes de negócio: `available()`, `loaned()`, `expiringSoon()`, `overdue()`, `active()`

---

## Passo 11 — Docker & Deploy ✅

### Dockerfile (multi-stage)
- [x] **Stage 1 — Frontend**: `node:20-alpine` → `npm ci` + `npm run build` (Vite/Tailwind)
- [x] **Stage 2 — Composer**: `composer:2.6` → `composer install --no-dev --optimize-autoloader`
- [x] **Stage 3 — Runtime**: `php:8.4-apache` → extensões (pdo_pgsql, gd, redis, zip, bcmath, etc.)
- [x] Apache com `mod_rewrite` + `mod_headers`, DocumentRoot `/var/www/html/public`
- [x] Healthcheck HTTP integrado (`curl -f http://localhost/`)
- [x] PHP config personalizado (`docker/php/subravo.ini`): timezone, upload 20M, memory 256M, opcache
- [x] Entrypoint inteligente (`docker/entrypoint.sh`): aguarda DB/Redis, composer dev em local, migrate, cache, storage:link
- [x] `.dockerignore` — exclui node_modules, vendor, .git, tests, docs do contexto de build

### docker-compose.yml (5 serviços)
- [x] **app** (porta 8000) — build multi-stage, volume mount dev, `env_file: .env`, healthcheck
- [x] **database** — `postgres:16-alpine`, healthcheck `pg_isready`, volume persistente
- [x] **redis** — `redis:7-alpine`, healthcheck `redis-cli ping`, appendonly, maxmemory 128mb
- [x] **scheduler** — sidecar com loop `schedule:run` a cada 60s (sem cron)
- [x] **mailpit** — SMTP (1025) + UI (8025)
- [x] Depends_on com `condition: service_healthy` (app espera DB+Redis healthy)
- [x] Portas externas configuráveis via `.env` (`APP_PORT`, `DB_EXTERNAL_PORT`, etc.)

### .env.example
- [x] Completo com todas as variáveis: APP, DB, Redis, Mail, Cache, Session, Vite
- [x] Seção Docker Compose com portas externas configuráveis

### Validação
- [x] `docker compose config` — YAML válido
- [x] `docker compose build app` — build OK (3 stages)
- [x] `docker compose up -d` — 5 containers running (app healthy)
- [x] `docker compose exec app php artisan migrate:fresh --seed` — 8 migrations + 4 seeders OK
- [x] `curl http://localhost:8000/login` — HTTP 200 (8.5KB)
- [x] `docker compose exec app php artisan loans:check-overdue` — OK

---

## Passo 12 — Testes e Validação Final ✅

- [x] Login com admin padrão (identidade 000000000 + senha subravo2026)
- [x] CRUD de produtos e categorias (create/read/update/delete, isBelowMinimum)
- [x] Entrada de material no estoque (StockItem + StockMovement, validade, nullable siscofis_entry_date)
- [x] Criar empréstimo individual (CAUTELA-2026-000001, borrower_type=individual, estoque 10→7)
- [x] Criar empréstimo por seção (CAUTELA-2026-000002, borrower_type=section, isOverdue, overdue scope)
- [x] Gerar cautela PDF (879 KB, %PDF válido, DomPDF)
- [x] Registrar devolução parcial (status=partial, getPendingQuantity=1)
- [x] Registrar devolução total (status=returned, isFullyReturned, actual_return_date)
- [x] Gerar comprovante de devolução PDF (879 KB, %PDF válido)
- [x] Verificar relatórios PDF (6/6: stock_summary, loans_active, loans_history, movements, low_stock, expiring)
- [x] Verificar relatórios Excel (6/6: todos os tipos funcionando)
- [x] Gerar etiqueta QR Code (SVG 4.674 chars, SimpleSoftwareIO)
- [x] Verificar alertas no dashboard (totalProducts, availableStock, expiringItems, alertCount)
- [x] Verificar log de movimentações (6 registros: 2 entry, 2 loan, 2 return)
- [x] HTTP Smoke Tests — todas as rotas HTTP 200 (dashboard, products, stock, loans, categories, admin/reports, admin/users)

### Bugs encontrados e corrigidos durante testes:
1. **stock_items.siscofis_entry_date NOT NULL** — Migration fix criada (2026_02_10_200000_fix_stock_items_nullable_columns.php) para tornar siscofis_entry_date e location nullable
2. **btn.blade.php ParseError** — Operador `??` dentro de interpolação `"{...}"` não funciona em PHP; extraídas variáveis intermediárias

---

## Decisões Arquiteturais

| Decisão | Escolha | Motivo |
| ------- | ------- | ------ |
| Auth | Identidade + senha local | Ambiente militar sem internet garantida |
| Perfil inicial | Apenas admin | Demais roles preparados para fase 2 |
| Estoque | Detalhado (lote, validade, série, SISCOFIS) | Rastreabilidade para material controlado |
| Empréstimo | Individual + seção | Cautela pessoal e para subunidades |
| Arquitetura | MVC sem service/repository layer | Padrão SAGA — simplicidade |
| QR Code | simplesoftwareio/simple-qrcode | Leve, compatível Laravel 11 |
| Documentos | dompdf + maatwebsite/excel | Mesmo padrão SAGA, validado |

---

## Passo 13 — Layout e Estilo Visual (padrão SAGA) ✅

Copiar e adaptar o layout, CSS, estilos e página de login do projeto SAGA para manter identidade visual unificada.

### Login
- [x] Reescrever `auth/login.blade.php` no estilo SAGA: gradiente, glass-card, logo com imagem, inputs modernos
- [x] Adaptar texto/branding para SUBRAVO (nome, subtítulo, cor olive→green emerald)
- [x] Manter campo `identity_number` (SUBRAVO) ao invés de email (SAGA)
- [x] Remover botões Google/Registro (SUBRAVO é cadastro somente via admin)

### Layout Principal
- [x] Trocar layout sidebar por **top navigation** (padrão SAGA `layouts/app.blade.php`)
- [x] Navbar com: logo+nome, links de navegação, info do usuário, botão sair
- [x] Incluir links condicionais por role (admin: Usuários, Relatórios)
- [x] Footer padronizado: "© 2026 SUBRAVO - Desenv: Augusto"
- [x] Flash messages com estilo SAGA (sucesso verde, erro vermelho)

### Dashboard
- [x] Header gradient (emerald→green) com logo, título SUBRAVO, user info, status online, botões
- [x] Quick stats bar glassmorphism no header (produtos, estoque, empréstimos, alertas)
- [x] Manter conteúdo existente do dashboard (alertas, tabelas, stat cards) abaixo do header

### Assets
- [x] Copiar favicons do SAGA para `/public/` (favicon.ico, favicon-16/32, apple-touch, android-chrome)
- [x] Copiar logo `folhaint_transparent.png` para `/public/images/`
- [x] Criar `site.webmanifest` adaptado para SUBRAVO

### CSS/Estilos
- [x] Adicionar `enhanced-forms.css` (inputs/selects/radios estilizados do SAGA)
- [x] Incluir Tailwind CDN como fallback além do Vite build
- [x] Atualizar componentes Blade para nova paleta (emerald-* primary, substituindo subravo-*)
- [x] Adicionar Chart.js CDN para gráficos

---

## Passo 14 — Dados de Demonstração ✅

- [x] `DemoSeeder` com 40 produtos de intendência realistas (Japonas PP/P/M/G/GG, Toldo, Barracas 2P/4P, Sacos Dormir, Colchonetes, Redes, Mochilas, Capacetes, Panelas, Lanternas, Coturno, Gandola, Cantis, Marmitas, Talheres, Cobertores, Bandejas, Canecas, Pá, Picareta, Facão, Papel, Caneta, Corda, etc.)
- [x] 192 itens de estoque com lotes, validades variadas, localizações (serializados + bulk)
- [x] 6 cautelas (ativas, vencidas, devolvidas, parcial) com itens variados
- [x] 5 usuários demo (2 almoxarifes, 2 solicitantes, 1 auditor) + 1 admin = 6 total
- [x] 220 movimentações históricas realistas (entradas SISCOFIS, empréstimos, devoluções, ajustes)
- [x] Comando `php artisan db:seed --class=DemoSeeder` separado do seed padrão
- [x] Credenciais de teste documentadas (senha: `subravo2026`)

---

## Passo 15 — PHPUnit / Feature Tests ⬜

- [ ] Testes de autenticação (login válido, inválido, logout, middleware)
- [ ] Testes CRUD de produtos e categorias
- [ ] Testes de entrada/ajuste de estoque com movimentação
- [ ] Testes de empréstimo (criação, devolução parcial, total)
- [ ] Testes de geração PDF (cautela, recibo, relatórios)
- [ ] Testes de exportação Excel
- [ ] Testes de regras de negócio (estoque negativo, item vencido, duplicidade)
- [ ] Configurar `phpunit.xml` para PostgreSQL de teste
- [ ] CI pipeline sugerido (GitHub Actions)

---

## Passo 16 — Backup Automático ⬜

- [ ] Script `backup-db.sh` com `pg_dump` compactado
- [ ] Container sidecar ou cron para backup diário
- [ ] Retenção de 30 dias (auto-cleanup)
- [ ] Volume Docker para armazenamento dos backups
- [ ] Comando `php artisan backup:run` e `backup:restore`

---

## Passo 17 — Roles e Permissões Completos ⬜

- [ ] Ativar perfis: `almoxarife` (estoque+cautelas), `solicitante` (visualização), `auditor` (relatórios)
- [ ] Middleware `role:` expandido com permissões granulares
- [ ] Sidebar/nav condicional por perfil completo
- [ ] Páginas de acesso negado (403) customizadas
- [ ] Testes de autorização por perfil

---

## Passo 18 — Notificações ⬜

- [ ] Alertas por email para cautelas vencidas (Mailpit → SMTP real)
- [ ] Notificação de estoque crítico para admin/almoxarife
- [ ] Resumo diário de pendências (command + mail)
- [ ] Configuração SMTP real para produção

---

## Passo 19 — Importação SISCOFIS ⬜

- [ ] Upload de planilha Excel/CSV para carga em massa do estoque
- [ ] Tela de mapeamento de colunas
- [ ] Validação prévia com relatório de erros
- [ ] Importação com transação (tudo ou nada)
- [ ] Log de importação

---

## Passo 20 — Auditoria Completa ⬜

- [ ] Log de todas as ações do usuário (login, CRUD, exports) com IP e timestamp
- [ ] Tabela `audit_logs` com user_id, action, model, changes, ip, user_agent
- [ ] Tela de consulta de logs para admin/auditor
- [ ] Trait `Auditable` para models
- [ ] Export de logs em Excel

---

## Passo 21 — PWA / Modo Offline ⬜

- [ ] Service worker para cache de assets e páginas estáticas
- [ ] Manifest completo com ícones
- [ ] Fallback offline para consulta de estoque
- [ ] Sync quando recuperar conexão

---

*Última atualização: 10/02/2026 — Passo 14 (Dados de Demonstração) concluído ✅*
