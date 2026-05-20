---
stepsCompleted:
- 1
- 2
- 3
- 4
- 5
- 6
- 7
- 8
inputDocuments:
- _bmad-output/planning-artifacts/prd.md
workflowType: architecture
project_name: tag-management-bundle
user_name: Thomas
date: '2026-02-23T12:19:14+01:00'
lastStep: 8
status: complete
completedAt: '2026-02-23T15:22:17.567903'
---

# Architecture Decision Document

_This document builds collaboratively through step-by-step discovery. Sections are appended as we work through each architectural decision together._

## Project Context Analysis

### Requirements Overview

**Functional Requirements:**
- Fehlerfreie Tag-Konfigurationen ohne Datenverlust bei Umbenennungen.
- Dynamisches Parameter-Handling (nicht limitiert auf 5 Parameter).
- Admin-UI erlaubt Tag-Umbenennung ohne Delete/Create.
- Tag-Injektion bleibt funktional, aber Logik wird in dedizierten Service verschoben.

**Non-Functional Requirements:**
- Stabilitaet: Keine Schreibzugriffe im Response-Listener.
- Performance: Caching fuer Tag\Config\Listing zur Reduktion von Parsing-Last.
- Wartbarkeit: Moderne Libraries (HTML-Parsing), Symfony-Serializer/Form-Binding, PHP-8-Typsierung.
- Konsistenz: Einheitliches Fehlerhandling und Typisierung in DAOs.

**Scale & Complexity:**
- Primary domain: Backend / Symfony Bundle
- Complexity level: Medium
- Estimated architectural components: 4-6 (Controller, Listener, Injection Service, DAO/Listing, Cache Layer, UI)

### Technical Constraints & Dependencies

- Ersatz von `simple_html_dom.php` durch `masterminds/html5` oder `symfony/dom-crawler`.
- Cache-Layer fuer `Tag\Config\Listing`.
- Auslagerung von Listener-Logik in einen neuen Service (z. B. `TagInjectionService`).
- Nutzung von Symfony Serializer oder Form-Components fuer Data Binding.
- Pruefung/Verbesserung der FOS-Routes Integration.

### Cross-Cutting Concerns Identified

- Datenintegritaet und konsistentes Fehlerhandling.
- Performance-Optimierung (Cache).
- Wartbarkeit/Modernisierung (Libraries, Typsierung, Naming).
- UX-Verbesserungen ohne Regressionen in bestehenden Tag-Flows.

## Starter Template Evaluation

### Primary Technology Domain

Backend / Symfony Bundle (Pimcore Bundle Refactor) basierend auf den Projektanforderungen.

### Starter Options Considered

1) **Bestehendes Bundle als Baseline**
   - Empfehlung fuer Refactor/Modernisierung ohne Neuaufsetzen.
   - Minimiert Migrationrisiken und erhaelt vorhandene Bundle-Struktur.

### Selected Starter: Bestehendes Bundle

**Rationale for Selection:**
- Das Bundle ist bereits funktional und erprobt.
- Refactor und Veroeffentlichung sollen auf der bestehenden Codebasis aufbauen.
- Kein Generator notwendig.

**Initialization Command:**

```bash
# Nicht erforderlich - Refactor basiert auf bestehender Codebasis
```

**Architectural Decisions Provided by Starter:**

**Language & Runtime:**
- PHP 8.1 (aktuell), Ziel: PHP 8.4 mit Pimcore 11 (Ziel-Kompatibilitaet).

**Framework:**
- Pimcore 10.x / Symfony 5.x (aktuell), langfristig Pimcore 11 / neues Symfony.

**Caching:**
- Pimcore Cache (`Pimcore\Cache`) auf PSR-6 tag-aware Cachepool.

**Parsing/DOM:**
- Symfony DomCrawler mit HTML5-Parser (masterminds/html5), um detaillierte DOM-Positionsangaben zu ermoeglichen.

**Data Binding:**
- Praeferenz Serializer (Form-Component nur falls zwingend).

**Note:** Projektinitialisierung ist nicht erforderlich; Refactor basiert auf bestehender Codebasis.

## Core Architectural Decisions

### Decision Priority Analysis

**Critical Decisions (Block Implementation):**
- Dateibasierte Datenhaltung im bestehenden Format bleibt erhalten.
- Tag-basiertes Caching ohne TTL mit expliziten Cache-Tags fuer CLI/API-Invalidierung.

