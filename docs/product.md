# product — LeitnerFlow (`mod_eledialeitnerflow`)

Produktidentität: welches Produkt ist das, für wen, mit welchem Versprechen,
und was ist es bewusst nicht. Diese Datei ist die Quelle für alle
Außenkommunikation. Die Katalog-Ableitung mit YAML-Frontmatter liegt in
`product.de.md` und darf keine Aussage enthalten, die hier nicht steht.

---

## §1 Identität

| | |
|---|---|
| Produktname | LeitnerFlow |
| Komponente | `mod_eledialeitnerflow` |
| Plugin-Typ | Moodle-Aktivitätsmodul |
| Tagline | Karteikartenlernen nach Leitner — direkt aus der Moodle-Fragensammlung. |
| Release | 1.0.0 (`MATURITY_STABLE`) |
| Lizenz | GPL v3 or later |
| Hersteller | eLeDia GmbH, https://eledia.de |
| Quellcode | https://github.com/jmoskaliuk/moodle-mod_eledialeitnerflow |

---

## §2 Zielgruppen

**Lernende** — Personen, die einen Fragenbestand über Wochen behalten müssen:
Prüfungsvorbereitung, Vokabeln, Fachbegriffe, Compliance-Wissen. Sie wollen
kurze Einheiten und wollen sehen, dass sich etwas bewegt.

**Lehrende** — Personen, die bereits Fragen in der Moodle-Fragensammlung
pflegen und daraus ein Wiederholungsangebot machen wollen, ohne einen zweiten
Inhaltsbestand aufzubauen.

**Entscheidende / Betrieb** — Verantwortliche, die verteiltes Lernen anbieten
wollen, ohne dass Lerndaten die eigene Moodle-Instanz verlassen.

---

## §3 Problem

Wiederholung ist der Teil des Lernens, der am zuverlässigsten wirkt und am
seltensten stattfindet. Moodle bietet dafür üblicherweise das Quiz an — aber
ein Quiz ist eine Prüfung: es hat einen Anfang, ein Ende und eine Note. Wer
damit über Wochen wiederholen will, legt entweder dieselbe Fragenmenge mehrfach
an oder verlässt Moodle und arbeitet mit einer externen Karteikarten-App. Beim
ersten Weg wächst der Pflegeaufwand, beim zweiten verlassen die Lerndaten die
eigene Instanz.

---

## §4 Nutzenversprechen

LeitnerFlow macht aus vorhandenen Fragen einen persönlichen Karteikasten, der
sich merkt, was noch nicht sitzt — innerhalb von Moodle, ohne zweiten
Inhaltsbestand und ohne externen Dienst.

Konkret:

- **Für Lernende:** kurze Sitzungen statt langer Wiederholungsblöcke; jederzeit
  sichtbar, wie viele Karten in welchem Fach liegen.
- **Für Lehrende:** eine Aktivität anlegen, Fragenkategorien auswählen, fertig.
  Die Fragen bleiben dort, wo sie gepflegt werden.
- **Für den Betrieb:** alle Lerndaten in der eigenen Instanz, mit Privacy-API,
  Backup/Restore, Kurs-Reset und Event-Logging.

---

## §5 Abgrenzung und Alleinstellung

| | LeitnerFlow | Moodle-Quiz | Externe Karteikarten-App |
|---|---|---|---|
| Zweck | verteiltes Wiederholen | Prüfung / Test | verteiltes Wiederholen |
| Fragenquelle | Moodle-Fragensammlung | Moodle-Fragensammlung | eigener Bestand |
| Lernstand | dauerhaft pro Person und Karte | pro Versuch | dauerhaft |
| Datenhaltung | eigene Moodle-Instanz | eigene Moodle-Instanz | Anbieter |
| Fragetypen | alle Question-Engine-Typen | alle | meist nur Text/Bild |

Alleinstellung: der Karteikasten sitzt **auf** der Fragensammlung. Wer Fragen
pflegt, pflegt damit automatisch den Kartenbestand mit.

---

## §6 Produktumfang

Kernfunktionen, jeweils mit dem zugehörigen Feature aus `01-features.md`:

| Kernfunktion | Features |
|---|---|
| Karten aus der Fragensammlung erzeugen | `feat01` |
| Lernsitzung mit Sofort-Feedback | `feat02` |
| Leitner-Regelwerk konfigurieren | `feat03` |
| Kartenauswahl steuern und gezielt üben | `feat04` |
| Lernfortschritt sichtbar machen | `feat05`, `feat06` |
| Rückmeldung und Motivation dosieren | `feat07`, `feat08` |
| Einstieg begleiten | `feat09` |
| Lernstand begleiten und zurücksetzen | `feat10` |
| Bewertung optional anbinden | `feat11` |
| Betriebspflichten erfüllen | `feat12`, `feat13`, `feat14` |

Jedes `featXX` gehört genau einer Kernfunktion an. Ein Feature ohne Zuordnung
ist ein Warnsignal.

---

## §7 Bewusst nicht

