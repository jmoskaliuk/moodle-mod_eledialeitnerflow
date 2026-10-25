# 01 — Features

Was wird gebaut und warum, mit Akzeptanzkriterien. Ein Feature ist erst **done**,
wenn die Checkliste in `00-master.md` §6 erfüllt ist.

**Status-Werte:** `geplant` · `in Arbeit` · `umgesetzt` · `verworfen`
**Release:** siehe `releasenotes.md`.

Übersicht:

| ID | Feature | Status | Release | Tests |
|---|---|---|---|---|
| feat01 | Karten aus der Fragensammlung | umgesetzt | rel01 | test01, test02 |
| feat02 | Lernsitzung mit Sofort-Feedback | umgesetzt | rel01 | test03, test10 |
| feat03 | Leitner-Regelwerk (Fächer, Schwelle, Falsch-Verhalten) | umgesetzt | rel01 | test04–test06 |
| feat04 | Kartenauswahl und gezieltes Box-Training | umgesetzt | rel01 | test07, test08 |
| feat05 | Lern-Dashboard | umgesetzt | rel01 | test09, test11 |
| feat06 | Sitzungshistorie und Trend | umgesetzt | rel01 | test12 |
| feat07 | Feedback-Stile und Animation | umgesetzt | rel01 | test13 |
| feat08 | Gamified-Modus mit Streaks | umgesetzt | rel01 | test13 |
| feat09 | Einführungstouren | umgesetzt | rel01 | test14 |
| feat10 | Teilnehmendenbericht mit Reset | umgesetzt | rel01 | test15, test16 |
| feat11 | Bewertungsintegration | umgesetzt | rel01 | test17 |
| feat12 | Backup, Restore, Kurs-Reset | umgesetzt | rel01 | test18 |
| feat13 | Datenschutz-Provider | umgesetzt | rel01 | test24, test19 (geplant) |
| feat14 | Rollen, Rechte, Event-Logging | umgesetzt | rel01 | test20 |
| feat15 | F07: Guided und separate Leitner-Wiederholung | umgesetzt, In Review | lokaler Pilot | test26–28 |

---

## feat01 — Karten aus der Fragensammlung

**Kernfunktion:** Karten erzeugen · **Status:** umgesetzt · **Warum:** Der
Kartenbestand soll keine zweite Inhaltsquelle sein (`product.md` §3).

Eine Aktivität bezieht ihre Karten aus einer oder mehreren Fragenkategorien.
Jede Frage der Kategorie wird zu einer Karte; Unterkategorien werden nicht
automatisch einbezogen (`adr11`).

**Akzeptanzkriterien**

- [x] Beim Anlegen sind eine oder mehrere Fragenkategorien wählbar; die Auswahl ist Pflicht.
- [x] Nur die jeweils höchste Fragenversion mit `status = 'ready'` wird verwendet.
- [x] Mehrfach vorkommende Fragen-IDs werden dedupliziert.
- [x] Die Auswahl kann nachträglich geändert werden; bestehende Lernstände zu weiterhin enthaltenen Fragen bleiben erhalten.
- [x] Ohne Fragen zeigt die Aktivität einen Hinweis statt eines Startknopfs.
- [x] Der Legacy-Einzelwert `questioncategoryid` wird gelesen, wenn `questioncategoryids` leer ist.

**Umsetzung:** `leitner_engine::get_category_ids()`,
`get_questions_from_category()`, `get_questions_from_categories()`,
`_eledialeitnerflow_process_categories()` in `lib.php`.

---

## feat02 — Lernsitzung mit Sofort-Feedback

**Kernfunktion:** Lernsitzung · **Status:** umgesetzt

Eine Sitzung enthält bis zu `sessionsize` Karten. Die Fragen werden über die
Moodle Question Engine mit dem Verhalten `immediatefeedback` gestellt
(`adr02`); pro Sitzung existiert genau eine `question_usage_by_activity`.

**Akzeptanzkriterien**

- [x] Eine gestartete Sitzung ist wiederaufnehmbar; `currentindex` merkt die Position.
- [x] Eine Sitzung kann vorzeitig beendet werden.
- [x] Es existiert höchstens eine aktive Sitzung pro Person und Aktivität.
- [x] Nach der letzten Karte wird die Sitzung abgeschlossen, `timecompleted` gesetzt und `status = 1`.
- [x] Jede Antwort aktualisiert genau eine Zeile in `eledialeitnerflow_card_state`.
- [x] Alle schreibenden Aufrufe sind mit `require_sesskey()` geschützt.

