# Story 2.3: Injektionslogik und Data Binding modernisieren

Als Admin
moechte ich eine klar getrennte Injektionslogik und modernes Data Binding,
damit Wartung und Erweiterungen einfacher werden.

## Acceptance Criteria

- Gegeben `TagManagerListener`
- Wenn Tag-Injektionen ausgefuehrt werden
- Dann erfolgt die Logik in einem dedizierten Service
- Und das Data Binding nutzt Serializer oder Form-Component statt manuelles Mapping
