# CP-0020 — Cloudron package (single app) pre Client/Control-Plane (installer-driven)

## Meta
- ID: CP-0020
- Version: 1.0.0
- Title: Cloudron package (single app)
- Status: ready
- Date: 2026-01-17

## Goal
Pripraviť Cloudron app package tak, aby sa tá istá aplikácia dala nasadiť na Cloudron a pri prvom spustení si užívateľ vyberie mode (client/control-plane) cez installer (CP-0017),
alebo sa mode nastaví cez env (non-interactive).

## Cloudron constraints (kľúčové)
- /app/code je read-only, /app/data je perzistentné RW.
- start.sh beží ako root, app má bežať ako cloudron user (gosu).

## Scope
- Pridať `cloudron/`:
  - CloudronManifest.json (addons: localstorage, mysql, redis, scheduler; httpPort 8000; healthCheckPath /health)
  - Dockerfile (cloudron/base)
  - start.sh (symlinky storage + bootstrap/cache -> /app/data; env wiring; migrate --force)
- Docs: docs/cloudron-deploy.md (cloudron build/install/update)

## DoD
- [ ] cloudron balík buildne (docker build)
- [ ] /health funguje
- [ ] secret hodnoty sa nelogujú

## Git workflow
- commit: "CP-0020: add Cloudron packaging for installer-driven deployment"
- push