**Umsetzung:** `attempt.php`, Tabelle `eledialeitnerflow_sessions`.

---

## feat03 — Leitner-Regelwerk

**Kernfunktion:** Regelwerk konfigurieren · **Status:** umgesetzt

Die Lehrkraft legt fest, wie viele Fächer es gibt (`boxcount`, 1–5), wie viele
richtige Antworten eine Karte als gelernt markieren (`correcttolearn`) und was
bei einer falschen Antwort passiert (`wrongbehavior`).

**Akzeptanzkriterien**

- [x] `correctcount` ist die führende Größe, das Fach wird daraus berechnet (`adr04`).
- [x] Richtige Antwort: `correctcount + 1`; ab `correcttolearn` gilt die Karte als gelernt (`status = 1`).
- [x] Falsch, Modus *Reset*: `correctcount = 0`, Fach 1, `status = 2`.
- [x] Falsch, Modus *Ein Fach zurück*: `correctcount - 1` (Minimum 0), Fach neu berechnet.
- [x] Falsch, Modus *Unverändert*: `correctcount` bleibt, `status = 2`.
- [x] `calculate_box()` liefert nie einen Wert außerhalb von `[1, boxcount]`.
- [ ] Wird `boxcount` nachträglich verkleinert, werden bestehende `currentbox`-Werte nachgezogen. → offen, siehe `bug01`

**Umsetzung:** `leitner_engine::calculate_box()`, `leitner_engine::process_answer()`.

---

## feat04 — Kartenauswahl und gezieltes Box-Training

**Kernfunktion:** Kartenauswahl · **Status:** umgesetzt

Zwei Strategien: *Niedrige Fächer zuerst* (Standard) füllt die Sitzung von
Fach 1 aufwärts; *Gemischt* zieht zufällig aus allen offenen Karten. Zusätzlich
kann eine einzelne Box gezielt geübt werden.

**Akzeptanzkriterien**

- [x] Gelernte Karten erscheinen in keiner der beiden Strategien.
- [x] Nie beantwortete Karten zählen als Fach 1.
- [x] Innerhalb eines Fachs ist die Reihenfolge zufällig.
- [x] Die Sitzung enthält höchstens `sessionsize` Karten.
- [x] Ein Klick auf ein Fach im Dashboard startet eine Sitzung ausschließlich mit Karten dieses Fachs.
- [x] Ist ein Fach leer, ist es nicht anklickbar.

**Umsetzung:** `leitner_engine::select_session_questions()`,
`select_questions_from_box()`.

---

## feat05 — Lern-Dashboard

**Kernfunktion:** Fortschritt sichtbar machen · **Status:** umgesetzt

`view.php` zeigt die Verteilung der Karten auf die Fächer, die Zahl gelernter
Karten und einen mehrsegmentigen Fortschrittsbalken (Farbverlauf warm → kühl).

**Akzeptanzkriterien**

- [x] Die Summe der angezeigten Karten entspricht der Zahl der Fragen im Pool.
- [x] Der Prozentwert „gelernt" entspricht `learned / total`, gerundet.
- [x] Bei leerem Pool erscheint ein Hinweis statt einer 0-Prozent-Anzeige.
- [x] Sind alle Karten gelernt, erscheint eine entsprechende Meldung.
- [ ] Nach Verkleinern von `boxcount` bleibt die Summe korrekt. → offen, siehe `bug01`

**Umsetzung:** `view.php`, `leitner_engine::get_box_distribution()`,
`get_user_stats()`, `styles.css` (`lf-`-Klassen).

---

## feat06 — Sitzungshistorie und Trend

**Kernfunktion:** Fortschritt sichtbar machen · **Status:** umgesetzt

Unter dem Dashboard erscheint die Historie abgeschlossener Sitzungen mit
Richtig-Quote, Fortschrittsbalken und Dauer, dazu ein Trend der letzten
Sitzungen gegen den eigenen Gesamtdurchschnitt.

**Akzeptanzkriterien**

