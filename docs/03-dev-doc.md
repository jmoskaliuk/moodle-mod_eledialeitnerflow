# 03 — Entwickler-Dokumentation

Wie LeitnerFlow tatsächlich implementiert ist. Verhalten aus Nutzersicht steht
in `02-user-doc.md`, das Warum hinter Strukturentscheidungen in `00-master.md` §4.

---

## 1. Aufbau

Der Repo-Root **ist** das Plugin-Verzeichnis. Beim Deployment liegt der Inhalt
unter `mod/eledialeitnerflow/` im Moodle-Baum.

```
lib.php                     Moodle-Callbacks (Instanz-CRUD, Bewertung, Reset, Features)
mod_form.php                Einstellungsformular
view.php                    Lern-Dashboard (Lernende) und Einstieg für Lehrende
attempt.php                 Sitzungssteuerung und Frageanzeige
report.php                  Teilnehmendenbericht
index.php                   Kursweite Instanzliste
styles.css                  Styles, ausschließlich unter .path-mod-eledialeitnerflow / .lf-*
classes/
  engine/leitner_engine.php Fachlogik: Auswahl, Fachwechsel, Statistik, Löschung
  event/                    session_started, session_completed, progress_reset
  local/tour_installer.php  Idempotenter Import der gebündelten User Tours
  privacy/provider.php      Privacy-API
db/
  install.xml               Schema (3 Tabellen)
  install.php               Post-Install (multilang-Filter)
  upgrade.php               Migrationsschritte
  access.php                Capabilities
  usertours/*.json          Tour-Definitionen
amd/src/                    card_transition, confirm_reset, quiz_session
backup/moodle2/             Backup- und Restore-Tasks und Stepslibs
tests/                      PHPUnit (Engine) und Behat (5 Features)
bin/                        precheck.sh, release.sh, build-user-doc.sh
```

---

## 2. Datenmodell

Drei Tabellen, definiert in `db/install.xml`.

### `eledialeitnerflow` — Aktivitätsinstanz

Neben den Standardfeldern (`course`, `name`, `intro`, `introformat`,
`timecreated`, `timemodified`):

| Feld | Typ | Bedeutung |
|---|---|---|
| `questioncategoryid` | int | **veraltet**, nur Rückwärtskompatibilität; wird mit der ersten Kategorie synchron gehalten |
| `questioncategoryids` | text | kommaseparierte Kategorie-IDs (`adr05`) |
| `sessionsize` | int | Karten pro Sitzung, Standard 20 |
| `boxcount` | int | Anzahl Fächer, 1–5, Standard 3 |
| `correcttolearn` | int | richtige Antworten bis „gelernt", Standard 3 |
| `wrongbehavior` | int | 0 = Reset auf Fach 1, 1 = ein Fach zurück, 2 = unverändert |
| `questionrotation` | int | 1 = immer frisch aus der Fragensammlung, 0 = fester Pool |
| `prioritystrategy` | int | 0 = niedrige Fächer zuerst, 1 = gemischt |
| `grade` / `grademethod` | int | 0 = keine Bewertung, 1 = Prozent gelernt |
| `showanimation` | int | Kartenbewegung an/aus |
| `feedbackstyle` | int | 0 aus, 1 minimal, 2 animiert, 3 detailliert, 4 gamifiziert |
| `animationdelay` | int | Auto-Weiterleitung in ms, 500–3000 |
| `showtour` | int | Einführungstour beim ersten Besuch |

### `eledialeitnerflow_card_state` — Lernstand je Karte

Genau eine Zeile pro (Instanz, Person, Frage), abgesichert durch den
Unique-Index `unique_state (eledialeitnerflowid, userid, questionid)`.
Felder: `currentbox`, `correctcount`, `attemptcount`, `status`
(0 = offen, 1 = gelernt, 2 = fehlerhaft), `timecreated`, `timemodified`.
Zusätzlicher Index `quiz_user (eledialeitnerflowid, userid)` für Dashboard und
Bericht.

**Nie beantwortete Fragen haben keine Zeile.** Sie gelten implizit als Fach 1.
Das hält die Tabelle klein, verlangt aber, dass jede Auswertung den Fragenpool
kennt und die Lücken als Fach 1 zählt.

