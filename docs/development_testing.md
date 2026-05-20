# Entwicklung & Testen des TagManagementBundles

Dieses Dokument beschreibt, wie das Bundle während der Entwicklung getestet und in einer lokalen Pimcore-Umgebung geprüft werden kann.

## 1. Lokale Tests (Unit & Integration)

Das Bundle enthält bereits einige Tests im Verzeichnis `tests/`. Diese können aktuell als einfache PHP-Skripte ausgeführt werden.

### Ausführung der vorhandenen Tests
```bash
php tests/tag_injection_service_test.php
php tests/html_insertion_test.php
```

### Empfehlung: PHPUnit nutzen
Um professionell zu testen, sollte PHPUnit verwendet werden. Da Pimcore selbst PHPUnit nutzt, bietet es sich an, eine `phpunit.xml.dist` im Root-Verzeichnis des Bundles zu erstellen.

#### Beispiel `phpunit.xml.dist`:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/9.5/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="TagManagementBundle Test Suite">
            <directory>tests</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

---

## 2. Integration in eine Pimcore-Installation (Symlink-Methode)

Um das Bundle "live" in einer Pimcore-Instanz zu testen, ohne es jedes Mal auf Packagist hochladen zu müssen, empfiehlt sich die Nutzung von Composer mit einem **Path Repository**.

### Schritt-für-Schritt Anleitung:

1.  **Pimcore Projekt vorbereiten**:
    Erstellen Sie eine neue Pimcore-Instanz oder nutzen Sie eine bestehende.

2.  **Repository zum `composer.json` des Pimcore-Projekts hinzufügen**:
    Fügen Sie den Pfad zu Ihrem lokalen Bundle-Verzeichnis hinzu:

    ```json
    "repositories": [
        {
            "type": "path",
            "url": "../pfad/zu/deinem/tag-management-bundle",
            "options": {
                "symlink": true
            }
        }
    ]
    ```

3.  **Bundle installieren**:
    Führen Sie im Pimcore-Projekt aus:
    ```bash
    composer require weblizards/tag-management-bundle:dev-master
    ```
    Composer erstellt nun einen Symlink in den `vendor/`-Ordner. Änderungen am Code im Bundle-Verzeichnis sind sofort in der Pimcore-Instanz wirksam.

4.  **Bundle aktivieren**:
    ```bash
    bin/console pimcore:bundle:enable WeblizardsTagManagementBundle
    ```

5.  **Assets verlinken (falls vorhanden)**:
    Falls das Bundle CSS/JS für das Pimcore Backend mitbringt:
    ```bash
    bin/console assets:install public --symlink
    ```

---

## 3. Manuelles Prüfen der Funktionalität

Nach der Installation und Aktivierung:

1.  **Backend**: Prüfen Sie, ob unter **Einstellungen > Tag & Snippet Management** die Oberfläche erscheint.
2.  **Konfiguration**: Erstellen Sie einen Test-Tag (z.B. ein einfaches `<script>console.log('Test');</script>`) für eine bestimmte URL.
3.  **Frontend**: Rufen Sie die entsprechende Seite im Frontend auf und prüfen Sie im Quelltext oder in der Browser-Konsole, ob das Snippet korrekt eingefügt wurde.
4.  **Logs**: Achten Sie auf `var/log/dev.log` in Ihrer Pimcore-Instanz bei Fehlern.
