# security — Datensicherheit

Schutzwerte, Bedrohungen, Maßnahmen und offene Befunde.

**Status:** beschreibt den Ist-Zustand aus der Code-Analyse vom 2026-08-27.
Fachliche Freigabe steht aus → `task12`. Ein externes Sicherheitsaudit hat es
nicht gegeben.

---

## 1. Schutzwerte

| Schutzwert | Warum | Schutzziel |
|---|---|---|
| Lernstand einzelner Personen | Leistungsdaten, Rückschluss auf Kenntnisstand | Vertraulichkeit, Integrität |
| Fragensammlung des Kurses | Prüfungsrelevante Inhalte | Vertraulichkeit — LeitnerFlow darf keinen Zugang eröffnen, den die Rolle sonst nicht hätte |
| Bewertungseintrag | fließt ggf. in eine Kursnote ein | Integrität |
| Aktivitätskonfiguration | steuert, welche Fragen sichtbar sind | Integrität |
| Verfügbarkeit der Instanz | eine Aktivität darf die Instanz nicht ausbremsen | Verfügbarkeit |

---

## 2. Rechtemodell

Sechs eigene Capabilities, definiert in `db/access.php`:

| Capability | Typ | Kontext | Risiko | Standardrollen |
|---|---|---|---|---|
| `addinstance` | write | Kurs | `RISK_XSS` | Trainer:in mit Bearbeitungsrecht, Manager:in |
| `view` | read | Modul | — | Lernende, Trainer:in, Manager:in; Gast `CAP_PREVENT` |
| `attempt` | write | Modul | — | Lernende, Manager:in; Trainer:in `CAP_PREVENT` |
| `viewreport` | read | Modul | — | Trainer:in, Manager:in |
| `manage` | write | Modul | `RISK_CONFIG` | Trainer:in mit Bearbeitungsrecht, Manager:in |
| `resetprogress` | write | Modul | `RISK_DATALOSS` | Trainer:in mit Bearbeitungsrecht, Manager:in |

Bemerkenswert: Lehrende haben `attempt` per Archetyp ausdrücklich **nicht** —
Lehrendenkonten erzeugen also keine Lernstände, die im eigenen Bericht
auftauchen. `addinstance` erbt Berechtigungen von
`moodle/course:manageactivities`.

Abgesichert durch `tests/behat/capabilities.feature`.

---

## 3. Umgesetzte Maßnahmen

### Zugriffskontrolle auf Seitenebene

Jede Einstiegsdatei ruft `require_login($course, true, $cm)` und prüft die
passende Capability:

| Datei | Prüfung |
|---|---|
| `view.php` | `require_login`; `resetprogress` vor dem Zurücksetzen |
| `attempt.php` | `require_login`; `mod/eledialeitnerflow:attempt` |
| `report.php` | `require_login`; `viewreport`; zusätzlich `resetprogress` vor dem Reset |
| `index.php` | `require_login($course)` |

Sitzungen werden zusätzlich über den Besitz gesichert: `attempt.php` lädt die
Sitzung mit `['id' => $sessid, 'userid' => $USER->id]` und `MUST_EXIST` — eine
fremde Sitzungs-ID führt zu einem Fehler statt zu fremden Daten.

### CSRF

Alle zustandsändernden Aufrufe sind mit `require_sesskey()` geschützt:
Antwort-POST in `attempt.php`, Fortschritts-Reset und Sitzungsabbruch in
`view.php`, Reset in `report.php`. Ausgehende Aktionslinks tragen `sesskey()`.

### Eingaben

Ausschließlich `required_param()` / `optional_param()` mit passendem
`PARAM_*`-Typ (`PARAM_INT` für IDs und Seitenzahlen, `PARAM_ALPHA` für
Sortierung und Richtung, `PARAM_TEXT` für die Suche im Bericht). Keine direkte
Verwendung von `$_GET` oder `$_POST` außer der Methodenprüfung in `attempt.php`.

### Datenbank

Ausschließlich die Moodle-DML-API mit benannten Platzhaltern; keine
String-Konkatenation in SQL. Die Antwortverarbeitung in `attempt.php` läuft in
einer `start_delegated_transaction()`, sodass Question-Usage, Kartenstand und
Sitzungszähler nicht auseinanderlaufen können.

### Ausgabe

Fragen werden über die Question Engine gerendert, nicht selbst zusammengebaut.
Namen laufen über `fullname()`, Aktivitätsnamen über `format_string()`,
Beschreibungen über `format_module_intro()`. `styles.css` schreibt
ausschließlich unter `.path-mod-eledialeitnerflow` und dem Klassenpräfix `lf-`
(`adr12`), sodass keine Styles in andere Plugins auslaufen.

### Fragensammlung

LeitnerFlow liest Fragen über die Question-Bank-API (`question_bank_entries` →
`question_versions` → `question`) und lädt sie mit
`question_bank::load_question()`. Sichtbar wird nur, was die Lehrkraft beim
Anlegen der Aktivität als Kategorie ausgewählt hat — die Auswahl selbst ist
durch `addinstance`/`manage` geschützt.

