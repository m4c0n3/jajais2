# Installer

## App mode
Set mode to one of:
- `client`
- `control-plane`

The selected mode is persisted in `system_settings` and locked after install.

## Web install
- Visit `/install` on a fresh instance.
- Choose the mode and admin credentials.
- Installer runs:
  - module discovery
  - enable module set
  - migrations
  - admin user creation
  - rbac sync

## CLI install
Non-interactive example:
```
php artisan system:install \
  --mode=client \
  --admin-name="Admin" \
  --admin-email="admin@example.com" \
  --admin-password="change-me" \
  --non-interactive
```

## Module sets
Defined in `config/module_sets.php`.
