<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Indica se o banco padrão deve ser seedado.
     */
    protected bool $seed = false;

    /**
     * Cria a aplicação forçando variáveis de ambiente de teste.
     * Docker env_file injeta variáveis como vars de processo (em $_ENV e $_SERVER),
     * que Laravel lê com prioridade sobre getenv(). Precisamos sobrepor nos 3 níveis.
     */
    public function createApplication()
    {
        $testEnv = [
            'APP_ENV' => 'testing',
            'DB_CONNECTION' => 'pgsql_testing',
            'DB_DATABASE' => 'helpsub_test',
            'SESSION_DRIVER' => 'array',
            'CACHE_STORE' => 'array',
            'QUEUE_CONNECTION' => 'sync',
            'MAIL_MAILER' => 'array',
        ];

        foreach ($testEnv as $key => $value) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        return parent::createApplication();
    }

    /**
     * Setup inicial para todos os testes.
     * Quando $seed = true nas classes filhas, RefreshDatabase
     * já executa DatabaseSeeder via migrate:fresh --seed.
     * NÃO chamar seeders manualmente para evitar duplicação.
     */
    protected function setUp(): void
    {
        parent::setUp();
    }
}
