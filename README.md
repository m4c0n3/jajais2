<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Licensing Gate (Modules)

Lokálny licensing cache a middleware pre modulové routy:

- Ulož token: `php artisan license:install --token="..."`
- Alebo z file: `php artisan license:install --file=/path/to/token.txt`
- Použitie v routach: `->middleware('module.active:blog')` alebo `EnsureModuleActive::class.':blog'`

Triedy sú v `app/Support/Licensing/LicenseService.php` a `app/Http/Middleware/EnsureModuleActive.php`.

## Module Discovery

Moduly ukladaj do `modules/<ModuleName>/module.json`. Discovery spustíš príkazom:
- `php artisan module:discover`

Manifest minimálne obsahuje `id`, `name`, `version`. Záznam v tabuľke `modules` sa vytvorí s `enabled=false`.

## Module Boot + Commands

- Enable modul: `php artisan module:enable HelloWorld`
- Disable modul: `php artisan module:disable HelloWorld`
- Cache registry: `php artisan module:cache`
- Clear cache: `php artisan module:clear-cache`

HelloWorld modul po enable pridá route `GET /hello`.

## RBAC (Permissions)

Permissions zapisuj do `module.json` (napr. `"helloworld.view"`). Synchronizácia:
- `php artisan rbac:sync`

Strategy pre `super-admin`: vytvára sa rola `super-admin` (a `admin`) a má mať všetky permissions priradené manuálne podľa potreby.

## Control Plane Agent

Kontrakt API je v `docs/control-plane-contract.md`. Agent modul je v `modules/Agent` a používa príkazy:
- `php artisan agent:register`
- `php artisan agent:heartbeat`
- `php artisan agent:license-refresh`

Nastavenie a scheduler sú popísané v `modules/Agent/docs/README.md`.

## Prompting Standard (Codex)

Všetky implementačné prompty pre Codex udržiavame verzované v repozitári v adresári `/prompts`.

### Číslovanie
- Každý prompt má unikátne ID: `CP-0001`, `CP-0002`, ...
- ID je sekvenčné a nikdy sa neopakuje.

### Verzionovanie
- Každý prompt má `Version: x.y.z` (SemVer).
- Opravy textu = patch, rozšírenie krokov = minor, zásadná zmena stratégie = major.
- Výrazná zmena cieľa = nový prompt s novým CP ID.

### Povinné časti promptu
Každý prompt musí obsahovať:
- Scope (in/out)
- Návrh riešenia (čo, prečo, alternatívy)
- Dopady (security, výkon, DB, kompatibilita)
- Kroky pre Codex
- DoD (akceptačné kritériá)
- Validácia (príkazy a očakávania)
- Rollback plán
- Report pre človeka

### Register promptov
- `prompts/PROMPT_INDEX.md` je zdroj pravdy pre poradie a stav promptov.
