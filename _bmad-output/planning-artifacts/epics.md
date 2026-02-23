---
stepsCompleted:
- 1
- 2
- 3
- 4
inputDocuments:
- _bmad-output/planning-artifacts/prd.md
- _bmad-output/planning-artifacts/architecture.md
---
# tag-management-bundle - Epic Breakdown

## Overview

This document provides the complete epic and story breakdown for tag-management-bundle, decomposing the requirements from the PRD, UX Design if it exists, and Architecture requirements into implementable stories.

## Requirements Inventory

### Functional Requirements

FR1: Datenverlust bei Umbenennung verhindern, indem zuerst die neue Konfiguration gespeichert und erst danach die alte geloescht wird.
FR2: Keine Schreibzugriffe im Response-Listener; automatisches Deaktivieren abgelaufener Tags aus dem Listener entfernen und in geeigneten Service/Job verlagern.
FR3: Model\Tag\Config\Dao::getByName() mit einheitlicher Typisierung und konsistentem Fehlerhandling implementieren.
FR4: Dynamisches Parameter-Handling im Controller und Frontend (keine Begrenzung auf 5 Parameter).
FR5: HTML-Manipulation modernisieren durch Ersatz von simple_html_dom.php (z. B. Symfony DomCrawler mit HTML5-Parser).
FR6: Cache-Layer fuer Tag\Config\Listing implementieren, um haeufiges Parsen zu vermeiden.
FR7: Injektions-Logik aus TagManagerListener in einen dedizierten Service auslagern (z. B. TagInjectionService).
FR8: Modernes Data Binding ueber Symfony Serializer oder Form-Component statt manuelles Mapping.
FR9: Vollstaendige PHP-8-Typsierung (Property/Return Types) fuer oeffentliche APIs sicherstellen.
FR10: Benennungen konsistent machen (disabled vs temporarily_disabled).
FR11: Konfig-Keys saeubern (keine ExtJS-IDs wie myId als Schluessel).
FR12: Symfony Request-Objekt statt $_GET/$_POST verwenden.
FR13: UX: Umbenennen von Tags im Admin-Panel ermoeglichen (UI-Feld aktivieren).
FR14: FOS-Routes Integration pruefen und verbessern.

### Non-Functional Requirements

NFR1: Keine Regressionen in bestehenden Tag-Flows.
NFR2: Aenderungen in den relevanten Komponenten dokumentieren.

### Additional Requirements

- Bestehendes Bundle als Baseline beibehalten; keine Neu-Initialisierung erforderlich.
- Ziel-Kompatibilitaet: PHP 8.4 und Pimcore 11; aktuell Pimcore 10.x/Symfony 5.x.
- Cache-Strategie: Tag-basiertes Caching ohne TTL mit expliziten Cache-Tags und Domain-Tag-Invalidierung.
- Zentraler Service `TagConfigService` kapselt Parsing, Normalisierung und Cache-Lookups.
- Datenhaltung bleibt dateibasiert im bestehenden Format.
- HTML-Parsing mit Symfony DomCrawler + HTML5-Parser (masterminds/html5) fuer DOM-Positionsangaben.
- JSON-Response-Format einheitlich `{success, data, error}`; Fehler `{code, message}`.
- Permission `tag_snippet_management` muss in Pimcore registriert und geprueft werden.
- Inline-Rename: direkte Validierung vor Persist und Logging mit altem/neuem Tag-Namen (Log-Level info).
- Namens-/Key-Normalisierung: `snake_case`, Umlaute ae/oe/ue/ss, lowercase, Sonderzeichen entfernen.
- Admin-Endpunkte bleiben intern, keine Public-API.

### FR Coverage Map