- **Kein Ersatz für das Quiz.** Keine Zeitbegrenzung, keine Versuchsanzahl,
  keine Zugriffsregeln, keine Prüfungssicherheit.
- **Kein eigener Inhaltsbestand.** LeitnerFlow legt keine Fragen an und
  bearbeitet keine; es liest die Fragensammlung.
- **Kein SM-2 / Anki-Algorithmus.** Der Fachwechsel hängt an der Anzahl
  richtiger Antworten, nicht an einem Zeitintervall. Wer echtes zeitbasiertes
  Spaced Repetition erwartet, bekommt es hier nicht.
- **Keine Unterkategorien-Rekursion.** Kategorien werden einzeln gewählt
  (`adr11`).
- **Keine externen Dienste, keine KI-Anbindung, kein Composer-Paket.**

---

## §8 Anwendungsfälle

1. **Prüfungsvorbereitung** über mehrere Wochen: eine Aktivität, drei Fächer,
   „Bei falscher Antwort: zurück auf Fach 1", Sitzungsgröße 20.
2. **Vokabel- und Faktenlernen** im Sprach- oder Fachkurs, mit gemischter
   Kartenauswahl und milderem Falsch-Verhalten („ein Fach zurück").
3. **Auffrischung von Sicherheits- oder Compliance-Wissen** in Pflicht-
   schulungen, mit Bewertungsanbindung „Prozent gelernt" als Nachweis.

---

## §9 Voraussetzungen und Betrieb

| | |
|---|---|
| Moodle | 4.4 (`2024042200`) oder neuer; getestet bis 5.1 |
| PHP | 8.1 oder neuer; getestet bis 8.3 |
| Datenbank | MySQL 8 / MariaDB 10.6 / PostgreSQL 13+ |
| Abhängigkeiten | keine externen; `tool_usertours` optional für die Einführungstouren |
| Filter | Core-Filter `multilang` wird bei Installation aktiviert (Tour-Übersetzungen) |
| Sprachen | Englisch im Release-ZIP; Deutsch im Git-Klon, nach Freigabe über AMOS (`adr09`) |
| Datenhaltung | ausschließlich in der Moodle-Instanz, drei eigene Tabellen |

Offene Betriebsfrage: `$plugin->supported` ist nicht gesetzt, die beworbene
Versionsspanne ist damit nicht maschinenlesbar belegt (→ `task02`, `q01`).
Lastverhalten bei sehr großen Fragenpools ist nicht gemessen (→ `task07`).

---

## §10 Reifegrad

Bewertung nach dem eLeDia-Proven-Modell **2026.2**, geprüft am **2026-08-21**,
DevFlow-Abgleich am **2026-08-27**.

| Kriterium | Punkte | Belegt durch |
|---|---|---|
| Zweckklarheit | 3 | Ein Anwendungsfall, `modulename_help` beschreibt ihn vollständig |
| Moodle-Kompatibilität | 2 | `requires 2024042200`, `MATURITY_STABLE`, aber kein `$plugin->supported` |
| Datenschutz / DSGVO | 3 | `classes/privacy/provider.php` mit Export und Löschung |
| Sicherheit / Rechte | 3 | `db/access.php` plus `tests/behat/capabilities.feature` |
| Installation / Upgrade | 3 | `db/install.xml`, `db/upgrade.php`, `db/access.php` |
| Codequalität / Tests | 2 | 5 Behat-Features, aber nur eine PHPUnit-Datei (Engine) |
| Usability / Doku / Mobile | 2 | `02-user-doc.md` vollständig; `screenshots/` leer, Moodle-App unverifiziert |
| Betrieb / Support | 2 | Event-Logging und Kurs-Reset vorhanden; keine Lastdaten |

Die 2-Punkte-Kriterien sind als Tasks erfasst: `task02` (supported),
`task04` (Testbreite), `task01`/`task06` (Screenshots, Mobile), `task07` (Last).

---

## §11 Support, Bezug, Lizenz

- **Bezug:** Moodle Plugins Directory (Einreichung — Status siehe `q05`) sowie
  direkt aus dem GitHub-Repository.
- **Issue-Tracker:** https://github.com/jmoskaliuk/moodle-mod_eledialeitnerflow/issues
- **Dokumentation:** `README.md` (EN), `README-DE.md` (DE), `docs/02-user-doc.md` (Bedienung)
- **Lizenz:** GNU GPL v3 or later
- **Maintainer:** eLeDia GmbH

### Lokale Trennung des F07-Piloten (24.10.2026)

Der frühere Szenen-/Guided-Pilot ist nach `mod_fahrup` ausgegliedert. LeitnerFlow bleibt generisch und ohne Fahrup betreibbar. Eine optionale, standardmäßig gesperrte interne PHP-Schnittstelle erlaubt autorisierten Moodle-Aktivitäten einmalige QE-Wiederholungsabschlüsse; keine zweite Fragen- oder Grafikbibliothek. Zwei zusätzliche generische Provenienztabellen, insgesamt fünf Tabellen im aktuellen Entwicklungsstand. Kein neuer Release-/Versionsmatrix- oder Proven-Reifegrad-Anspruch.
