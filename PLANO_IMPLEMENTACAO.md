# HelpSub — Plano de Implementacao

> Sistema de Controle de Estoque e Empréstimo de Material de Intendência
> Baseado na arquitetura do sistema SAGA (Laravel 11 + PostgreSQL + Blade/Tailwind + Docker)

---

## Visão Geral

O **HelpSub** é um sistema web monolitico MVC para controle de estoque de material de intendencia militar e gestao de emprestimos (cautelas). Login por numero de identidade militar + senha local. Gera Cautelas/Termos de Responsabilidade em PDF, comprovantes de devolucao, relatorios de movimentacao/estoque e etiquetas com QR Code. Emprestimos podem ser feitos para individuos ou secoes/subunidades. Estoque detalhado com: lote, validade, numero de serie, localizacao fisica, subunidade e data de entrada no SISCOFIS.

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
| PDF Parser        | smalot/pdfparser ^2.12              |
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
- [x] Criar `.env.example` com variaveis: DB, Redis, APP_NAME=HelpSub
- [x] Instalar dependências Composer:
  - `barryvdh/laravel-dompdf` ^2.0 (v2.2.0)
  - `maatwebsite/excel` ^3.1 (v3.1.67)
  - `simplesoftwareio/simple-qrcode` ^4.2 (v4.2.0)
  - `smalot/pdfparser` ^2.12 (v2.12.3)
- [x] Configurar Vite + Tailwind CSS com paleta `helpsub` (emerald/militar)
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
| role              | enum (admin, manager, user) |
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
| loaned_by                | FK → users — quem emprestou                 |
| subunit                  | string nullable — subunidade do criador     |
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

**Migrations consolidadas (11 arquivos):**
- `0001_01_01_000000` — ranks, organizations, users (admin/manager/user), password_reset_tokens, sessions
- `0001_01_01_000001` — cache, cache_locks
- `0001_01_01_000002` — jobs, job_batches, failed_jobs
- `0001_01_01_000003` — categories, products (+ subunit, siscofis_code, is_durable, shelf_life_months)
- `0001_01_01_000004` — stock_items (+ nullable siscofis_entry_date/location, unit_cost)
- `0001_01_01_000005` — loans (+ subunit, borrower_cpf, borrower_phone)
- `0001_01_01_000006` — loan_items
- `0001_01_01_000007` — stock_movements
- `0001_01_01_000008` — extensão pg_trgm (PostgreSQL)
- `0001_01_01_000009` — inventory_uploads, inventory_items (+ índices GIN trigram)
- `0001_01_01_000010` — durable_goods_inventory (+ subunit)

**Nota:** Portas Docker ajustadas (app:8095, postgres:5434, redis:6380) para evitar conflito com outros projetos. **Sistema renomeado para HelpSub**.

---

## Passo 3 — Seeders ✅

- [x] `RankSeeder` — 16 postos/graduações com abreviações (Gen Ex → Sd)
- [x] `OrganizationSeeder` — 13 OMs com abreviações (11º D Sup como host)
- [x] `CategorySeeder` — 8 categorias: Fardamento, Equipamento Individual, Material de Camping, Cama e Mesa, Cozinha, Ferramentas, Material de Expediente, Outros
- [x] `AdminSeeder` — Usuario admin (identity_number: `000000000`, senha: `helpsub2026`, role: admin)
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
- [x] Roles simplificados: `admin`, `manager`, `user`
  - **Admin**: Gerencia usuários, relatórios e reset de estoque; enxerga dados de todas as subunidades
  - **Manager**: Acesso completo ao sistema exceto menus "Usuários" e "Reset" (restritos ao admin); isolado à sua subunidade
  - **User**: Perfil padrão; isolado à sua subunidade
- [x] Middleware `EnsureUserIsActive` — desloga usuários desativados (alias `active`)
- [x] Middleware `CheckRole` — restrição por perfil (alias `role:admin,almoxarife,...`)
- [x] Middlewares registrados em `bootstrap/app.php` (padrão Laravel 11)
- [x] Rotas: `GET /` → redirect login, `GET/POST /login`, `POST /logout`, `GET /dashboard` (auth+active)

**Testes realizados:**
- `Auth::attempt` com credenciais corretas → SUCESSO (Cel ADMIN logado)
- `Auth::attempt` com senha errada → BLOQUEADO
- `Auth::attempt` com identidade inexistente → BLOQUEADO
- HTTP `GET /` → 302 redirect → `/login`
- HTTP `GET /login` → 200 (formulario com identity_number + HelpSub)
- HTTP `GET /dashboard` sem auth → 302 redirect → `/login`

---

## Passo 6 — Layout e Frontend Base ✅

- [x] `layouts/app.blade.php` — layout com sidebar colapsável (Alpine.js), navbar sticky, flash messages, footer
- [x] `layouts/partials/sidebar-nav.blade.php` — navegação: Dashboard, Produtos, Estoque, Categorias, Cautelas, Movimentações, Usuários, Relatórios
- [x] Sidebar condicional por role (`@if($user->isAdmin())` para seção Administração)
- [x] Sidebar responsiva: colapsável desktop + drawer mobile com overlay
- [x] Tailwind CSS paleta militar `helpsub` (emerald 50-950), custom utilities em `app.css`
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
- [x] `loans/create.blade.php` — formulário com Alpine.js: dados da cautela, cautelado manual com busca opcional, campos de cautelado/assinante com nome de guerra e posto/grad em select, itens dinâmicos, condição saída
- [x] `loans/show.blade.php` — detalhes da cautela + cautelado/assinante opcional + itens com condição saída/retorno
- [x] `loans/return.blade.php` — formulário de devolução parcial/total com condição de retorno
- [x] `loans/pdf/cautela.blade.php` — template DomPDF A4 com cabeçalho militar, tabela de itens, assinante opcional e assinatura em nome completo + posto/grad
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
- [x] **Layout PDF simplificado para inventário** — `layouts/pdf-inventory.blade.php` — cabeçalho minimalista com apenas organização, título e metadados
- [x] **6 templates PDF de relatórios administrativos** — `admin/reports/pdf/{stock_summary,loans_active,loans_history,movements,low_stock,expiring}.blade.php` — todos convertidos para usar @extends('layouts.pdf')
- [x] **3 templates PDF do módulo de inventário** — `inventory/{materials-pdf,reports/pdf,reports/monthly-consolidated-pdf}.blade.php` — todos convertidos para usar @extends('layouts.pdf-inventory') com cabeçalho simplificado
- [x] **ReportController** — detecta formato e usa views PDF separadas para DomPDF (landscape A4)

### Design Profissional de Relatórios ✅
- [x] **Layout PDF redesenhado com estilo institucional profissional**:
  - Margens adequadas (30mm laterais, 25mm topo/fundo) configuradas via @page
  - Cabeçalho institucional completo com 3 camadas (brasão + info + sistema)
  - Gradiente verde militar profissional (065f46 → 047857 → 059669)
  - Brasão circular com bandeira do Brasil 🇧🇷
  - Badge HelpSub em destaque com gradiente dourado
  - Informação organizacional completa (Ministério da Defesa, 11º D Sup, etc.)
- [x] **Título de relatório impactante**:
  - Ícones decorativos laterais (📊)
  - Bordas grossas (6px) em verde militar
  - Lettering grande (16px) com espaçamento (2px)
  - Gradiente sutil de fundo (f9fafb → ffffff)
- [x] **Área de metadados redesenhada**:
  - Fundo gradiente verde claro (ecfdf5 → d1fae5)
  - Bordas arredondadas (10px) com borda verde (2px)
  - Informações organizadas em duas colunas
  - Tipografia melhorada com uppercase em strong
