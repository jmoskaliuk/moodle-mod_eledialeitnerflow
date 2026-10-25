# changelog — technische Änderungen je Plugin-Version

Technische Sicht: was sich pro `$plugin->version` am Schema, an den Callbacks
und an den mitgelieferten Daten geändert hat.

**Abgrenzung**

| Datei | Sicht | Zielgruppe |
|---|---|---|
| `../CHANGELOG.md` | ausgeliefert, je Release (`1.0.0`) | Anwendende, Plugins Directory |
| **diese Datei** | je `$plugin->version`, inkl. Schema und Upgrade-Schritten | Entwicklung, Betrieb |
| `../upgrade.txt` | API-Brüche für Integrationen | Fremdcode, der auf uns aufsetzt |
| `releasenotes.md` | Auslieferungssicht je `relXX` | Betrieb, Kommunikation |

Ob diese Aufteilung so bleibt, ist als `q06` offen. `upgrade.txt` widerspricht
derzeit der ausgelieferten Versionsnummer → `task03`.

---

## 2026042101 — Release 1.0.0 (rel01), 2026-04-21

**Version:** `$plugin->version = 2026042101`, `$plugin->release = '1.0.0'`,
`MATURITY_STABLE`, `requires = 2024042200`

- Reifegrad von `MATURITY_BETA` (0.9.0) auf `MATURITY_STABLE` gehoben.
- Versionszählung für die Erstveröffentlichung auf `1.0.0` zurückgesetzt; die
  interne Vorgeschichte reichte bis 2.3.0 (`adr07`).
- **Upgrade-Schritt `2026042101`:** `tour_installer::reimport_bundled_tours()` —
  entfernt sämtliche über den früheren, nicht idempotenten Importpfad
  angesammelten LeitnerFlow-Touren und importiert einmalig neu.
- `\mod_eledialeitnerflow\local\tour_installer` eingeführt; der Tour-Import läuft
  jetzt lazy beim Seitenaufruf statt in `xmldb_eledialeitnerflow_install()`
  (`adr08`).
- `.gitattributes`: `docs/`, `bin/`, `cli/`, `tests/`, `phpunit.xml`,
  `.github/` und die nicht-englischen Sprachpakete als `export-ignore` markiert
  (`adr09`).
- Kein Schemawechsel.

---

## 2024120124

- **Upgrade-Schritt:** verwaiste `grade_items` mit `itemmodule = 'leitnerflow'`
  werden auf `eledialeitnerflow` migriert, sofern die zugehörige Instanz noch
  existiert; sonst werden `grade_items` und `grade_grades` entfernt.
- Behebt `coding_exception: Invalid component used in plugin/component_callback():
  mod_leitnerflow` beim Neuberechnen der Noten eines betroffenen Kurses — eine
  Altlast der Komponentenumbenennung (`adr06`).

## 2024120123

- **Upgrade-Schritt:** Lehrenden-Tour wird neu importiert. Nötig, weil Instanzen,
  die bereits auf `2024120121` standen, den ursprünglichen Import übersprungen
  hatten. Vorher werden Touren mit `pathmatch LIKE '%/report.php%'` entfernt.

## 2024120121

- **Schema:** `eledialeitnerflow.animationdelay` (INT, Standard `1000`).
- Lehrenden-Tour `db/usertours/eledialeitnerflow_teacher.json` ergänzt und
  importiert.

## 2024120120

- **Schema:** `eledialeitnerflow.showtour` (INT, Standard `1`).

## 2024120119

- **Schema:** `eledialeitnerflow_sessions.currentstreak` und `.beststreak`
  (INT, Standard `0`).
- `feedbackstyle` auf fünf Stufen erweitert (0 aus … 4 gamifiziert); die
  Bedeutung von Wert `2` bleibt gleich, daher keine Datenmigration.

## 2024120118

- Touren neu importiert: Hinweis „jede Frage ist eine Karte" ergänzt, die
  Sitzungshistorie wird jetzt über eine ID statt über eine Klasse adressiert.

## 2024120117

- **Schema:** `eledialeitnerflow.feedbackstyle` (INT, Standard `1`).

## 2024120116

- Touren neu importiert: Texte auf die „Mein"-Perspektive umgestellt, weitere
  Schritte ergänzt.

## 2024120115

- Touren neu importiert: `targettype` von `1` (Block) auf `0` (CSS-Selektor)
  korrigiert — die Schritte hatten sich zuvor an nichts festgemacht.