- [x] Nur abgeschlossene Sitzungen (`status = 1`) erscheinen, neueste zuerst.
- [x] Die Quote berechnet sich aus `questionscorrect / questionsasked`.
- [x] Ohne abgeschlossene Sitzung wird der Block nicht gerendert.
- [x] Die Dauer ergibt sich aus `timecompleted - timecreated`.

**Umsetzung:** `leitner_engine::get_session_history()`, `get_session_stats()`.

---

## feat07 — Feedback-Stile und Animation

**Kernfunktion:** Rückmeldung dosieren · **Status:** umgesetzt

Fünf Stufen: *Aus*, *Minimal*, *Animiert*, *Detailliert*, *Gamifiziert*, dazu
eine konfigurierbare Verzögerung (`animationdelay`, 500–3000 ms) und ein
Schalter für die Kartenbewegung (`showanimation`).

**Akzeptanzkriterien**

- [x] *Aus* zeigt keine zusätzliche Rückmeldung über das Question-Engine-Feedback hinaus.
- [x] *Detailliert* wartet auf einen Klick statt automatisch weiterzuspringen.
- [x] Alle anderen Stufen springen nach `animationdelay` automatisch zur nächsten Karte.
- [x] Die Rückmeldung nennt Ausgangs- und Zielfach der Karte.
- [x] `showanimation = 0` unterdrückt die Bewegungsanimation, nicht den Text.

**Umsetzung:** `attempt.php` (`_eledialeitnerflow_render_with_feedback()`,
`_eledialeitnerflow_get_feedback_text()`, `_eledialeitnerflow_get_detailed_text()`),
`amd/src/card_transition.js`.

---

## feat08 — Gamified-Modus mit Streaks

**Kernfunktion:** Rückmeldung dosieren · **Status:** umgesetzt

Im Stil *Gamifiziert* zählt LeitnerFlow aufeinanderfolgende richtige Antworten
innerhalb einer Sitzung und hält die beste Serie fest.

**Akzeptanzkriterien**

- [x] `currentstreak` erhöht sich bei richtiger Antwort und fällt bei falscher auf 0.
- [x] `beststreak` ist das Maximum von `currentstreak` innerhalb der Sitzung.
- [x] Beide Werte werden pro Sitzung geführt, nicht kumulativ über Sitzungen.
- [x] Andere Feedback-Stile schreiben die Felder nicht fort.

**Umsetzung:** `attempt.php` (`_eledialeitnerflow_render_gamified_feedback()`),
Felder `currentstreak`, `beststreak` in `eledialeitnerflow_sessions`.

---

## feat09 — Einführungstouren

**Kernfunktion:** Einstieg begleiten · **Status:** umgesetzt

Zwei gebündelte `tool_usertours`-Touren: eine für Lernende auf `view.php`, eine
für Lehrende auf `report.php`. Inhalte zweisprachig über den Core-Filter
`multilang`.

**Akzeptanzkriterien**

- [x] Die Touren werden nicht in `install.php` importiert, sondern lazy bzw. per Upgrade (`adr08`).
- [x] Ein wiederholter Import erzeugt keine Dubletten.
- [x] Der Core-Filter `multilang` wird bei Installation aktiviert, falls er aus ist.
- [x] Fehlt `tool_usertours`, schlägt weder Installation noch Seitenaufruf fehl.
- [x] `showtour = 0` unterdrückt die Tour für die betreffende Aktivität.

**Umsetzung:** `classes/local/tour_installer.php`, `db/usertours/*.json`,
`db/install.php`, Upgrade-Schritt `2026042101`.

---

## feat10 — Teilnehmendenbericht mit Reset

**Kernfunktion:** Lernstand begleiten · **Status:** umgesetzt

`report.php` zeigt pro Person gelernte/offene/fehlerhafte Karten, einen
Fortschrittsbalken, die Zahl abgeschlossener Sitzungen und den Zeitpunkt der
letzten Sitzung. Dazu eine Kopfzeile mit Teilnehmendenzahl, Poolgröße und
durchschnittlichem Lernstand. Der Lernstand einzelner Personen kann
zurückgesetzt werden.

**Akzeptanzkriterien**

- [x] Zugriff nur mit `mod/eledialeitnerflow:viewreport`.
- [x] Reset nur mit `mod/eledialeitnerflow:resetprogress` und gültigem Sesskey.
- [x] Der Reset löscht Kartenstände, Sitzungen und die zugehörigen `question_usages`.
- [x] Die Liste ist sortier-, durchsuch- und seitenweise blätterbar.
- [x] Sind Personen eingeschrieben, aber ohne Fortschritt, erscheinen sie mit 0 statt zu fehlen.
- [x] Der Reset ist durch einen Bestätigungsdialog abgesichert.
- [ ] `perpage` ist nach oben begrenzt. → offen, siehe `bug03`

