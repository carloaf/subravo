# HelpSub

Sistema de Controle de Estoque e Empréstimo de Material de Intendência

## Sobre

O **HelpSub** é um sistema web monolítico MVC para controle de estoque de material de intendência militar e gestão de empréstimos (cautelas). Desenvolvido com Laravel 11, PostgreSQL e Docker.

### Funcionalidades Principais

- ✅ Controle detalhado de estoque (lote, validade, nº série, localização, SISCOFIS)
- ✅ Empréstimo (cautela) para indivíduos e seções/subunidades
- ✅ Devolução total e parcial com registro de condição
- ✅ Geração de Cautelas/Termos de Responsabilidade (PDF)
- ✅ Comprovante de Devolução (PDF)
- ✅ Relatórios de movimentação e estoque (PDF/Excel)
- ✅ Etiquetas com QR Code
- ✅ Dashboard com alertas (validade, estoque baixo, empréstimos vencidos)
- ✅ Log completo de auditoria

## Stack Tecnológica

- **Backend**: PHP 8.4 / Laravel 11 / Eloquent ORM
- **Banco**: PostgreSQL 16
- **Frontend**: Blade + Tailwind CSS 3 + Vite 5
- **Infra**: Docker (PHP 8.4-apache + Postgres + Redis + Mailpit)

## Instalação

1. Clone o repositório
2. Copie `.env.example` para `.env`
3. Configure as variáveis de ambiente, mantendo `APP_URL` com a mesma porta definida em `APP_PORT`
4. Execute `docker compose up -d --build`
5. Acesse `http://localhost:8095`

Observação: neste ambiente o container web usa a porta interna `8081`, publicada externamente em `8095`, para evitar bloqueios locais no acesso à porta `80` da bridge Docker.

### Migração automática de ambiente legado

Se o volume PostgreSQL ainda estiver com a base antiga `smartsub`, o container `app` tenta criar automaticamente o role/banco novos (`helpsub`) e aplicar as permissões mínimas para a transição. Os valores de fallback são controlados por `LEGACY_DB_DATABASE`, `LEGACY_DB_USERNAME` e `LEGACY_DB_PASSWORD` no `.env`.

## Desenvolvimento

- Arquitetura baseada no padrão SAGA
- Lógica de negócio nos controllers
- Autorização por coluna `role`
- Login por número de identidade militar + senha

## Licença

Este projeto é propriedade do Exército Brasileiro.

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
