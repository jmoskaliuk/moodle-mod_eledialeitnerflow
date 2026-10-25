# 05 — Qualität

Funktioniert es, und was ist kaputt? Bugs mit Severity, Tests mit Bezug zu den
Akzeptanzkriterien aus `01-features.md`.

**Severity**

| Stufe | Bedeutung | Konsequenz |
|---|---|---|
| S1 | Kritisch — Kernfunktion kaputt, Datenverlust, Sicherheitslücke | Hotfix, blockiert Release |
| S2 | Schwer — Feature unbrauchbar, kein Workaround | im laufenden Release beheben |
| S3 | Mittel — eingeschränkt, Workaround vorhanden | nächstes Release |
| S4 | Gering — kosmetisch oder Randfall | Backlog |

**Status:** `open` · `in_progress` · `fixed` · `wontfix`

---

## Bug-Übersicht

| ID | Titel | Feature | Sev | Status | Fix |
|---|---|---|---|---|---|
| bug01 | Karten verschwinden nach Verkleinern der Fächerzahl | feat03, feat05 | S3 | open | task13 |
| bug02 | Kurs-Reset setzt das Bewertungselement nicht zurück | feat11, feat12 | S3 | open | task14 |
| bug03 | `perpage` im Bericht ist unbegrenzt | feat10 | S4 | open | — |
| bug04 | Privacy-Kontextermittlung filtert nicht auf das Modul | feat13 | S3 | open | task15 |
| bug05 | Personen mit Sitzung, aber ohne Kartenstand, werden übersehen | feat13 | S3 | open | task15 |
| bug06 | Ungenutztes `global $DB` in `process_answer()` | feat03 | S4 | open | — |
| bug07 | Kategorienzuordnung bleibt unverändert, wenn das Formularfeld fehlt | feat01 | S4 | open | — |

Alle sieben Befunde stammen aus der Code-Analyse beim DevFlow-Aufbau am
2026-08-27. **Keiner ist bisher an einer laufenden Instanz reproduziert
worden** — das ist Teil des jeweiligen Fixes.

---

### bug01 — Karten verschwinden nach Verkleinern der Fächerzahl

Feature:  feat03, feat05
Severity: S3
Status:   open
Linked:   task13 (Fix), test21 (Reproduktion, geplant)

**Beschreibung**
`currentbox` wird bei jeder Antwort gespeichert, aber nie nachgezogen, wenn eine
Lehrkraft `boxcount` nachträglich verkleinert. Karten, die in einem nun nicht
mehr existierenden Fach stehen, fallen aus beiden Auswertungen heraus:
`get_box_distribution()` legt das Zielarray mit `array_fill(1, $boxcount, 0)` an
und erhöht nur bei `isset($dist[$box])` — Karten mit höherem Fach werden
schlicht nicht gezählt. `select_session_questions()` iteriert beim
Prioritätsmodus nur `1..$boxcount` und erreicht die betroffenen Karten nie.

**Reproduktion (erwartet)**
1. Aktivität mit `boxcount = 5` und `correcttolearn = 5` anlegen.
2. Als Lernende eine Karte viermal richtig beantworten → Fach 4.
3. Als Lehrkraft `boxcount` auf 3 stellen und speichern.
4. Dashboard als Lernende öffnen und eine Sitzung im Standardmodus starten.

**Erwartet**
Die Karte erscheint in einem der drei verbliebenen Fächer und wird wieder
abgefragt.

**Tatsächlich (erwartet)**
Die Karte fehlt in der Fächerverteilung — die Summe der angezeigten Karten ist
kleiner als der Fragenpool — und wird im Prioritätsmodus nicht mehr abgefragt.
Im gemischten Modus (`prioritystrategy = 1`) taucht sie weiterhin auf, weil dort
über alle Eimer gemischt wird.

**Umgebung** unabhängig von Moodle-Version und Datenbank; folgt allein aus der
Engine-Logik.