### `eledialeitnerflow_sessions` — Lernsitzung

`qubaid` (Verweis auf `question_usages`), `questionids` (JSON-Array in
Sitzungsreihenfolge), `currentindex`, `questionsasked`, `questionscorrect`,
`currentstreak`, `beststreak`, `status` (0 aktiv, 1 abgeschlossen),
`timecreated`, `timecompleted`.

`qubaid` wird beim Abschluss der Sitzung auf `NULL` gesetzt und die
Question-Usage gelöscht — die Historie bleibt, der Frageballast nicht.

---

## 3. Die Engine

`\mod_eledialeitnerflow\engine\leitner_engine` ist eine reine Statik-Klasse.
Die Fachlogik liegt vollständig hier; `view.php`, `attempt.php` und `report.php`
rufen nur auf.

### Fachwechsel

`correctcount` ist die führende Größe, `currentbox` wird daraus berechnet
(`adr04`):

```php
calculate_box(correctcount, correcttolearn, boxcount)
  correctcount <= 0                → 1
  correctcount >= correcttolearn   → boxcount   (Karte wird separat als gelernt markiert)
  sonst                            → clamp(floor(correctcount * boxcount / correcttolearn), 1, boxcount)
```

`process_answer()` liefert das aktualisierte Zustandsobjekt zurück, **ohne** zu
speichern; `save_card_state()` schreibt. Richtige Antwort erhöht `correctcount`
und markiert ab `correcttolearn` als gelernt. Falsche Antwort folgt
`wrongbehavior`:

| Modus | `correctcount` | Fach | Status |
|---|---|---|---|
| `WRONG_RESET` (0) | 0 | 1 | `STATUS_ERROR` |
| `WRONG_BACK1` (1) | `max(0, n-1)` | neu berechnet | `ERROR` bei 0, sonst `OPEN` |
| `WRONG_NOCHANGE` (2) | unverändert | unverändert | `STATUS_ERROR` |

### Kartenauswahl

`select_session_questions()` verteilt alle nicht gelernten Fragen des Pools auf
Eimer `1..boxcount`, mischt innerhalb jedes Eimers und füllt dann entweder von
Fach 1 aufwärts (`prioritystrategy = 0`) oder aus einem gemischten Gesamttopf
(`prioritystrategy = 1`), bis `sessionsize` erreicht ist.
`select_questions_from_box()` ist die Variante für gezieltes Üben eines Fachs;
bei Fach 1 zählen auch nie beantwortete Karten dazu.

### Fragen aus der Fragensammlung

`get_questions_from_category()` nutzt die Moodle-4.x-Question-Bank-API — Join
über `question_bank_entries → question_versions → question` — und liefert je
Bank-Eintrag die höchste Version mit `status = 'ready'`. Kein direkter Zugriff
auf `{question}`. Unterkategorien werden nicht einbezogen (`adr11`).

### Auswertung

`get_user_stats()` (Gesamt/gelernt/offen/fehlerhaft/Prozent),
`get_box_distribution()` (Karten je Fach),
`get_session_history()` und `get_session_stats()` (Historie und Durchschnitt),
`get_all_students_stats()` für den Bericht — Letzteres aggregiert die
Kartenstände aller Personen in **einer** Gruppierungsabfrage, um N+1 zu
vermeiden; Sitzungszahl und letzter Zeitpunkt werden allerdings weiterhin je
Person abgefragt.

`delete_user_data()` ist der gemeinsame Löschpfad für Reset, Kurs-Reset und
Privacy-Löschung: erst die `question_usages` der Sitzungen, dann Sitzungen,
dann Kartenstände.

---

## 4. Ablauf einer Sitzung (`attempt.php`)

1. **Start** (`?start=1`): Noch aktive Sitzungen werden abgeschlossen statt
   gelöscht — Teilergebnisse bleiben erhalten, deren `question_usages` werden
   freigegeben.
2. Fragen auswählen (gesamt oder per `?box=N`). Ist die Auswahl leer, Rück-
   leitung auf `view.php` mit Hinweis.
3. `question_usage_by_activity` für `mod_eledialeitnerflow` anlegen, Verhalten
   `immediatefeedback`, alle Fragen mit Gewicht 1 hinzufügen, starten, speichern.