**Umsetzung:** `report.php`, `leitner_engine::get_all_students_stats()`,
`delete_user_data()`, `amd/src/confirm_reset.js`.

---

## feat11 — Bewertungsintegration

**Kernfunktion:** Bewertung optional anbinden · **Status:** umgesetzt

Wahlweise keine Bewertung oder „Prozent gelernter Karten" als Wert von 0–100 im
Bewertungsbereich.

**Akzeptanzkriterien**

- [x] `grademethod = 0` legt kein Bewertungselement an (`GRADE_TYPE_NONE`).
- [x] `grademethod = 1` legt ein Element mit `grademax = 100`, `grademin = 0` an.
- [x] Die Note entspricht `percent_learned` aus `get_user_stats()`.
- [x] Das Bewertungselement wird beim Löschen der Aktivität mitentfernt.
- [ ] Der Kurs-Reset setzt auch das Bewertungselement zurück. → offen, siehe `bug02`

**Umsetzung:** `eledialeitnerflow_grade_item_update()`, `_delete()`,
`_update_grades()`, `_get_user_grade()`, `_get_all_grades()` in `lib.php`.

---

## feat12 — Backup, Restore, Kurs-Reset

**Kernfunktion:** Betriebspflichten · **Status:** umgesetzt

**Akzeptanzkriterien**

- [x] `FEATURE_BACKUP_MOODLE2` wird gemeldet; Aktivitätseinstellungen werden gesichert.
- [x] Beim Kurs-Reset erscheint eine eigene Sektion mit einer Checkbox.
- [x] Der Reset löscht Kartenstände, Sitzungen und `question_usages` aller Instanzen des Kurses.
- [x] Das Löschen der Aktivität hinterlässt keine verwaisten Zeilen in den drei Tabellen.

**Umsetzung:** `backup/moodle2/*`, `eledialeitnerflow_reset_course_form_definition()`,
`eledialeitnerflow_reset_userdata()`, `eledialeitnerflow_delete_instance()`.

---

## feat13 — Datenschutz-Provider

**Kernfunktion:** Betriebspflichten · **Status:** umgesetzt · Details in `privacy.md`

**Akzeptanzkriterien**

- [x] `metadata\provider`, `request\plugin\provider` und `core_userlist_provider` sind implementiert.
- [x] Beide personenbezogenen Tabellen sind in den Metadaten beschrieben.
- [x] Export liefert Kartenstände und Sitzungen im Aktivitätskontext.
- [x] Löschung für eine Person, für alle Personen eines Kontexts und für eine Personenliste ist umgesetzt.
- [ ] Die Kontextermittlung filtert auf das Modul `eledialeitnerflow`. → offen, siehe `bug04`
- [ ] Personen mit Sitzung, aber ohne Kartenstand, werden gefunden. → offen, siehe `bug05`

**Umsetzung:** `classes/privacy/provider.php`, Sprachstrings `privacy:metadata:*`.

---

## feat14 — Rollen, Rechte, Event-Logging

**Kernfunktion:** Betriebspflichten · **Status:** umgesetzt · Details in `security.md`

Sechs eigene Capabilities und drei Events.

**Akzeptanzkriterien**

- [x] `addinstance`, `view`, `attempt`, `viewreport`, `manage`, `resetprogress` sind definiert.
- [x] Lehrende haben `attempt` per Archetyp **nicht** (`CAP_PREVENT`).
- [x] Gäste haben `view` nicht.
- [x] `addinstance` trägt `RISK_XSS`, `manage` trägt `RISK_CONFIG`, `resetprogress` trägt `RISK_DATALOSS`.
- [x] `session_started`, `session_completed`, `progress_reset` werden ausgelöst und tragen passende `crud`- und `edulevel`-Werte.

**Umsetzung:** `db/access.php`, `classes/event/*.php`,
`tests/behat/capabilities.feature`.

---

## Ideen — vorgeschlagen, nicht freigegeben

Nichts davon ist beschlossen. Umsetzung erst nach PO-Entscheidung.