FR1: Epic 1 - Datenverlust bei Umbenennung verhindern
FR2: Epic 1 - Keine Schreibzugriffe im Response-Listener
FR3: Epic 1 - getByName() typ- und fehlerkonsistent
FR4: Epic 3 - Dynamisches Parameter-Handling
FR5: Epic 2 - HTML-Manipulation modernisieren
FR6: Epic 2 - Cache-Layer Tag\Config\Listing
FR7: Epic 2 - Injektions-Logik aus Listener auslagern
FR8: Epic 2 - Modernes Data Binding
FR9: Epic 3 - PHP-8-Typsierung
FR10: Epic 3 - Naming-Konsistenz
FR11: Epic 3 - Konfig-Keys bereinigen
FR12: Epic 3 - Symfony Request statt Superglobals
FR13: Epic 3 - UI-Rename ermoeglichen
FR14: Epic 4 - FOS-Routes pruefen/verbessern

## Epic List

### Epic 1: Fehler und kritische Logikmaengel
Ziel: Admins koennen Tags ohne Datenverlust oder Race-Conditions zuverlaessig verwalten.
**FRs covered:** FR1, FR2, FR3

### Epic 2: Verbesserungen
Ziel: Admins profitieren von stabilerer Performance und modernerer Verarbeitung bei gleicher Funktionalitaet.
**FRs covered:** FR5, FR6, FR7, FR8

### Epic 3: Unschoenheiten
Ziel: Admin-Workflows sind konsistenter, moderner und weniger fehleranfaellig im taeglichen Gebrauch.
**FRs covered:** FR4, FR9, FR10, FR11, FR12, FR13

### Epic 4: Sonstiges
Ziel: Administrativer Betrieb bleibt kompatibel und integrativ mit bestehenden Routen-Setups.
**FRs covered:** FR14

## Epic 1: Fehler und kritische Logikmaengel

Admins koennen Tags ohne Datenverlust oder Race-Conditions zuverlaessig verwalten.

### Story 1.1: Sichere Umbenennung ohne Datenverlust

Als Admin
moechte ich Tag-Konfigurationen sicher umbenennen,
damit bei fehlgeschlagenem Speichern kein Datenverlust entsteht.

**Acceptance Criteria:**

**Gegeben** eine bestehende Tag-Konfiguration
**Wenn** ich den Namen aendere und das Speichern fehlschlaegt
**Dann** bleibt die alte Konfiguration unveraendert erhalten
**Und** es erfolgt keine Loeschung vor erfolgreichem Speichern

### Story 1.2: Keine Persistenz im Response-Listener

Als Admin
moechte ich eine stabile Tag-Verarbeitung ohne Schreibzugriffe im Response-Listener,
damit keine Race-Conditions auftreten.

**Acceptance Criteria:**

**Gegeben** der Response-Listener `TagManagerListener::onKernelResponse`
**Wenn** die Seite gerendert wird
**Dann** werden keine Persistenzoperationen ausgefuehrt
**Und** das automatische Deaktivieren abgelaufener Tags ist in einen Service/Job verlagert

### Story 1.3: Konsistentes `getByName()`-Verhalten

Als Admin
moechte ich konsistente Rueckgabetypen und einheitliches Fehlerverhalten bei `getByName()`,
damit Tag-Konfigurationen zuverlaessig geladen werden.

**Acceptance Criteria:**

**Gegeben** `Model\Tag\Config\Dao::getByName()`
**Wenn** ein existierender Name abgefragt wird
**Dann** wird ein konsistenter, dokumentierter Typ zurueckgegeben
**Und** bei nicht vorhandenem Namen ist das Fehlerverhalten einheitlich und nachvollziehbar

## Epic 2: Verbesserungen

Admins profitieren von stabilerer Performance und modernerer Verarbeitung bei gleicher Funktionalitaet.

### Story 2.1: Modernes HTML-Parsing

Als Admin
moechte ich, dass die HTML-Manipulation mit einer modernen Library erfolgt,
damit das Parsing robust und wartbar bleibt.

**Acceptance Criteria:**

**Gegeben** die bisherige Nutzung von `simple_html_dom.php`
**Wenn** HTML verarbeitet wird
**Dann** erfolgt das Parsing mit DomCrawler + HTML5-Parser
**Und** `simple_html_dom.php` wird nicht mehr verwendet

### Story 2.2: Cache-Layer fuer Tag\Config\Listing

