# Story 3.2: Konsistente Keys & Benennungen mit rollender Umstellung

Als Admin
moechte ich konsistente Benennungen und bereinigte Konfig-Keys,
damit Konfigurationen wartbar und eindeutig bleiben.

## Acceptance Criteria

- Gegeben bestehende Konfigurationen mit vorhandenen Keys
- Wenn Konfigurationen gelesen oder aktualisiert werden
- Dann bleiben bestehende Keys weiterhin gueltig
- Und neue/aktualisierte Keys werden in `snake_case` normalisiert
- Und die Umstellung ist rollend moeglich, ohne bestehende Systeme zu brechen
- Und Lesen ist tolerant (alte Keys werden akzeptiert)
- Und Schreiben normiert (Keys werden beim Speichern normalisiert)
- Und alte und neue Keys koennen in einer Uebergangszeit koexistieren
- Und Benennungen wie `disabled`/`temporarily_disabled` sind vereinheitlicht
