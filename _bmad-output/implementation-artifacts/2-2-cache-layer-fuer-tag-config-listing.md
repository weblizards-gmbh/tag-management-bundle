# Story 2.2: Cache-Layer fuer Tag\Config\Listing

Als Admin
moechte ich schnellere Tag-Konfigurationen,
damit wiederholte Requests kein unnoetiges Dateiparsing ausloesen.

## Acceptance Criteria

- Gegeben wiederholte Zugriffe auf `Tag\Config\Listing`
- Wenn die Konfiguration bereits im Cache ist
- Dann wird kein erneutes Parsen durchgefuehrt
- Und der Cache ist tag-basiert ohne TTL