Als Admin
moechte ich schnellere Tag-Konfigurationen,
damit wiederholte Requests kein unnoetiges Dateiparsing ausloesen.

**Acceptance Criteria:**

**Gegeben** wiederholte Zugriffe auf `Tag\Config\Listing`
**Wenn** die Konfiguration bereits im Cache ist
**Dann** wird kein erneutes Parsen durchgefuehrt
**Und** der Cache ist tag-basiert ohne TTL

### Story 2.3: Injektionslogik und Data Binding modernisieren

Als Admin
moechte ich eine klar getrennte Injektionslogik und modernes Data Binding,
damit Wartung und Erweiterungen einfacher werden.

**Acceptance Criteria:**

**Gegeben** `TagManagerListener`
**Wenn** Tag-Injektionen ausgefuehrt werden
**Dann** erfolgt die Logik in einem dedizierten Service
**Und** das Data Binding nutzt Serializer oder Form-Component statt manuelles Mapping

## Epic 3: Unschoenheiten

Admin-Workflows sind konsistenter, moderner und weniger fehleranfaellig im taeglichen Gebrauch.

### Story 3.1: Dynamische Parameter & modernes Request-Handling

Als Admin
moechte ich beliebig viele Parameter verwalten koennen,
damit keine kuenstlichen Limits meine Konfigurationen einschraenken.

**Acceptance Criteria:**

**Gegeben** die Tag-Parameterverwaltung
**Wenn** mehr als 5 Parameter eingegeben werden
**Dann** werden sie korrekt verarbeitet und gespeichert
**Und** das Request-Handling nutzt das Symfony Request-Objekt statt Superglobals

### Story 3.2: Konsistente Keys & Benennungen mit rollender Umstellung

Als Admin
moechte ich konsistente Benennungen und bereinigte Konfig-Keys,
damit Konfigurationen wartbar und eindeutig bleiben.

**Acceptance Criteria:**

**Gegeben** bestehende Konfigurationen mit vorhandenen Keys
**Wenn** Konfigurationen gelesen oder aktualisiert werden
**Dann** bleiben bestehende Keys weiterhin gueltig
**Und** neue/aktualisierte Keys werden in `snake_case` normalisiert
**Und** die Umstellung ist rollend moeglich, ohne bestehende Systeme zu brechen
**Und** Lesen ist tolerant (alte Keys werden akzeptiert)
**Und** Schreiben normiert (Keys werden beim Speichern normalisiert)
**Und** alte und neue Keys koennen in einer Uebergangszeit koexistieren
**Und** Benennungen wie `disabled`/`temporarily_disabled` sind vereinheitlicht

### Story 3.3: Admin-UI Umbenennen & PHP-8-Typsierung

Als Admin
moechte ich Tags im Admin-Panel direkt umbenennen koennen,
damit der Workflow ohne Loeschen/Neuanlage funktioniert.

**Acceptance Criteria:**

**Gegeben** das Admin-Panel fuer Tags
**Wenn** der Name bearbeitet wird
**Dann** ist das Umbenennen moeglich und validiert
**Und** oeffentliche APIs sind vollstaendig mit PHP-8-Typsierungen versehen

## Epic 4: Sonstiges

Administrativer Betrieb bleibt kompatibel und integrativ mit bestehenden Routen-Setups.

### Story 4.1: FOS-Routes-Nutzung im Admin-Bereich evaluieren und optional vorbereiten

Als Admin
moechte ich eine klare und kompatible Routing-Basis im Admin-Bereich,
damit FOS-Routes optional nutzbar ist, ohne andere Bundles zu beeintraechtigen.

**Acceptance Criteria:**

**Gegeben** der Pimcore-Admin-Bereich (ExtJS)
**Wenn** die Routing-Integration geprueft wird
**Dann** ist dokumentiert, ob FOS-Routes dort ueberhaupt genutzt/unterstuetzt wird
**Und** falls eine Voraussetzung geschaffen wird, beeintraechtigt sie keine anderen Bundles
**Und** die Loesung ist zukunftssicher, sodass eine spaetere Pimcore-Unterstuetzung ohne groessere Aenderungen nutzbar waere
