# Tag Management Bundle for Pimcore 10

Dieses Bundle stellt den Bereich "Tag and Snippet Management" zur Verfügung, welcher in Pimcore 6.x als veraltet markiert und mit Version 10 entfernt wurde.

## Funktionen

Das Bundle ermöglicht es, HTML-Tags und Snippets (z.B. Google Analytics, Facebook Pixel, Custom JS/CSS) flexibel in die Webseite zu integrieren, basierend auf verschiedenen Regeln:

- **URL-Pattern**: Anzeige nur auf bestimmten Seiten.
- **HTTP-Methoden**: Einschränkung auf GET, POST etc.
- **Site-Check**: Zuweisung zu spezifischen Pimcore-Sites.
- **Parameter**: Prüfung auf bestimmte Query-Parameter.
- **Positionierung**: Einfügen am Anfang oder Ende von `<head>` oder `<body>`, oder an spezifischen CSS-Selektoren.
- **Zeitsteuerung**: Ablaufdatum für einzelne Snippets. Diese werden automatisch deaktiviert, wenn das Datum erreicht ist (erfordert Command-Ausführung).

## Installation

1. **Installation via Composer**

   Aktuell muss das Bundle manuell zum Projekt hinzugefügt werden (da es noch nicht auf Packagist ist oder als lokales Repository eingebunden werden muss):

   ```bash
   composer require weblizards/tag-management-bundle:Pimcore-10.x
   ```

2. **Bundle aktivieren**

   Aktivieren Sie das Bundle in der `config/bundles.php` oder über das Pimcore Admin-Panel / CLI:

   ```bash
   bin/console pimcore:bundle:enable WeblizardsTagManagementBundle
   ```
   
3. **Migrations**

   Nach der Installation des Bundles müssen die Migrations ausgeführt werden.

   Migrations anzeigen:

   ```bash
   bin/console doctrine:migrations:list --prefix "Weblizards\TagManagementBundle"
   ```
 
   Migrations ausführen:
 
   ```bash
   bin/console doctrine:migrations:migrate --prefix "Weblizards\TagManagementBundle"
   ```

4. **Datenbank & Speicher**

   Das Bundle verwendet `PhpArrayTable` zur Speicherung der Konfiguration. Die Daten werden standardmäßig im Pimcore-Verzeichnis unter `var/config/tag-manager.php` (oder ähnlich) gespeichert. Es ist keine manuelle Datenbank-Migration erforderlich.

5. **Automatische Deaktivierung abgelaufener Snippets (optional)**

   Um Snippets mit gesetztem Ablaufdatum automatisch zu deaktivieren, kann ein Cronjob eingerichtet werden, der den folgenden Command regelmäßig ausführt:

   ```bash
   bin/console weblizards:tag-management:disable-expired-items
   ```

   Dieser Command prüft alle konfigurierten Tags und setzt das "Deaktiviert"-Flag für alle Items, deren Ablaufdatum in der Vergangenheit liegt.

## Konfiguration

Nach der Installation finden Sie den neuen Menüpunkt unter **Einstellungen > Tag & Snippet Management**.

## Admin-Routing / FOS-Routes

Im Pimcore-Admin nutzt das Bundle bevorzugt `Routing.generate(...)`, wenn im jeweiligen Setup eine kompatible Routing-Basis bereitgestellt wird. Eine harte Abhaengigkeit auf FOSJsRouting besteht jedoch nicht: fuer die eigenen Admin-Endpunkte existiert ein interner Fallback auf stabile Bundle-Pfade unter `/admin/tag-management/...`.

Das bedeutet:

- Ist eine globale JS-Routing-Basis vorhanden, wird sie weiter genutzt.
- Ist sie nicht vorhanden, bleibt das Bundle fuer seine eigenen Admin-Requests funktionsfaehig.
- Andere Bundles oder globale Routing-Setups werden dadurch nicht beeinflusst.

Damit ist die Routing-Basis fuer den Admin-Bereich dokumentiert und so vorbereitet, dass spaetere Pimcore-/Routing-Aenderungen ohne groesseren Umbau aufgenommen werden koennen.

## Entwicklung und Tests

Informationen dazu, wie das Bundle während der Entwicklung getestet und in eine Pimcore-Instanz eingebunden werden kann, finden Sie im [Development & Testing Guide](docs/development_testing.md).

## Lizenz

Dieses Bundle steht unter der [GPL-3.0-or-later](LICENSE.md).

---
Entwickelt von [pimcore](https://pimcore.com/) und weiterentwickelt von [Weblizards GmbH](https://www.weblizards.de).
