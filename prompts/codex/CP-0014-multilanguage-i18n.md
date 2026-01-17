# CP-0014 — Multijazyčnosť (i18n) pre jadro aj moduly

## Meta
- ID: CP-0014
- Version: 1.0.0
- Title: Multijazyčnosť (i18n)
- Status: ready
- Date: 2026-01-17

## Context
Požiadavka: multijazyčnosť. Aplikácia je modulárna → aj moduly musia niesť svoje preklady.

## Scope
### In scope
- default locale (sk), fallback (en)
- user locale (profil / config)
- modulové preklady: modules/<Modul>/lang/<locale>/*.php + loader pre active moduly
- admin UI: basic language switch
- docs: docs/i18n.md
- testy pre loader + fallback

## DoD
- [ ] modulové translations fungujú
- [ ] user locale funguje
- [ ] testy prechádzajú

## Git workflow
- commit: "CP-0014: add multi-language support for core and modules"
- push
