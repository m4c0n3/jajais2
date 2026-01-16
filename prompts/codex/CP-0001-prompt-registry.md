# CP-0001 — Prompt registry a štandard

## Meta
- ID: CP-0001
- Version: 1.0.0
- Title: Prompt registry a štandard
- Status: ready
- Date: 2026-01-16

## Context
V projekte používame Codex na implementáciu funkcií. Aby bola práca auditovateľná, opakovateľná a s minimálnou ľudskou interakciou, zavádzame štandard pre číslovanie a štruktúru promptov, ktoré budú žiť v repozitári.

## Scope
### In scope
- Vytvoriť adresár `/prompts/` a podadresár `/prompts/codex/`.
- Pridať register promptov `PROMPT_INDEX.md`.
- Pridať šablónu promptu `PROMPT_TEMPLATE.md`.
- Pridať túto špecifikáciu (Prompting Standard) do `README.md` alebo `docs/`.
- Pridať prvý záznam CP-0001 do indexu a pripraviť “draft” placeholder pre CP-0002.

### Out of scope
- Implementácia modulárneho systému, licencovania, RBAC, API, webhookov (to bude CP-0002+).

## Návrh riešenia
### Čo navrhujem
Zavedieme “prompt-as-code”:
- každý prompt je súbor v repozitári (verzovaný v git),
- má pevné ID `CP-000X` a SemVer `Version`,
- obsahuje definované sekcie (scope, dopady, DoD, validácia, rollback).

### Prečo
- audit (vieme spätne kto/čo/ako navrhol),
- opakovateľnosť (rovnaký prompt = rovnaké kroky),
- minimalizácia ľudskej interakcie (Codex dostane presné inštrukcie),
- kontrola rizík (bezpečnosť, rollback, validácia sú povinné).

### Alternatívy
- Voľné prompty mimo repa (napr. len v chate) — odmietame, lebo sa ťažko verzujú a auditujú.

## Dopady
- Bezpečnosť: žiadne priame dopady, iba zlepšenie procesu (povinné Security notes v ďalších promptoch).
- Výkon: bez dopadu.
- Kompatibilita: bez dopadu.
- Databáza/Migrácie: bez dopadu.
- Prevádzka/DevOps: bez dopadu.

## Predpoklady a závislosti
- Git repo existuje.
- Nezáleží na konkrétnej verzii Laravelu (toto je procesný krok).

## Úlohy pre Codex (kroky)
1) Vytvor adresáre:
   - `/prompts`
   - `/prompts/codex`
2) Vytvor súbor `/prompts/PROMPT_INDEX.md` s obsahom podľa štandardu (sekcia Register + pravidlá).
3) Vytvor súbor `/prompts/PROMPT_TEMPLATE.md` – šablóna pre ďalšie prompty.
4) Vytvor súbor `/prompts/codex/CP-0001-prompt-registry.md` – tento dokument.
5) Aktualizuj `README.md` (alebo vytvor `docs/prompting-standard.md` a zlinkuj ho z README) a pridaj sekciu:
   - “Prompting Standard”
   - pravidlá číslovania (CP-000X)
   - verzovanie (SemVer)
   - povinné sekcie (Scope, DoD, Validácia, Rollback…)
6) Skontroluj, že linky v `PROMPT_INDEX.md` sedia na existujúce súbory.

## Akceptačné kritériá (DoD)
- [ ] Existuje `/prompts/` a `/prompts/codex/`.
- [ ] Existuje `PROMPT_INDEX.md` a obsahuje CP-0001 a placeholder CP-0002.
- [ ] Existuje `PROMPT_TEMPLATE.md`.
- [ ] Existuje `CP-0001-prompt-registry.md`.
- [ ] README alebo docs obsahuje “Prompting Standard” a popisuje pravidlá.
- [ ] Všetky odkazy v indexe sú platné.

## Validácia
- Príkazy:
  - `ls -la prompts prompts/codex`
  - `test -f prompts/PROMPT_INDEX.md && echo OK`
  - `test -f prompts/PROMPT_TEMPLATE.md && echo OK`
  - `test -f prompts/codex/CP-0001-prompt-registry.md && echo OK`
- Očakávaný výsledok:
  - súbory existujú a majú obsah podľa tohto promptu.

## Rollback plán
- Odstráň adresár `/prompts/` a revertni zmeny v README:
  - `git revert <commit>` alebo manuálne zmazanie súborov.

## Report pre človeka
- Zmenené súbory:
  - `prompts/PROMPT_INDEX.md`
  - `prompts/PROMPT_TEMPLATE.md`
  - `prompts/codex/CP-0001-prompt-registry.md`
  - `README.md` alebo `docs/prompting-standard.md`
- Ako otestovať:
  - spusti validáciu príkazmi vyššie.
