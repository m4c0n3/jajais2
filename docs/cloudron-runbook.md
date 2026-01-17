# Cloudron Runbook

## Logs
- Use Cloudron logs in the admin UI for container logs.
- In the container: `/app/data/logs` (if configured) or `storage/logs` symlink.

## Diagnostic commands
Run inside the container:
- `php artisan cloudron:diag`
- `php artisan system:status`

## Common issues

### 500 errors
- Check app logs and `php artisan cloudron:diag` for DB status.
- Verify migrations ran (`php artisan migrate --force`).

### Webhook failures
- Check `webhook_deliveries` table status and retry.
- Verify target URL and signatures.

### License refresh failures
- Check agent logs and `instance_state` timestamps.
- Verify control plane connectivity.

### Database issues
- Ensure MySQL addon is healthy and credentials are wired.
- Check `DB_*` env values in Cloudron settings.

### Queue/scheduler
- Ensure queue driver is running (redis recommended).
- Confirm scheduler addon is enabled.