4. Sitzungszeile schreiben, `session_started` auslösen, auf die Sitzungs-URL
   weiterleiten.
5. **Anzeige:** Slot `currentindex + 1` (Quba-Slots sind 1-basiert).
6. **Antwort (POST):** `require_sesskey()`, dann eine
   `start_delegated_transaction()` um: `process_all_actions()`, Speichern der
   Quba, Bewertung über `get_question_fraction($slot) >= 1.0`, Kartenstand
   aktualisieren, Sitzungszähler und Streaks fortschreiben, `currentindex + 1`.
7. **Rückmeldung:** Stil 0 leitet sofort weiter; Stile 1–4 rendern die Frage mit
   Feedback-Overlay und lassen `card_transition.js` nach `animationdelay`
   weiterleiten — außer Stil 3 (detailliert), der auf einen Klick wartet.
8. **Abschluss:** Status auf abgeschlossen, `timecompleted`, Quba löschen und
   `qubaid` auf `NULL`, `session_completed` auslösen, bei aktiver Bewertung
   `eledialeitnerflow_update_grades()`, Ergebnisseite mit Weiter-Knopf.

---

## 5. Moodle-Anbindung

### Gemeldete Features (`eledialeitnerflow_supports()`)

`FEATURE_MOD_INTRO`, `FEATURE_SHOW_DESCRIPTION`, `FEATURE_GRADE_HAS_GRADE`,
`FEATURE_COMPLETION_TRACKS_VIEWS`, `FEATURE_BACKUP_MOODLE2`,
`FEATURE_MOD_PURPOSE = MOD_PURPOSE_ASSESSMENT`. Alles Weitere gibt `null`
zurück — insbesondere keine eigenen Abschlussbedingungen.

### Bewertung

`grademethod = 0` erzeugt `GRADE_TYPE_NONE`, sonst `GRADE_TYPE_VALUE` mit
0–100. Die Note ist `get_user_stats()->percent_learned`. Aktualisiert wird beim
Sitzungsabschluss (einzelne Person) und über `eledialeitnerflow_update_grades()`
(alle). `eledialeitnerflow_delete_instance()` holt den Datensatz **vor** dem
Löschen, weil `grade_update()` das Feld `course` braucht.

### Backup und Restore

`backup_eledialeitnerflow_stepslib` sichert die Instanz, bei `userinfo`
zusätzlich Kartenstände und Sitzungen, dazu die referenzierten Fragen-
kategorien als `question_category_ref`. Annotiert werden `user`, `question`,
`question_categoryref` sowie die Dateien des Intros.

### Sicherheit auf Seitenebene

Jede Einstiegsdatei ruft `require_login($course, true, $cm)` und prüft die
passende Capability; `report.php` zusätzlich `resetprogress` vor dem Reset.
Alle zustandsändernden Aufrufe sind über `require_sesskey()` abgesichert
(`view.php` Reset und Sitzungsabbruch, `attempt.php` Antwort-POST, `report.php`
Reset). Eingaben laufen über `required_param`/`optional_param` mit passendem
`PARAM_*`-Typ.

### Sprache

`lang/en/eledialeitnerflow.php` ist die Quelle (161 Strings). Der Dateikopf
listet bewusst die Core-Strings auf, die **nicht** neu definiert werden
(`question`, `progress`, `continue`, `cancel`, `date`, `participants`,
`questionbank`). `lang/de/` existiert im Git, wird aber per `.gitattributes`
nicht ausgeliefert (`adr09`).

### JavaScript

Drei AMD-Module, Quelle in `amd/src/`, gebaut nach `amd/build/`:
`card_transition.js` (Bewegungsanimation und Auto-Weiterleitung),
`confirm_reset.js` (Bestätigungsdialog vor dem Zurücksetzen),
`quiz_session.js` (Tastaturkürzel, Autofokus). Nach jeder Änderung an
`amd/src/` muss `grunt amd` laufen, sonst schlägt der Precheck fehl.

### User Tours

`tour_installer::install_bundled_tours()` importiert die JSONs aus
`db/usertours/`, prüft vorher über `tour_exists_for_path()` auf vorhandene
Touren und ist damit idempotent. `reimport_bundled_tours()` räumt zuerst auf und
importiert dann — genutzt vom Upgrade-Schritt `2026042101`. Der Import läuft
bewusst nicht in `install.php` (`adr08`).

