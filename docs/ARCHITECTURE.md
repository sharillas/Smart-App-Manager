# 🏗️ Arquitetura do Ecossistema Smartchoice

## Visão Geral

┌─────────────────────────────────────────────────────────┐
│ SMARTCHOICE ECOSYSTEM                                   │
├─────────────────────────┬───────────────────────────────┤
│ Smart App Manager       │ LED Calculator                │
│ (smartvideo.tech)       │ (led.smartvideo.tech)         │
├─────────────────────────┼───────────────────────────────┤
│ Laravel 11 + Filament   │ App independente              │
│ SQLite                  │                               │
│ Gestão AV Completa      │ Cálculo de painéis LED        │
│ - Equipamentos          │ - Dimensões                   │
│ - Orçamentos            │ - Resoluções                  │
│ - Colaboradores         │ - Quantidade de módulos       │
│ - Reparações            │ - Peso e potência             │
│ - Guias Transporte      │                               │
│ - QR Codes              │                               │
│ - PWA                   │                               │
└─────────────────────────┴───────────────────────────────┘

## Ligações

- A **Smart App Manager** inclui um link direto para a **LED Calculator** no menu "Comercial"
- Ambas partilham o mesmo domínio base (smartvideo.tech)
- A LED Calculator é uma ferramenta complementar usada durante a orçamentação
