

# Prompt Index (Codex)

Tento súbor je zdroj pravdy pre číslovanie a stav promptov.

## Pravidlá
- Každý prompt má unikátne ID: `CP-0001`, `CP-0002`, ...
- ID je sekvenčné a nikdy sa neopakuje.
- Každý prompt má verziu `Version: x.y.z` (SemVer).
- Výrazná zmena cieľa = nový prompt (nové CP-XXXX).
- Každý prompt musí obsahovať: Scope, Návrh riešenia, Dopady, Security notes, Kroky, DoD, Validáciu, Rollback, Report.

## Register

| ID      | Version | Title                           | Status | File |
|---------|---------|----------------------------------|--------|------|
| CP-0001 | 1.0.0   | Prompt registry a štandard       | ready  | prompts/codex/CP-0001-prompt-registry.md |
| CP-0002 | 1.0.0   | Module discovery + modules tabuľka + module:discover | ready  | prompts/codex/CP-0002-module-discovery.md |
| CP-0003 | 1.0.0   | Licensing cache + EnsureModuleActive middleware | ready  | prompts/codex/CP-0003-licensing-gate.md |
| CP-0004 | 1.0.0   | Boot aktívnych modulov + enable/disable + cache | ready  | prompts/codex/CP-0004-module-boot-manager.md |