---

## 6. Datenbank-Migrationen

`db/upgrade.php` enthält 14 Schritte. Bemerkenswert:

| Version | Inhalt |
|---|---|
| `2024120106` | `questioncategoryids` ergänzt, Einzelwert migriert |
| `2024120109` | `showanimation` |
| `2024120113`–`2024120118` | mehrfacher Neuimport der Touren (Konfigurationsformat, `targettype`, multilang-Syntax, Texte) |
| `2024120117` | `feedbackstyle` |
| `2024120119` | `currentstreak`, `beststreak` |
| `2024120120` | `showtour` |
| `2024120121` | `animationdelay`, Import der Lehrenden-Tour |
| `2024120123` | Neuimport der Lehrenden-Tour (konnte zuvor übersprungen werden) |
| `2024120124` | verwaiste `grade_items` mit `itemmodule = 'leitnerflow'` migrieren oder entfernen (`adr06`) |
| `2026042101` | Tour-Dubletten entfernen und einmalig neu importieren |

Die Häufung der Tour-Schritte ist Altlast: der frühere Importpfad war nicht
idempotent. Seit `tour_installer` ist er es; neue Tour-Änderungen brauchen
keinen eigenen Schritt mehr, sondern nur einen Aufruf von
`reimport_bundled_tours()`.

---

## 7. Tests

**PHPUnit** — `tests/engine/leitner_engine_test.php`, 29 Testmethoden
(mit Data-Provider-Zeilen zählt PHPUnit mehr). Abgedeckt: `calculate_box()`,
`process_answer()` in allen drei Falsch-Modi, Auswahlstrategien, Statistik.
`tests/generator/lib.php` liefert den Instanzgenerator.
**Nicht abgedeckt:** `lib.php`, `privacy/provider.php`, `tour_installer` (→ `task04`).

**Behat** — 5 Features: `activity_create`, `attempt_session`, `student_view`,
`teacher_report`, `capabilities`; eigener Kontext in
`tests/behat/behat_mod_eledialeitnerflow.php`.

**CI** — `.github/workflows/moodle-ci.yml`, Matrix PHP 8.2 / `MOODLE_405_STABLE`
und PHP 8.3 / `MOODLE_500_STABLE`, PostgreSQL 14.

**Lokal** — `bin/precheck.sh` fährt die Prüfungen des Plugins-Directory-
Prechecks im `webserver`-Container der lokalen Docker-Umgebung und ergänzt zwei
statische Blocker: keine nicht-englischen Sprachpakete im ZIP und
CSS-Namensraum.

---

## 8. Release-Mechanik

`bin/release.sh` baut über `git archive --prefix=eledialeitnerflow/` ein ZIP aus
`HEAD` und respektiert damit die `export-ignore`-Einträge aus `.gitattributes`:
`lang/de/`, `bin/`, `cli/`, `docs/`, `tests/`, `phpunit.xml`, `.github/`,
`.gitattributes`. Der Dateiname zieht die Version aus `$plugin->release`.

**Folge für diesen DevFlow:** weil `docs/` ausgeschlossen ist, wandert er nicht
ins Release (`adr10`). Wer `.gitattributes` anfasst, muss das prüfen.

`bin/build-user-doc.sh` erzeugt `docs/user-doc.html` aus `docs/02-user-doc.md`
mit Pandoc und `docs/user-doc.css`. Die HTML-Datei ist ein Artefakt, sie ist in
`.gitignore` und wird nicht bearbeitet.

---

## 9. Bekannte Schwachstellen der Implementierung

Ausführlich mit Severity in `05-quality.md`.

- Wird `boxcount` nachträglich verkleinert, bleiben Kartenstände oberhalb des
  neuen Maximums stehen und fallen aus Anzeige und Priorisierung heraus (`bug01`).
- Der Kurs-Reset löscht Lerndaten, setzt aber das Bewertungselement nicht
  zurück (`bug02`).
