# Tag Management Bundle for Pimcore 11

Dieses Bundle stellt den Bereich "Tag & Snippet Management" wieder bereit, der in älteren Pimcore-Versionen verfügbar war und später entfernt wurde.

Für Pimcore 10 bitte den [pimcore 10 branch](https://github.com/weblizards-gmbh/tag-management-bundle/tree/Pimcore-10.x) verwenden.

## Funktionen

Das Bundle ermöglicht es, HTML-Tags und Snippets flexibel in Webseiten zu integrieren, basierend auf verschiedenen Regeln:

- **URL-Pattern**: Ausgabe nur auf passenden Requests.
- **HTTP-Methoden**: Einschränkung auf `GET`, `POST` usw.
- **Site-Check**: Zuordnung zu bestimmten Pimcore-Sites.
- **Parameter**: Prüfung auf Query- oder Request-Parameter.
- **Positionierung**: Einfügen am Anfang oder Ende von `<head>` oder `<body>` sowie in CSS-selektierte Zielelemente.
- **Zeitsteuerung**: Ablaufdatum pro Item.
- **Editmode-Steuerung**: getrennte Aktivierung im Pimcore-Editmode.
- **Dynamische Parameter**: keine starre 5-Felder-Begrenzung mehr im Admin-UI.
- **Umbenennen im Admin**: Tags können im Backend sicher umbenannt werden.

## Voraussetzungen

- PHP `>= 8.2`
- Pimcore `^11.0`

## Installation

1. Bundle per Composer einbinden:

```bash
composer require weblizards/tag-management-bundle
```

2. Bundle aktivieren:

```bash
bin/console pimcore:bundle:enable WeblizardsTagManagementBundle
```

3. Bundle-Migrations ausführen:

```bash
bin/console doctrine:migrations:migrate --prefix="Weblizards\\TagManagementBundle"
```

4. Falls vorhanden, Legacy-Konfiguration aus `var/config/tag-manager.php` in den Pimcore `SettingsStore` migrieren:

```bash
bin/console weblizards:tag-management:migrate-config-storage --dry-run
bin/console weblizards:tag-management:migrate-config-storage
```

Optional kann die alte Datei nach erfolgreicher Migration entfernt werden:

```bash
bin/console weblizards:tag-management:migrate-config-storage --cleanup-legacy-file
```

## Speicherung

Aktuelle Versionen speichern Tag-Konfigurationen im Pimcore `SettingsStore` unter einem bundle-eigenen Scope. Das ist der vorgesehene Pimcore-11-kompatible Persistenzpfad.

Für Bestandsinstallationen bleibt während des Rollouts ein lesender Fallback auf die alte Datei `var/config/tag-manager.php` erhalten. Neue oder aktualisierte Einträge werden jedoch im `SettingsStore` persistiert.

## Commands

### Abgelaufene Items deaktivieren

Zur persistierten Deaktivierung abgelaufener Items:

```bash
bin/console weblizards:tag-management:disable-expired-items
```

Das eignet sich für einen regelmäßigen Cronjob.

### Legacy-Datumswerte normalisieren

Falls Bestandsdaten noch alte Epoch-Zeitstempel oder uneinheitliche Datumsformate enthalten:

```bash
bin/console weblizards:tag-management:migrate-item-dates
```

### Legacy-Speicher in SettingsStore migrieren

Zur Migration der alten PHP-Array-Datei in den `SettingsStore`:

```bash
bin/console weblizards:tag-management:migrate-config-storage --dry-run
bin/console weblizards:tag-management:migrate-config-storage
```

Nützliche Optionen:

- `--dry-run`: zeigt nur an, was migriert würde
- `--overwrite`: überschreibt bereits vorhandene Settings-Store-Einträge
- `--cleanup-legacy-file`: entfernt die alte `tag-manager.php` nach erfolgreicher Migration

## Admin-Routing

Im Pimcore-Admin nutzt das Bundle bevorzugt `Routing.generate(...)`, wenn im jeweiligen Setup eine kompatible globale Routing-Basis vorhanden ist. Für die eigenen Admin-Endpunkte existiert zusätzlich ein interner Fallback auf stabile Bundle-Pfade unter `/admin/tag-management/...`.

Dadurch bleibt das Bundle auch ohne harte Abhängigkeit auf ein bestimmtes JS-Routing-Setup funktionsfähig.

## Entwicklung und Tests

Das Repository enthält einfache PHP-basierte Regressionstests sowie Konfigurationshilfen für die lokale Entwicklung. Weitere Hinweise dazu stehen in [docs/development_testing.md](docs/development_testing.md).

## Hinweise zum Upgrade

Beim Upgrade von älteren Bundle-Ständen sind diese Punkte relevant:

- Tag-Konfigurationen werden jetzt über den `SettingsStore` persistiert.
- Alte Konfigurationen aus `var/config/tag-manager.php` sollten mit dem Migrations-Command übernommen werden.
- Legacy-Key-Namen und ältere Request-Payloads werden während des Übergangs weiterhin tolerant gelesen.
- Ablaufdaten werden intern auf ein konsistentes ISO-8601-UTC-Format normalisiert.

## Lizenz

Dieses Bundle steht unter der [GPL-3.0-or-later](LICENSE.md).

---
Entwickelt von [pimcore](https://pimcore.com/) und weiterentwickelt von [Weblizards GmbH](https://www.weblizards.de).
