# 00 — Master: mod_eledialeitnerflow (LeitnerFlow)

Eintrittspunkt des Projekt-DevFlows. Wer neu in eine Session einsteigt, liest
diese Datei zuerst, danach `product.md`, danach `04-tasks.md`.

Rahmen: [eLeDia.OS_DevFlow](https://github.com/jmoskaliuk/eLeDia.OS_DevFlow),
lokale Kopie unter `~/Documents/Code/eLeDia.OS_DevFlow`.

---

## 1. Projekt auf einen Blick

| | |
|---|---|
| Komponente | `mod_eledialeitnerflow` |
| Produktname | LeitnerFlow |
| Typ | Moodle-Aktivitätsmodul (`mod`) |
| Repository-Root | ist zugleich das Plugin-Verzeichnis (`mod/eledialeitnerflow`) |
| Release | Veröffentlichte Basis 1.0.0; lokaler Entwicklungsstand 1.0.1 / Schema 2026102402, kein neuer Release-Sign-off |
| Mindestanforderung | Moodle 4.4 (`2024042200`), PHP 8.1 |
| Lizenz | GPL v3 or later |
| Copyright | 2026 eLeDia GmbH |
| Upstream | https://github.com/jmoskaliuk/moodle-mod_eledialeitnerflow |

---

## 2. Wo liegt was

```
mod_eledia_leitnerflow/            ← Repo-Root = Plugin-Root
├── docs/                          ← DIESER DevFlow (per .gitattributes export-ignore)
│   ├── 00-master.md               Eintrittspunkt, Rollen, ADRs, Prompts
│   ├── product.md                 Produktidentität, Zielgruppen, Umfang
│   ├── product.de.md              Katalog-/PluginFlow-Ableitung von product.md
│   ├── 01-features.md             featXX mit Akzeptanzkriterien
│   ├── 02-user-doc.md             Bedienung (Quelle der Wahrheit für Nutzersicht)
│   ├── 03-dev-doc.md              Implementierung
│   ├── 04-tasks.md                🆕 Neu, taskXX, qXX, verifyXX
│   ├── 05-quality.md              bugXX, testXX
│   ├── screenshots.md             Bildinventar (shotXX)
│   ├── privacy.md                 Datenverarbeitung, Löschung, Rechte
│   ├── security.md                Schutzwerte, Bedrohungen, Maßnahmen
│   ├── changelog.md               Technische Änderungen je $plugin->version
│   ├── releasenotes.md            relXX — Auslieferungssicht
│   ├── screenshots/               Bildmaterial (aktuell leer → task01)
│   ├── user-doc.css               Styling des HTML-Exports
│   └── user-doc.html              generiertes Artefakt, nicht bearbeiten
├── CHANGELOG.md                   ausgeliefertes, nutzerseitiges Changelog
├── RELEASE-NOTES-v1.0.0.md        veröffentlichte Release Notes zu rel01
├── README.md / README-DE.md       Installations- und Überblicksdoku
└── upgrade.txt                    API-Änderungen für Integratoren
```

**Warum der DevFlow in `docs/` liegt** → `adr10`.

---

## 3. Prompt-Shortcuts

| Kürzel | Bedeutung |
|---|---|
| `#status` | Aktueller Stand: offene Tasks, offene `qXX`, Bugs nach Severity, Doku-Lücken |
| `#next` | Die nächsten 1–3 sinnvollsten Schritte, begründet |
| `#sync` | Betroffene DevFlow-Dateien nach einer Code-Änderung konsistent ziehen |
| `#done featXX` | Done-Checkliste (§6) gegen ein Feature prüfen und Lücken benennen |
| `#bug` | Befund als `bugXX` mit Severity und Reproduktion in `05-quality.md` anlegen |
| `#precheck` | `bin/precheck.sh` interpretieren und Befunde einsortieren |
| `#release` | Release-Checkliste (§7) durchgehen |

---

## 4. Architektur-Entscheidungen (ADRs)

### adr01 — Eigenes Aktivitätsmodul statt Erweiterung von `mod_quiz`

**Status:** akzeptiert · **Datum:** 2026-04 (Initial)

Ein Quiz ist eine Prüfung: Anfang, Ende, Note. Verteiltes Wiederholen über Wochen
lässt sich darin nur durch Vervielfachen von Instanzen abbilden. Ein eigenes
Modul erlaubt einen persistenten, personenbezogenen Kartenbestand ohne
Versuchsbegriff.

**Konsequenz:** Kein Zugriff auf Quiz-Reports, Quiz-Backup-Logik oder
Quiz-Zugriffsregeln; alles Genannte musste eigenständig gebaut werden.

### adr02 — Fragen über die Question Engine mit `immediatefeedback`

**Status:** akzeptiert

LeitnerFlow rendert Fragen nicht selbst, sondern nutzt `question_usage_by_activity`
mit dem Verhalten `immediatefeedback`. Jede Sitzung besitzt genau eine `qubaid`.

**Konsequenz:** Alle Fragetypen der Instanz funktionieren automatisch, inklusive
Bewertung und Feedback. Dafür hängt der Datenbestand an `question_usages`, die
bei Löschung (`delete_instance`, Reset, Privacy) explizit mit
`question_engine::delete_questions_usage_by_activity()` aufgeräumt werden müssen.

### adr03 — Kartenstatus in eigener Tabelle, nicht aus `question_attempts` abgeleitet

**Status:** akzeptiert

`eledialeitnerflow_card_state` hält pro (Instanz, Person, Frage) genau eine Zeile
mit `correctcount`, `attemptcount`, `currentbox`, `status` (Unique-Index
`eledialeitnerflowid,userid,questionid`).

**Konsequenz:** Dashboard und Auswahl sind mit wenigen Queries beantwortbar;
Sitzungen können gelöscht werden, ohne den Lernstand zu verlieren. Preis: der
Lernstand ist eine zweite Wahrheit neben den Frageversuchen und kann bei
manuellen Eingriffen auseinanderlaufen.

### adr04 — `correctcount` ist die Wahrheit, `currentbox` ist abgeleitet

**Status:** akzeptiert

`leitner_engine::calculate_box()` berechnet das Fach linear:
`floor(correctcount * boxcount / correcttolearn)`, geklemmt auf `[1, boxcount]`.
`currentbox` wird bei jeder Antwort mitgeschrieben, ist aber redundant.

**Konsequenz:** Die Fächerzahl einer Aktivität kann nachträglich geändert werden,
ohne dass Lernstände unbrauchbar werden — allerdings werden bestehende
`currentbox`-Werte nicht nachgezogen (→ `bug01`).

### adr05 — Mehrere Fragenkategorien als kommaseparierte Textspalte

**Status:** akzeptiert · ersetzt die ursprüngliche Einzelkategorie

`eledialeitnerflow.questioncategoryids` (TEXT) statt einer Relationstabelle.
`questioncategoryid` (INT) bleibt für Rückwärtskompatibilität bestehen und wird
mit der ersten gewählten Kategorie synchron gehalten.

**Konsequenz:** Kein Schema-Overhead, aber kein Fremdschlüssel: gelöschte
Kategorien werden nicht erkannt, sondern liefern schlicht keine Fragen mehr.
`leitner_engine::get_category_ids()` kapselt beide Felder.

### adr06 — Komponente umbenannt: `mod_leitnerflow` → `mod_eledialeitnerflow`

**Status:** akzeptiert · umgesetzt in 2.0.0 (intern)

Namensraum-Präfix `eledia` für alle eLeDia-Plugins im Plugins Directory.

**Konsequenz:** Alle Tabellen, Capabilities, Sprachstrings und Funktionen tragen
den neuen Namen. Upgrade-Schritt `2024120124` migriert bzw. entfernt verwaiste
`grade_items` mit `itemmodule = 'leitnerflow'`.

### adr07 — Versionszählung für die Erstveröffentlichung auf 1.0.0 zurückgesetzt

**Status:** akzeptiert · **Datum:** 2026-04-21

Intern existierten Stände bis 2.3.0. Für die erste öffentliche Auslieferung im
Plugins Directory wird `$plugin->release = '1.0.0'` gesetzt; `$plugin->version`
bleibt monoton (`2026042101`).

**Konsequenz:** `upgrade.txt` beschreibt weiterhin die internen Stände 2.0.0–2.3.0
und widerspricht damit dem ausgelieferten Release-String (→ `task03`).

### adr08 — User Tours als gebündeltes JSON, Import idempotent und lazy

**Status:** akzeptiert

Touren liegen als `db/usertours/*.json` im Plugin. Der Import läuft **nicht** in
`xmldb_eledialeitnerflow_install()`, sondern über
`\mod_eledialeitnerflow\local\tour_installer` beim ersten Seitenaufruf bzw.
über Upgrade-Schritte.

**Begründung:** Ein Import in `install.php` bringt `tool_usertours/manager.php`
während der Behat-/PHPUnit-Initialisierung zum Absturz.
**Konsequenz:** `install_bundled_tours()` muss idempotent bleiben; Schritt
`2026042101` räumt historisch entstandene Dubletten auf.

### adr09 — Nicht-englische Sprachpakete gehören nicht ins Release-ZIP

**Status:** akzeptiert

`.gitattributes` markiert `lang/de/` als `export-ignore`. Übersetzungen laufen
nach der Freigabe über AMOS (lang.moodle.org).

**Konsequenz:** Das ZIP aus `bin/release.sh` enthält nur `lang/en/`. Lokale
Installationen aus dem Git-Klon haben Deutsch weiterhin.

### adr10 — Der DevFlow liegt in `docs/`, nicht in einem separaten Ordner

**Status:** akzeptiert · **Datum:** 2026-08-27

Bei anderen eLeDia-Plugins liegt der DevFlow im Projekt-Root neben dem
Plugin-Verzeichnis, damit er nicht ins Release-ZIP wandert. Hier **ist** der
Repo-Root das Plugin-Verzeichnis; ein zusätzlicher Root-Ordner wäre Teil des
Plugins. `docs/` ist in `.gitattributes` bereits `export-ignore` und enthält
mit `02-user-doc.md` und `product.de.md` schon DevFlow-Dateien.

**Konsequenz:** `git archive` liefert weiterhin ein sauberes Release-ZIP ohne
Doku. Wer `.gitattributes` ändert, muss diesen Zusammenhang kennen.

### adr11 — Nur direkte Kategoriemitglieder, keine Unterkategorien

**Status:** akzeptiert

`leitner_engine::get_questions_from_category()` liest ausschließlich Fragen, deren
`question_bank_entries.questioncategoryid` exakt der gewählten Kategorie
entspricht — jeweils die höchste Version mit `status = 'ready'`.

**Konsequenz:** Wer Unterkategorien einbeziehen will, wählt sie einzeln aus. Das
ist explizit und vorhersagbar, kostet aber Klicks bei tiefen Kategoriebäumen.

### adr12 — CSS strikt unter `.path-mod-eledialeitnerflow` und Präfix `lf-`

**Status:** akzeptiert

`styles.css` schreibt ausschließlich unterhalb des Moodle-Body-Klassenpfads und
verwendet den Klassenpräfix `lf-`.

**Konsequenz:** Kein Style-Leakage in andere Plugins oder Themes; Voraussetzung
für die Freigabe im Plugins Directory.

---

### adr13 — Fahrup als eigenes Modul mit generischer LeitnerFlow-Schnittstelle

**Status:** vorgeschlagen (technischer Detailentwurf) · **Datum:** 2026-10-24.

Die Produktgrenze wurde vom Nutzer ausdrücklich bestätigt: `mod_fahrup` soll Szenen/Übung besitzen, LeitnerFlow bleibt eigenständig. Vorgeschlagenes technisches Modell: einseitige Pluginabhängigkeit Fahrup → LeitnerFlow, internes generisches PHP-Service-API mit QE-Prüfung und Deduplizierungsbelegen; keine Fahrup-spezifische Laufzeit in LeitnerFlow. Datenübernahme vor Entfernung der alten Kopplung, ohne Wiederanwendung bereits gezählter Ergebnisse. Details, Alternativen, Voraussetzungen und Migration: task19–24.

Planungsauftrag, kein Umbau-/Releaseauftrag. Historischer F07-Plan TD-01 (alles im bestehenden LeitnerFlow-Pilot) ist für das Zielkonzept überholt; vorhandene Implementierung bleibt bis zum kontrollierten Cutover Migrationsquelle.

---

## 5. Rollen in diesem Projekt

| Rolle | Wer | Schwerpunkt |
|---|---|---|
| Product Owner | Johannes Moskaliuk | Scope, Sign-off, Release, Submission, `product.md` |
| Architekt | Mensch, KI berät | ADRs oben |
| Implementer | KI primär | Code, Tests |
| Doc-Sync | KI primär | Diese Dateien konsistent halten |
| QA-Reviewer | Mensch, KI erzeugt Entwürfe | manuelle Verifikation, BFSG/WCAG |
| Triage | Mensch | „🆕 Neu" → `taskXX`, Priorität |

**KI darf alleine:** Code für freigegebene Tasks, Doku-Sync, Status-Reports.
**KI muss vorschlagen und warten:** neue Features, ADRs, neue Abhängigkeiten,
Lösch-Operationen, Änderungen am Nutzenversprechen.
**Nur Mensch:** Done-Sign-off, Release, Deploy, Submission, Privacy/Secrets.

Bei Unklarheit rät die KI nicht — die Frage landet als `qXX` in `04-tasks.md`.

---

## 6. Done-Checkliste (verbindlich)

Ein Feature ist **done**, wenn alle zutreffenden Punkte erfüllt sind:

- [ ] Feature in `01-features.md` beschrieben, inklusive Akzeptanzkriterien
- [ ] Bedienung in `02-user-doc.md` beschrieben
- [ ] Implementierung in `03-dev-doc.md` beschrieben
- [ ] Produktumfang und Reifegrad in `product.md` stimmen
- [ ] Sichtbare Oberfläche mit aktuellem Bild in `screenshots.md` belegt
- [ ] `privacy.md` geprüft, falls personenbezogene Daten berührt sind
- [ ] `security.md` geprüft, falls Rechte, Eingaben oder Löschpfade berührt sind
- [ ] `testXX` in `05-quality.md` vorhanden und grün
- [ ] `changelog.md` ergänzt; bei Auslieferung auch `releasenotes.md` und `CHANGELOG.md`
- [ ] PO-Sign-off liegt vor

Fehlt ein zutreffender Punkt → nicht done. Wortgleiche Fassung in `04-tasks.md`.

---

## 7. Release-Checkliste

1. `bin/precheck.sh` vollständig grün (phplint, phpcs, phpdoc, savepoint, eslint, stylelint, grunt, thirdparty)
2. PHPUnit grün — `05-quality.md` §Tests aktualisiert
3. Behat grün
4. `version.php`: `version` erhöht, `release` gesetzt, `maturity` passend
5. `upgrade.txt` ergänzt, falls sich eine öffentliche API oder das Schema ändert
6. `docs/changelog.md` und `CHANGELOG.md` ergänzt
7. `docs/releasenotes.md` mit neuem `relXX`, Release-Notes-Datei erzeugt
8. Screenshots aktuell (`screenshots.md`)
9. `bin/release.sh` ausführen, ZIP-Inhalt prüfen (keine `docs/`, `bin/`, `tests/`, `lang/de/`)
10. PO-Sign-off, dann Tag und Upload

---

## 8. ID-System

`featXX` Feature · `taskXX` Task · `qXX` offene Frage · `bugXX` Bug (S1–S4) ·
`testXX` Test · `verifyXX` Verifikation nach Deploy · `shotXX` Screenshot ·
`adrXX` Architektur-Entscheidung · `relXX` Release.

IDs sind projektweit eindeutig und werden nie wiederverwendet.

---

## 9. Einstieg für eine neue Session

1. Diese Datei lesen.
2. `product.md` — welches Produkt, für wen, welcher Umfang.
3. `04-tasks.md` — offene Tasks und `qXX`.
4. Bei Moodle-Fragen den Skill `moodle-framework` heranziehen.
5. `#status`, dann `#next`.