- `report.php` nimmt `perpage` ungeprüft entgegen (`bug03`).
- Der frühere Modulfilterfehler im Privacy-Provider (`bug04`) ist im Entwicklungsstand korrigiert; test29 belegt die neue Ermittlung.
- Die frühere Lücke bei Daten ohne Kartenstand (`bug05`) ist im Entwicklungsstand korrigiert: native Sitzungen und externe Registrierungen werden berücksichtigt (test29).
- `process_answer()` deklariert ein ungenutztes `global $DB` (`bug06`).
- `_eledialeitnerflow_process_categories()` lässt `questioncategoryids`
  unverändert, wenn das Formularfeld fehlt (`bug07`).


### Rückmeldung der letzten Antwort (24.10.2026)

`attempt.php` rendert bei aktivierter Rückmeldung auch nach der letzten Antwort den beantworteten Slot. Der nächste GET beendet die Sitzung über den bestehenden Abschlusszweig. Die letzte Rückmeldung hat einen manuellen Continue-Link und keinen Auto-Redirect. Im ausführlichen Modus wird auch generalfeedback sichtbar. Modus 0 bleibt ohne Rückmeldung. Keine Schema-/API-Änderung. Lokal auf h5p52 eingespielt; kein Release.


## Trennung in Fahrup und generisches LeitnerFlow (24.10.2026)

Der frühere task17-/F07-Pilot wurde nach `mod_fahrup` überführt. In LeitnerFlow gibt es keinen Szenencontroller, Player, Filemanager, Manifestresolver, iframe oder Fahrup-Klassenaufruf mehr. Schema 2026102401 ergänzt das generische PHP-API; 2026102402 entfernt nach geprüftem Transfer die Pilotfelder/-tabelle. Upgrade blockiert Restdaten oder alte Szenendateien. Historische Upgradeblöcke sind reine Kompatibilität und keine Fahrup-Laufzeitabhängigkeit.

`classes/local/external_review_service.php`, API_VERSION 1: `target_configuration`, `validate_question`, `register_attempt`, `commit_slot`, `locked`, `is_registration_open`, `close_attempt`, `revoke_source`, `delete_provenance`. Registrierungen binden Zielinstanz/Person, zugelassene Quellkomponente und Quell-cmid, attemptuuid, QUBA und konkrete Fragen. Global konfigurierte `reviewsources` ist standardmäßig leer; jedes Ziel benötigt zusätzlich `externalenabled=1`. Der generische Callback `<modname>_validate_leitnerflow_review` muss Eigentümer und fachliche Wiederholungsberechtigung serverseitig bestätigen. Es gibt keinen öffentlichen Web-Endpunkt.

Finale Bewertung wird ausschließlich aus QE gelesen, gegen Quelle/Kontext/Person und Kategorie geprüft und unter demselben Ziel-/Nutzer-Lock wie native Antworten einmalig angewendet. Reentrant innerhalb eines Requests; äußerer Connector-Lock muss bis zum Commit aller QE-/Quelldaten bestehen. Eindeutige `eledialeitnerflow_ext_results` belegen die Anwendung; `eledialeitnerflow_ext_attempts` hält minimale Provenienz. Keine Antwortkopien oder Szenenfelder. Reset schließt alte Registrierungen; Privacy/Aktivitätslöschung entfernt personenbezogene Provenienz. Native Sitzungen bleiben eigenständig unter `session_lifecycle`.

Backup/Restore enthält minimale Provenienz, restauriert sie geschlossen und ersetzt keine Ziel-/Quellbindung anhand alter IDs. Alte Szenen-Backups werden vor stiller Umdeutung abgewiesen. Extern gewertete Antworten werden auf dem Dashboard gesondert ausgewiesen, native Sitzungshistorie bleibt getrennt. Kartenwissen und bestehende Boxenregeln werden nicht auf Fragenversionen umgeschrieben.

Fahrup besitzt seine QE-Usages, Antworten, Dateien, Modi, 90-Tage-Nachlese und Datenschutzpfade. Die kontrollierte Legacy-Migration liegt dort im CLI, nicht als Fahrup-Laufzeit in LeitnerFlow. Bedien-/Betriebsdetails im neuen Modul-DevFlow; ursprünglicher Trennungsplan task19–24 bleibt hier. Keine neue Abhängigkeit von LeitnerFlow auf Fahrup.