---

### bug02 — Kurs-Reset setzt das Bewertungselement nicht zurück

Feature:  feat11, feat12
Severity: S3
Status:   open
Linked:   task14 (Fix), test22 (Reproduktion, geplant)

**Beschreibung**
`eledialeitnerflow_reset_userdata()` in `lib.php` löscht Kartenstände,
Sitzungen und Question-Usages, ruft aber weder
`eledialeitnerflow_grade_item_update($instanz, 'reset')` noch
`eledialeitnerflow_update_grades()`. Moodle-Aktivitätsmodule setzen beim
Kurs-Reset üblicherweise auch ihr Bewertungselement zurück.

**Reproduktion (erwartet)**
1. Aktivität mit Bewertung „Prozent gelernt" anlegen.
2. Als Lernende Karten lernen, bis im Bewertungsbereich ein Wert > 0 steht.
3. Kurs zurücksetzen, Häkchen bei LeitnerFlow setzen.

**Erwartet** Der Wert im Bewertungsbereich verschwindet oder wird 0.
**Tatsächlich (erwartet)** Der alte Wert bleibt stehen, obwohl es keine
Lerndaten mehr gibt, bis eine neue Sitzung eine Neuberechnung auslöst.

---

### bug03 — `perpage` im Bericht ist unbegrenzt

Feature:  feat10
Severity: S4
Status:   open

**Beschreibung**
`report.php` liest `$perpage = optional_param('perpage', 25, PARAM_INT)` ohne
Obergrenze. Wer die URL manipuliert, kann eine sehr große Seite anfordern; bei
großen Kursen erzeugt das unnötige Last. Kein Sicherheitsproblem — die
Capability-Prüfung greift vorher —, aber eine offene Flanke für Selbst-DoS.

**Erwartet** Ein sinnvolles Maximum (z. B. 200), auf das geklemmt wird.

---

### bug04 — Privacy-Kontextermittlung filtert nicht auf das Modul

Feature:  feat13
Severity: S3
Status:   open
Linked:   task15 (Fix), test23 (Reproduktion, geplant)

**Beschreibung**
`provider::get_contexts_for_userid()` verbindet
`{context} → {course_modules} → {eledialeitnerflow}` über
`lq.id = cm.instance`, ohne über `{modules}` einzuschränken, dass `cm` überhaupt
eine LeitnerFlow-Instanz ist. Trifft die Instanz-ID einer anderen
Aktivitätsart auf dieselbe Zahl, wird deren Modulkontext mit in die Liste
aufgenommen.

**Folge** Beim Datenexport können LeitnerFlow-Daten unter einer fremden
Aktivität ausgegeben werden; beim Löschen kann ein fremder Kontext als betroffen
angezeigt werden. Der Kern der Löschung arbeitet über
`delete_user_data($leitnerflowid, $userid)` und trifft weiterhin die richtigen
Zeilen — es geht um falsche Kontextzuordnung, nicht um fremde Daten.

**Reproduktion (erwartet)** Kurs mit einer LeitnerFlow-Instanz der ID `N` und
einer beliebigen anderen Aktivität, deren Instanz-ID ebenfalls `N` ist; danach
Privacy-Export für eine Person mit Kartenständen anfordern.

**Erwartet** Nur der LeitnerFlow-Kontext erscheint.

---

### bug05 — Personen mit Sitzung, aber ohne Kartenstand, werden übersehen

Feature:  feat13
Severity: S3
Status:   open
Linked:   task15 (Fix), test23 (Reproduktion, geplant)

**Beschreibung**
Sowohl `get_contexts_for_userid()` als auch `get_users_in_context()` stützen
sich ausschließlich auf `eledialeitnerflow_card_state`. Eine Person, die eine
Sitzung startet und beendet, ohne eine Frage zu beantworten, hat eine Zeile in
`eledialeitnerflow_sessions`, aber keine in `card_state` — und ist für die
Privacy-API damit unsichtbar.

