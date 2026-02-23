# Story 2.1: Modernes HTML-Parsing

Als Admin
moechte ich, dass die HTML-Manipulation mit einer modernen Library erfolgt,
damit das Parsing robust und wartbar bleibt.

## Acceptance Criteria

- Gegeben die bisherige Nutzung von `simple_html_dom.php`
- Wenn HTML verarbeitet wird
- Dann erfolgt das Parsing mit DomCrawler + HTML5-Parser
- Und `simple_html_dom.php` wird nicht mehr verwendet
