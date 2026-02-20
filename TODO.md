# TagManagementBundle TODO

### 1. Fehler & Kritische Logikmängel (Bugs) [Hohe Priorität]
- [ ] **Datenverlust bei Umbenennung (`TagManagementController`):** In `updateAction` wird die alte Konfiguration gelöscht, bevor die neue erfolgreich gespeichert wurde. Lösung: Erst neu speichern, dann alt löschen.
- [ ] **Race-Conditions & Seiteneffekte im Listener (`TagManagerListener`):** Automatisches Deaktivieren abgelaufener Tags via `$tag->save()` während `onKernelResponse` entfernen. Schreibzugriffe gehören nicht in einen Response-Listener.
- [ ] **Typsicherheit in DAOs:** Inkonstante API in `Model\Tag\Config\Dao::getByName()`. Typsierung und Fehlerhandling vereinheitlichen.
- [ ] **Fixes Parameter-Handling:** Die Begrenzung auf genau 5 Parameter (0-4) im Controller und Frontend aufheben und dynamisch gestalten.

### 2. Verbesserungen (Performance & Architektur) [Mittlere Priorität]
- [ ] **Refactoring HTML-Manipulation:** `simple_html_dom.php` entfernen. Umstellung auf moderne Bibliotheken wie `masterminds/html5` oder `symfony/dom-crawler` für effizientere DOM-Manipulation.
- [ ] **Caching implementieren:** `Tag\Config\Listing` nutzt aktuell kein Caching. Implementierung eines Cache-Layers, um ständiges Parsen von Dateien bei jedem Request zu vermeiden.
- [ ] **Injektions-Logik auslagern:** Die Logik zur Code-Injektion aus dem `TagManagerListener` in einen dedizierten Service (z.B. `TagInjectionService`) verschieben.
- [ ] **Modernisierung Data Binding:** Nutzung des Symfony Serializers oder Form-Components im `TagManagementController`, anstatt manuelles Mapping von Strings.

### 3. Unschönheiten (Code Style & UX) [Niedrige Priorität]
- [ ] **PHP 8 Typsierung:** Vollständige Einführung von PHP 8.x Type-Hints (Property Types, Return Types) im gesamten Bundle.
- [ ] **Inkonsistente Benennung:** Begriffe wie `disabled` vs. `temporarily_disabled` vereinheitlichen.
- [ ] **Konfigurations-Keys säubern:** Verwendung von ExtJS-IDs (`myId`) als Schlüssel in der PHP-Konfiguration vermeiden, um die Lesbarkeit der Dateien zu verbessern.
- [ ] **Symfony Request-Objekt:** Nutzung von globalen Variablen (`$_GET`, `$_POST`) im `TagManagerListener` durch das Symfony Request-Objekt ersetzen.
- [ ] **UX im Admin-Panel:** Umbenennen von Tags ermöglichen, ohne diese löschen und neu anlegen zu müssen (Feld im UI ist aktuell deaktiviert).

### 4. Sonstiges
- [ ] FOS-Routes Integration prüfen/verbessern.