**Folge** Ihre Sitzungsdaten (Zeitpunkte, Zählerstände) werden weder exportiert
noch bei einer Löschanfrage erfasst. Das ist eine Vollständigkeitslücke der
Betroffenenrechte, siehe `privacy.md`.

**Erwartet** Beide Abfragen berücksichtigen zusätzlich die Sitzungstabelle
(`UNION` oder zweite `add_from_sql()`-Zeile).

---

### bug06 — Ungenutztes `global $DB` in `process_answer()`

Feature:  feat03
Severity: S4
Status:   open

`leitner_engine::process_answer()` deklariert `global $DB;`, verwendet die
Variable aber nie — die Methode ist bewusst frei von Datenbankzugriffen und
gibt nur das aktualisierte Objekt zurück. Die Zeile ist irreführend: sie legt
nahe, dass die Methode schreibt.

---

### bug07 — Kategorienzuordnung bleibt unverändert, wenn das Formularfeld fehlt

Feature:  feat01
Severity: S4
Status:   open

`_eledialeitnerflow_process_categories()` in `lib.php` schreibt
`questioncategoryids` nur, wenn `$data->questioncategoryids_array` gesetzt und
ein Array ist. Fehlt das Feld — etwa bei einem programmatischen
`eledialeitnerflow_update_instance()`-Aufruf aus einem Generator oder einer
Fremdintegration —, bleibt die alte Zuordnung stillschweigend bestehen, statt
dass der Aufruf fehlschlägt. Über das reguläre Formular kann das nicht
passieren, weil die Auswahl dort Pflichtfeld ist.

---

## Tests

### Automatisiert — PHPUnit

`tests/engine/leitner_engine_test.php`, 29 Testmethoden; mit den Zeilen des
Data-Providers zählt PHPUnit mehr Fälle. Ausgeführt gegen die lokale
Docker-Umgebung sowie in der CI-Matrix (PHP 8.2 / `MOODLE_405_STABLE`,
PHP 8.3 / `MOODLE_500_STABLE`, PostgreSQL 14).

