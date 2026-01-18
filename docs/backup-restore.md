# Backup and restore

This document covers backup/restore expectations for Cloudron and manual recovery.

## Database backup

- Cloudron: rely on built-in backups for the database service.
- Manual: create a dump from the database service used by the app.

Example (MySQL):

```bash
mysqldump --single-transaction -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" > backup.sql
```

Restore:

```bash
mysql -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" < backup.sql
```

## Storage backup

- Cloudron: `/app/data` is backed up by Cloudron.
- Manual: archive the local storage directory.

```bash
tar -czf storage-backup.tgz storage/
```

## Restore smoke test

After restore:

1. Run migrations.
2. Run a health check.
3. Verify module discovery and status.

```bash
php artisan migrate --force
curl -s http://localhost:8000/health
php artisan module:discover
```