> **Bewusste Eigenschaft, kein Fehler:** Wer eine Aktivität anlegen darf, kann
> damit Fragen einer gewählten Kategorie für die eingeschriebenen Lernenden
> sichtbar machen. Das entspricht dem Verhalten des Core-Quiz. Instanzen mit
> geteilten Fragenkategorien über Kursgrenzen hinweg sollten das kennen.

### Abhängigkeiten

Keine externen Bibliotheken, kein Composer-Paket, kein Drittanbieter-Code im
Plugin (`thirdparty`-Precheck grün). Damit keine Lieferketten-Angriffsfläche
über Paketabhängigkeiten.

---

## 4. Bedrohungen und Bewertung

| # | Bedrohung | Bewertung | Maßnahme |
|---|---|---|---|
| T1 | Fremde Sitzung übernehmen (`sessid` raten) | gering | Sitzung wird mit `userid` geladen, `MUST_EXIST` |
| T2 | Fortschritt fremder Personen zurücksetzen | gering | `resetprogress` plus `require_sesskey()` |
| T3 | Bericht ohne Berechtigung einsehen | gering | `viewreport`, durch Behat abgesichert |
| T4 | SQL-Injection | gering | ausschließlich DML-API mit Platzhaltern |
| T5 | XSS über Aktivitätsname oder Beschreibung | gering | `format_string()` / `format_module_intro()`; `addinstance` trägt `RISK_XSS` bewusst |
| T6 | XSS über Frageninhalte | gering | Rendering durch die Question Engine |
| T7 | Selbst verursachte Last über `perpage` | gering | **offen** → `bug03` |
| T8 | Last durch sehr große Fragenpools | unbekannt | **offen** → `task07` |
| T9 | Zugang zu Fragen einer fremden Kategorie über eine selbst angelegte Aktivität | gering, systemimmanent | entspricht dem Core-Quiz; siehe Kasten oben |
| T10 | Datenreste nach Löschung | gering | alle Löschpfade räumen Question-Usages mit auf; Ausnahme Bewertung → `bug02` |
| T11 | Manipulation der Note durch Lernende | gering | Note wird serverseitig aus `card_state` berechnet, nie aus einer Eingabe übernommen |

---

## 5. Offene Befunde

| ID | Befund | Sev | Sicherheitsrelevanz |
|---|---|---|---|
| bug03 | `perpage` im Bericht unbegrenzt | S4 | Lasterzeugung durch berechtigte Personen; keine Rechteumgehung |
| bug04 | Privacy-Kontexte nicht auf das Modul gefiltert | S3 | Datenschutz-Korrektheit, keine Preisgabe fremder Daten |
| bug05 | Reine Sitzungsdaten für die Privacy-API unsichtbar | S3 | Vollständigkeit von Auskunft und Löschung |
| bug02 | Bewertung überlebt den Kurs-Reset | S3 | Datenrest, Integrität der Note |

Kein Befund der Stufen S1 oder S2. Details in `05-quality.md`.

---

## 6. Lücken in der Absicherung selbst

- **Keine automatisierten Tests für die Löschpfade.** `delete_instance()`,
  `reset_userdata()` und der Privacy-Provider sind von keinem PHPUnit-Test
  berührt → `task04`.
- **Keine Barrierefreiheitsprüfung.** WCAG 2.2 / BFSG ist nicht geprüft; das
  Dashboard arbeitet mit Farbcodierung der Fächer.
- **Keine Lastdaten** für große Fragenpools → `task07`.
- **Kein externes Audit.**

---

## 7. Meldeweg

Sicherheitsrelevante Funde gehen nicht in den öffentlichen Issue-Tracker,
sondern direkt an die eLeDia GmbH. Im DevFlow werden sie als `bugXX` mit
Severity S1 in `05-quality.md` geführt und blockieren das nächste Release.


## Generische externe Wiederholung nach Trennung (24.10.2026)

Szenen, Sichtungen, Guided und 90-Tage-Antwortnachlese gehören zum eigenständigen `mod_fahrup`. LeitnerFlow besitzt nur generische externe Registrierungen und Wertungsbelege zusätzlich zu Karten und nativen Sitzungen. Keine Fahrup-Pluginabhängigkeit. Die Privacy-Kontext-/Personensuche umfasst Karten, native Sitzungen und externe Registrierungen und filtert das tatsächliche Modul. Export und Löschung erfassen die neuen Tabellen; das Löschen eines Quellversuchs setzt nicht still Kartenwissen zurück.

Quelle und Ziel sind standardmäßig nicht für externe Wertung freigegeben. Interne PHP-Aufrufe prüfen explizite Zulassung, Modul-/Kursbindung, Zielrecht, aktuelle Person, Quellcallback und QE-Komponente/Kontext. Nur finale automatisch bewertete QE-Slots, keine Client-Korrektheitsangaben. Transaktion und Unique-Beleg verhindern Doppelwertung; Reset widerruft alte Registrierungen, Restore aktiviert keine fremden Quellen. Keine externen Dienste oder neuen Authentifizierungstoken.
