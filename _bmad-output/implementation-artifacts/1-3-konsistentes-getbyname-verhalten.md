# Story 1.3: Konsistentes `getByName()`-Verhalten

Als Admin
moechte ich konsistente Rueckgabetypen und einheitliches Fehlerverhalten bei `getByName()`,
damit Tag-Konfigurationen zuverlaessig geladen werden.

## Acceptance Criteria

- Gegeben `Model\Tag\Config\Dao::getByName()`
- Wenn ein existierender Name abgefragt wird
- Dann wird ein konsistenter, dokumentierter Typ zurueckgegeben
- Und bei nicht vorhandenem Namen ist das Fehlerverhalten einheitlich und nachvollziehbar
