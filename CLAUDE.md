# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

Eqleem is a multi-tenant SaaS that gives each tenant a customizable "super page that sells" — a storefront with pluggable content modules (store, blog, courses, services, properties, portfolio, digital products/services, playlists, menus, newsletter, bookings). Stack: **PHP 8.4, Laravel 13, Livewire 4 (Volt SFCs), Tailwind v4**, served locally by **Laravel Herd at `https://eqleem.test`** (never run a `serve` command — Herd is always up).

## Commands

```bash
composer dev                              # server + queue + pail logs + vite (concurrent) — the normal dev loop
composer setup                            # first-time install (env, key, migrate, npm, build)

npm run dev            / npm run build            # main Vite bundle (app.css, app.js)
npm run dev.editorjs   / npm run build.editorjs   # SEPARATE Editor.js bundle (vite.editorjs.config.js)

php artisan test --compact                        # all tests (Pest, sqlite :memory:)
php artisan test --compact --filter=SomeTest      # single test / method
vendor/bin/pint --dirty --format agent            # REQUIRED after editing PHP — format before finalizing
```

If a frontend change doesn't show up, the user likely needs `npm run dev`/`npm run build` (or `composer dev`). A `ViteException: Unable to locate file in Vite manifest` means the same.

## Authoritative conventions

**`AGENTS.md` (Laravel Boost guidelines) is the source of truth for coding conventions** — read it. Highlights: use `php artisan make:*` (with `--no-interaction`) to scaffold; always `search-docs` (Boost MCP) before code changes; run read-only DB inspection via Boost `database-query`/`database-schema`; every change needs a test; keep state server-side in Livewire; PHP 8 constructor promotion, explicit return types, curly braces always.

Domain skills live in `.cursor/skills/**` (laravel-best-practices, livewire-development, pest-testing, socialite-development, tailwindcss-development). Activate the relevant skill when working in that domain.

## Architecture

### Multi-tenancy (path-based)
Tenant routes are prefixed `{tenant}` (the tenant's `handle`). `ResolveTenantFromPath` middleware (`routes/tenant.php`) loads the active `Tenant` and calls `setCurrentTenant()`. Access the current tenant anywhere via the `helpers.php` globals: `currentTenant()`, `tenant()`, `currentTenantId()`, `user()`, `authClient()`. Models scoped to a tenant use the `App\Traits\BelongsToTenant` trait. Because Livewire updates re-run middleware, tenancy/admin/theme middleware are registered as **persistent Livewire middleware** in `AppServiceProvider`.

### Theming
Each tenant has a `Theme`; `SetTenantTheme` middleware prepends view namespaces `tenant-theme::` → `public/themes/{slug}` and `default-tenant-theme::` → `public/themes/default`, and shares `themeOptions`/`themePrimaryPalette`. Render tenant pages through the **`tenantView($view, $data)`** helper, which resolves `tenant-theme::` → `default-tenant-theme::` → base view and wraps them in `layouts.tenant`. Theme customization values are stored per-tenant on the `tenantable` pivot (`Tenant::themeSettingsFor()` / `saveThemeSettingsFor()`).

### Routing & Livewire components
`bootstrap/app.php` loads `routes/{web,auth,client-auth,admin,tenant}.php`. Routes use `Route::livewire('/path', 'namespace::component')`. Livewire component namespaces are configured in `config/livewire.php` (`admin::`, `auth::`, `pages::`, `layouts::`, `tenant::`) and class namespace is `App\Livewire`. **Volt single-file components are Blade files prefixed with the ⚡ emoji** (e.g. `resources/views/admin/⚡home.blade.php`) — preserve this when creating/renaming.

Gotcha in `routes/tenant.php`: it imports fake prefixes `use Pages\X;` / `use Store\X;` and applies `->namespace('App\Livewire\Tenant')` to the group. Bare `Home::class` and prefixed `Pages\Branches::class` are string class names that the group namespace resolves to `App\Livewire\Tenant\...`. These `use` statements are not real classes — don't "fix" them.

### Page builder: Blocks & Content
Pages are composed of **`Block`s** (component + type + variant + position + ordered `data`) linked to **`Content`** records (polymorphic `type`/`template`, taxonomy-tagged via `aliziodev/laravel-taxonomy`, media-enabled). The registries in `app/Support` — `BlockTypeRegistry`, `ContentTypeRegistry`, `BlockVariants`, `PageTabRegistry`, `TenantThemeOptions` — are bound as singletons in `AppServiceProvider` and drive what block/content types and variants exist. `UiServiceProvider` registers the `ui::` anonymous-component namespace (`resources/views/ui`) and a custom `UiTagCompiler` precompiler for UI tags.

### Other cross-cutting pieces
- **Actions** (`app/Actions`, `lorisleiva/laravel-actions`): single-purpose invokable classes (`CreateTenant`, `RegisterTenant`, `SeedTenantDefaults`, payment callbacks, email senders). Prefer an Action over fat controllers/components.
- **Subscriptions**: `lucasdotvin/laravel-soulbscription` (vendored as a local path package in `packages/`) — `Tenant` uses `HasSubscriptions`, plans via the `Plan` model.
- **Media/storage**: `spatie/laravel-medialibrary`; files served from the `spaces` (S3) disk. Use `contentImageUrl()` for content image URLs.
- **Money**: `app/Support/Money.php` + `money_*()` helpers (values stored in minor units); currency/symbol come from tenant settings.
- **Auth**: separate admin auth (`routes/auth.php`, `admin` middleware) and tenant-facing client auth (`routes/client-auth.php`, Socialite google/github, passwordless login codes).
- Localization defaults to Arabic (`resources/lang/ar`).
