# HelpSub — Testes Automatizados

## 📋 Visão Geral

Suite completa de testes automatizados para o sistema HelpSub, cobrindo autenticacao, CRUD, regras de negocio, emprestimos e geracao de documentos.

## 🏗️ Estrutura

```
tests/
├── TestCase.php                    # Base class com RefreshDatabase e seeds
├── Traits/                         # Helpers reutilizáveis
│   ├── CreatesUsers.php           # Criar usuários (admin, almoxarife, solicitante)
│   ├── CreatesStock.php           # Criar categorias, produtos, estoque
│   └── CreatesLoans.php           # Criar empréstimos e devoluções
└── Feature/                        # Testes de integração
    ├── AuthenticationTest.php     # Login, logout, middleware
    ├── AuthorizationTest.php      # Controle de acesso por role
    ├── ProductTest.php            # CRUD de produtos
    ├── CategoryTest.php           # CRUD de categorias
    ├── StockTest.php              # Entrada/ajuste de estoque
    ├── LoanTest.php               # Empréstimos e devoluções
    ├── BusinessRulesTest.php      # Regras de negócio críticas
    └── ReportExportTest.php       # Geração de PDF/Excel
```

## 🚀 Executando os Testes

### Rodar TODOS os testes
```bash
docker compose exec app php artisan test
```

### Rodar apenas testes Feature
```bash
docker compose exec app php artisan test --testsuite=Feature
```

### Rodar arquivo específico
```bash
docker compose exec app php artisan test tests/Feature/AuthenticationTest.php
```

### Com saída detalhada
```bash
docker compose exec app php artisan test --verbose
```

### Parar no primeiro erro
```bash
docker compose exec app php artisan test --stop-on-failure
```

## 📊 Cobertura

| Módulo | Arquivo | Testes | Status |
|--------|---------|--------|--------|
| **Autenticação** | AuthenticationTest | 13 | ✅ |
| **Autorização** | AuthorizationTest | 8 | ✅ |
| **Produtos** | ProductTest | 14 | ✅ |
| **Categorias** | CategoryTest | 7 | ✅ |
| **Estoque** | StockTest | 13 | ✅ |
| **Empréstimos** | LoanTest | 15 | ✅ |
| **Regras de Negócio** | BusinessRulesTest | 17 | ✅ |
| **Relatórios/Exports** | ReportExportTest | 13 | ✅ |
| **TOTAL** | — | **100+** | ✅ |

## 🧪 Banco de Dados de Teste

Os testes usam PostgreSQL (`helpsub_test`) configurado em `phpunit.xml`:

- **Conexão**: `pgsql_testing` (config/database.php)
- **Trait**: `RefreshDatabase` — banco é recriado a cada teste
- **Seeds**: Ranks, Organizations, Categories carregados automaticamente

## 🛠️ Traits de Teste

### CreatesUsers
```php
$admin = $this->createAdmin();
$almoxarife = $this->createAlmoxarife();
$solicitante = $this->createSolicitante();
```

### CreatesStock
```php
$category = $this->createCategory();
$product = $this->createProduct(['name' => 'Japona']);
$stockItem = $this->createStockItem(['quantity' => 100]);
```

### CreatesLoans
```php
$loan = $this->createIndividualLoan($borrower, [
    ['stock_item' => $stockItem, 'quantity' => 5],
]);
```

## 📝 Escrevendo Novos Testes

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Traits\CreatesUsers;

class MyNewTest extends TestCase
{
    use CreatesUsers;

    protected bool $seed = true; // Carrega seeds básicos

    /** @test */
    public function example_test(): void
    {
        $user = $this->createAdmin();

        $response = $this->actingAs($user)->get('/rota');

        $response->assertStatus(200);
        $this->assertDatabaseHas('table', ['campo' => 'valor']);
    }
}
```

## 🔍 Comandos Úteis

```bash
# Listar todos os testes sem executar
docker compose exec app php artisan test --list-tests

# Executar testes em paralelo (requer paratest)
docker compose exec app php artisan test --parallel

# Gerar relatório de cobertura (requer Xdebug)
docker compose exec app php artisan test --coverage

# Executar apenas testes marcados com @group nome
docker compose exec app php artisan test --group=authentication
```

## 🚨 Troubleshooting

### Erro "Database does not exist"
Certifique-se de que o banco `helpsub_test` existe:
```bash
docker compose exec database psql -U helpsub_user -c "CREATE DATABASE helpsub_test;"
```

### Erro "Connection refused"
Verifique se os containers estão rodando:
```bash
docker compose ps
docker compose up -d
```

### Erro "Class not found"
Re-gerar autoload:
```bash
docker compose exec app composer dump-autoload
```

## 📚 Referências

- [Laravel Testing](https://laravel.com/docs/11.x/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Database Testing](https://laravel.com/docs/11.x/database-testing)
- [HTTP Tests](https://laravel.com/docs/11.x/http-tests)

---

**Última atualização**: 14/02/2026  
**Mantido por**: Equipe HelpSub
