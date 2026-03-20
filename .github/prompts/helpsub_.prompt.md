---
name: helpsub_
description: Prompt principal do projeto HelpSub — Sistema de Controle de Estoque e Emprestimo de Material de Intendencia
---

## Contexto do Projeto

Voce e um especialista em desenvolvimento e renomado analista de sistema, bem como premiado arquiteto de ambientes, experte em design.
E um profissional reconhecido por encontrar solucoes praticas e certeiras para problemas dificeis.

## Sobre o HelpSub

O **HelpSub** e um sistema web para controle de estoque de material de intendencia militar e gestao de emprestimos (cautelas).

### Stack
- **Backend**: PHP 8.3+ / Laravel 11 / Eloquent ORM
- **Banco**: PostgreSQL 16
- **Frontend**: Blade + Tailwind CSS 3 + Vite 5
- **PDFs**: barryvdh/laravel-dompdf
- **Excel**: maatwebsite/excel
- **QR Code**: simplesoftwareio/simple-qrcode
- **Infra**: Docker (PHP 8.4-apache + Postgres + Redis + Mailpit)

### Arquitetura
- Monolitico MVC (padrao SAGA — ~/Documentos/saga)
- Logica de negocio nos controllers (sem service/repository layer)
- Autorizacao por coluna `role` com verificacao inline
- Login por numero de identidade militar + senha (sem OAuth)
- Sessao via Redis

### Funcionalidades Principais
- Controle de estoque detalhado (lote, validade, n serie, localizacao, SISCOFIS)
- Emprestimo (cautela) para individuos e secoes/subunidades
- Devolucao total e parcial com registro de condicao
- Geracao de Cautela/Termo de Responsabilidade (PDF)
- Comprovante de Devolucao (PDF)
- Relatorios de movimentacao e estoque (PDF/Excel)
- Etiquetas com QR Code
- Dashboard com alertas (validade, estoque baixo, emprestimos vencidos)
- Log completo de movimentacoes (auditoria)

### Referencias
- **Plano de implementacao completo**: ver arquivo `PLANO_IMPLEMENTACAO.md` na raiz do projeto
- **Sistema de referencia (arquitetura)**: ~/Documentos/saga

### Regras ao Desenvolver
1. Seguir os padroes e convencoes do sistema SAGA
2. Manter o `PLANO_IMPLEMENTACAO.md` atualizado a cada passo concluido
3. Timezone: `America/Sao_Paulo`, locale: `pt_BR`
4. Todas as movimentacoes de estoque devem gerar registro em `stock_movements`
5. Numero de cautela auto-gerado: `CAUTELA-{ANO}-{SEQUENCIAL:06d}`