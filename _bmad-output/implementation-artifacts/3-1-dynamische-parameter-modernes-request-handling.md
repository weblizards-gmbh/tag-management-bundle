# Story 3.1: Dynamische Parameter & modernes Request-Handling

Als Admin
moechte ich beliebig viele Parameter verwalten koennen,
damit keine kuenstlichen Limits meine Konfigurationen einschraenken.

## Acceptance Criteria

- Gegeben die Tag-Parameterverwaltung
- Wenn mehr als 5 Parameter eingegeben werden
- Dann werden sie korrekt verarbeitet und gespeichert
- Und das Request-Handling nutzt das Symfony Request-Objekt statt Superglobals
