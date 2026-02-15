<?php

namespace Tests\Traits;

use App\Models\User;
use App\Models\Rank;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;

trait CreatesUsers
{
    /**
     * Criar usuário admin para testes.
     */
    protected function createAdmin(array $attributes = []): User
    {
        return $this->createUser(array_merge([
            'role' => 'admin',
            'war_name' => 'ADMIN',
        ], $attributes));
    }

    /**
     * Criar usuário almoxarife para testes.
     */
    protected function createAlmoxarife(array $attributes = []): User
    {
        return $this->createUser(array_merge([
            'role' => 'almoxarife',
            'war_name' => 'ALMOXARIFE',
        ], $attributes));
    }

    /**
     * Criar usuário solicitante para testes.
     */
    protected function createSolicitante(array $attributes = []): User
    {
        return $this->createUser(array_merge([
            'role' => 'solicitante',
            'war_name' => 'SOLICITANTE',
        ], $attributes));
    }

    /**
     * Criar usuário auditor para testes.
     */
    protected function createAuditor(array $attributes = []): User
    {
        return $this->createUser(array_merge([
            'role' => 'auditor',
            'war_name' => 'AUDITOR',
        ], $attributes));
    }

    /**
     * Criar usuário genérico para testes.
     */
    protected function createUser(array $attributes = []): User
    {
        $rank = Rank::first() ?? Rank::factory()->create();
        $organization = Organization::first() ?? Organization::factory()->create();

        return User::create(array_merge([
            'identity_number' => $this->generateIdentityNumber(),
            'password' => Hash::make('password'),
            'full_name' => fake()->name(),
            'war_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'rank_id' => $rank->id,
            'organization_id' => $organization->id,
            'armed_force' => 'EB',
            'gender' => 'M',
            'role' => 'solicitante',
            'is_active' => true,
        ], $attributes));
    }

    /**
     * Gerar número de identidade único.
     */
    private function generateIdentityNumber(): string
    {
        do {
            $number = str_pad((string) rand(1, 999999999), 9, '0', STR_PAD_LEFT);
        } while (User::where('identity_number', $number)->exists());

        return $number;
    }
}