> Die Zahlenangaben in `README.md` („22 Tests") und `CHANGELOG.md`
> („37 tests / 81 assertions") widersprechen sich und dieser Datei → `task09`.
> Diese Datei ist ab sofort die einzige Stelle, an der die Zahl gepflegt wird.

| ID | Test | Prüft Kriterium aus |
|---|---|---|
| test01 | `test_get_category_ids_prefers_new_field`, `…falls_back_to_legacy_field`, `…returns_empty_when_unconfigured` | feat01 — Kategorienauflösung inkl. Legacy-Feld |
| test02 | `test_card_states_are_isolated_per_user` | feat01 — Lernstand ist personenbezogen |
| test03 | `test_attempt_count_increments_on_every_answer` | feat02 — jede Antwort aktualisiert genau eine Zeile |
| test04 | `test_calculate_box` (Data-Provider) | feat03 — Fach nie außerhalb `[1, boxcount]` |
| test05 | `test_correct_answer_on_new_card_increments_correctcount`, `…advances_card_to_next_box`, `test_reaching_threshold_marks_card_as_learned`, `test_learned_card_stays_in_highest_box`, `test_process_answer_on_already_learned_card_stays_learned`, `test_correct_after_reset_rebuilds_progress` | feat03 — Verhalten bei richtiger Antwort |
| test06 | `test_wrong_answer_with_reset_behavior`, `…with_back_one_behavior`, `…with_no_change_behavior`, `test_wrong_answer_on_fresh_card_with_back1_stays_at_box_1` | feat03 — alle drei Falsch-Modi |
| test07 | `test_open_cards_are_all_eligible_for_session` | feat04 — offene Karten sind wählbar |
| test08 | `test_learned_cards_excluded_from_session` | feat04 — gelernte Karten erscheinen nicht |
| test09 | `test_get_user_stats_counts_correctly`, `test_get_user_stats_zero_total_gives_zero_percent` | feat05 — Statistik und leerer Pool |
| test11 | `test_get_box_distribution_groups_cards_correctly`, `test_boxcount_5_distributes_correctly` | feat05 — Fächerverteilung |
| test12 | `test_get_session_history_returns_most_recent_first`, `test_get_session_stats_returns_zeros_for_new_user`, `test_get_session_stats_aggregates_correctly` | feat06 — Historie und Durchschnitt |
| test24 | `test_delete_user_data_removes_all_records` | feat13 — gemeinsamer Löschpfad der Engine |
| — | `test_save_and_reload_card_state`, `test_save_card_state_updates_existing_record`, `test_get_card_state_returns_null_for_unknown` | Persistenzschicht der Engine |

### Automatisiert — Behat

| ID | Feature-Datei | Szenarien | Prüft |
|---|---|---|---|
| test10 | `attempt_session.feature` | Sitzung starten; während der Sitzung zurück zur Übersicht; Sitzung vorzeitig beenden | feat02 |
| test15 | `teacher_report.feature` | Bericht aus der Aktivität öffnen; Hinweis, wenn niemand gestartet hat; Lernende kommen nicht hinein | feat10 |
| test16 | `student_view.feature` | Dashboard erscheint; Hinweis ohne Fragen; kein Bericht-Knopf für Lernende | feat05, feat10 |
| test17 | `activity_create.feature` | Anlegen mit Standardwerten; Leitner-Einstellungen; Anzeige- und Feedback-Einstellungen; Bewertung | feat03, feat07, feat11 |
| test20 | `capabilities.feature` | Lehrkraft darf anlegen, Lernende nicht | feat14 |

### Lücken — geplant

| ID | Test | Deckt ab | Bezug |
|---|---|---|---|
| test13 | Feedback-Stile und `animationdelay` in `attempt.php` | feat07, feat08 | task04 |
| test14 | `tour_installer`: zweiter Aufruf erzeugt keine Dublette | feat09 | task04 |
| test18 | Backup mit Nutzerdaten und Restore in einen neuen Kurs | feat12 | task04 |
| test19 | `privacy_provider_test` mit dem Core-Trait `provider_testcase` | feat13 | task04, task15 |
| test21 | Fächerzahl verkleinern, Verteilung und Auswahl prüfen | feat03, feat05 | bug01, task13 |
| test22 | Kurs-Reset mit aktiver Bewertung | feat11, feat12 | bug02, task14 |
| test23 | Privacy-Kontexte: fremdes Modul mit gleicher Instanz-ID; Person nur mit Sitzung | feat13 | bug04, bug05, task15 |

### Manuell

Die manuelle Abnahme läuft über die Verifikationsliste `verify01`–`verify11` in
`04-tasks.md`. Barrierefreiheit (WCAG 2.2 / BFSG) ist bisher **nicht** geprüft;
das Dashboard arbeitet mit Farbcodierung der Fächer und wäre ein sinnvoller
erster Prüfgegenstand.

---

## Statische Prüfungen

`bin/precheck.sh` fährt die Prüfungen des Plugins-Directory-Prechecks im
`webserver`-Container: `phplint`, `phpcs` (Moodle-Standard), `phpdoc`,
`savepoint`, `js/eslint`, `css/stylelint`, `grunt` (AMD-Build), `thirdparty`.
Ergänzt um zwei projekteigene Blocker: keine nicht-englischen Sprachpakete im
ZIP (`adr09`) und CSS ausschließlich unter `.path-mod-eledialeitnerflow` /
`.lf-` (`adr12`).

Letzter dokumentierter Stand (Release 1.0.0, `CHANGELOG.md`): alle Prüfungen
grün, `phpcs` mit 0 Fehlern und 0 Warnungen. Ein Nachlauf nach den Fixes zu
`bug01`–`bug07` steht aus.


### test24 — Letzte Rückmeldung vor Zusammenfassung

Status: pass (manueller Browserlauf), 24.10.2026, h5p52 Moodle 5.2.4+, Admin-Testkonto. F-07 Sitzung 4: erste zwei Mehrfachantworten richtig, letzte Einzelantwort absichtlich falsch. Letzte Frage zeigt Erklärung und Continue-Link ohne automatischen Wechsel. Erst Klick zeigt 2/3-Zusammenfassung. Antwortoptionen der Fahrup-Fragen ohne Nummerierung. PHP-Lint: 25 Dateien bestanden; git diff --check bestanden. Screenshot im Fahrup-Projekt: content/nuggets-pilot/moodle-pilot/last-answer-feedback.png. Vollständige Behat-Suite nicht ausgeführt.


### test25 — Szene und Training in derselben Aktivität

Status: pass (gezielter manueller Browserlauf), 24.10.2026, h5p52. F-07: eingebettete 3D-Ansicht, Blickpunkt 3, Übergang zu Fragen. Antwort ausgewählt, Szene geöffnet und geschlossen: Auswahl bleibt erhalten; anschließende Bewertung korrekt. Szene auch in Rückmeldung vorhanden. F-03: Einstieg zeigt Bus-Szene, bestehende Sitzung wird fortgesetzt statt ersetzt; Originalfilm im eingebetteten Dialog mit geladenem Videobild. PHP-Lint 26 Dateien und PHPCS bestanden. Anfangs direkte iframe-URL leer; auf escaped srcdoc in Sandbox umgestellt. Authentifiziertes Video in opaque Sandbox zunächst nicht abspielbar; Pilotfilm als data-URL eingebettet, Videobild danach sichtbar. Keine vollständige automatisierte Regression, Rollen-/Backup-/Restore-Abnahme oder Accessibility-Prüfung.

DevFlow-Heuristik meldet weiterhin sechs bereits vorhandene Dokumentpfade als außerhalb ihres Vertrags (u. a. docs/changelog.md und docs/product.md, die das lokale Masterdokument ausdrücklich vorsieht). Keine Bereinigung fremder Dokumente vorgenommen. git diff --check bestanden.

### test26 — F07 serverseitige Modus- und QE-Regression

**Status:** pass · 24.10.2026 · feat15.AC01–05/07. h5p52, Moodle 5.2.4+ (Build 20261021), PHP 8.3.32, PostgreSQL 16; isolierte PHPUnit-Präfixe t_ gegenüber Pilot m_. Vollständige Pluginsuite: **61 Tests, 139 Assertions, grün**. Davon neue Szenentests: Guided verändert auch vorhandene Karten nicht; getrennte spätere Review; finaler QE-Zustand und idempotenter Abschluss; unveränderte Frageposition im Feedback; gespeicherte ungeprüfte Antwort ohne Bewertung; falsche Person/Aktivität/Slot und fehlende attempt/manage-Rechte; richtige/teilrichtige/falsche Mehrfachantwort; verborgene Frage; unbekannte Assets/Zielreferenzen und unabhängige Reihenfolge der Mapping-Schlüssel; Sichtungsreset; Guided-only-Privacy-Kontexte, Export und Löschung; 90-Tage-Cleanup; tatsächliches Backup/Restore mit QE-Rückmeldung, drei Bindungen und aufgelöstem Manifest.

Eine absichtlich falsche Kartenzahl-Erwartung wurde gegen den Haupttest ausgeführt: erwartetes Scheitern „0 matches expected 1“. Danach Originaltest wiederhergestellt und gesamte Suite grün. Keine Test-Erwartung zugunsten der Implementierung gelockert. XMLDB-Upgrade lokal auf zuvor bestehender Installation erfolgreich, Bestandskarten unverändert. Same-site-Restore benötigt wie Core QE einen Fallback auf bestehende geteilte Fragen, wenn keine neue Frage angelegt wird; dies ist implementiert und getestet. Standortübergreifende Wiederherstellung ist nicht separat nachgewiesen.

### test27 — F07 Browser und reale konkurrierende Requests

**Status:** pass · 24.10.2026 · feat15.AC01–06. Chromium, h5p52, lokales Admin-Testkonto. Guided-Sitzung 7: drei Antworten, 2/3 richtig, einschließlich letzter absichtlich falscher Antwort jeweils Erklärung vor Weiter/Summary. Snapshotvergleich sämtlicher Kartenfelder vor/nach Guided: identisch (Fragen 7, 9, 10). Ungeprüfte Auswahl, Frageindex, geprüftes Feedback und Sichtungen bei Szene↔Frage, Reload/Wiederaufnahme erhalten.

Review-Sitzung 8: getrennt von Guided, 3/3 richtig. Zwei gleichzeitig per Browser-Tabs abgesendete HTTP-Prüfrequests für Slot 1 (Moodle-Sessionsperre vor Verarbeitung freigegeben): beide zeigen Feedback, DB weist exakt +1 attemptcount und +1 correctcount für Frage 9 aus; andere Karten unverändert. Weitere Slots regulär gewertet. Neue Guided-Sitzung 9 bleibt ohne Kartenwertung.

Mobil 320×740: Dokumentbreite 320, Stage 304, 5 platzierte Marker, 0 unplatzierte, 0 Platzierungsverletzungen; auch nach Drehen, Neigen und Zoom. Zahlenliste per Maus/Tastatur erreichbar. Escape schließt Erklärung und gibt Fokus an die Zahl zurück. WebGL-deaktivierter Aufruf `render=flat`: nutzbare 2D-Ansicht und deaktiviertes 3D. Originaldialog zeigt den bestätigten fehlenden F07-Medienhinweis. Desktop/3D geladen, Kenney-Textur im versionierten Paket ergänzt. Screenshots shot08–10. Keine allgemeine fachliche/amtliche Freigabe und kein vollständiges Accessibility-Audit.

### test28 — Automatisierte F07-Browserregression

**Status:** pass im gezielten Nachlauf · 24.10.2026. Erstlauf 16 Szenarien: 14 pass, zwei Testfixtures/Steps korrigiert: älterer Core-Schritt für Aktivitätswähler war im lokalen 5.2-Layout nicht ausführbar (durch den bereits bewährten Core-Schritt zum Öffnen der Modulkonfiguration ersetzt); die neue True/False-Frage verwendet jetzt das tatsächliche Generatorfeld `correctanswer` statt eines nicht wirksamen `answer 1`-Felds. Erwartung „2 von 3“ beibehalten. Gezielter Nachlauf der beiden korrigierten Szenarien: 2/2 Szenarien und 40/40 Schritte pass. Die anderen 14 Szenarien waren im Erstlauf grün und wurden nach den Fixture-/Stepkorrekturen nicht erneut ausgeführt. Neue F07-Prüfung enthält den Core-Accessibility-Check auf der Erkundungsfläche.

Statisch: PHP-Lint, Moodle PHPCS (0 Fehler/0 Warnungen), PHPDoc, Pluginvalidierung, Upgrade-Savepoints und Mustache bestanden. Der native ES-Modul-Build mit esbuild ist erfolgreich; keine neue AMD-Datei. Die parallel gestartete erste PHPCS-Prüfung kollidierte mit der temporären Savepoint-Prüfdatei; isolierter Nachlauf ist grün.

DevFlow-Heuristik: sechs bekannte Altpfade außerhalb ihrer generischen Heuristik sowie die erforderliche Drittanbieter-Lizenz `player/THREE-LICENSE.txt`; Mastervertrag dokumentiert die Altpfade. Lizenzdatei bleibt beim ausgelieferten Three-Code. Kein stilles Bereinigen fremder Dokumente.

### test29 — LeitnerFlow unabhängig und generischer Connector

**Status:** pass (lokaler Zielstand) · **Akzeptanzkriterium:** feat15.AC02/03/05/08/10 · **Typ:** PHPUnit, Installation, API-Vertrag.

Ohne Fahrup installieren/aktualisieren/benutzen. Dummy-Quellmodul registriert nur berechtigte QE-Review. Fremde Nutzer, Quell-/Zielkontexte, Kategorien/Versionen, deaktivierte Quelle, Replay und veränderte Identität zurückweisen. Guided erzeugt weder Registrierung noch Kartenänderung. Native und externe Ergebnisse verwenden dieselben Boxenregeln.

Nachweis 24.10.2026: 45 Tests/99 Assertions in aktueller Suite; separat 44/94 bei entferntem Fahrup-Verzeichnis und frisch initialisiertem Teststand. Native LeitnerFlow-Frage in dieser Konstellation tatsächlich beantwortet/abgeschlossen. Neue Privacy-Prüfung erfasst externe Zustände ohne Karten und erhält fremde Quell-Usage. Frühere test26–28 bleiben historische Pilotnachweise. Aktuelles Detailprotokoll: mod_fahrup/docs/05-quality.md.

### test30 — Fahrup-Ablauf und Datenhoheit

**Status:** pass (gezielte lokale Suite) · **Akzeptanzkriterium:** feat15.AC01–07/09/10 · **Typ:** PHPUnit, Behat.

Echte QE-Fragen, immutable Guided/Review, abgeschlossener Guided als Voraussetzung, gespeicherte Auswahl/Feedback und letzte Erklärung. Ausschließlich Connectorzugriff auf Karten. Eigene Privacy, Dateien, Reset und 90-Tage-Cleanup; Detailablauf löscht kein Kartenwissen.

Nachweis 24.10.2026: 29 Tests/72 Assertions in mod_fahrup. Echte QE, Guided ohne Registrierung/Wertung, spätere Review, Rechte/Versionen/Slots, Nachlese/Privacy/Same-site-Backup, Reset-Widerruf und explizite Wiederanbindung geprüft. Frühere test26–28 bleiben historische Pilotnachweise. Aktuelles Detailprotokoll: mod_fahrup/docs/05-quality.md.

### test31 — Migrationsprobe und Eigentümerwechsel

**Status:** pass (isolierte Proben) · **Akzeptanzkriterium:** feat15.AC04/05/07/11 · **Typ:** Integration/CLI auf isolierter Restorekopie.

Aktive ungeprüfte, aktive Feedback-, abgeschlossene Guided-/Review- und abgelaufene Versuche; Antwortdateien und Legacy-F03-Konfiguration. QUBA-Komponente/Kontext, Slots/Steps/Fractions, Dateihashes und Zeitstempel vergleichen. Zweiter Lauf ohne Änderung; Abbruch und Wiederanlauf. Keine verwaisten oder doppelt besessenen QUBA. Inkonsistente Quellen blockieren vor Bereinigung.

Nachweis 24.10.2026: Frisches Code-/DB-/Dateibackup separat wiederhergestellt. Drei bestehende Szenenversuche übertragen; zweiter Lauf ohne Änderung, eigener QUBA-Eigentümer und unveränderte Kartenhashes. Partielle Review/Response-Datei/Fristen erhalten. Wartungsmodus-Guard negativ geprüft. Früher lokaler Wartungspfadfehler und die korrigierte Ausführung sind im Fahrup test02 offengelegt. Frühere test26–28 bleiben historische Pilotnachweise. Aktuelles Detailprotokoll: mod_fahrup/docs/05-quality.md.

### test32 — Keine doppelte Wertung bei Migration und Parallelität

**Status:** pass (gezielte Fälle) · **Akzeptanzkriterium:** feat15.AC02/03/10/11 · **Typ:** PHPUnit und reale parallele HTTP-Requests.

Alle Kartenfelder/Noten vor und nach Migration identisch. Historisch angewendete Review replayen ohne Änderung; offene Slots nach Migration genau einmal werten. Native und externe Antworten gleichzeitig ohne verlorene Updates; Lock-/Transaktionsfehler und Rollback. Reset/Restore/Retentionsablauf erlauben kein Wiederanwenden alter Registrierungen.

Nachweis 24.10.2026: Reale parallele Fh-HTTP-Prüfrequests genau ein Beleg; Guidance-Kartenhash vollständig unverändert. Historischer Replay nach Transfer ohne Änderung, offene Slots einer partiell gewerteten Review genau einmal. Reset widerruft Altversuch und neue Review erhält neue Identität. Gemischter nativer/Connector-Parallelrequest nicht separat ausgeführt; gemeinsame Schreibsperre implementiert. Frühere test26–28 bleiben historische Pilotnachweise. Aktuelles Detailprotokoll: mod_fahrup/docs/05-quality.md.

### test33 — Fahrup-Oberfläche und Wiederherstellung

**Status:** pending (Teilnachweise vorhanden) · **Akzeptanzkriterium:** feat15.AC01/04–07/09 · **Typ:** Behat/manuell, Backup/Restore.

320 px und 200 % Zoom, Tastatur/Escape/Fokus, 2D/3D/Kamera/Fallback und Originalmedien. Privacy-Export/-Löschung Guided-only. Backup/Restore same-site und cross-site mit Fragen-/Nutzer-/Zielreferenzen, fehlendem Ziel und bereits verarbeiteten Ergebnissen; nach Restore keine automatische Neuwertung.

Nachweis 24.10.2026: Manueller Desktop/Mobil-320-/Kamera-/2D-/Original-/Escape-/Rollenlauf und Same-site-Guided-Restore grün; erster Behat-Hauptablauf 1/34 pass. Lehrendenvorschau in späterem Lauf pass. Abschließender Gesamtlauf nach Async-Client-/Testwarte-Korrektur und regulärem Neuaufbau des inzwischen freien Behat-Stands: 2 Szenarien / 49 Schritte pass, einschließlich Core-Accessibility-Prüfung. Cross-site und 200%-Zoom noch offen. Frühere test26–28 bleiben historische Pilotnachweise. Aktuelles Detailprotokoll: mod_fahrup/docs/05-quality.md.

### test34 — Cutover, Bereinigung und unabhängige Pakete

**Status:** pending (lokaler Cutover belegt) · **Akzeptanzkriterium:** feat15.AC08/09/11 · **Typ:** XMLDB/Upgrade, statisch, CI, Betrieb.

Neuinstallation und Upgrade von lokalem Schema 2026102400; Endschema gleich. Keine Fahrup-Assets/Klassen im LeitnerFlow-Paket. Restdaten verhindern destruktives Cleanup. Fahrup-Deinstallation in Testkopie lässt LeitnerFlow funktionsfähig. Verifiziertes Code-/DB-/Moodledata-Rollback vor Schreibfreigabe, Versionsmatrix und nachvollziehbarer Cutoverbericht.

Nachweis 24.10.2026: Lokales Upgrade/Cutover und reine LeitnerFlow-Neuinstallation ohne Fh belegt; unabhängige Runtime und Pakete geprüft. Eigene Moodle-Deinstallationsprobe in DB-Kopie erhält alle Kartenfelder und entfernt Fh-QUBA. Weitere Versionsmatrix, Cross-site und vollständige Betriebs-/PO-Abnahme vor Release offen. Frühere test26–28 bleiben historische Pilotnachweise. Aktuelles Detailprotokoll: mod_fahrup/docs/05-quality.md.
