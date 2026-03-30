# Story 2.1: Modernes HTML-Parsing

## Story

Als Admin
moechte ich, dass die HTML-Manipulation mit einer modernen Library erfolgt,
damit das Parsing robust und wartbar bleibt.

## Acceptance Criteria

- Gegeben die bisherige Nutzung von `simple_html_dom.php`
- Wenn HTML verarbeitet wird
- Dann erfolgt das Parsing mit DomCrawler + HTML5-Parser
- Und `simple_html_dom.php` wird nicht mehr verwendet

## Tasks/Subtasks

- [x] HTML-Parsing auf DomCrawler + HTML5-Parser umgestellt
- [x] Verwendung von `simple_html_dom.php` entfernt (keine Nutzung mehr im Code)

## Dev Notes

- Keine zusaetzlichen Anforderungen dokumentiert.

## Dev Agent Record

### Implementation Plan

- Bestehende Implementierung pruefen und mit AC abgleichen.

### Debug Log

- N/A

### Completion Notes

- Vorhandene Implementierung in `HtmlInsertionService` nutzt `Masterminds\\HTML5` + `Symfony\\Component\\DomCrawler\\Crawler`.
- Keine Vorkommen von `simple_html_dom` im Code gefunden.
- Keine Tests in diesem Schritt ausgefuehrt (nur Verifikation durch Code-Inspektion).

## File List

- src/TagManagementBundle/Service/HtmlInsertionService.php

## Change Log

- 2026-03-26: Story-Template ergaenzt; bestehende Implementierung verifiziert; Status auf `review` gesetzt.
- 2026-03-26: Code-Review abgeschlossen (Approve).

## Senior Developer Review (AI)

- Date: 2026-03-26
- Outcome: Approve
- Summary: AC erfuellt; HTML-Parsing via `Masterminds\\HTML5` + `Symfony\\Component\\DomCrawler\\Crawler`, keine Nutzung von `simple_html_dom` im Code. Einfache Tests vorhanden (`tests/html_insertion_test.php`, `tests/tag_injection_service_test.php`), nicht ausgefuehrt.
- Action Items: None

## Status

review