| Idee | Gedanke | Bezug |
|---|---|---|
| Zeitbasierte Intervalle | Echtes Spaced Repetition (SM-2-artig) statt reiner Zählung | `product.md` §7 grenzt das derzeit bewusst aus |
| Unterkategorien rekursiv einbeziehen | Weniger Klicks bei tiefen Kategoriebäumen | `adr11` |
| Moodle-App-Unterstützung | `mobile.php` plus Templates | `task06` |
| Fälligkeits-Erinnerung | Nachricht, wenn länger nicht geübt wurde | benötigt Scheduled Task |
| Export des Lernstands | CSV für Lehrende | Abgrenzung zum Bewertungsbereich klären |


### Optionale Lernszene — beauftragter Pilot (task17)

Akzeptanz für F-03/F-07: Szene vor Fragen innerhalb derselben Aktivität; Fragen direkt start-/fortsetzbar; Szene während Frage und Rückmeldung aufklappbar, aktuelle Auswahl bleibt beim Klappen erhalten. Ohne Szene bleibt der bisherige Ablauf verfügbar. Erkundungsabschluss ist freiwillig und kein Kompetenznachweis. Keine Aussage über persistierten Szenenstatus zwischen Seiten. Umsetzung/Test: task17, test25.


## feat15 — F07: angeleitete Übung vor Leitner-Wiederholung

**Status:** bestehender Pilot umgesetzt, Zielarchitektur in Planung · Nutzerauftrag 24.10.2026. Der alte Fahrup-Plan REQ-01–07 beschreibt das bisherige Verhalten; für die Trennung gilt jetzt der Plan in task19–24. Noch kein Umbauauftrag.

- feat15.AC01: Erkunden → drei vorhandene Bankfragen → explizite Rückmeldung je Frage, einschließlich letzter → Zusammenfassung.
- feat15.AC02: Guided legt keine Kartenstände an und verändert weder Fach noch Antwort-/Erfolgszähler bestehender Karten.
- feat15.AC03: Erst eine getrennte Review-Sitzung nach abgeschlossenem Guided zählt; jeder finale Slot genau einmal.
- feat15.AC04: Ungeprüfte Auswahl wird ohne Bewertung gespeichert; Szene, Frage und Feedback sind wiederaufnehmbar. Stabile Blickpunkt-IDs sind nur Sichtungsstatus.
- feat15.AC05: Explizite Bankeintragszuordnung, fixierte Version pro Sitzung, echte QE-Bewertung und autorisierter Zugriff je Person/Aktivität.
- feat15.AC06: Direkter 2D/3D-Player des veröffentlichten Baukastenvertrags 1.0.0; F07 ohne Originalmedium mit verständlichem Hinweis.
- feat15.AC07: Nachlese für abgeschlossene Antworten 90 Tage (Nutzerentscheidung 24.10.2026); Kartenwissen bleibt erhalten. Privacy, Reset und Sicherung berücksichtigen Szenensitzungen.

Keine neue Kompetenzbewertung, keine neuen Fragen, keine Veränderung der Szenengeometrie. Kein PO-Done-Sign-off oder Release behauptet.

Ergänzung auf direkten Nutzerauftrag zur Trennung vom 24.10.2026 (Planungsumfang, kein Implementierungs-Sign-off):

- feat15.AC08: LeitnerFlow lässt sich ohne Fahrup installieren, aktualisieren und vollständig benutzen; keine Fahrup-Klassen, Szenenassets oder Pflichtkonfiguration im generischen Betrieb.
- feat15.AC09: Szenen, Player, Übungsversuche und Antwortnachlese gehören `mod_fahrup`; ausschließlich Fahrup hängt von der generischen LeitnerFlow-API ab.
- feat15.AC10: Wiederholungsabschlüsse laufen über einen szenenneutralen, serverseitig autorisierten, versionierten und idempotenten Vertrag; kein Client-Ergebnis als Autorität und keine direkte Tabellen-/Enginebenutzung durch Fahrup.
- feat15.AC11: Übernahme erhält vorhandene Kartenstände unverändert, bewahrt Antworten/Feedback/Fristen und aktive Versuche, zählt gewertete Review-Slots nicht erneut und verändert keine fremden Arbeitsbaumänderungen; Probe, Wiederanlauf und Rollback sind vor Cutover nachgewiesen.