**Important Decisions (Shape Architecture):**
- Parsing/Verarbeitung wird in einen dedizierten Service ausgelagert.
- Rolling Migration ueber Service-Abstraktion moeglich.

**Deferred Decisions (Post-MVP):**
- Konkrete Migrationsstrategie zu alternativen Datenquellen (DB/Hybrid).

### Data Architecture

- **Data Source:** Zwingend dateibasiert (bestehendes Format bleibt kompatibel).
- **Data Access Layer:** Dedizierter Service (z. B. `TagConfigService` / `TagConfigParser`) kapselt Parsing, Normalisierung und Cache-Lookups.
- **Migration Path:** Service-Abstraktion ermoeglicht spaetere Rolling-Migration ohne Aenderungen an Aufrufern.
- **Caching Strategy:** Tag-basiert ohne TTL.
- **Cache Tags (technisch):** Separate Cache-Tags fuer gezielte Invalidierung via CLI/API.
- **Domain-Tag Invalidation:** Aenderungen am Domain-Tag invalidieren zugehoerige Cache-Eintraege automatisch.

### Authentication & Security

- **AuthN/AuthZ:** Pimcore/Symfony-Security bleibt Standard (Bundle ist Teil der Admin-Oberflaeche).
- **Endpoints:** Vorhandene Admin-Endpunkte bleiben abgesichert; zusaetzliche Schutzmassnahmen fuer sicherheitskritische Bereiche werden geprueft.
- **Permissions:**
  - `tag_snippet_management` wird als explizite Pimcore-Permission eingefuehrt.
  - Der aktuelle Code-Check (`$this->checkPermission('tag_snippet_management')`) bleibt, aber die Permission muss fuer neue Installationen in Pimcore registriert werden.

### API & Communication Patterns

- **JSON-Response-Format:** Einheitlich `{success, data, error}`.
- **Public Endpoints:** Nicht erlaubt; nur interne Admin-Endpunkte.
- **Rate Limiting:** Nicht erforderlich (Admin-Absicherung ausreichend).

### Frontend Architecture

- **UI-Aenderung:** Minimal-invasiv im bestehenden Admin-Formular.
- **Inline-Rename:** Feld aktivieren, direkte Validierung, kein neuer Dialog-Flow.
- **Parameter-Handling:** Dynamische Liste mit Add/Remove Controls.
- **Security:** Permission-Check bleibt verpflichtend.
- **Logging:** Umbenennung erzeugt Log-Eintrag (Audit-Pfad).
- **Warnhinweis (optional):** Hinweis, falls Umbenennung bestehende Injektionen beeinflusst.

### Infrastructure & Deployment

- **CI/CD:** Keine Pipeline geplant.
- **Tests:** Nicht verpflichtend im Scope (separates Projekt).
- **Logging/Monitoring:** Keine speziellen Vorgaben.
- **Kompatibilitaet:** Fokus Pimcore 10.x; Pimcore 11 als naechster Schritt. Parallel-Branches sind eigenes Projekt, sollen aber architektonisch vorbereitet werden.

## Implementation Patterns & Consistency Rules

### Pattern Categories Defined

**Critical Conflict Points Identified:** 6 Bereiche

### Naming Patterns

**Code Naming Conventions:**
- Klassen/Services: `TagConfigService` (PascalCase)
- Variablen: `camelCase`
- Dateinamen: PSR-4 (z. B. `TagConfigService.php`)

**Config Keys & Domain Tag IDs:**
- Config-Keys: `snake_case`
- Domain-Tag-Key wird **automatisch** aus dem Namen erzeugt:
  - Umlaute: `ae`, `oe`, `ue`, `ss`
  - Separator: `_`
  - Alles **lowercase**
  - Alle Zeichen ausserhalb `[a-z0-9]` entfernen
  - Mehrfach-Separatoren zu einem `_` komprimieren
  - Leerzeichen -> `_`

### Structure Patterns

**Project Organization:**
- Services liegen in `src/Service/`
- Parsing/Normalisierung/Cache-Lookup gehoeren in `TagConfigService`

### Format Patterns

**API Response Formats:**
- Einheitlich `{success, data, error}`
- Fehler: `error: {code, message}`

**Data Exchange Formats:**
- JSON-Keys: `snake_case`
- Booleans: `true/false`