## 2024120114

- Core-Filter `multilang` wird bei Bedarf aktiviert
  (`_eledialeitnerflow_ensure_multilang_filter()`).
- Touren neu importiert, Übersetzungssyntax von `{mlang}` (benötigt
  `filter_multilang2`) auf die Core-HTML-Syntax `<span class="multilang">`
  umgestellt.

## 2024120113

- Touren neu importiert: fehlerhafte `configdata` aus früheren Importversuchen
  ersetzt; vorhandene LeitnerFlow-Touren werden vorher gelöscht.

## 2024120111

- Platzhalterschritt ohne eigene Wirkung; die Tour-Behandlung erfolgt im
  Folgeschritt.

## 2024120109

- **Schema:** `eledialeitnerflow.showanimation` (INT, Standard `1`).

## 2024120106

- **Schema:** `eledialeitnerflow.questioncategoryids` (TEXT) ergänzt.
- **Migration:** vorhandene `questioncategoryid`-Werte werden in das neue Feld
  kopiert. Das alte Feld bleibt für Rückwärtskompatibilität bestehen und wird
  weiterhin mit der ersten gewählten Kategorie synchron gehalten (`adr05`).

## 2024120100 und früher — interne Vorgeschichte

- Komponente von `mod_leitnerflow` nach `mod_eledialeitnerflow` umbenannt;
  Tabellen, Capabilities, Sprachstrings und Funktionen entsprechend
  (`adr06`). Details in `../upgrade.txt`, Abschnitte `2.0.0` bis `2.3.0` —
  deren Versionsnummern sind gegenüber dem ausgelieferten Release irreführend
  (`task03`).

---

## Konventionen für neue Einträge

1. Überschrift ist die `$plugin->version`, danach ggf. Release und Datum.
2. Schemaänderungen mit **Schema:** kennzeichnen, Tabelle, Feld, Typ, Standard.
3. Migrationsschritte mit **Upgrade-Schritt:** kennzeichnen und beschreiben,
   was mit vorhandenen Daten geschieht.
4. Bricht eine öffentliche API, gehört derselbe Sachverhalt zusätzlich in
   `../upgrade.txt`.
5. Ändert sich nutzerseitig etwas, gehört es zusätzlich in `../CHANGELOG.md`
   und in `releasenotes.md`.
6. Tour-Änderungen brauchen seit `tour_installer` keinen eigenen
   Upgrade-Schritt mehr — ein Aufruf von `reimport_bundled_tours()` genügt.


### Unreleased · 24.10.2026

- Letzte Antwort zeigt vor dem Sitzungsabschluss ihre aktivierte Rückmeldung mit manuellem Weiter.
- Ausführlicher Rückmeldungsmodus zeigt allgemeine Fragenerklärungen.


### Unreleased · Szenen in LeitnerFlow

Optionaler Szenen-Dateibereich mit Einstieg vor dem Training und aufklappbarer Szene während der Fragen/Rückmeldungen. Dateien über Moodle File API und Backup/Restore. Keine Schemaänderung.


## 2026102400 — F07 guided/review (lokaler Integrationsstand)

Additive Sessionfelder (mode=review als Altbestand-Default, sceneid, sceneversion, phase, surface, scenedata, visited) und neue Slotbindungstabelle. Serverautoritativer QE-Ablauf mit getrenntem Guided und Review, idempotenter Kartenwertung, Antwortspeicherung und expliziter Feedbackphase. Direkter veröffentlichter Szenenrenderer, 90-Tage-Nachlese, erweiterte Privacy-/Lifecycle-/Backup-Verarbeitung. Mindestversion unverändert. Kein Release oder Push durchgeführt.

## 2026102401–2026102402 — Trennung des lokalen Fahrup-Piloten

Generische autorisierte QE-Review-API mit eindeutigen Belegen und gemeinsamer Kartenschreibsperre. Szenen und Übungen nach eigenem `mod_fahrup` überführt; Pilotdaten vor Schema-Bereinigung geprüft migrieren. Vorhandene Kartenstände nicht neu werten. Frische Installation enthält keine Szenendaten. Backup/Restore und Privacy umfassen generische Provenienz; keine Fahrup-Abhängigkeit in LeitnerFlow. Lokal geprüft, keine Releasefreigabe oder Remoteveröffentlichung.
