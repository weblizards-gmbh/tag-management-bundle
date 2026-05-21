# Tag Management Bundle for Pimcore 11

This bundle restores the "Tag & Snippet Management" area that was available in older Pimcore versions and removed later on.


For Pimcore 10 please use the [pimcore 11 branch](/weblizards-gmbh/tag-management-bundle/tree/Pimcore-10.x)

## Features

The bundle allows you to inject HTML tags and snippets into websites based on different rules:

- **URL patterns**: output only on matching requests
- **HTTP methods**: restrict output to `GET`, `POST`, and similar methods
- **Site checks**: assign tags to specific Pimcore sites
- **Parameters**: check query or request parameters
- **Positioning**: insert content at the beginning or end of `<head>` or `<body>`, or into CSS-selected target elements
- **Scheduling**: define an expiry date per item
- **Editmode control**: enable or disable items separately for Pimcore editmode
- **Dynamic parameters**: no more fixed 5-field limitation in the admin UI
- **Rename in admin**: tags can be renamed safely in the backend

## Requirements

- PHP `>= 8.2`
- Pimcore `^11.0`

## Installation

1. Require the bundle via Composer:

```bash
composer require weblizards/tag-management-bundle
```

2. Enable the bundle:

```bash
bin/console pimcore:bundle:enable WeblizardsTagManagementBundle
```

3. Run the bundle migrations:

```bash
bin/console doctrine:migrations:migrate --prefix="Weblizards\\TagManagementBundle"
```

4. If present, migrate legacy configuration from `var/config/tag-manager.php` into the Pimcore `SettingsStore`:

```bash
bin/console weblizards:tag-management:migrate-config-storage --dry-run
bin/console weblizards:tag-management:migrate-config-storage
```

Optionally remove the legacy file after a successful migration:

```bash
bin/console weblizards:tag-management:migrate-config-storage --cleanup-legacy-file
```

## Storage

Current versions persist tag configurations in the Pimcore `SettingsStore` using a bundle-specific scope. This is the intended Pimcore 11-compatible persistence path.

For existing installations, a read-only fallback to the legacy file `var/config/tag-manager.php` remains in place during rollout. New or updated entries are persisted in the `SettingsStore`.

## Commands

### Disable expired items

To persistently disable expired items:

```bash
bin/console weblizards:tag-management:disable-expired-items
```

This is suitable for a regular cron job.

### Normalize legacy date values

If existing data still contains legacy epoch timestamps or inconsistent date formats:

```bash
bin/console weblizards:tag-management:migrate-item-dates
```

### Migrate legacy storage into SettingsStore

To migrate the old PHP array file into the `SettingsStore`:

```bash
bin/console weblizards:tag-management:migrate-config-storage --dry-run
bin/console weblizards:tag-management:migrate-config-storage
```

Useful options:

- `--dry-run`: show what would be migrated without writing anything
- `--overwrite`: overwrite existing SettingsStore entries
- `--cleanup-legacy-file`: remove the old `tag-manager.php` after a successful migration

## Admin Routing

Inside the Pimcore admin, the bundle prefers `Routing.generate(...)` when a compatible global routing base is available in the current setup. For its own admin endpoints, it also provides an internal fallback to stable bundle paths under `/admin/tag-management/...`.

This keeps the bundle functional even without a hard dependency on a specific JavaScript routing setup.

## Development and Testing

The repository contains simple PHP-based regression tests and helper configuration for local development. Additional details can be found in [docs/development_testing.md](docs/development_testing.md).

## Upgrade Notes

When upgrading from older bundle versions, these points are relevant:

- Tag configurations are now persisted via the `SettingsStore`.
- Existing configurations from `var/config/tag-manager.php` should be migrated using the provided migration command.
- Legacy key names and older request payloads are still read tolerantly during the transition.
- Expiry dates are normalized internally to a consistent ISO-8601 UTC format.

## License

This bundle is licensed under [GPL-3.0-or-later](LICENSE.md).

---
Developed by [pimcore](https://pimcore.com/) and further maintained by [Weblizards GmbH](https://www.weblizards.de).
