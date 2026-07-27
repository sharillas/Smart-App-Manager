# 🎪 Smartchoice Event Manager v1.0

Sistema de Gestão de Aluguer de Equipamentos AV para Eventos

## Stack
- Laravel 11 + PHP 8.2
- Filament 3.x
- SQLite
- Nginx

## Funcionalidades
- Dashboard com KPIs, Agenda Semanal e Calendário Mensal
- Gestão de Equipamentos (Departamento → Família → SubFamília)
- Orçamentos com editor avançado
- Stock automático, QR Codes, PWA
- 5 níveis de permissões

## Instalação
git clone https://github.com/Sharillas/smartchoice-event-manager.git
cd smartchoice-event-manager
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan make:filament-user

## Desenvolvido por
Nelson Teixeira — Smartchoice©2026

---

## 🔗 Ecossistema Smartchoice

| App | URL | Descrição |
|-----|-----|-----------|
| **Smart App Manager** | [smartvideo.tech](https://smartvideo.tech) | Gestão de equipamentos AV, orçamentos, logística |
| **LED Calculator** | [led.smartvideo.tech](https://led.smartvideo.tech) | Calculadora de painéis LED para eventos |

> Ambas as apps partilham o mesmo ecossistema e estão ligadas via menu de navegação.
