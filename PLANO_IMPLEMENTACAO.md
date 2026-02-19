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
- [x] Criar `.env.example` com variáveis: DB, Redis, APP_NAME=SUBRAVO
- [x] Instalar dependências Composer:
  - `barryvdh/laravel-dompdf` ^2.0 (v2.2.0)
  - `maatwebsite/excel` ^3.1 (v3.1.67)
  - `simplesoftwareio/simple-qrcode` ^4.2 (v4.2.0)
  - `smalot/pdfparser` ^2.12 (v2.12.3)
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
  - Badge SUBRAVO em destaque com gradiente dourado
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
- [x] Busca por Nome do Material (input text com ILIKE)
- [x] Busca por Código do Material (input text com ILIKE específico em `material_code`)
- [x] Busca por Número de Ficha (input text com ILIKE específico em `ficha_number`)
- [x] Todos os filtros aplicados com queries PostgreSQL case-insensitive (ILIKE)
- [x] Combinação de múltiplos filtros simultaneamente
- [x] Validação no backend via `InventoryController@generateReport`

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
- [x] `phpunit.xml` configurado para PostgreSQL de teste (`subravo_test`)
- [x] `config/database.php` com conexão `pgsql_testing`
- [x] `TestCase` base com trait `RefreshDatabase` e seed automático
- [x] `.env.example` atualizado com `DB_TEST_DATABASE`

### Traits de Teste (helpers)
- [x] `CreatesUsers` trait — helpers para criar usuários (admin, almoxarife, solicitante, auditor)
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
- [x] Disco `inventario` configurado em `config/filesystems.php` e validado
- [x] Autoload regenerado (7545 classes), caches limpos (config, routes, optimize)
- [x] Classe `Smalot\PdfParser\Parser` acessível e validada

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

## Passo 18 — Roles e Permissões Completos ⬜

- [ ] Temos perfis: `admin` (gerencia usuarios), `user` (menos gerenciar usuarios),
- [ ] Middleware `role:` expandido com permissões granulares
- [ ] Sidebar/nav condicional por perfil completo
- [ ] Páginas de acesso negado (403) customizadas
- [ ] Testes de autorização por perfil

---

## Passo 19 — Notificações ⬜

- [ ] Alertas por email para cautelas vencidas (Mailpit → SMTP real)
- [ ] Notificação de estoque crítico para admin/almoxarife
- [ ] Resumo diário de pendências (command + mail)
- [ ] Configuração SMTP real para produção

---

## Passo 20 — Importação SISCOFIS ⬜

- [ ] Upload de planilha Excel/CSV para carga em massa do estoque
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

*Última atualização: 16/02/2026 — Passo 9 (Design Profissional de Relatórios) completamente concluído ✅ — Todos os 9 templates PDF (6 admin + 3 inventário) convertidos para usar o layout institucional com cabeçalho militar triplo, margens adequadas e design profissional. 4 exports Excel aprimorados com formatação emerald e condicional*
