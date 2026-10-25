# releasenotes — Auslieferungen

Was Nutzende und Betrieb über eine Auslieferung wissen müssen. Ein `relXX` ist
die Klammer um einen Satz Features und Fixes.

Die veröffentlichte Fassung eines Releases liegt als eigene Datei im Repo-Root
(`RELEASE-NOTES-v1.0.0.md`); diese Datei ist das Register mit der Betriebssicht.

---

## Register

| ID | Version | Datum | Reifegrad | Status |
|---|---|---|---|---|
| rel01 | 1.0.0 | 2026-04-21 | `MATURITY_STABLE` | veröffentlicht (Directory-Status siehe `q05`) |

Frühere interne Stände (bis 2.3.0) sind keine Releases im Sinne dieses
Registers, siehe `adr07`.

---

## rel01 — LeitnerFlow 1.0.0

**Datum:** 2026-04-21 · **Plugin-Version:** `2026042101` ·
**Veröffentlichte Fassung:** `../RELEASE-NOTES-v1.0.0.md`

### Umfang

Erstveröffentlichung. Enthalten sind `feat01` bis `feat14` — der vollständige
Produktumfang aus `product.md` §6:

- Karten aus einer oder mehreren Fragenkategorien, Sitzungen mit Sofort-Feedback
- konfigurierbares Leitner-Regelwerk (1–5 Fächer, Lernschwelle, drei Falsch-Modi)
- Auswahlstrategie und gezieltes Üben einzelner Fächer
- Lern-Dashboard, Sitzungshistorie, Trendanzeige
- fünf Feedback-Stile inklusive Gamified-Modus mit Serien
- Einführungstouren für Lernende und Lehrende
- Teilnehmendenbericht mit Reset einzelner Lernstände
- optionale Bewertung „Prozent gelernt"
- Backup und Restore, Kurs-Reset, Privacy-API, Event-Logging

### Voraussetzungen

| | |
|---|---|
| Moodle | 4.4 (`2024042200`) oder neuer, getestet bis 5.1 |
| PHP | 8.1 oder neuer, getestet bis 8.3 |
| Datenbank | MySQL 8 / MariaDB 10.6 / PostgreSQL 13+ |
| Optional | `tool_usertours` für die Einführungstouren |

Die beworbene Obergrenze ist nicht über `$plugin->supported` belegt →
`task02`, `q01`.

### Für den Betrieb wichtig

1. **Der Upgrade-Schritt `2026042101` greift in `tool_usertours` ein.** Er löscht
   alle LeitnerFlow-Touren und importiert sie einmalig neu. Sites mit einem
   Vor-1.0-Stand hatten durch den früheren, nicht idempotenten Importpfad
   Dubletten angesammelt; der Schritt räumt sie auf. Eigene Anpassungen an einer
   LeitnerFlow-Tour gehen dabei verloren.
2. **Der Core-Filter `multilang` wird bei der Installation aktiviert**, falls er
   aus ist — die Touren brauchen ihn für ihre Zweisprachigkeit. Das ist eine
   site-weite Einstellung.
3. **Das Release-ZIP enthält nur Englisch.** Deutsch liegt im Git-Repository und
   geht nach der Freigabe über AMOS (`adr09`).
4. **Keine externen Dienste, kein Composer.** Die Installation braucht keinen
   Netzwerkzugang.
5. **Drei neue Tabellen** werden angelegt: `eledialeitnerflow`,
   `eledialeitnerflow_card_state`, `eledialeitnerflow_sessions`.

### Bekannte Einschränkungen zum Zeitpunkt der Auslieferung

| Punkt | Bezug |
|---|---|
| Fächerzahl nachträglich zu verkleinern lässt Karten aus Anzeige und Priorisierung fallen | `bug01` |
| Der Kurs-Reset löscht Lerndaten, setzt aber die Note nicht zurück | `bug02` |
| Die Privacy-API erfasst Personen ohne Kartenstände nicht vollständig | `bug04`, `bug05` |
| Keine Screenshots in Doku und Katalog | `task01` |
| Moodle-App-Unterstützung nicht geprüft | `task06` |
| Kein Lastprofil für sehr große Fragenpools | `task07` |
| Barrierefreiheit (WCAG 2.2 / BFSG) nicht geprüft | `05-quality.md` |

Diese Punkte wurden beim DevFlow-Aufbau am 2026-08-27 erhoben, also **nach** der
Auslieferung. Sie sind keine Regressionen, sondern bislang unerfasste Lücken.

### Rückrollen

Keine Datenmigration im engeren Sinn, aber der Tour-Neuimport ist nicht
umkehrbar. Ein Downgrade unterstützt Moodle ohnehin nicht; im Ernstfall
Datenbank-Backup einspielen.

### Verifikation nach Deploy

`verify01`–`verify11` in `04-tasks.md`.

---

## Vorlage für das nächste Release

```
## relXX — LeitnerFlow <version>

**Datum:** YYYY-MM-DD · **Plugin-Version:** <version> · **Reifegrad:** <maturity>

### Umfang
Neue Features (featXX), behobene Bugs (bugXX), abgeschlossene Tasks (taskXX).

### Voraussetzungen
Änderungen an Moodle-, PHP- oder Datenbankanforderungen.

### Für den Betrieb wichtig
Schemaänderungen, Eingriffe in Core-Einstellungen, Laufzeit der Upgrade-Schritte,
Auswirkungen auf vorhandene Daten.

### Bekannte Einschränkungen
Offene bugXX und taskXX, die bewusst mit ausgeliefert werden.

### Rückrollen
Was passiert, wenn zurückgerollt werden muss.

### Verifikation nach Deploy
verifyXX-Liste.
```

Vor jeder Auslieferung die Release-Checkliste in `00-master.md` §7 durchgehen.


## F07-Integrationsstand zur lokalen Prüfung (24.10.2026)

Angeleitete F07-Übung zählt nicht für Leitnerboxen; spätere Wiederholungen zählen regulär. Drei bestehende Bankfragen, Rückmeldung nach jeder Frage und wiederaufnehmbare Auswahl/Szene. Konfiguration durch explizites F07-Manifest und Bankeintragszuordnung. Antworten nach Abschluss 90 Tage nachlesbar. Additives Datenbankupgrade erforderlich; vor Code-Downgrade ist eine Datenmigration/Wiederherstellung nötig. Lokal in Prüfung, kein veröffentlichter Release; PO-Abnahme separat.

## 2026102401–2026102402 — Trennung des lokalen Fahrup-Piloten

Generische autorisierte QE-Review-API mit eindeutigen Belegen und gemeinsamer Kartenschreibsperre. Szenen und Übungen nach eigenem `mod_fahrup` überführt; Pilotdaten vor Schema-Bereinigung geprüft migrieren. Vorhandene Kartenstände nicht neu werten. Frische Installation enthält keine Szenendaten. Backup/Restore und Privacy umfassen generische Provenienz; keine Fahrup-Abhängigkeit in LeitnerFlow. Lokal geprüft, keine Releasefreigabe oder Remoteveröffentlichung.
