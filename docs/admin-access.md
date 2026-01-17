# Admin Access

## Bootstrap
- Installer ensures the first admin user exists and is granted `super-admin`.
- Core permissions are created: `admin.access`, `users.manage`.

## Grant access via CLI
```
php artisan admin:grant admin@example.com --role=super-admin --permission=admin.access
```

## Filament access
- Users need `super-admin` or `admin.access` to access `/admin`.
- User management requires `users.manage` (or `super-admin`).