### Communication Patterns

**Logging:**
- Log-Level: `info`
- Log-Eintrag enthaelt alten und neuen Tag-Namen

### Process Patterns

**Validation:**
- Automatische Key-Generierung aus Name bei Create/Update
- Inline-Rename validiert Name vor Persist

### Enforcement Guidelines

**All AI Agents MUST:**
- `TagConfigService` als zentrale Zugriffsschicht nutzen
- `snake_case` fuer Config-/JSON-Keys verwenden
- Key-Normalisierung exakt nach definierter Regel implementieren

**Pattern Enforcement:**
- Review-Checkliste in Code Review: Naming/Keys/Service-Pfad/Response-Format
- Pattern-Abweichungen als Review-Issues dokumentieren

### Pattern Examples

**Good Examples:**
- Name: `Kaese Oel` -> Key: `kaese_oel`
- Response: `{success: true, data: {...}, error: null}`

**Anti-Patterns:**
- `KaeseOel` -> `KaeseOel` (nicht normalisiert)
- Response ohne `success`-Feld

## Project Structure & Boundaries

### Complete Project Directory Structure
```

tag-management-bundle/
├── src/
│   └── TagManagementBundle/
│       ├── WeblizardsTagManagementBundle.php
│       ├── DependencyInjection/
│       │   └── WeblizardsTagManagementExtension.php
│       ├── Controller/
│       │   └── Admin/
│       │       └── TagManagementController.php
│       ├── EventListener/
│       │   └── TagManagerListener.php
│       ├── Model/
│       │   └── Tag/
│       │       ├── Config.php
│       │       └── Config/
│       │           ├── Dao.php
│       │           ├── Listing.php
│       │           └── Listing/
│       │               └── Dao.php
│       ├── Migrations/
│       │   ├── AbstractTranslationMigration.php
│       │   └── Version20260218082024.php
│       └── Resources/
│           ├── config/
│           │   ├── services.yml
│           │   └── pimcore/
│           │       ├── config.yml
│           │       └── routing.yml
│           └── public/
│               └── js/
│                   └── pimcore/
│                       ├── startup.js
│                       └── settings/
│                           └── tagmanagement/
│                               ├── panel.js
│                               └── item.js
```

### Architectural Boundaries

**API Boundaries:**
- Admin-Endpunkte in `Controller/Admin/TagManagementController.php`
- Zugriffe nur innerhalb Pimcore-Admin-Kontext (keine Public-API)

**Component Boundaries:**
- UI/ExtJS in `Resources/public/js/...`
- Server-Side Logik in `Controller/`, `EventListener/`, `Model/`

**Service Boundaries:**
- Neuer Service (`TagConfigService`) unter `src/TagManagementBundle/Service/`
- Service kapselt Parsing/Normalisierung/Cache-Lookup und trennt Controller/Listener von Datenquelle

**Data Boundaries:**
- Dateibasierte Configs ueber `Model/Tag/Config` und `Dao`
- Cache-Zugriff ueber Pimcore Cache (`Pimcore\Cache`)

### Requirements to Structure Mapping

**Feature Mapping:**
- Tag-CRUD & Umbenennung: `Controller/Admin/TagManagementController.php`
- Listener-Refactor/Injektion: `EventListener/TagManagerListener.php` + `Service/TagConfigService.php`
- Config-Zugriff/Caching: `Model/Tag/Config*` + `Service/TagConfigService.php`
- Admin-UI-Aenderungen: `Resources/public/js/pimcore/settings/tagmanagement/*`

**Cross-Cutting Concerns:**
- Permission `tag_snippet_management`: Controller + Pimcore config (`Resources/config/pimcore/config.yml`)
- Logging (Rename): Controller/Service

### Integration Points

**Internal Communication:**
- Controller/Listener rufen `TagConfigService` auf
- Service nutzt DAO + Cache

**External Integrations:**
- Pimcore Cache (`Pimcore\Cache`)
- Pimcore Admin-UI und Routing

**Data Flow:**
- Admin-UI -> Controller -> Service -> DAO/Cache -> Response

### File Organization Patterns

**Configuration Files:**
- Bundle-Services: `Resources/config/services.yml`
- Pimcore-Config/Routing: `Resources/config/pimcore/*.yml`

**Source Organization:**
- Domain-Model & DAO unter `Model/`
- Service-Layer unter `Service/` (neu)

