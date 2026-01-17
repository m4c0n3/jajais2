
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
| CP-0005 | 1.0.0   | RBAC sync z manifestov + audit log minimum | ready  | prompts/codex/CP-0005-rbac-and-audit.md |
| CP-0006 | 1.0.0   | Control Plane kontrakt + Agent modul (heartbeat + license refresh) | ready  | prompts/codex/CP-0006-control-plane-agent.md |
| CP-0007 | 1.0.0   | Agent hardening + trust model (JWT RS256) + ops | ready  | prompts/codex/CP-0007-agent-hardening-rs256.md |
| CP-0008 | 1.0.0   | Outgoing Webhooks engine + n8n integrácia | ready  | prompts/codex/CP-0008-webhooks-n8n.md |
| CP-0009 | 1.0.0   | Admin UI (Filament) pre Webhooks + Deliveries | ready  | prompts/codex/CP-0009-admin-ui-filament.md |
| CP-0010 | 1.0.0   | Dev environment standardization (PHP 8.4+) + pre-flight checks | ready  | prompts/codex/CP-0010-dev-env-standardization.md |
| CP-0011 | 1.0.0   | CI pipeline (GitHub Actions) pre Laravel (PHP 8.4+) | ready  | prompts/codex/CP-0011-ci-github-actions.md |
| CP-0012 | 1.0.0   | Ops/Release minimum (health + system status) | ready  | prompts/codex/CP-0012-ops-release-minimum.md |
| CP-0013 | 1.0.0   | API auth + RBAC + rate limiting + audit | ready  | prompts/codex/CP-0013-api-auth-rate-limit-audit.md |
| CP-0014 | 1.0.0   | Multijazycnost (i18n) pre jadro aj moduly | ready  | prompts/codex/CP-0014-multilanguage-i18n.md |
| CP-0015 | 1.0.0   | Automaticke aktualizacie jadra a modulov (MVP) | ready  | prompts/codex/CP-0015-auto-updates-core-modules.md |
| CP-0016 | 1.0.0   | Control Plane Server MVP (registry + entitlements) | ready  | prompts/codex/CP-0016-control-plane-server-mvp.md |