- [x] **Tabelas profissionais**:
  - Cabeçalho com gradiente triplo (065f46 → 047857 → 059669)
  - Bordas arredondadas (10px) com shadow (0 4px 10px)
  - Zebra striping sutil (#ffffff / #f9fafb)
  - Footer com gradiente verde claro
  - Padding aumentado (12px cabeçalho, 10px células)
- [x] **Cards de resumo melhorados**:
  - Barra superior verde decorativa (4px)
  - Gradiente sutil de fundo
  - Sombras mais pronunciadas
  - Valores gigantes (26px) com font-weight 900
  - Espaçamento entre cards (12px)
- [x] **Badges modernos**:
  - Bordas mais grossas (1.5px)
  - Box-shadow para profundidade
  - Font-weight 700
  - Padding aumentado (4px 12px)
- [x] **Section titles destacados**:
  - Borda esquerda grossa (6px)
  - Bordas superior/inferior (2px)
  - Gradiente de fundo (ecfdf5 → d1fae5 → ecfdf5)
  - Font-size maior (12px) e font-weight 800
- [x] **Alertas profissionais**:
  - Gradientes sutis nos fundos
  - Bordas laterais grossas (5px) + bordas topo/fundo (2px)
  - Padding aumentado (15px 20px)
  - Box-shadow para destaque
- [x] **Footer institucional completo**:
  - Borda superior dupla (3px double)
  - Informações de validação jurídica
  - Classificação e uso (OSTENSIVO - INTERNO)
  - Timestamp com timezone

### Excel (maatwebsite/excel)
- [x] `app/Exports/StockReportExport.php` — estoque atual com produto, categoria, disponível, emprestado, mínimo, status
- [x] `app/Exports/MovementReportExport.php` — movimentações por período com tipo, quantidade ±, responsável
- [x] `app/Exports/LoanReportExport.php` — cautelas ativas ou histórico com mutuário, datas, status (scope parametrizável)
- [x] `app/Exports/ExpirationAlertExport.php` — itens próximos da validade (60 dias) com dias restantes
- [x] Todos os exports: header estilizado (fundo emerald #059669, substituiu olive #4a5d23), auto-size colunas, título de aba
- [x] Formatação condicional aplicada (estoque baixo vermelho, vencidos vermelho, atrasadas vermelho)
- [x] Freeze panes, zebra striping (verde-50), bordas cinza-300
- [x] Alinhamento centralizado em colunas numéricas

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

### Layout PDF de Inventário (Simplificado)
- [x] Criado layout específico `layouts/pdf-inventory.blade.php` com design minimalista
- [x] Cabeçalho padronizado: "11º Depósito de Suprimento — Relatório de Material"
- [x] Título dinâmico: "RELATÓRIO {LOCALIZAÇÃO}" (ex: "RELATÓRIO DE INVENTÁRIO GERAL", "RELATÓRIO CONSOLIDADO MENSAL - FEVEREIRO/2026")
- [x] Metadados: Data de geração, Gerado por, Período
- [x] Seções de material: "INVENTÁRIO — DETALHAMENTO COMPLETO DE ITENS" com fundo verde (#d1fae5), borda verde (#10b981) e texto verde escuro (#065f46)
- [x] Sem gradientes, emojis ou design elaborado — foco em clareza e funcionalidade
- [x] Formato paisagem (landscape A4) para mais colunas

### Filtros Avançados de Relatórios de Inventário
- [x] Filtro por Upload Específico (select com filename, dependency e data)
- [x] Filtro por Tipo de Material (select com todos os tipos cadastrados)
- [x] Filtro por Dependência (select com todas as dependências)
- [x] **3 campos de "Buscar Material" independentes** — cada um busca simultaneamente em nome, código e ficha (ILIKE)
- [x] Busca ampla: os 3 campos são aplicados com operador **OR** entre si (união de resultados)
- [x] Busca interna: cada campo busca com OR em `material_name`, `material_code` e `ficha_number`
- [x] Queries PostgreSQL case-insensitive (ILIKE) em todos os campos de busca
- [x] Combinação de múltiplos filtros simultaneamente
- [x] Validação no backend via `InventoryController@generateReport`
- [x] Exemplo de uso: "beliche" + "mesa" + "armário" retorna todos os beliches **+** todas as mesas **+** todos os armários
- [x] **Relatórios abrem em nova aba** — `target="_blank"` em formulários de relatórios e links de PDFs

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
- [x] PHP config personalizado (`docker/php/helpsub.ini`): timezone, upload 20M, memory 256M, opcache
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
- [x] `docker/entrypoint.sh` atualizado para garantir seed básico idempotente em ambiente local após `migrate`, evitando banco vazio sem usuário admin

---

## Passo 12 — Testes e Validação Final ✅

- [x] Login com admin padrao (identidade 000000000 + senha helpsub2026)
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
- [x] Adaptar texto/branding para HelpSub (nome, subtitulo, cor olive→green emerald)
- [x] Manter campo `identity_number` (HelpSub) ao inves de email (SAGA)
- [x] Remover botoes Google/Registro (HelpSub e cadastro somente via admin)

### Layout Principal
- [x] Trocar layout sidebar por **top navigation** (padrão SAGA `layouts/app.blade.php`)
- [x] Navbar com: logo+nome, links de navegação, info do usuário, botão sair
- [x] Incluir links condicionais por role (admin: Usuários, Relatórios)
- [x] Footer padronizado: "© 2026 HelpSub - Desenv: Augusto"
- [x] Flash messages com estilo SAGA (sucesso verde, erro vermelho)

### Dashboard
- [x] Header gradient (emerald→green) com logo, titulo HelpSub, user info, status online, botoes
- [x] Quick stats bar glassmorphism no header (produtos, estoque, empréstimos, alertas)
- [x] Manter conteúdo existente do dashboard (alertas, tabelas, stat cards) abaixo do header

### Assets
- [x] Copiar favicons do SAGA para `/public/` (favicon.ico, favicon-16/32, apple-touch, android-chrome)
- [x] Copiar logo `folhaint_transparent.png` para `/public/images/`
- [x] Criar `site.webmanifest` adaptado para HelpSub

### CSS/Estilos
- [x] Adicionar `enhanced-forms.css` (inputs/selects/radios estilizados do SAGA)
- [x] Incluir Tailwind CDN como fallback além do Vite build
- [x] Atualizar componentes Blade para nova paleta (emerald-* primary, substituindo helpsub-*)
- [x] Adicionar Chart.js CDN para gráficos

---

## Passo 14 — Dados de Demonstração ✅

- [x] `DemoSeeder` com 40 produtos de intendência realistas (Japonas PP/P/M/G/GG, Toldo, Barracas 2P/4P, Sacos Dormir, Colchonetes, Redes, Mochilas, Capacetes, Panelas, Lanternas, Coturno, Gandola, Cantis, Marmitas, Talheres, Cobertores, Bandejas, Canecas, Pá, Picareta, Facão, Papel, Caneta, Corda, etc.)
- [x] 192 itens de estoque com lotes, validades variadas, localizações (serializados + bulk)
- [x] 6 cautelas (ativas, vencidas, devolvidas, parcial) com itens variados
- [x] 5 usuários demo (2 almoxarifes, 2 solicitantes, 1 auditor) + 1 admin = 6 total
- [x] 220 movimentações históricas realistas (entradas SISCOFIS, empréstimos, devoluções, ajustes)
- [x] Comando `php artisan db:seed --class=DemoSeeder` separado do seed padrão
- [x] Credenciais de teste documentadas (senha: `helpsub2026`)

---

## Passo 15a — Estrutura de Produtos com Validade SISCOFIS ✅

- [x] Migration para adicionar `siscofis_code`, `shelf_life_months` e `is_durable` em `products`
- [x] Arquivo mestre `database/data/military_products.php` com 40 produtos padronizados
- [x] Cada produto tem: nome, categoria, unidade, código SISCOFIS, validade em meses, flags serializado/durável, estoque mínimo
- [x] Campo `is_durable` (Uso Duradouro): true para itens metálicos/ferramentas (12 produtos), false para tecidos/consumíveis (28 produtos)
- [x] Lógica automática: ao criar `StockItem` com `siscofis_entry_date`, calcula `expiration_date` = data entrada + validade do produto
- [x] Product model atualizado com novos campos no fillable e casts
- [x] StockItem model: hook `booted()` para auto-cálculo de validade

---

## Passo 15b — Monitoramento de Material de Uso Duradouro ✅

- [x] Rota `/durables` para página de monitoramento de produtos duráveis
- [x] Método `durables()` no StockController com agregações por produto
- [x] View `stock/durables.blade.php` com design SAGA (emerald/glassmorphism)
- [x] Dashboard de produtos duráveis com estatísticas (total, disponível, emprestado, danificado)
- [x] Alertas de validade: itens vencidos (vermelho) e vencendo em 30 dias (amarelo)
- [x] Detalhamento collapsible de cada produto com tabela de itens
- [x] Link no menu de navegação principal entre "Estoque" e "Cautelas" (desktop + mobile)
- [x] Quick stats globais: total de itens, disponíveis, emprestados, danificados, vencendo, vencidos
- [x] Ordenação por nome, total, disponíveis, emprestados ou validade
- [x] Formulários de produto (create/edit) com campos SISCOFIS
- [x] Campo Código SISCOFIS (opcional)
- [x] Campo Validade em meses (opcional)
- [x] Checkbox "Uso Duradouro" com descrição
- [x] Validação no ProductController para novos campos

---

## Passo 16 — PHPUnit / Feature Tests ✅

### Configuração do Ambiente de Testes
- [x] `phpunit.xml` configurado para PostgreSQL de teste (`helpsub_test`)
- [x] `config/database.php` com conexão `pgsql_testing`
- [x] `TestCase` base com trait `RefreshDatabase` e seed automático
- [x] `.env.example` atualizado com `DB_TEST_DATABASE`

### Traits de Teste (helpers)
- [x] `CreatesUsers` trait — helpers para criar usuários (admin, manager, user)
- [x] `CreatesStock` trait — helpers para criar categorias, produtos, items de estoque (serializados e bulk)
- [x] `CreatesLoans` trait — helpers para criar empréstimos individuais e por seção, adicionar itens

### Suítes de Testes Feature (8 arquivos, 70+ testes)

#### AuthenticationTest (13 testes)
- [x] Exibição da página de login
- [x] Autenticação com credenciais válidas
- [x] Bloqueio com senha inválida
- [x] Bloqueio com identidade inexistente
- [x] Bloqueio de usuários inativos no login
- [x] Logout de usuários autenticados
- [x] Redirecionamento de unauthenticated para login
- [x] Logout automático de usuários desativados (middleware `active`)
- [x] Validação de campos obrigatórios (identity_number, password)

#### AuthorizationTest (8 testes)
- [x] Admin pode acessar rotas `/admin/*`
- [x] Não-admin recebe HTTP 403 em rotas admin
- [x] Admin pode acessar relatórios
- [x] Usuários autenticados acessam dashboard, products, stock, loans

#### ProductTest (14 testes)
- [x] CRUD completo de produtos (list, show, create, update, delete)
- [x] Impedir exclusão de produto com estoque
- [x] Validações (nome, categoria, unidade obrigatórios)
- [x] Produtos serializados e duráveis
- [x] Auto-cálculo de validade baseado em `shelf_life_months`

#### CategoryTest (7 testes)
- [x] CRUD completo de categorias (list, create, update, delete)
- [x] Impedir exclusão de categoria com produtos
- [x] Validação de nome obrigatório e único

#### StockTest (13 testes)
- [x] Visualização de estoque e detalhes de itens
- [x] Entrada de estoque com registro de movimento
- [x] Ajuste de quantidade com log de movimento
- [x] Validações (produto, quantidade obrigatórios)
- [x] Proteção contra quantidade negativa
- [x] Produtos serializados: serial_number obrigatório e único
- [x] Auto-cálculo de validade na entrada
- [x] Filtros por status (available, loaned)
- [x] Histórico de movimentações
- [x] Identificação de itens vencidos e vencendo

#### LoanTest (15 testes)
- [x] Visualização de cautelas e detalhes
- [x] Criação de empréstimo individual e por seção
- [x] Geração automática de número de cautela (CAUTELA-{ANO}-{SEQ})
- [x] Validações (mutuário, itens obrigatórios)
- [x] Impedir empréstimo acima do estoque disponível
- [x] Impedir empréstimo de itens vencidos
- [x] Devolução total (status → returned, estoque restaurado)
- [x] Devolução parcial (status → partial, quantidade pendente)
- [x] Impedir devolução acima do emprestado
- [x] Impedir devolução duplicada
- [x] Detecção de empréstimos vencidos (scope `overdue`)
- [x] Registro de movimentações (loan, return)

#### BusinessRulesTest (17 testes)
- [x] Estoque não pode ficar negativo (DomainException)
- [x] Produto abaixo do mínimo (isBelowMinimum)
- [x] Status do item muda para `loaned` quando totalmente emprestado
- [x] Status permanece `available` quando parcialmente emprestado
- [x] Status da cautela: `active` → `partial` → `returned`
- [x] Restauração de estoque após devolução
- [x] Auto-cálculo de validade com shelf_life_months
- [x] Produtos serializados e duráveis identificados corretamente
- [x] Padrão de número de cautela validado
- [x] Números de cautela sequenciais
- [x] Cálculo correto de quantidade pendente (getPendingQuantity)
- [x] Estoque disponível exclui itens emprestados
- [x] Estoque emprestado conta apenas itens `loaned`

#### ReportExportTest (13 testes)
- [x] Geração de cautela PDF
- [x] Geração de comprovante de devolução PDF
- [x] Relatórios PDF: stock_summary, loans_active, movements, low_stock, expiring
- [x] Relatórios Excel: stock_summary
- [x] Relatórios com filtro de período (date_range)
- [x] Bloqueio de não-admin
- [x] Validações (type, format obrigatórios)
- [x] PDFs contêm metadados válidos (%PDF)
- [x] Excel contém dados exportados

### Estatísticas
- **8 arquivos de teste**
- **70+ casos de teste**
- **Cobertura**: Autenticação, Autorização, CRUD, Estoque, Empréstimos, Regras de Negócio, Exports
- **Traits reutilizáveis**: 3 (CreatesUsers, CreatesStock, CreatesLoans)
- **Mock/Seed**: Seeders básicos (Ranks, Organizations, Categories) em `setUp()`

### Execução
```bash
# Rodar todos os testes
docker compose exec app php artisan test

# Rodar suite específica
docker compose exec app php artisan test --testsuite=Feature

# Rodar arquivo específico
docker compose exec app php artisan test tests/Feature/AuthenticationTest.php

# Com cobertura (requer Xdebug)
docker compose exec app php artisan test --coverage
```

### Próximos Passos (Opcional)
- [ ] CI pipeline (GitHub Actions) — `.github/workflows/tests.yml`
- [ ] Code coverage report (Xdebug + PHPUnit)
- [ ] Testes Unit para Models (relacionamentos, scopes, helpers)
- [ ] Paralelização (ParaTest)

---

## Passo 16b — Inventário SISCOFIS (Carga de PDF) ✅

Implementação do módulo de importação de Relação de Material Carga do SISCOFIS em PDF, com extração automática de dados e armazenamento no banco.

### Dependência adicionada
- [x] `smalot/pdfparser` ^2.12 — biblioteca PHP para extração de texto de PDFs

### Banco de Dados

#### Tabela `inventory_uploads` — PDFs carregados
| Coluna         | Tipo                                          |
| -------------- | --------------------------------------------- |
| id             | bigint PK                                     |
| filename       | string — nome original do arquivo             |
| stored_path    | string — caminho em storage/app/inventario    |
| dependency     | string nullable — ex: "11º D Sup"             |
| unit           | string nullable — ex: "1ª CIA SUP"            |
| unit_code      | string nullable — ex: "37"                    |
| uploaded_by    | FK → users                                    |
| status         | enum (pending, processing, completed, error)  |
| total_items    | integer default 0                             |
| total_value    | decimal(15,2) default 0                       |
| error_message  | text nullable                                 |
| processed_at   | timestamp nullable                            |
| timestamps     |                                               |

#### Tabela `inventory_items` — Itens extraídos do PDF
| Coluna              | Tipo                                     |
| ------------------- | ---------------------------------------- |
| id                  | bigint PK                                |
| inventory_upload_id | FK → inventory_uploads (cascade delete)  |
| material_type       | string nullable — "MATERIAL PERMANENTE"  |
| material_name       | string — nome do material                |
| ficha_number        | string nullable — Nr Ficha               |
| material_code       | string nullable — Cód Mat                |
| accounting_account  | string nullable — Conta Contábil         |
| acervo              | string nullable — N ou S                 |
| quantity            | integer default 1                        |
| unit_value          | decimal(15,2) default 0                  |
| total_value         | decimal(15,2) default 0                  |
| patrimony_numbers   | JSON nullable — array de nrs patrimoniais|
| raw_text            | text nullable — texto bruto (debug)      |
| timestamps          |                                          |

### Models (`app/Models/`)
- [x] `InventoryUpload` — belongsTo User (uploader), hasMany InventoryItem, STATUSES const, scopes completed/recent, helpers statusLabel/statusColor/headerDisplay/isCompleted/hasError
- [x] `InventoryItem` — belongsTo InventoryUpload, cast patrimony_numbers JSON, helpers hasPatrimonyNumbers/patrimonyCount/formattedUnitValue/formattedTotalValue

### Service
- [x] `app/Services/InventoryPdfParser.php` — parser de PDF SISCOFIS com:
  - Extração de cabeçalho (dependência, unidade, código)
  - Detecção de seções (MATERIAL PERMANENTE, USO DURADOURO, etc.)
  - Parsing de itens: Nr Ficha, Cód Mat, Conta Contábil, Qtd, Valor Unit, Valor Total, Acervo, Nome
  - Extração de números patrimoniais (separados por vírgula)
  - Conversão de valores decimais BR (1.356,16 → 1356.16)

### Controller (`app/Http/Controllers/InventoryController.php`)
- [x] `index` — listagem com busca, filtro por status, estatísticas globais, paginação
- [x] `create` — formulário de upload com drag-and-drop (Alpine.js)
- [x] `store` — upload do PDF, parsing automático, salvamento transacional no banco
- [x] `show` — detalhes do upload com itens agrupados por tipo, patrimônios expansíveis
- [x] `reprocess` — re-parsear PDF (admin only), substitui itens existentes
- [x] `destroy` — exclui upload + PDF do storage (admin only)
- [x] `download` — download do PDF original

### Views (`resources/views/inventory/`)
- [x] `index.blade.php` — 4 stat cards, filtros, tabela de uploads com status, ações (ver/baixar/excluir)
- [x] `upload.blade.php` — formulário drag-and-drop com instruções, accept PDF, max 20MB
- [x] `show.blade.php` — header gradient, stats, itens agrupados por tipo com patrimônios collapsible, totais

### Rotas adicionadas (7 rotas)
```
GET    /inventory                          → inventory.index
GET    /inventory/upload                   → inventory.create
POST   /inventory                          → inventory.store
GET    /inventory/{inventoryUpload}         → inventory.show
POST   /inventory/{inventoryUpload}/reprocess → inventory.reprocess
DELETE /inventory/{inventoryUpload}         → inventory.destroy
GET    /inventory/{inventoryUpload}/download → inventory.download
```

### Menu de Navegação
- [x] Item "Inventário" adicionado ao menu principal (desktop + mobile + sidebar)
- [x] Visível para todos os usuários autenticados (não restrito a admin)
- [x] Posicionado após "Cautelas" e antes de "Usuários/Relatórios"

### Armazenamento
- [x] PDFs salvos em `storage/app/inventario/` com prefixo timestamp
- [x] Nome do arquivo: `{YYYYMMDD_HHmmss}_{nome_original_sanitizado}.pdf`

### Validação com PDF real
- [x] Parser testado com `1 cia sup.pdf` — 6 itens extraídos corretamente
- [x] Header: 11º D Sup / 1ª COMPANHIA DE SUPRIMENTO - 1ª CIA SUP - 37
- [x] Total: R$ 5.570,04 (confere com total do PDF)
- [x] Patrimônios: 22 números extraídos (14 colchões + 4 beliches + 4 outros)
- [x] Migration executada sem erros
- [x] Views compilam sem erros (`view:cache`)
- [x] Dependência `smalot/pdfparser` instalada via Composer (v2.12.3)
- [x] Extensão PostgreSQL `pg_trgm` habilitada para índices de busca full-text

---

## Passo 16c — Integração Inventário × Uso Duradouro ✅

Extensão do parser de inventário para processar a seção "2. Material de Uso Duradouro" dos PDFs SISCOFIS e sincronizar automaticamente com o cadastro de produtos duráveis.

### Funcionalidades Implementadas

#### Parser de PDF Estendido (`app/Services/InventoryPdfParser.php`)
- [x] Detecção automática de 2 formatos de material:
  - **MATERIAL PERMANENTE**: `NrFicha CodMat ContaContabil Qtd ValorUnit ValorTotal Acervo NomeMaterial`
  - **MATERIAL DE USO DURADOURO**: `NrFicha CodMat ContaContabil ValorUnit NomeMaterial... ValorTotal Qtd`
- [x] **Parsing multi-linha**: produtos cujo nome se estende por 2-3 linhas são corretamente capturados usando sistema de buffer
- [x] Regex dual para parsing de linhas conforme tipo de seção detectado
- [x] Campo `acervo` definido como `null` para materiais de uso duradouro
- [x] Números patrimoniais não são coletados para materiais duráveis (sem campo "Nr Patrimoniais")
- [x] **Filtro de cabeçalho otimizado**: verifica comprimento da linha (< 100 chars) para não capturar indevidamente produtos com "Exército Brasileiro" no nome

#### Correções Aplicadas (19/02/2026)
- [x] **Bug crítico corrigido**: filtro de cabeçalho PDF estava bloqueando produtos com "Exército Brasileiro" no nome do material
- [x] **Solução**: filtro limitado a linhas curtas + verificação de contexto (presença de códigos numéricos)
- [x] **Resultado**: taxa de captura aumentou de 20 para 60 produtos (100% dos itens esperados)
- [x] **Validação**: subtenencia.pdf agora importa 60 produtos de Uso Duradouro com 1.786 unidades totais

#### Serviço de Sincronização (`app/Services/InventoryDurableSyncService`)
- [x] Criado serviço dedicado para sincronização automática
- [x] Filtra itens com `material_type` contendo "USO DURADOURO"
- [x] Para cada item:
  - Busca produto existente por `siscofis_code = material_code`
  - Se não existe: cria produto com `is_durable = true`, vinculado à categoria "Uso Duradouro"
  - Se existe: garante que `is_durable = true`
  - **Criação automática de estoque** (20/02/2026):
    - Verifica se o produto já possui movimentação no estoque (`stock_movements`)
    - Se **não houver movimentação prévia** e `quantity > 0`:
      - Cria registro em `stock_items` com quantidade do inventário
      - Batch: `INV-{upload_id}`
      - Location: dependência do upload ou "Inventário SISCOFIS"
      - Status: `available`
      - Registra em `stock_movements` como entrada tipo "entry"
      - Notes: "Entrada inicial automática do inventário SISCOFIS - Upload #X (arquivo.pdf)"
- [x] Criação automática de categoria "Uso Duradouro" se não existir
- [x] Estatísticas retornadas: `created`, `already_exists`, `stock_created`, `stock_skipped`, `errors`, `total_items`
- [x] Logging detalhado de todas as operações
- [x] Transação DB para garantir atomicidade

#### Controller Atualizado (`app/Http/Controllers/InventoryController`)
- [x] Método `store`: chama `InventoryDurableSyncService->sync()` após parsing bem-sucedido
- [x] Método `reprocess`: chama sincronização também ao reprocessar inventários
- [x] Mensagens de sucesso incluem estatísticas detalhadas:
  - "X produtos duráveis criados"
  - "X produtos duráveis já existentes"
  - "X itens adicionados ao estoque automaticamente" (20/02/2026)

### Estrutura de Dados

#### Formato PDF "Material de Uso Duradouro"
```
Nr Ficha | Cod Mat. | Conta Contábil | Nome material | Quantidade | Valor Unitário | Valor Total
458      | 231810   | 123110301      | VENTILADOR    | 2          | 112,50         | 225,00
```

#### Produto criado automaticamente
- `name`: Nome do material extraído do PDF
- `siscofis_code`: Código do material (Cod Mat.)
- `category_id`: FK para categoria "Uso Duradouro" (auto-criada)
- `is_durable`: `true`
- `shelf_life_months`: `null` (duráveis não perecem)
- `is_serialized`: `false`
- `minimum_stock`: `0`

### Fluxo de Integração

1. **Upload de PDF** → Controller recebe arquivo
2. **Parsing** → `InventoryPdfParser` extrai ambas seções (Permanente + Uso Duradouro)
3. **Salvamento** → Itens salvos em `inventory_items` e `durable_goods_inventory` conforme tipo
4. **Sincronização de Produtos** → `InventoryDurableSyncService` processa itens "USO DURADOURO"
5. **Criação de Produtos** → Produtos criados/atualizados na tabela `products`
6. **Criação Automática de Estoque** (20/02/2026) → Para produtos sem movimentação prévia:
   - Cria `stock_items` com quantidade do inventário
   - Registra `stock_movements` como entrada inicial
   - Permite empréstimo imediato dos materiais
7. **Feedback** → Usuário recebe estatísticas completas:
   - "45 itens importados."
   - "12 produtos duráveis criados."
   - "8 produtos duráveis já existentes."
   - "12 itens adicionados ao estoque automaticamente."

### Benefícios

- ✅ **Automação completa**: produtos duráveis cadastrados automaticamente via PDF
- ✅ **Sincronização bidirecional**: inventário ↔ cadastro de produtos
- ✅ **Rastreabilidade**: código SISCOFIS vincula inventário aos produtos
- ✅ **Categorização automática**: materiais duráveis agrupados em categoria dedicada
- ✅ **Idempotência**: múltiplos uploads do mesmo PDF não duplicam produtos
- ✅ **Estoque imediato** (20/02/2026): materiais sem movimentação prévia são automaticamente adicionados ao estoque controlado, permitindo empréstimos imediatos
- ✅ **Rastreamento completo**: entrada inicial registrada em `stock_movements` com referência ao upload do inventário
- ✅ **Auditoria**: logs completos de criação/atualização de produtos

---
- [x] Disco `inventario` configurado em `config/filesystems.php` e validado
- [x] Autoload regenerado (7545 classes), caches limpos (config, routes, optimize)
- [x] Classe `Smalot\PdfParser\Parser` acessível e validada

---

## Passo 16d — Reconciliação de Quantidades (Sync Duráveis) ✅

Implementação de sincronização manual de quantidades entre inventário SISCOFIS e estoque controlado, com reconciliação automática de discrepâncias.

### Problema Identificado

Após importação de inventários SISCOFIS, os totais não batiam:
- **Inventário SISCOFIS**: 1.786 unidades registradas
- **Estoque Controlado**: 1.482 itens individualizados
- **Diferença**: 304 unidades faltantes

### Causa Raiz

1. **Duplicação de produtos no PDF**: 8 produtos aparecem em 2 linhas separadas no mesmo PDF (ex: JAPONA M com 19 unidades numa seção e 88 em outra)
2. **Lógica anterior**: processava linha a linha, comparando cada linha individual contra estoque acumulado
3. **Resultado**: segunda linha marcada como "surplus" (estoque > inventário), nunca adicionando quantidade correta

### Solução Implementada

#### Serviço de Reconciliação Aprimorado (`app/Services/InventoryDurableSyncService`)
- [x] **Agrupamento por `material_code`**: antes de reconciliar, agrupa itens por código SISCOFIS e soma quantidades
- [x] **Reconciliação inteligente**:
  - Produto não existe → cria produto + estoque inicial
  - Produto existe, estoque = 0 → adiciona estoque faltante
  - Produto existe, estoque < inventário → adiciona diferença
  - Produto existe, estoque > inventário → mantém (pode haver cautelas abertas)
  - Produto existe, estoque = inventário → ignora (já sincronizado)
- [x] **Idempotência**: múltiplas execuções não duplicam ou alteram desnecessariamente
- [x] **Estatísticas detalhadas**:
  - `total_items`: linhas brutas do PDF (60)
  - `total_unique`: produtos únicos após agrupamento (52)
  - `qty_adjusted`: produtos com quantidade ajustada
  - `qty_added`: unidades totais adicionadas
  - `in_sync`: produtos já sincronizados

#### Interface de Usuário
- [x] **Botão "Sync Duráveis"**: adicionado à tela de detalhes do inventário (`inventory/show.blade.php`)
- [x] **Rota dedicada**: `POST /inventory/{inventoryUpload}/sync-durables` → `inventory.sync-durables`
- [x] **Mensagens flash aprimoradas**:
  - `success`: alterações realizadas (produtos criados/ajustados)
  - `info`: tudo já sincronizado
  - `warning`: estoque acima do inventário (requer atenção)
  - `error`: erros durante processamento

#### Controller Atualizado (`app/Http/Controllers/InventoryController`)
- [x] Método `syncDurables()`: executa reconciliação manual
- [x] Método `reprocess()`: atualizado para usar novas estatísticas
- [x] Autorização: apenas administradores podem executar sync

### Resultado Final

Após implementação:
- **Inventário SISCOFIS**: 1.786 unidades
- **Estoque Controlado**: 1.786 itens
- **Diferença**: 0 (totais batem perfeitamente)

### Benefícios

- ✅ **Reconciliação automática**: identifica e corrige discrepâncias de quantidade
- ✅ **Agrupamento inteligente**: lida com produtos duplicados no PDF
- ✅ **Idempotência**: execuções repetidas são seguras
- ✅ **Feedback detalhado**: usuário sabe exatamente o que foi alterado
- ✅ **Auditoria completa**: todas as adições registradas em `stock_movements`
- ✅ **Interface intuitiva**: botão dedicado na tela de inventário

---

## Passo 16c — Comparação de Inventários ✅

Funcionalidade de comparação temporal de inventários e cruzamento com dados de Uso Duradouro do sistema.

### Finalidade
O módulo de inventário é **independente** do restante do sistema. Serve para:
1. **Comparar inventários ao longo do tempo** — detectar entradas, saídas e alterações em quantidades, valores e patrimônios entre dois PDFs carregados
2. **Cruzar com Uso Duradouro** — confrontar "Material de Uso Duradouro" do PDF com os produtos duráveis cadastrados no sistema

### Controller — Novos métodos
- [x] `compareForm` — formulário para selecionar dois inventários concluídos para comparação
- [x] `compareResults` — processa a comparação temporal entre dois uploads:
  - Indexa itens por `material_code`
  - Classifica em: **Entradas** (novos), **Saídas** (removidos), **Alterados** (diferenças em qtd/valor/patrimônios), **Sem alteração**
  - Detecta patrimônios adicionados e removidos em itens alterados
  - Calcula resumo: saldo de quantidade e valor
- [x] `compareDurables` — compara itens "DURADOURO" de um upload com o estoque de produtos duráveis:
  - Cruza por `material_code` ↔ `Product.siscofis_code`
  - Classifica em: **Correspondentes** (com detalhes de disponível/cautelado/danificado), **Só no inventário PDF**, **Só no sistema**

### Views (`resources/views/inventory/`)
- [x] `compare.blade.php` — formulário de seleção de dois inventários com dropdowns e cards de referência
- [x] `compare-results.blade.php` — resultado da comparação temporal:
  - Header com os dois inventários lado a lado (A=anterior, B=recente)
  - 4 stat cards (entradas/saídas/alterados/sem alteração)
  - Barra de resumo com diferença total de quantidade e valor
  - Seção verde: **Entradas** com quantidades e valores positivos
  - Seção vermelha: **Saídas** com itens riscados
  - Seção amarela: **Alterados** com diff de qtd/valor + patrimônios adicionados/removidos (collapsible)
  - Seção colapsável: **Sem alteração**
  - Rodapé com resumo geral da comparação
- [x] `compare-durables.blade.php` — comparação com Uso Duradouro do sistema:
  - Header gradiente roxo com contexto do inventário
  - 6 stat cards (correspondentes/só inventário/só sistema/qtd inv/qtd sistema/valor)
  - Legenda explicativa sobre correspondência por código SISCOFIS
  - Tabela verde: itens correspondentes com qtd inventário × qtd sistema × diferença × disponível/cautelado/danificado
  - Tabela amarela: itens apenas no PDF (sem correspondência — dica para cadastrar código SISCOFIS)
  - Tabela laranja: produtos apenas no sistema (não encontrados no PDF — alerta de possível descarga)

### Rotas adicionadas (3 novas rotas)
```
GET    /inventory/compare                     → inventory.compare
POST   /inventory/compare                     → inventory.compare.results
GET    /inventory/{inventoryUpload}/compare-durables → inventory.compare.durables
```

### Integração na interface
- [x] Botão "Comparar" adicionado ao header da listagem de inventários (`index.blade.php`)
- [x] Botão "Comparar c/ Duradouro" adicionado ao header de detalhes do inventário (`show.blade.php`)

---

## Passo 17 — Backup Automático ⬜

- [ ] Script `backup-db.sh` com `pg_dump` compactado
- [ ] Container sidecar ou cron para backup diário
- [ ] Retenção de 30 dias (auto-cleanup)
- [ ] Volume Docker para armazenamento dos backups
- [ ] Comando `php artisan backup:run` e `backup:restore`

---

## Passo 18 — Roles e Permissões Completos 🔧

- [x] Roles simplificados para `admin`, `manager`, `user` (migration + model + controllers)
- [x] Middleware `role:admin` protege rotas de administração
- [x] Sidebar/nav condicional por `isAdmin()` (Usuários, Relatórios, Reset Estoque)
- [x] Isolamento de dados por subunidade — ver Passo 18a
- [ ] Páginas de acesso negado (403) customizadas
- [ ] Testes de autorização por perfil

---

## Passo 18a — Isolamento de Dados por Subunidade ✅

Implementação de isolamento completo de dados entre subunidades da mesma OM, garantindo que cada usuário acesse apenas os dados da sua subunidade.

### Regras de Negócio
- Admin: enxerga **todos** os dados de todas as subunidades
- Manager/User com subunidade: enxerga **apenas** dados da sua subunidade
- Manager/User **sem** subunidade cadastrada: enxerga todos os dados (modo de transição)
- Máximo de **2 usuários não-admin** por subunidade

### Migration
- [x] `2026_02_24_090922_add_subunit_to_inventory_loans_durable_tables.php`:
  - Adiciona coluna `subunit` (string nullable) a `inventory_uploads`, `loans`, `durable_goods_inventory`
  - `stock_items` já possuía coluna `subunit` (pré-existente)

### Scope Global (`app/Scopes/SubunitScope.php`)
- [x] Global Eloquent scope aplicado automaticamente nas queries
- [x] Lógica: `if admin → bypass` | `if user has subunit → WHERE subunit = user.subunit` | `if no subunit → bypass`
- [x] Usa `$model->getTable() . '.subunit'` para qualificar coluna e evitar ambiguidade em JOINs

### Models Atualizados
- [x] `StockItem` — `booted()` com `static::addGlobalScope(new SubunitScope())`
- [x] `InventoryUpload` — scope + `subunit` adicionado ao `$fillable`
- [x] `DurableGoodsInventory` — scope + `subunit` adicionado ao `$fillable`
- [x] `Loan` — scope + `subunit` adicionado ao `$fillable`

### Controllers Atualizados
- [x] `InventoryController@store` — `InventoryUpload::create()` recebe `subunit => Auth::user()->subunit`
- [x] `InventoryController@store` — `DurableGoodsInventory::create()` (upload direto) recebe `subunit => Auth::user()->subunit`
- [x] `InventoryController@reprocess` — `DurableGoodsInventory::create()` (reprocessamento) recebe `subunit => $inventory->uploader->subunit`
- [x] `StockController@storeEntry` — `StockItem::create()` força `subunit => Auth::user()->subunit` (ignora input do formulário)
- [x] `LoanController@store` — `Loan::create()` recebe `subunit => Auth::user()->subunit`

### AdminController — Validação Max 2 Usuários
- [x] `storeUser()`: valida `User::where('subunit')->where('organization_id')->where('role', '!=', 'admin')->count() < 2` antes de criar
- [x] `updateUser()`: valida limite ao trocar subunidade (exclui o próprio usuário da contagem)
- [x] Mensagem de erro amigável: `"Limite atingido: já existem 2 usuários cadastrados na subunidade..."`

### Benefícios
- ✅ **Transparente**: sem mudança nas queries existentes — scope aplicado automaticamente
- ✅ **Dashboard escopo automático**: queries de StockItem e Loan já filtradas
- ✅ **Segurança por defesa em profundidade**: isolamento no ORM, não apenas na UI
- ✅ **Admin irrestrito**: administrador sempre vê tudo
- ✅ **Transição suave**: usuários sem subunidade não são bloqueados

---

## Passo 19 — Notificações ⬜

- [ ] Alertas por email para cautelas vencidas (Mailpit → SMTP real)
- [ ] Notificação de estoque crítico para admin/almoxarife
- [ ] Resumo diário de pendências (command + mail)
- [ ] Configuração SMTP real para produção

---

## Passo 16d — Separação de Tabelas: Material Permanente vs Uso Duradouro ✅

Refatoração da arquitetura de dados para separar Material Permanente patrimonial do Material de Uso Duradouro operacional em tabelas distintas.

### Motivação

A separação em tabelas distintas reflete a diferença conceitual entre dois tipos de material militar:

1. **Material Permanente** (`inventory_items`): Bens patrimoniais rastreados individualmente por número de patrimônio, com controle de acervo (N/S) e localização específica
2. **Material de Uso Duradouro** (`durable_goods_inventory`): Bens operacionais consumíveis controlados por quantidade total, sem rastreamento individual de patrimônio

Esta arquitetura evita misturar lógicas de controle incompatíveis (patrimonial vs operacional) e permite consultas mais eficientes.

### Nova Tabela

#### `durable_goods_inventory` — Material de Uso Duradouro
| Coluna              | Tipo                                     |
| ------------------- | ---------------------------------------- |
| id                  | bigint PK                                |
| inventory_upload_id | FK → inventory_uploads (cascade delete)  |
| subunit             | string nullable — subunidade do criador  |
| material_name       | string — nome do material                |
| ficha_number        | string nullable — Nr Ficha               |
| material_code       | string nullable — Cód Mat                |
| accounting_account  | string nullable — Conta Contábil         |
| quantity            | integer default 1                        |
| unit_value          | decimal(15,2) default 0                  |
| total_value         | decimal(15,2) default 0                  |
| raw_text            | text nullable — texto bruto (debug)      |
| timestamps          |                                          |

**Diferenças em relação a `inventory_items`:**
- ❌ Sem campo `acervo` (não aplicável a consumíveis)
- ❌ Sem campo `material_type` (toda a tabela é implicitamente "USO DURADOURO")
- ❌ Sem campo `patrimony_numbers` (não há controle individual)

### Model Criado

- [x] `app/Models/DurableGoodsInventory.php`:
  - `fillable`: todos os campos exceto id e timestamps
  - `casts`: `unit_value`, `total_value` como `decimal:2`
  - `belongsTo`: `InventoryUpload`
  - Helpers: `formattedUnitValue()`, `formattedTotalValue()`

### Relacionamento Atualizado

- [x] `app/Models/InventoryUpload.php`:
  - Adicionado `durableGoods()` → `hasMany(DurableGoodsInventory::class)`
  - Mantido `items()` → `hasMany(InventoryItem::class)` para Material Permanente
  - Cascade delete em ambos relacionamentos

### Parser Refatorado

- [x] `app/Services/InventoryPdfParser.php`:
  - Propriedades: `$permanentItems` e `$durableGoods` (arrays separados)
  - Método `addItemToCorrectArray()`: distribui itens conforme `$currentType`
  - Retorno do método `parse()`:
    ```php
    return [
        'header'          => [...],
        'permanent_items' => [...],  // Material Permanente
        'durable_goods'   => [...],  // Material de Uso Duradouro
        'raw'             => $text,
    ];
    ```

### Controller Atualizado

- [x] `app/Http/Controllers/InventoryController.php`:
  - Método `store`:
    - Loop separado para `$result['permanent_items']` → cria `InventoryItem`
    - Loop separado para `$result['durable_goods']` → cria `DurableGoodsInventory`
    - `total_items` e `total_value` somam ambos os tipos
  - Método `reprocess`:
    - Deleta itens de ambas tabelas: `$inventory->items()->delete()` + `$inventory->durableGoods()->delete()`
    - Re-popula ambas tabelas após parsing

### Sincronização de Produtos Atualizada

- [x] `app/Services/InventoryDurableSyncService.php`:
  - Alterado para consultar `DurableGoodsInventory::where('inventory_upload_id', $uploadId)` ao invés de filtrar `InventoryItem` por `material_type`
  - Lógica de criação/atualização de produtos permanece idêntica
  - Estatísticas: `total_items`, `created`, `already_exists`, `errors`

### Migration Executada

- [x] `database/migrations/2026_02_19_205411_create_durable_goods_inventory_table.php`:
  - Criada tabela `durable_goods_inventory` com 9 colunas + timestamps
  - Foreign key `inventory_upload_id` com cascade delete
  - Migration executada com sucesso (35.74ms)

### Validação Completa

- [x] Teste com PDF real `subtenencia.pdf`:
  - ✅ Parser: 46 itens permanentes + 20 itens duráveis
  - ✅ Salvamento: 46 registros em `inventory_items`, 20 em `durable_goods_inventory`
  - ✅ Sincronização: 20 produtos criados/atualizados em `products` (is_durable=true)
  - ✅ Total: 66 itens, R$ 150.863,08 (confere com PDF original)

### Benefícios da Arquitetura

- ✅ **Separação de conceitos**: Patrimonial (inventory_items) vs Operacional (durable_goods_inventory)
- ✅ **Consultas otimizadas**: Sem necessidade de filtrar por `material_type`, tabela já define o tipo
- ✅ **Schema semântico**: Campos existem apenas onde fazem sentido (ex: `acervo` só em permanentes)
- ✅ **Escalabilidade**: Futuras funcionalidades específicas de cada tipo não poluem a outra tabela
- ✅ **Auditoria clara**: Logs e relatórios podem distinguir facilmente os dois tipos de controle

---

## Passo 19 — Uso Duradouro: Carregamento por Lote sem Divergência ✅

### Problema Identificado

Ao sincronizar o inventário SISCOFIS com o estoque controlado, havia divergência sistemática de unidades. Exemplo real: SISCOFIS = 1.786 unidades × Estoque = 1.662 unidades (gap de 124 un.).

**Causa raiz:** Algumas fichas do SISCOFIS possuem sub-itens (mesmo material em tamanhos/cores distintos), gerando múltiplas linhas com o **mesmo `ficha_number`** em um único PDF. O código anterior processava cada linha individualmente — a segunda linha do mesmo produto gerava apenas um diff de reconciliação, sobrescrevendo a quantidade em vez de somá-la.

### Solução: Agregação por `ficha_number` antes de processar

`app/Services/InventoryDurableSyncService.php` foi reescrito para **agregar por `ficha_number` com `sum(quantity)`** antes de criar/atualizar `StockItems`:

```php
// 1. Busca todos os registros do upload
$inventoryItems = DurableGoodsInventory::where('inventory_upload_id', $uploadId)->get();

// 2. Agrupa por ficha_number (ou gera chave fallback) e SOMA as quantidades
$aggregated = $inventoryItems
    ->groupBy(function ($item) use ($uploadId, &$lotCounter) {
        return $item->ficha_number
            ? 'FICHA-' . $item->ficha_number
            : 'INV-' . $uploadId . '-' . (++$lotCounter);
    })
    ->map(function ($rows, $batchKey) {
        return (object) [
            'batch_key'  => $batchKey,
            'quantity'   => $rows->sum('quantity'),   // ← chave do fix
            'unit_value' => $rows->max('unit_value'),
            'row_count'  => $rows->count(),
            // ... demais campos do primeiro registro do grupo
        ];
    });

// 3. Itera sobre os lotes agregados (não sobre as linhas brutas)
foreach ($aggregated as $lot) {
    // upsert de Product + StockItem usando $lot->batch_key como $item->batch
}
```

**Formato do batch key:** `FICHA-{ficha_number}` para fichas identificadas, `INV-{uploadId}-{n}` como fallback.

### Preço por Lote (`unit_cost`)

- Migração `2026_03_03_000001_add_unit_cost_to_stock_items_table.php` adicionou `unit_cost decimal(15,2) nullable` à tabela `stock_items`.
- `StockItem::$fillable` e `$casts` atualizados; accessor `getFormattedUnitCostAttribute()` retorna `'R$ x.xxx,xx'` ou `'—'`.
- Durante a sincronização, `unit_value` do registro SISCOFIS é gravado em `stock_items.unit_cost`.
- A coluna **Preço Unit.** é exibida na listagem `/stock`.

### Reset de Estoque para Admin Global

**Problema:** O usuário admin tem `subunit = NULL`. A query `WHERE subunit = NULL` (SQL) não bate nenhum registro — o reset não excluía nada.

**Fix em `AdminController::resetStockExecute()`:**

```php
$subunit     = Auth::user()->subunit;
$globalReset = blank($subunit); // true quando subunit é NULL ou ''

$bySubunit = function (Builder $query, string $column = 'subunit') use ($subunit, $globalReset) {
    return $globalReset ? $query : $query->where($column, $subunit);
};
// Todas as 5 etapas de deleção usam $bySubunit() em vez de ->where('subunit', $subunit)
```

Banner de aviso laranja adicionado em `resources/views/admin/reset-stock.blade.php` quando `$globalReset` é verdadeiro.

### View `/stock` — Melhorias de UX

- **Alinhamento de colunas:** substituído `<template x-if>` + `colspan` + CSS grid por `<td x-show>` individuais por coluna — o browser agora alinha os `<td>` com os `<th>` nativamente.
- **Coluna Preço Unit.:** exibida à direita em verde-esmeralda quando disponível.
- **Preço unitário na própria linha:** além da coluna dedicada, cada linha de lote exibe `Preço Unit.` em badge no bloco de detalhes para evitar ambiguidade visual.
- **Fallback de preço para estoque legado:** quando `stock_items.unit_cost` está nulo em lotes antigos importados do inventário (`INV-*`), a exibição resolve o valor a partir de `durable_goods_inventory` usando `upload_id + material_code`.
- **Backfill persistente de preço:** comando `stock:backfill-unit-cost` preenche `unit_cost` nulo em registros antigos com base no inventário SISCOFIS, eliminando dependência operacional do fallback para os lotes legados.
- **Edição manual de preço unitário:** o modo inline de edição em `/stock` passou a permitir alterar `unit_cost` junto com lote, data SISCOFIS, localização e observações.
- **Edição manual de quantidade:** o modo inline de edição em `/stock` também permite alterar `quantity`, registrando automaticamente a diferença em `stock_movements` como ajuste.
- **Entrada manual com preço unitário:** o formulário `/stock/entry` agora aceita preenchimento de `Preço Unit.` e persiste o valor diretamente em `stock_items.unit_cost` na criação do item.
- **Coluna Localização:** removida para dar mais espaço ao produto.
- **Nome resumido do produto:** lógica PHP extrai `base / Cor / Tamanho` do nome completo:
  - `COTURNO DE COMBATE / Cor: Preto; Tamanho: 38` → **COTURNO DE COMBATE Preto 38**
  - `CONJUNTO CAMUFLADO / Tamanho: M` → **CONJUNTO CAMUFLADO M**
  - Nome completo permanece no atributo `title` (tooltip no hover).
- **Listagem por lote:** `/stock` passou a agrupar registros por produto, lote, série, data de entrada SISCOFIS, validade, preço unitário e status, separando corretamente lotes distintos do mesmo tamanho/modelo.
- **Contador de lotes por tamanho/modelo:** a tabela informa quantos lotes existem para cada variação reduzida do produto, facilitando conferência visual no estoque.
- **Status abreviado na grade:** a coluna Status agora usa siglas operacionais (`D`, `E`, `A`, `X`) com tooltip do rótulo completo para reduzir ruído visual.
- **Linhas distintas por preço/data:** o mesmo produto aparece em linhas separadas quando houver diferença de preço unitário, data de entrada SISCOFIS ou validade, com ordenação priorizando essas variações.

### View `/durables` — Melhorias

- **Painel de resumo 3 colunas:** Inventário SISCOFIS × Estoque Controlado × Divergência (âmbar quando > 0).
- **Badge por produto:** mostra `⚖ SISCOFIS +N vs estoque` quando divergente, `✓ Em sincronia` quando igual.
- **Filtro "Só divergentes"** e **ordenação por divergência**.
- **Tabela de lotes SISCOFIS** com colunas Entrada SISCOFIS e Validade (extraídas do `StockItem` via `batch = 'FICHA-{ficha_number}'`).
- **Cores:** alteradas de índigo para esmeralda no card SISCOFIS.
- **Colapsível duplicado removido** (o segundo "Ver N lotes do SISCOFIS" foi eliminado).

---

## Passo 20 — Importação SISCOFIS ⬜

- [ ] Upload de planilha Excel/CSV ou arquivo .pdf para carga em massa do estoque
- [ ] Tela de mapeamento de colunas
- [ ] Validação prévia com relatório de erros
- [ ] Importação com transação (tudo ou nada)
- [ ] Log de importação

---

## Passo 21 — Auditoria Completa ⬜

- [ ] Log de todas as ações do usuário (login, CRUD, exports) com IP e timestamp
- [ ] Tabela `audit_logs` com user_id, action, model, changes, ip, user_agent
- [ ] Tela de consulta de logs para admin/auditor
- [ ] Trait `Auditable` para models
- [ ] Export de logs em Excel

---

## Passo 22 — PWA / Modo Offline ⬜

- [ ] Service worker para cache de assets e páginas estáticas
- [ ] Manifest completo com ícones
- [ ] Fallback offline para consulta de estoque
- [ ] Sync quando recuperar conexão

---

## Passo 23 — Tabela IRDU (Duração Regulamentar de Material) ✅

Importação da tabela de duração regulamentar de materiais conforme IRDU (Instruções Reguladoras para Distribuição de Uniformes), com atualização automática da validade (`shelf_life_months`) dos produtos cadastrados.

### Tabela `irdu_items` — Itens IRDU
| Coluna           | Tipo                                                      |
| ---------------- | --------------------------------------------------------- |
| id               | bigint PK                                                 |
| annex            | char(1) — Anexo IRDU (A, B, C, D, E)                     |
| annex_title      | string — Título do anexo                                  |
| item_number      | integer — Nº do item no anexo                             |
| material_name    | string — Nome do material                                 |
| duration_text    | string — Duração original em texto (ex: "2 Anos")        |
| duration_months  | integer nullable — Duração em meses (null = indeterminado)|
| dotacoes         | json — Detalhamento de todas as dotações                  |
| timestamps       |                                                           |

**Índices:** unique(annex, item_number), index(material_name), index(duration_months)

### Migration
- [x] `2026_03_31_100000_create_irdu_items_table` — tabela com índices

### Model (`app/Models/IrduItem.php`)
- [x] Fillable: annex, annex_title, item_number, material_name, duration_text, duration_months, dotacoes
- [x] Casts: item_number→integer, duration_months→integer, dotacoes→array
- [x] Scopes: `byAnnex($annex)`, `withDefinedDuration()`, `indeterminate()`
- [x] Helpers: `isIndeterminate()`, `getDurationDisplay()`, `parseDurationToMonths(string)`

### Seeder (`database/seeders/IrduSeeder.php`)
- [x] Lê `IRDU.json` da raiz do projeto (5 anexos: A-E)
- [x] Importa 136 itens IRDU com todas as dotações em JSON
- [x] Para cada item, calcula `duration_months` como a duração máxima entre todas as dotações
- [x] Durações "Indeterminado" → `null`
- [x] Usa `updateOrCreate` (idempotente — pode rodar múltiplas vezes)
- [x] Atualiza `shelf_life_months` dos produtos existentes via matching por nome
- [x] **Matching por keyword-scoring com anchor**:
  - Extrai nome base (antes de "/") + cor + tipo como atributos
  - Anchor: primeira palavra do produto deve corresponder à primeira do IRDU
  - Score = sobreposição de palavras-chave (stem 4 chars, tolera gênero preto/preta)
  - Tiebreak: IRDU match ratio (prefere itens mais específicos)
  - Aliases: NYLON↔NÁILON, INSÍGNA↔INSÍGNIA
  - Threshold mínimo: 2 palavras coincidentes
- [x] Registrado no `DatabaseSeeder` para rodar automaticamente

### Dados importados
- [x] **Anexo A**: 110 itens — Uniformes de Uso Comum (Cabos/Soldados)
- [x] **Anexo B**: 1 item — Roupas de Cama e Banho
- [x] **Anexo C**: 8 itens — Uniformes de OM de Guarda (GD)
- [x] **Anexo D**: 11 itens — Uniformes de OM de Polícia do Exército (PE)
- [x] **Anexo E**: 6 itens — Uniformes de Motociclistas Militares
- [x] **111 itens com duração definida** (12 a 60 meses)
- [x] **25 itens com duração indeterminada** (material permanente)
- [x] **108 produtos existentes** atualizados com validade IRDU
- [x] **10 produtos sem correspondência IRDU** (bússola, roupas de cama, bandeira personalizada, etc.)

### Validação
- [x] Migration executada sem erros
- [x] Seeder importa 136 itens e atualiza 108 produtos
- [x] Model helpers testados (getDurationDisplay, parseDurationToMonths, scopes)
- [x] Matching correto verificado: Fivela→36m, Boina→24m, Japona→60m, Tênis→12m, etc.
- [x] Sem falsos positivos (TOALHA DE ROSTO, COLCHA, COBERTOR corretamente sem match)

---

*Última atualização: 31/03/2026 — Matching IRDU aprimorado com anchor + keyword-scoring. 108 produtos atualizados (vs 20 anterior). Falsos positivos eliminados.*

---

## Passo 24 — Auto-cálculo de Validade nos Itens de Estoque ✅

Cálculo automático de `expiration_date` nos itens de estoque (`stock_items`) com base na duração regulamentar IRDU (`shelf_life_months` do produto).

### Problema
- 122 de 124 itens de estoque exibiam "N/A" na coluna Validade
- O campo `expiration_date` não era preenchido automaticamente na importação/entrada
- O model tinha auto-cálculo mas exigia `siscofis_entry_date` (muitos itens sem essa data)

### Correção no Model (`app/Models/StockItem.php`)
- [x] Auto-cálculo no evento `saving` usa fallback: `siscofis_entry_date` → `created_at` → `now()`
- [x] Só calcula se o produto tem `shelf_life_months` definido
- [x] Dispara automaticamente em novas entradas de material

### Backfill de Itens Existentes
- [x] 110 itens atualizados com `expiration_date` calculada
- [x] **46** via `siscofis_entry_date + shelf_life_months`
- [x] **64** via `created_at + shelf_life_months` (sem data de entrada)
- [x] **12** sem alteração (produtos sem durabilidade IRDU: fronha, lençol, colcha, bandeira personalizada, bússola, etc.)

### Views Atualizadas
- [x] `stock/index.blade.php` — "N/A" substituído por "Indeterminada" (itálico)
- [x] `stock/show.blade.php` — "N/A" substituído por "Indeterminada" (itálico)

### Resultado Final
| Métrica | Antes | Depois |
|---|---|---|
| Itens com validade | 2 | **112** |
| Itens "N/A" | 122 | **0** |
| Itens "Indeterminada" | — | **12** (correto — sem IRDU) |

---

## Passo 25 — Cautela: Campos de Contato e Assinante ✅

Adição de campos detalhados de identificação no formulário de cautela para gravar dados do cautelado e assinante diretamente na cautela, independente do cadastro no sistema.

### Migrations
- [x] `2026_03_27_120000_add_cautela_contact_fields_to_loans_table` — campos: `borrower_identity_number`, `borrower_rank`, `borrower_war_name`, `signer_name`, `signer_rank`, `signer_war_name`, `signer_identity_number`, `signer_cpf`, `signer_phone`
- [x] `2026_03_27_123000_add_borrower_name_to_loans_table` — campo `borrower_name` (nome completo do cautelado)

### Model (`app/Models/Loan.php`)
- [x] Campos adicionados ao `$fillable`
- [x] Accessors para exibição: `getBorrowerDisplayName()`, `getSignerDisplayName()`
- [x] Preenchimento automático a partir do usuário selecionado (full_name, identity_number, rank, war_name)

### Controller (`app/Http/Controllers/LoanController.php`)
- [x] Validação dos novos campos no `store()`
- [x] Auto-preenchimento a partir do `borrower_user_id` quando campos em branco
- [x] Busca de cautelado ampliada: busca por `full_name` além de `identity_number` e `war_name`
- [x] `searchBorrower()` retorna `full_name`, `rank`, `war_name` para preenchimento automático no frontend
- [x] Passa `rankOptions` (postos/graduações) para a view `create`

### Views
- [x] `loans/create.blade.php` — campos de cautelado (nome, identidade, posto/grad, nome de guerra) e assinante (nome, posto/grad, nome de guerra, identidade, CPF, telefone) com preenchimento automático via busca
- [x] `loans/show.blade.php` — exibição dos dados completos do cautelado e assinante
- [x] `loans/pdf/cautela.blade.php` — PDF com dados do cautelado e assinante gravados na cautela

### Testes
- [x] `tests/Feature/LoanTest.php` — testes atualizados com novos campos

---

## Passo 26 — Estoque: Agrupamento por Lote e Custo Unitário ✅

Melhorias na listagem e entrada de estoque: agrupamento inteligente por produto/lote, campo custo unitário, e edição rápida com registro de movimentação.

### Controller (`app/Http/Controllers/StockController.php`)
- [x] `index()` reescrito com JOIN + GROUP BY: agrupa itens por produto, lote, nº série, data entrada, validade, custo e status
- [x] Exibe `SUM(quantity)` e `COUNT(*)` por grupo + `product_lot_count` (total lotes do produto)
- [x] Ordenação: nome do produto → custo desc → data entrada desc → validade desc → lote
- [x] Busca otimizada via JOIN (sem subquery `whereHas`)
- [x] `storeEntry()` aceita `unit_cost` (custo unitário)
- [x] `updateItem()` aceita `quantity` e `unit_cost`, registra `StockMovement` automático em caso de alteração de quantidade

### Views
- [x] `stock/entry.blade.php` — campo "Custo Unitário (R$)" adicionado ao formulário de entrada
- [x] `stock/index.blade.php` — listagem agrupada com colunas atualizadas

### Command
- [x] `app/Console/Commands/BackfillStockUnitCost.php` — comando artisan para backfill de custo unitário em itens existentes

---

## Passo 27 — Produtos: Remoção do Campo Serializado ✅

Remoção do campo "Serializado" das views de produtos, substituído por campos mais relevantes.

### Views Atualizadas
- [x] `products/show.blade.php` — removido "Serializado", adicionados "Estoque Mínimo" e "Subunidade"
- [x] `products/edit.blade.php` — removido checkbox "Produto serializado"
- [x] `products/create.blade.php` — removido checkbox "Produto serializado (nº série individual)"
