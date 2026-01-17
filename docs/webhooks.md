# Webhooks (Outgoing)

## Tables
- `webhook_endpoints`: configuration for destinations
- `webhook_deliveries`: delivery log and retry state

## Endpoint fields
- `url`, `secret`, `events`, optional `headers`
- `timeout_seconds`, `max_attempts`, `backoff_seconds`

## Events
- `module.enabled`
- `module.disabled`
- `rbac.synced`
- `license.updated`

## Payload
```json
{
  "id": "<correlation_id>",
  "event": "module.enabled",
  "occurred_at": "2026-01-17T12:34:56Z",
  "actor": {"type": "user|system", "id": 123},
  "data": {"id": "HelloWorld"}
}
```

## Headers
- `X-Webhook-Event`
- `X-Webhook-Id`
- `X-Webhook-Timestamp`
- `X-Webhook-Signature: v1=<hex>`

Signature base string:
```
v1:<timestamp>:<raw_body>
```

Retry policy:
- Retry on 429/5xx only
- 4xx (except 429) fail fast

## CLI
- `php artisan webhook:list`
- `php artisan webhook:test <endpoint_id> --event=module.enabled`
- `php artisan webhook:retry <delivery_id>`
