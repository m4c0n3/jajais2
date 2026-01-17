# CP-0023 — Observability minimum (correlation IDs, admin metrics, log policy)

## Meta
- ID: CP-0023
- Version: 1.0.0
- Title: Observability minimum
- Status: ready
- Date: 2026-01-17

## Context
Na produkcii potrebujeme rýchlo zistiť čo je zle. Cloudron logy sú dostupné, ale musíme mať:
- korelačné ID naprieč requestmi/jobmi
- základné metriky v admin UI
- jasnú log policy (žiadne sekréty)

## Scope
### In scope
- Correlation ID middleware:
  - generovať/request header `X-Request-Id`
  - pridať do log contextu + do odpovedí
  - propagate do queued jobs (webhooks deliveries, agent refresh)
- Log policy doc:
  - `docs/logging-policy.md` (čo sa nesmie logovať, ako maskovať)
- Admin metrics (Filament widget / dashboard cards):
  - failed webhook deliveries count (posledných 24h)
  - last agent heartbeat (client mode) / unhealthy instances (control-plane mode)
  - invalid/expired license token count (ak sa dá z auditov)
  - last update attempt (CP-0015)
- Testy:
  - request id prítomné v response
  - request id pridané do log context (aspoň unit/feature test na middleware)

### Out of scope
- Prometheus export
- distributed tracing

## DoD
- [ ] X-Request-Id funguje (response + log context)
- [ ] docs/logging-policy.md existuje
- [ ] admin metrics existujú (client aj control-plane mode)
- [ ] testy prechádzajú

## Git workflow
- commit: "CP-0023: add observability minimum"
- push