**Test Organization:**
- Keine Tests vorhanden (derzeit ausserhalb Scope)

**Asset Organization:**
- Admin-UI Assets unter `Resources/public/js/...`

## Architecture Validation Results

### Coherence Validation ✅

**Decision Compatibility:**
- Dateibasierte Datenhaltung + Service-Abstraktion + Cache-Tags sind konsistent.
- AuthN/AuthZ bleiben Pimcore-konform, keine Public-API.
- JSON-Responses und Naming-Regeln widersprechen keiner Technologieentscheidung.

**Pattern Consistency:**
- Naming/Key-Regeln konsistent mit Services (`TagConfigService`) und PSR-4.
- `snake_case` fuer Config/JSON passt zur bestehenden Konfiguration.
- Cache-Invalidation klar definiert (Domain-Tag + technische Cache-Tags).

**Structure Alignment:**
- Projektstruktur deckt alle neuen Komponenten ab (Service-Layer, UI-Assets).
- Grenzen zwischen Controller/Listener/Service/Model klar definiert.

### Requirements Coverage Validation ✅

**Functional Requirements Coverage:**
- Datenverlust-Bug: durch Service-Refactor + Reihenfolge fixbar.
- Race-Conditions: Listener-Schreibzugriffe entfernen/auslagern.
- Typsicherheit: DAO-Konventionen festgelegt.
- Dynamische Parameter: UI-Pattern und Server-Seite vorgesehen.
- Tag-Rename UI: Inline-Rename + Logging.
- HTML-Parsing modernisiert: DomCrawler + masterminds/html5 vorgesehen.
- Caching: Pimcore Cache integriert.

**Non-Functional Requirements Coverage:**
- Stabilitaet: Listener-Write-Verbot, Service-Abstraktion.
- Performance: Cache-Layer.
- Wartbarkeit: klare Patterns, PSR-4, Service-Layer.

### Implementation Readiness Validation ✅

**Decision Completeness:**
- Kritische Entscheidungen dokumentiert (Datenhaltung, Cache, Security, API-Format).
- Versionsstrategie: Pimcore 10.x jetzt, 11 spaeter.

**Structure Completeness:**
- Vollstaendige Projektstruktur dokumentiert.
- Service-Layer klar verortet.

**Pattern Completeness:**
- Naming/Key-Regeln, Response-Format, Logging und Validierung spezifiziert.

### Gap Analysis Results

**Critical Gaps:** Keine
**Important Gaps:**
- Konkrete CLI/API-Invalidation Mechanik fuer Cache-Tags (Design offen, aber vorgesehen).
- Optionaler Warnhinweis bei Umbenennung (UI-Logik noch zu definieren).

**Nice-to-Have:**
- Audit-Log-Spezifikation (Format, Ziel).

### Validation Issues Addressed

- Permission `tag_snippet_management` muss in Pimcore registriert werden.

### Architecture Completeness Checklist

**✅ Requirements Analysis**
- [x] Project context thoroughly analyzed
- [x] Scale and complexity assessed
- [x] Technical constraints identified
- [x] Cross-cutting concerns mapped

**✅ Architectural Decisions**
- [x] Critical decisions documented with versions
- [x] Technology stack fully specified
- [x] Integration patterns defined
- [x] Performance considerations addressed

**✅ Implementation Patterns**
- [x] Naming conventions established
- [x] Structure patterns defined
- [x] Communication patterns specified
- [x] Process patterns documented

**✅ Project Structure**
- [x] Complete directory structure defined
- [x] Component boundaries established
- [x] Integration points mapped
- [x] Requirements to structure mapping complete

### Architecture Readiness Assessment

**Overall Status:** READY FOR IMPLEMENTATION
**Confidence Level:** high

**Key Strengths:**
- Konsistente Datenhaltung + Cache-Strategie
- Klare Service-Abstraktion
- Minimale, risikoarme UI-Aenderungen

**Areas for Future Enhancement:**
- Cache-Invalidation via CLI/API formalisieren
- Audit-Logging-Standard

### Implementation Handoff

**AI Agent Guidelines:**
- Entscheidungen exakt befolgen
- Patterns konsistent anwenden
- Struktur und Grenzen respektieren

**First Implementation Priority:**
- `TagConfigService` einziehen und Controller/Listener darauf umstellen
