# Key compromise playbook

This document outlines the actions to take when keys are compromised.

## Signing key compromise

1. Rotate signing keys to issue new tokens.
2. Revoke the compromised key by `kid`.
3. Force instances to refresh tokens.
4. Notify affected operators and audit the incident.

Commands:

```bash
php artisan control-plane:key:rotate
php artisan control-plane:key:revoke <kid>
```

## Instance API key compromise

1. Regenerate the instance secret.
2. Update the instance with the new secret on the client.
3. Monitor heartbeats and license refresh for failures.

Command:

```bash
php artisan control-plane:instance:rekey <instance-uuid>
```

## Operational communication

- Trigger webhooks for `agent.heartbeat_failed` and `license.updated`.
- Record audit events for key rotation and instance rekeying.
- Notify stakeholders with the incident timeline and recovery steps.
