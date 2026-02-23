# PRD: TagManagementBundle – Stabilitaet, Performance, Architektur & UX

## 1. Ueberblick
- Ziel: Stabilisieren, modernisieren und verbessern des Tag-Management-Bundles durch Bugfixes, Performance/Architektur-Verbesserungen und UX-Optimierungen.
- Zielgruppe: Administratoren mit technischem Background (Admin-Panel-Nutzer).
- Nicht-Ziele: Funktionsneuentwicklung ausserhalb der TODO-Liste; grundlegender UI-Relaunch.

## 2. Problemstatement
- Kritische Bugs koennen zu Datenverlust und inkonsistentem Verhalten fuehren.
- Architektur- und Performance-Schwaechen erzeugen unnoetige Last und technische Schulden.
- UX-Einschraenkungen erschweren Admin-Workflows (z. B. Umbenennen von Tags).

## 3. Ziele
- Beseitigung der kritischen Fehler.
- Reduktion von technischem Risiko durch sauberere Architektur.
- Verbesserte Bedienbarkeit im Admin-Panel.

## 4. Priorisierte Anforderungen

### 4.1 Hohe Prioritaet (Bugs)
1. Datenverlust bei Umbenennung vermeiden
   - Problem: TagManagementController::updateAction loescht alte Konfiguration vor erfolgreichem Speichern.
   - Anforderung: Reihenfolge aendern: zuerst neu speichern, erst danach alt loeschen.
2. Race-Conditions im Listener vermeiden
   - Problem: Schreibzugriffe via $tag->save() in onKernelResponse.
   - Anforderung: Automatisches Deaktivieren abgelaufener Tags aus Response-Listener entfernen (in geeigneten Service/Job verlagern).
3. Typsicherheit in DAO konsistent
   - Problem: Model\Tag\Config\Dao::getByName() inkonsistent.
   - Anforderung: Einheitliche Typisierung und Fehlerhandling.
4. Parameter-Handling dynamisch
   - Problem: Max 5 Parameter (0-4).
   - Anforderung: Dynamisches Handling im Controller + Frontend.

### 4.2 Mittlere Prioritaet (Performance & Architektur)
1. HTML-Manipulation modernisieren
   - Ersetzen von simple_html_dom.php durch moderne Library (z. B. masterminds/html5 oder symfony/dom-crawler).
2. Caching fuer Tag\Config\Listing
   - Implementierung Cache-Layer, um haeufiges Parsen zu vermeiden.
3. Injektions-Logik aus TagManagerListener auslagern
   - Dedizierter Service, z. B. TagInjectionService.
4. Modernes Data Binding
   - Nutzung Symfony Serializer oder Form-Components statt manuelles Mapping.

### 4.3 Niedrige Prioritaet (Code Style & UX)
1. Vollstaendige PHP-8-Typsierung (Property/Return Types).
2. Konsistente Benennung (disabled vs temporarily_disabled).
3. Konfig-Keys saeubern (keine ExtJS-IDs wie myId als Schluessel).
4. Symfony Request-Objekt statt $_GET/$_POST.
5. UX: Umbenennen von Tags im Admin-Panel ermoeglichen (UI-Feld aktivieren).

### 4.4 Sonstiges
- FOS-Routes Integration pruefen/verbessern.

## 5. Erfolgskriterien

### Bugs (Hohe Prioritaet)
- Kein Datenverlust bei Umbenennung: Bei fehlgeschlagenem Speichern bleibt die alte Konfiguration unveraendert erhalten (manueller Negativtest).
- Keine Schreibzugriffe im Response-Listener: TagManagerListener::onKernelResponse fuehrt keine Persistenzoperationen aus (Code-Check + optionaler Test).
- getByName() konsistent: Einheitliche Rueckgabetypen und Fehlerverhalten sind dokumentiert und in Tests abgedeckt.
- Parameter-Handling dynamisch: Controller und Frontend akzeptieren >5 Parameter ohne Fehler.

### Performance & Architektur (Mittlere Prioritaet)
- Entfernen von simple_html_dom.php: Keine Abhaengigkeit mehr; neue Library ist integriert und genutzt.
- Caching fuer Tag\Config\Listing: Wiederholte Requests verursachen kein erneutes Dateiparsing, solange Cache gueltig ist (messbar ueber Logging/Profiler).
- Injektions-Logik ausgelagert: TagManagerListener enthaelt keine Injektionslogik mehr, nur Orchestrierung.
- Data Binding modernisiert: Controller nutzt Symfony Serializer oder Form-Component statt manuelles Mapping.

### Code Style & UX (Niedrige Prioritaet)
- PHP 8-Typsierung: Oeffentliche APIs haben Property/Return Types; statische Analyse laeuft ohne neue Typwarnungen.
- Benennungen konsistent: disabled/temporarily_disabled vereinheitlicht, keine gemischten Keys.
- Konfig-Keys bereinigt: Keine ExtJS-IDs als Schluessel in gespeicherter PHP-Konfig.
- Symfony Request statt Superglobals: Kein $_GET/$_POST im Listener.
- UX Umbenennen: Admin-UI erlaubt Umbenennen ohne Loeschen/Neuanlage; Flow ist rueckwaertskompatibel.

### Sonstiges
- FOS-Routes: Integration geprueft, offene Punkte dokumentiert oder Fix umgesetzt.

## 6. Nicht-funktionale Anforderungen
- Keine Regressionen in bestehenden Tag-Flows.
- Aenderungen dokumentiert in den relevanten Komponenten.

## 7. Risiken & Abhaengigkeiten
- Wahl der HTML-Library kann Parsing-Verhalten veraendern.
- Auslagerung der Listener-Logik kann Timing/State-Effekte beeinflussen.
