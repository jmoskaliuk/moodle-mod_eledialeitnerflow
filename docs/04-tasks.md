# 04 — Tasks

Operatives Zentrum. Alles, was gerade zu tun oder zu klären ist.

**Status-Werte:** `offen` · `in Arbeit` · `wartet` · `erledigt` · `verworfen`
**Priorität:** `hoch` · `mittel` · `niedrig`

---

## 🆕 Neu — noch nicht triagiert

Rohmaterial. Der Product Owner entscheidet, was daraus ein `taskXX`, ein
`featXX`-Vorschlag oder ein `qXX` wird.

- Nichts offen. (Stand 2026-08-27 — der Bestand unten ist bereits triagiert.)

---

## Done-Checkliste (wortgleich zu `00-master.md` §6)

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

---

## Offene Tasks

| ID | Titel | Prio | Status | Bezug |
|---|---|---|---|---|
| task01 | Screenshots aufnehmen und inventarisieren | hoch | offen | `screenshots.md`, `product.md` §10 |
| task02 | `$plugin->supported` in `version.php` setzen | hoch | wartet auf `q01` | `product.md` §9 |
| task03 | `upgrade.txt` mit Release 1.0.0 in Einklang bringen | hoch | offen | `adr07` |
| task04 | PHPUnit-Abdeckung außerhalb der Engine | hoch | offen | `05-quality.md` |
| task05 | Release-Doku und `.gitignore` committen | hoch | offen | Arbeitsverzeichnis |
| task06 | Moodle-App-Kompatibilität klären | mittel | wartet auf `q02` | `01-features.md` Ideen |
| task07 | Lastverhalten bei großen Fragenpools messen | mittel | offen | `product.md` §10 |
| task08 | Umlaute in `02-user-doc.md` korrigieren | mittel | offen | `02-user-doc.md` |
| task09 | Testzahlen in README vereinheitlichen | mittel | offen | `README.md`, `CHANGELOG.md` |
| task10 | `.gitattributes` von toten Einträgen befreien | niedrig | offen | `.gitattributes` |
| task11 | Englische Fassung der Nutzerdoku | niedrig | wartet auf `q03` | `02-user-doc.md` |
| task12 | `privacy.md` und `security.md` freigeben lassen | mittel | wartet | `privacy.md`, `security.md` |
| task13 | Fix für `bug01` (Fächerzahl verkleinert) | hoch | offen | `bug01` |
| task14 | Fix für `bug02` (Kurs-Reset ohne Notenreset) | mittel | offen | `bug02` |
| task15 | Fix für `bug04`/`bug05` (Privacy-Kontexte) | hoch | offen | `bug04`, `bug05` |
| task18 | F07: Guided-/Review-Anbindung (historischer Pilot) | hoch | wartet | feat15, task19–24, test26–28 |

---

### task01 — Screenshots aufnehmen und inventarisieren

**Prio:** hoch · **Status:** offen · **Bezug:** `screenshots.md`

`docs/screenshots/` enthält nur eine `.gitkeep`; weder Nutzerdoku noch README
noch die Katalogdatei zeigen ein Bild. Die Done-Checkliste verlangt für jedes
sichtbare Feature einen aktuellen Beleg.

**Zu tun**
1. Aufnahmekonvention aus `screenshots.md` umsetzen (Auflösung, Theme, Sprache).
2. Mindestens `shot01`–`shot07` aufnehmen (siehe Inventar dort).
3. In `02-user-doc.md` an den passenden Stellen einbinden.
4. `screenshots.md` mit Datum, Moodle-Version und Feature-Bezug füllen.

**Fertig, wenn:** jedes Feature mit sichtbarer Oberfläche in `screenshots.md`
einen Eintrag mit Datum hat und die Bilder in der Nutzerdoku erscheinen.

---

### task02 — `$plugin->supported` setzen

**Prio:** hoch · **Status:** wartet auf `q01`

`version.php` deklariert `requires = 2024042200`, aber keine `supported`-Spanne.
README, `CHANGELOG.md` und `product.md` bewerben „getestet bis 5.1" — nicht
maschinenlesbar belegt. Die Proven-Bewertung zieht dafür einen Punkt ab.

**Fertig, wenn:** `$plugin->supported = [404, XXX];` gesetzt ist, die Spanne mit
der CI-Matrix übereinstimmt und README, `product.md` §9 und `CHANGELOG.md`
dieselbe Aussage treffen.

---

### task03 — `upgrade.txt` mit Release 1.0.0 in Einklang bringen

**Prio:** hoch · **Status:** offen · **Bezug:** `adr07`

`upgrade.txt` beschreibt API-Änderungen unter den Überschriften `2.3.0`, `2.2.0`,
`2.1.0` und `2.0.0`. Ausgeliefert wird jedoch `1.0.0`. Für Integratoren ist das
irreführend, für den Directory-Review ein Rückfragegrund.

**Zu tun:** Entweder alle internen Stände zu einem Abschnitt `1.0.0`
zusammenfassen (mit Hinweis auf die interne Vorgeschichte) oder die
Vorgeschichte als „pre-1.0 (intern)" kennzeichnen.

**Fertig, wenn:** keine Versionsnummer in `upgrade.txt` oberhalb von `1.0.0`
steht und der Bezug zu `adr07` erklärt ist.

---

### task04 — PHPUnit-Abdeckung außerhalb der Engine

**Prio:** hoch · **Status:** offen

Getestet ist ausschließlich `leitner_engine`. Ungetestet: die Callbacks in
`lib.php` (Instanz-CRUD, Bewertung, Kurs-Reset), `privacy/provider.php` und
`tour_installer`. Gerade die Löschpfade sind sicherheits- und datenschutz-
relevant und werden von keinem automatischen Test berührt.

**Zu tun**
1. `mod_eledialeitnerflow_lib_test`: `add`/`update`/`delete_instance`,
   Bewertungselement, `reset_userdata`.
2. `mod_eledialeitnerflow_privacy_provider_test` mit dem Core-Trait
   `provider_testcase` — deckt gleichzeitig `bug04` und `bug05` ab.
3. `tour_installer`: zweimaliger Aufruf erzeugt keine Dublette.

**Fertig, wenn:** die drei Testklassen grün sind und `05-quality.md` die neue
Zahl führt.

---

### task05 — Release-Doku und `.gitignore` committen

**Prio:** hoch · **Status:** offen

Das Arbeitsverzeichnis enthält unversionierte Release-Dokumentation:
`CHANGELOG.md`, `RELEASE-NOTES-v1.0.0.md`, `bin/build-user-doc.sh`, `docs/`,
dazu die Löschung von `CHANGES.md` und Änderungen an `.gitignore`, `README.md`,
`README-DE.md`. Solange das nicht eingecheckt ist, ist der veröffentlichte Stand
nicht reproduzierbar — und `bin/release.sh` baut aus `HEAD`, also ohne diese
Änderungen.

**Zu tun:** Änderungen in nachvollziehbaren Commits einchecken, insbesondere die
Ablösung von `CHANGES.md` durch `CHANGELOG.md` begründen.

**Fertig, wenn:** `git status` sauber ist und `bin/release.sh` denselben Stand
erzeugt, der dokumentiert ist.

---

### task06 — Moodle-App-Kompatibilität klären

**Prio:** mittel · **Status:** wartet auf `q02`

Es gibt keine `db/mobile.php` und keine Mobile-Templates. Ob die Aktivität in
der Moodle App überhaupt sinnvoll erscheint, ist nicht geprüft. `product.md` §10
zieht dafür einen Punkt ab.

**Fertig, wenn:** entweder Mobile-Unterstützung implementiert und getestet ist
oder die Nichtunterstützung in `product.md` §7 und im README ausdrücklich steht.

---

### task07 — Lastverhalten bei großen Fragenpools messen

**Prio:** mittel · **Status:** offen

`get_questions_from_categories()` lädt bei jedem Dashboard-Aufruf alle Fragen-
IDs der gewählten Kategorien und `get_all_card_states()` alle Kartenstände der
Person. `get_all_students_stats()` fragt Sitzungszahl und letzten Zeitpunkt je
Person einzeln ab. Für 50 Fragen und 30 Personen unkritisch; für 2000 Fragen und
500 Personen unbekannt.

**Zu tun:** Messreihe mit 200 / 1000 / 5000 Fragen und 50 / 500 Teilnehmenden,
Dashboard und Bericht getrennt messen, Ergebnis in `product.md` §9 festhalten.

**Fertig, wenn:** belastbare Zahlen vorliegen und entweder eine Obergrenze
dokumentiert oder eine Optimierung als Task abgeleitet ist.

---

### task08 — Umlaute in `02-user-doc.md` korrigieren

**Prio:** mittel · **Status:** offen

Die Nutzerdoku schreibt durchgängig `ae`, `oe`, `ue` statt Umlauten
(„Lehrkraefte", „Fortschrittsbalken … Groesse"). Für ein ausgeliefertes
deutsches Dokument ist das unsauber; die HTML-Ausgabe erbt es.

**Fertig, wenn:** `docs/02-user-doc.md` korrekte Umlaute und ß verwendet und
`bin/build-user-doc.sh` erneut gelaufen ist.

---

### task09 — Testzahlen vereinheitlichen

**Prio:** mittel · **Status:** offen

`README.md` und `README-DE.md` nennen „22 PHPUnit-Tests", `CHANGELOG.md` nennt
„37 tests / 81 assertions", die Testdatei enthält 29 Testmethoden. Mindestens
zwei der drei Aussagen sind veraltet.

**Fertig, wenn:** eine einzige, aktuell gemessene Zahl in `05-quality.md` steht
und README sowie `CHANGELOG.md` darauf verweisen statt sie zu wiederholen.

---

### task10 — `.gitattributes` aufräumen

**Prio:** niedrig · **Status:** offen

`.gitattributes` markiert `lang/de_du/` und `lang/de_formal/` als
`export-ignore`; beide Verzeichnisse existieren nicht. Ebenso ist `cli/` leer.
Harmlos, aber irreführend.

**Fertig, wenn:** nur noch existierende Pfade aufgeführt sind und `cli/`
entweder Inhalt hat oder entfernt ist.

---

### task11 — Englische Nutzerdokumentation

**Prio:** niedrig · **Status:** wartet auf `q03`

`docs/02-user-doc.md` existiert nur auf Deutsch, das Plugin wird aber englisch
ausgeliefert (`adr09`). Für ein internationales Publikum fehlt die Bedienanleitung.

---

### task12 — `privacy.md` und `security.md` freigeben lassen

**Prio:** mittel · **Status:** wartet

Beide Dateien sind neu angelegt und beschreiben den Ist-Zustand aus der
Code-Analyse. Vor einer Verwendung gegenüber Kundinnen und Kunden müssen sie
fachlich freigegeben werden.

**Fertig, wenn:** Freigabe dokumentiert ist, mit Datum und freigebender Person.

---

### task13 — Fix für `bug01`

**Prio:** hoch · **Status:** offen · **Bezug:** `bug01`, `feat03`, `feat05`

Beim Speichern des Formulars muss `boxcount` gegen bestehende Kartenstände
abgeglichen werden, oder Anzeige und Auswahl müssen Werte oberhalb des Maximums
tolerieren. Empfehlung: beides — Werte beim Speichern nachziehen **und** in
`get_box_distribution()`/`select_session_questions()` auf `[1, boxcount]`
klemmen, damit Altbestände nicht verschwinden.

---

### task14 — Fix für `bug02`

**Prio:** mittel · **Status:** offen · **Bezug:** `bug02`, `feat11`

`eledialeitnerflow_reset_userdata()` muss bei aktivierter Bewertung
`eledialeitnerflow_grade_item_update($instanz, 'reset')` aufrufen, wie es die
Moodle-Konvention für Aktivitätsmodule vorsieht.

---

### task15 — Fix für `bug04` und `bug05`

**Prio:** hoch · **Status:** offen · **Bezug:** `bug04`, `bug05`, `feat13`

`get_contexts_for_userid()` braucht eine Einschränkung auf das Modul
(`JOIN {modules} m ON m.id = cm.module AND m.name = 'eledialeitnerflow'`), und
sowohl Kontext- als auch Personenermittlung müssen zusätzlich
`eledialeitnerflow_sessions` berücksichtigen. Gemeinsam mit `task04` umsetzen,
damit der Fix durch einen Test belegt ist.

---

## Offene Fragen (`qXX`)

### q01 — Welche Moodle-Versionsspanne wird offiziell unterstützt?

**Blockiert:** `task02` · **Entscheidung durch:** PO

README und `product.md` sagen „4.4 bis 5.1", die CI testet 4.5 und 5.0. Was soll
in `$plugin->supported` stehen, und welche Spanne wird tatsächlich getestet?

### q02 — Soll die Moodle App unterstützt werden?

**Blockiert:** `task06` · **Entscheidung durch:** PO

Vollständige Mobile-Unterstützung ist Aufwand. Alternative: ausdrücklich als
nicht unterstützt dokumentieren.

### q03 — Braucht die Nutzerdoku eine englische Fassung?

**Blockiert:** `task11` · **Entscheidung durch:** PO

Das Plugin liefert nur englische Sprachstrings aus, die Nutzerdoku ist deutsch.

### q04 — Wer nimmt die Screenshots auf, in welcher Sprache, mit welchen Daten?

**Blockiert:** `task01` · **Entscheidung durch:** PO

Deutsch oder Englisch? Auf der lokalen Docker-Instanz oder auf einer
Demo-Instanz? Mit welchem Beispiel-Fragenbestand, damit die Bilder reproduzierbar
bleiben?

### q05 — Ist das Plugin im Plugins Directory eingereicht, und in welchem Status?

**Entscheidung durch:** PO

`RELEASE-NOTES-v1.0.0.md` beschreibt die Erstveröffentlichung, `.submission-draft.md`
existiert lokal und ist per `.gitignore` ausgeschlossen. Der tatsächliche
Einreichungsstand ist im DevFlow nirgends festgehalten.

### q06 — Bleibt `CHANGELOG.md` im Repo-Root kanonisch?

**Entscheidung durch:** PO

Aktuelle Aufteilung: `CHANGELOG.md` im Root ist die ausgelieferte, nutzerseitige
Fassung je Release; `docs/changelog.md` führt die technische Sicht je
`$plugin->version` inklusive Schema- und Upgrade-Schritten. Wenn das so bleiben
soll, gehört es als ADR festgehalten.

---

## Verifikationen nach Deploy (`verifyXX`)

Durchzuführen nach jedem Deploy auf eine Zielumgebung; Ergebnis mit Datum und
Umgebung hier eintragen.

| ID | Prüfung | Erwartung |
|---|---|---|
| verify01 | Plugin erscheint unter *Website-Administration → Plugins → Aktivitäten* | Version 1.0.0, Status stabil |
| verify02 | Aktivität in einem Kurs anlegen, Kategorie wählen, speichern | Aktivität erscheint, keine Warnung |
| verify03 | Sitzung als Lernende starten und abschließen | Karten wechseln das Fach, Historie wächst |
| verify04 | Sitzung mitten drin verlassen und fortsetzen | Fortsetzung an derselben Karte |
| verify05 | Bericht als Lehrkraft öffnen | Alle eingeschriebenen Personen sichtbar, auch ohne Fortschritt |
| verify06 | Fortschritt einer Person zurücksetzen | Bestätigung erscheint, danach Werte auf 0 |
| verify07 | Kurs-Reset mit gesetzter Checkbox | Lerndaten weg; Notenprüfung siehe `bug02` |
| verify08 | Backup mit Nutzerdaten, Restore in neuen Kurs | Einstellungen und Lernstände vorhanden |
| verify09 | Privacy-Export für eine Testperson | Kartenstände und Sitzungen im Aktivitätskontext |
| verify10 | Einführungstour beim ersten Besuch | erscheint einmal, keine Dubletten in `tool_usertours` |
| verify11 | Bewertung „Prozent gelernt" aktivieren | Wert im Bewertungsbereich entspricht dem Dashboard |


### task16 — Rückmeldung vor Sitzungsabschluss

Status: In Review. Vom Nutzer am 24.10.2026 beauftragt. Implementiert: letzte Antwort vor Zusammenfassung anzeigen, manuell weiter, allgemeine Erklärung im ausführlichen Modus sichtbar. Lokal h5p52 geprüft (test24). Keine Freigabe/Veröffentlichung vorgenommen. Nächster Schritt: Review und automatisierte Regression in bestehender Behat-Suite.


### task17 — Optionale Lernszene in LeitnerFlow

Status: In Review. Nutzerauftrag 24.10.2026: F-03/F-07 in einer Aktivität erkunden und üben, Szene während Fragen wieder öffnen. Optionaler Szenen-Dateibereich und Filemanager, isolierter eingebetteter Player auf Einstieg/Frage/Rückmeldung, gemeinsamer Trainingsstart implementiert und lokal eingespielt. Keine neuen Tabellen, keine automatisierte Kompetenzvergabe, kein Pflicht-Erkundungsabschluss. Pilotkonfiguration liegt in Fahrup scripts/attach_leitner_scenes.php. Siehe test25. Backup/Restore-Lauf und gesonderte Lernendenrolle vor Release prüfen.


### task18 — F07-Moodle-Anbindung gemäß technischem Plan

**Status:** wartet · **Prio:** hoch · **Bezug:** feat15, test26–28.

Architekturkorrektur vom Nutzer am 24.10.2026 bestätigt: Dieser implementierte Pilot ist die Migrationsquelle, kein freizugebender Zielstand. Ablösung durch eigenständiges `mod_fahrup` und generische LeitnerFlow-Schnittstelle gemäß task19–24. Bis zum gesonderten Umbauauftrag keine weiteren Pilotänderungen. Die folgenden Nachweise bleiben historische Evidenz.
Direkter Implementierungs- und lokaler Testauftrag des Nutzers vom 24.10.2026.

Umgesetzt: deklaratives Manifest mit expliziten Bankeinträgen; fixierte QE-Versionen; immutable guided/review-Sitzung; separate Phase/Arbeitsfläche; einmaliger Review-Commit unter Aktivität/Person-Lock und Transaktion; Antwortspeicherung ohne Submit; direkter Baukasten-Player; Sichtungsstatus; Nachlese/90-Tage-Cleanup. Additives Schema 2026102400, Altversuche bleiben review. Bestehende Arbeitsbaumänderungen erhalten.

Validierung läuft lokal auf h5p52, Moodle 5.2.4+ / PHP 8.3.32 / PostgreSQL 16, getrennte PHPUnit-DB `t_` gegenüber Pilot `m_`. Vor lokaler Migration Sicherung von Plugin und Pilotdatenbank unter `/tmp/f07-before/`.

Nachweise: PHPUnit, reale QE-/Browserabläufe, parallele HTTP-Prüfrequests, Same-site-Backup/Restore, Privacy, Desktop/Mobil und gezielter Behat-Nachlauf in Quality dokumentiert. Nächster Schritt: PO-Review; vor allgemeinem Release zusätzlich Cross-site-Restore und weitere unterstützte Moodle-Versionen prüfen. Keine Veröffentlichung oder Remoteänderung beauftragt.


## Trennungsplan Fahrup / LeitnerFlow — Revision 1, 24.10.2026

### task19 — Vertrag und sichere Übernahme vorbereiten

**Status:** offen · **Prio:** hoch · **Feature:** feat15 · **Abnahme:** feat15.AC01–11.
**Planstatus:** Ursprünglicher Detailentwurf (Revision 1); anschließend vom Nutzer ausdrücklich zur Prüfung, Umsetzung und lokalen Erprobung beauftragt. Umsetzung im lokalen Entwicklungsstand erfolgt; PO-Abnahme/Release offen. Führender Detailplan für beide Komponenten ist dieser Abschnitt; Fahrup task12 verweist hierher. adr13 ist ein vorgeschlagener technischer ADR, kein Implementierungs- oder Release-Sign-off.

**Direct input contract**

Quelle: direkte Nutzeranweisung in diesem Chat vom 24.10.2026: „Plane die Trennung in mod_fahrup mit einer generischen Schnittstelle zu LeitnerFlow und die sichere Übernahme der bisherigen Änderungen. LeitnerFlow muss eigenständig bleiben. Erst den Plan, noch nicht umbauen.“ Vorher bestätigt: erst angeleitete Übung ohne Boxenwertung, spätere getrennte Wiederholungen zählen; abgeschlossene Antworten/Feedback 90 Tage aufbewahren.

| Plananforderung | Verbindlicher Inhalt | Umsetzung / Prüfung |
|---|---|---|
| SPLIT-01 | LeitnerFlow bleibt ohne Fahrup vollständig betreibbar | task20/24; feat15.AC08; test29/34 |
| SPLIT-02 | Eigenes `mod_fahrup` übernimmt Szenen und Übungsablauf | task21; feat15.AC01/04–07/09; test30/33 |
| SPLIT-03 | Guided niemals werten; spätere Review genau einmal | task20–22; feat15.AC02/03/10; test29/31/32 |
| SPLIT-04 | Bestehende Änderungen, Antworten und Kartenstände sicher übernehmen | task19/22/23; feat15.AC04/05/07/11; test31/32/34 |
| SPLIT-05 | Nur planen, keine Laufzeit-/Schemaänderung | alle Tasks offen, Prüfungen pending; Abschluss dieses Planungsauftrags nur Dokumentvergleich |

**Evidenz und Zielumgebung**

Gelesener LeitnerFlow-Stand: HEAD `06e889d7fc19f2d3608d3d38ecf7289e2ee855d7` plus uncommitteter Arbeitsbaum; DevFlow-Dateien ebenfalls uncommittet, daher keine separate Spezifikations-Commitrevision vorhanden. `version.php` 2026102400 / Release 1.0.1 / Mindestversion 2024042200 (Moodle 4.4). Lokaler Pilot h5p52: Moodle 5.2.4+ / PHP 8.3.32 / PostgreSQL 16. CI deklariert 4.5/PHP8.2, 5.0/PHP8.3, 5.2/PHP8.3; das ist keine Bestätigung aller unterstützten Versionen. Zielpilot für Fahrup zunächst 5.2; LeitnerFlow-Mindestversion nicht anheben. q01 zur öffentlich zugesicherten Versionsspanne bleibt separat.

Die vorhandene Szenenimplementierung betrifft `view.php`, `attempt.php`, `scene.php`, `mod_form.php`, `lib.php`, `classes/local/scene_*`, `answer_commit_service`, `classes/task/scene_cleanup.php`, `db/tasks.php`, `player/`, Templates, Dateien sowie Backup/Restore und Privacy. Generische Löschpfade (`lib.php`, `leitner_engine::delete_user_data`, Privacy) rufen derzeit den Szenendienst auf. `scene_manifest` und Cleanup kennen ausdrücklich `fahrup.scene.f07`. Es gibt bisher keine veröffentlichte generische Connector-API. Allein das Abschalten der Weiterleitung würde die Kopplung deshalb nicht entfernen.

**Technische Entscheidungen / Eigentümer**

- TD-S01: `mod_fahrup` besitzt den Player samt Grafikadapter/Assets, Manifest-/Fragenresolver, Szenen-/Blickpunkt-IDs, Phasen, angeleitete und spätere Review-Versuche, QUBA, Antworten/Feedback, Dateien, 90-Tage-Cleanup, eigene Privacy-/Backup-/Reset-Pfade und fachliche Guided-Voraussetzung. Vom Nutzer benanntes Zielrepository: `https://gitlab.eledia.de/eledia_plugins/mod/fahrup`; vorgeschlagener lokaler Checkout `/Users/moskaliuk/Documents/Code/mod_fahrup`, Moodle-Verzeichnis `mod/fahrup`. Noch keinen Plugin-Scaffold anlegen. Nutzerklärung: Repository ausschließlich für die spätere Umsetzung; diesen Plan nicht dorthin veröffentlichen. Vor Scaffold Komponentennamen-/Installationskollision prüfen. Keine weiteren Szenen produzieren; F07 migrieren, vorhandene F03-Integration gesondert inventarisieren und erhalten.
- TD-S02: LeitnerFlow besitzt weiterhin Kartenidentität `(LeitnerFlow-Instanz, userid, questionid)`, Boxenregeln, Auswahlstrategie, generische Wiederholung und Bewertung des Kartenstands. Keine neue zeitbasierte Fälligkeit ergänzen. Es erhält nur ein szenenneutrales internes PHP-Service-API mit Transaktions-/Idempotenzbelegen. Kein drittes Connector-Plugin, kein REST-Dienst und kein Browserzugriff auf diese API im ersten Schritt.
- TD-S03: Abhängigkeit ausschließlich Fahrup → LeitnerFlow. Für den ersten integrierten Pilot deklariert `mod_fahrup/version.php` die tatsächlich implementierte Mindestversion der neuen LeitnerFlow-API als erforderliche Pluginabhängigkeit. LeitnerFlow deklariert keinerlei Fahrup-Abhängigkeit, lädt keine Fahrup-Klasse und setzt keine Fahrup-Konfiguration voraus. Ein zusätzlich vollständig ohne LeitnerFlow installierbares Fahrup ist nicht Bestandteil dieses Trennungsauftrags.
- TD-S04: Bankfragen und QE bleiben Moodle-Core. Fragen werden weder kopiert noch erneut bewertet. Geometrie und Bankreferenzen aus den bestehenden Definitionen unverändert übernehmen. Frageversionen bleiben pro Versuch fixiert; bestehendes questionid-basiertes Kartenwissen nicht auf neue Versionen übertragen.
- TD-S05: Wiederholungsoberfläche bleibt in Fahrup, damit Szene, ungeprüfte Auswahl und Feedback wiederaufnehmbar bleiben. LeitnerFlow verarbeitet nur autorisierte Wiederholungsabschlüsse. Alternative „für jede Review in die LeitnerFlow-Oberfläche wechseln“ würde den bestehenden Ablauf verändern; daher hier nicht gewählt. Fahrup implementiert keine zweite Karten- oder Bewertungsengine.

**Zielmodell (vorgeschlagene Namen, noch keine vorhandenen APIs)**

| Eigentümer / Tabelle | Inhalt / Schlüssel |
|---|---|
| Fahrup `fahrup` | Kursaktivität, freigegebene Definition/Manifest, explizite Fragenzuordnung und verknüpfte LeitnerFlow-cmid im selben Kurs |
| Fahrup `fahrup_attempts` | Nutzer, Aktivität, unveränderlicher Modus, QUBA, Szene-/Versionssnapshot, phase/surface/index, Sichtungsstatus, Abschlusszeiten, stabile zufällige attemptuuid; kein eigener Kartenstand |
| Fahrup `fahrup_attempt_slots` | questionRef, viewpointRef, QBE-ID, fixierte Version/questionid, finaler Prüfstatus und Connectorbeleg je Versuch/Slot |
| LeitnerFlow `eledialeitnerflow_ext_attempts` | generische registrierte Quelle: Quellkomponente/-kontext, attemptuuid, Nutzer, Zielinstanz, QUBA, Status; unique auf Quellkontext + attemptuuid; kein Szenentext, kein Guided-Modus |
| LeitnerFlow `eledialeitnerflow_ext_results` | registrierter Versuch/Slot, konkrete Frage, idempotenter Verarbeitungsbeleg/Zeiten; unique auf Registrierung + Slot; keine Antwortkopie |
| Fahrup `fahrup_migrations` | Legacy-Instanz/Sitzung → Zielaktivität/Versuch/QUBA, Zustandsphase, Prüfsummen und Verifikationsstatus; keine Antwortinhalte im CLI-Bericht |

XMLDB-Feld-/Indexgrenzen und Privacy-/Backup-Metadaten bei Umsetzung prüfen. Fremdkomponentenbezüge bewusst als validierte IDs und portable Mappingdaten behandeln; keine bidirektionalen DB-Fremdschlüssel zwischen Plugins. Registrierungen benötigen `userid` auch zur generischen Privacy-Suche. Kartenstände bleiben die einzige Wahrheit über gezählte Wiederholungen.

**Schritte / erwartetes Ergebnis**

1. Vor jedem Umbau aktuellen Arbeitsbaum und lokale Installation erneut inventarisieren. Neuere Änderungen anderer Sessions nicht aus diesem Plan ableiten. Vorhandene Sicherung `/tmp/f07-before/` nur als frühere Referenz verwenden; sie ist kein frisches, verifiziertes Vollbackup.
2. Frisches Wiederherstellungspaket aus aktuellem Quellcode beider Komponenten, vollständiger Pilot-DB und Moodledata/File Storage erstellen, Wiederherstellung in isolierter Kopie nachweisen. Zusätzlich alle aktuellen untracked Dateien sichern. Keine Sicherung oder personenbezogenen Antworten ins Repository schreiben.
3. Datei-/Hunk-Zuordnung gegen HEAD **und** Vor-F07-Snapshot: (a) Fahrup-Code zum Umzug, (b) generische Verbesserungen behalten, (c) vorhandene fremde Änderungen unangetastet. Der Vor-F07-Snapshot enthält bereits optionale Szeneneinbettung. Daher gesondert übernehmen: letzte Rückmeldung aus task16 bleibt generisch; korrekte Privacy-Kontext-/Personensuche bleibt generisch; optionale alte HTML/iframe-Szenenpfade aus task17 samt F03-Dateien und Konfiguration werden inventarisiert und nach Fahrup überführt, nicht als versteckter Szenenplayer in LeitnerFlow belassen. Nicht blind Reset/Checkout des Repositories oder der Datenbank ausführen.
4. Quell-/Zielinstanzen, Nutzer, QUBA, Slots, Versionen, Status, Dateien und vollständige Kartenfelder pro betroffenem Nutzer zählen/hashbasiert vergleichen. Szenenfreie Sitzungen nicht migrieren. Ältere F03-Sitzungen ohne eindeutige Szenenkennung nur anhand belegter Konfiguration zuordnen; mehrdeutige Fälle blockieren und Quelle erhalten.
5. Je bestehender Szenen-LeitnerFlow-Aktivität Ziel-Fahrup-Aktivität anlegen. Bisherige LeitnerFlow-Instanz und Kartenstände bleiben als Wiederholungsziel erhalten; keine globale Zusammenlegung pro Frage über mehrere Instanzen. Verknüpfungen, Rollenüberschreibungen, Sichtbarkeit, Gruppen, Abschluss-/Notenregeln und Kurslinks erfassen. Bestehende Kartenbewertung bleibt in LeitnerFlow; keine neue Fahrup-Note erfinden. Alte Lernendenlinks nach Cutover auf Fahrup aktualisieren. Optionale Übergangszuordnung/Redirect gehört Fahrup bzw. zum Kursschnitt, nicht als dauerhafter Fahrup-Sonderfall in LeitnerFlow.

**Abhängigkeit:** keine Implementierung ohne gesonderten Nutzerauftrag. Ergebnis ist eine verifizierbare Übernahmeliste; bei unzuordenbaren Daten stoppt die Ausführung vor destruktiven Schritten. Prüfungen test31/32.

**Done-Checkliste**

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

### task20 — Generische LeitnerFlow-API für externe Wiederholungen

**Status:** offen · **Prio:** hoch · **Feature:** feat15 · **Abnahme:** feat15.AC02/03/05/08/10.
**Abhängigkeit:** task19; als eigenständige generische Erweiterung testbar, ohne installiertes Fahrup.
**Ziel:** Ein Vertrag, den beliebige autorisierte Moodle-Aktivitäten verwenden können.

**Schritte / technischer Vertrag**

- Vorgeschlagen: `classes/local/external_review_service.php` mit Vertragsversion 1, `register_attempt(...)`, `commit_slot(registrationid, slot)` und lesender Statusabfrage. PHP-Signaturen während Umsetzung dokumentieren; keine existierende API behaupten. Ein Connector in Fahrup kapselt diese Aufrufe; Fahrup greift nie direkt auf LeitnerFlow-Tabellen oder `leitner_engine` zu.
- Registrierung übergibt Ziel-cmid, Quell-Modulkontext, QUBA-ID, stabile attemptuuid und erwartete Slots/Frageversionen. Person wird vom autorisierten Serverkontext abgeleitet, nicht aus Browserparametern. Vorabprüfung: installierte/aktivierte Quellkomponente, explizit administrativ zugelassene Quelle, freigeschaltete Zielaktivität, gleicher Kurs, sicht-/zugängliche Aktivitäten, `mod/eledialeitnerflow:attempt` im Ziel und erlaubte Bankfragen/-versionen. Quellplugin muss seine eigene attempt-Capability und Wiederholungsberechtigung erzwingen; LeitnerFlow prüft zusätzlich QUBA-Komponente/-Kontext und gespeicherte Bindung. Kein hart codierter Fahrup-Klassenaufruf/Allowlist-Eintrag; Konfiguration erlaubt generische Komponentenkennungen.
- Registrierung nur für neue, noch nicht final bewertete QE-Slots. Fahrup ruft sie ausschließlich für serverseitig erzeugte spätere Review auf, nach abgeschlossenem Guided. Guided erzeugt niemals eine Registrierung. Fehlerhafte Zielverknüpfung vor Versuchsbeginn erklären, nicht einen vermeintlich gewerteten Versuch starten.
- Commit lädt gespeicherte Registrierung und QE selbst, prüft Ziel/Nutzer/Quelle/Slot/Version und finalen automatisch bewerteten Zustand. Kein `correct`, `fraction`, `mode`, beliebiges `userid` oder Zeitstempel als autoritativer Clientinput. Bestehende Regel `fraction >= 1.0` übernehmen; nicht automatisch bewertete Zustände zurückweisen.
- Unter **demselben** instanz-/nutzerbezogenen Lock wie reguläre LeitnerFlow-Kartenänderungen: Beleg prüfen → QE-Ergebnis lesen → bestehende Boxenengine anwenden → Kartenstand und eindeutigen Beleg in einer DB-Transaktion speichern. Auch native Antwortpfade und Reset müssen diese gemeinsame Schreibsperre verwenden, sonst kann ein nativer Versuch einen Connector-Schreibvorgang überschreiben. Lockreihenfolge festlegen: Fahrup-Versuch → LeitnerFlow-Ziel/Nutzer, niemals umgekehrt. Ereignisse erst nach erfolgreichem Commit als Benachrichtigung, keine Wertung über Event-Observer.
- Registrierung, QE-Prüfung und Wertung im gemeinsamen synchronen Ablauf der lokalen Moodle-DB; bei Fehler Rollback von Prüfung/Wertung/Beleg oder verlässlich wiederaufnehmbare unverarbeitete Registrierung. Kein asynchroner ungedeckter Zwischenzustand „bestätigt, aber nicht gezählt“. Replay gibt denselben Beleg zurück; abweichende Metadaten unter derselben Identität werden abgewiesen.
- Kartenbewertung, Notenfortschreibung und generische Reports berücksichtigen externe Ergebnisse genau einmal. Keine synthetische zweite native Sitzung mit derselben QUBA; externe Provenienz separat ausweisen. Native Sitzungsstatistiken nicht durch Fahrup-Guided verändern.
- Belege bleiben mindestens bis zum Ende der zugehörigen Replay-Möglichkeit erhalten. Nach 90 Tagen benötigt LeitnerFlow keine Antworten; Registrierung kann generisch geschlossen werden. Bei Reset/Ersatz/Löschung darf ein alter Abschluss nie den Lernstand wiederherstellen: alte Registrierungen widerrufen, nur eine neue zugelassene Review erhält eine neue Identität. Kein stilles Löschen von Deduplizierung bei fortbestehenden Quellversuchen. Privacy, Backup/Restore und administrative Löschung erfassen diese personenbezogenen Minimalbelege.

**Erwartetes Ergebnis:** mockfähiger, dokumentierter Vertrag ohne Szene/F07/3D-Abhängigkeit. Nachweis test29, Concurrency test32.

**Done-Checkliste**

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

### task21 — Eigenständiges Fahrup-Aktivitätsmodul und Connector

**Status:** offen · **Prio:** hoch · **Feature:** feat15 · **Abnahme:** feat15.AC01–07/09/10.
**Abhängigkeit:** task19/20. **Ziel:** Vorhandenes Verhalten unter eigener Datenhoheit wiederverwenden.

**Schritte**

1. Neues Modul mit Moodle-Standarddateien, eigenem Namespace, Sprachstrings, `mod/fahrup:{addinstance,view,attempt,manage,viewreport}` und Resetrecht anlegen. `addinstance` im Kurskontext, übrige Rechte im Modulkontext. Getrennte Lehrendenvorschau ohne erzeugten Lernendenversuch. Konfiguration von Manifest/Bankzuordnung und Ziel-LeitnerFlow braucht manage sowie Fragenbenutzungsrechte im jeweiligen Bankkontext; Zieländerung bei bestehenden Registrierungen nicht rückwirkend erlauben.
2. Vorhandene Loader, Resolver, Zustandsautomat, Renderer und Tests gezielt verschieben/anpassen. Neue QUBA erzeugt Fahrup mit `make_questions_usage_by_activity('mod_fahrup', $context)`; Moodle-Formulare und QE-Renderer weiterhin verwenden. Templates/CSS unter `.path-mod-fahrup` kapseln, UX-Baukasten beibehalten, Third-Party-Lizenzen mitnehmen. Keine parallele Grafikbibliothek erstellen.
3. Guided/Review weiterhin bei Start unveränderlich setzen; Review nur nach abgeschlossenem Guided, separate neue attemptuuid. Guided berührt den Connector nie. Die bestehende Frage-/Feedback-/Summary-Folge und ungeprüftes Speichern ohne QE-Submit erhalten. Szene/Original getrennt; F07 hat kein zugeordnetes Originalmedium. F03-Bestandsmedien geschützt übernehmen, keine zusätzlichen F03-Fragen oder veränderte Geometrie erzeugen.
4. `classes/local/leitnerflow_connector.php` verwendet nur den Vertrag aus task20. Fehlende/inaktive/zu alte LeitnerFlow-API und unzulässiges Ziel vor Review verständlich behandeln. Beim Entfernen einer Zielinstanz keine LeitnerFlow-Rückreferenz auf Fahrup-Code benötigen; Fahrup erkennt ungültiges Ziel und verhindert weitere Review-Wertung.
5. Eigene `pluginfile`-Prüfung für statische Szenen/Medien und QE-Antwortdateien; Kontext, Versuchseigentümer oder Reportrecht prüfen. Kein beliebiger Upload von ausführbarem HTML/JS im neuen Manifestpfad. Alt-HTML für Migration gesondert prüfen, nicht ungeprüft als native DOM-Inhalte übernehmen.
6. Fahrup-Cleanup nach 90 Tagen ab ursprünglichem Abschluss entfernt QUBA/Antwortdateien und Detailbindungen; Minimalabschluss für Guided-Voraussetzung bleibt ohne Antwortinhalt. Migration/Restore setzen die Uhr nicht neu. Kein Kartenreset bei Ablauf. Privacy entdeckt auch Guided-only-Nutzer, exportiert/löscht vollständige Versuchsdaten; Kursreset/Aktivitätslöschung löschen eigene Daten und widerrufen eigene Registrierungen, verändern jedoch nicht still Kartenwissen im anderen Modul. Gemeinsamer Kursreset führt jedes Modul über dessen eigene Optionen aus.
7. Backup/Restore verwendet Core-Fragenusage-Strukturen; remappt Fragen-/Nutzer-/Kontext-/Datei- und Zielaktivitätsreferenzen. Bei fehlendem LeitnerFlow-Ziel keine stille Ersatzaktivität/Neuwertung. Wiederhergestellte abgeschlossene Slots werden nicht erneut committet. Kartenstand lebt im LeitnerFlow-Backup; Belege und Zielbindung müssen vor Fortsetzung konsistent sein. Nicht gemeinsam wiederhergestellte Verbindungen zunächst deaktivieren und ausdrücklich neu zuordnen.

**Erwartetes Ergebnis:** F07-Ablauf unter `/mod/fahrup/`, unveränderte Bewertung und sichere Fehlerzustände. Test30/33; technische Pflege beider Plugin-DevFlows bei Umsetzung.

**Done-Checkliste**

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

### task22 — Wiederaufnehmbare Datenmigration und Cutover

**Status:** offen · **Prio:** hoch · **Feature:** feat15 · **Abnahme:** feat15.AC02–05/07/10/11.
**Abhängigkeit:** task19–21; zwingend zuerst isolierte Wiederherstellung der aktuellen Pilotdaten.
**Ziel:** Antworten erhalten, keinen bereits gewerteten Slot erneut anwenden.

**Schritte / Migrationsprotokoll**

1. Einmaliges administratives CLI-Werkzeug im **Fahrup**-Repository, nicht dauerhafte Fahrup-Migrationslogik in LeitnerFlow. Default `--dry-run`; ausführender Modus erst in gesondertem Umsetzungsauftrag. Explizite Legacy-/Zielaktivitätszuordnung, Mengen, Prüfsummen, Blocker und geplante Dateiübernahme ausgeben. Keine Antworten/Personendetails in regulären Berichten. Globale planbasierte Schätzung reicht nicht; tatsächlich vorhandene Daten vor jedem Lauf lesen.
2. Vor Cutover Schreibzugriffe für betroffene Pilotaktivitäten stoppen und alte Requests auslaufen lassen; Cron-Cleanup/Reset dürfen während der Übernahme nicht dieselben Daten verändern. Moodle-Wartungsfenster, Lock und freigegebene DB-Transaktionsgrenzen verwenden. Kein währenddessen parallel aktiver alter Player.
3. Pro Legacy-Sitzung idempotent Zielversuch/Slots und Migrationszuordnung erstellen; Originalmodus, UUID-Zuordnung, Szenenhash, Sichtungen, Frageversionen, QE-Sequenzen, Feedbackphase und Zeitstempel erhalten. Bereits abgelaufene Details nicht aus Sicherungen wiederbeleben. Aktive Versuche samt ungeprüfter Antwort oder Feedback bleiben fortsetzbar. Alte normale Sitzungen `sceneid=none` bleiben in LeitnerFlow; nicht automatisch als Fahrup klassifizieren.
4. QE-Eigentümerwechsel ist ein ausdrücklicher kontrollierter Migrationsschritt. Im gelesenen Moodle-5.2-Core hat `question_usage_by_activity` Getter, aber keine öffentlichen Setter für owning component/context. Daher **keine erfundene Setter-API**, kein Reflection-Hack und kein Wiederabspielen der Antworten. Bevorzugter eng begrenzter DML-Schritt: bestehende `question_usages.id` erhalten, exakt geprüfte Quell-Komponente/-Kontext auf `mod_fahrup`/Zielkontext ändern, danach Usage frisch über `question_engine::load_questions_usage_by_activity()` laden und sämtliche Slots/Steps vergleichen. Diese Core-Metadatenänderung nur im dedizierten Migrationstool mit Vorbedingungen, Journal und Transaktion; nicht in normalem Laufzeitcode. Test31 muss Sicherheit dieser Ausnahme vor Cutover nachweisen, sonst bleibt die Quelle erhalten und Cutover ist blockiert.
5. Für alle zugehörigen QE-Step-IDs Antwortdateien der von `question_engine::get_all_response_file_areas()` gemeldeten Bereiche berücksichtigen. Gelesener Core: `file_storage::move_area_files_to_new_context(oldcontext, newcontext, component, filearea, itemid)` unterstützt gezielte Step-Dateiübernahme; niemals den gesamten Frage-Dateibereich eines gemeinsam genutzten Kontexts verschieben. Manifest-/Szenenmedien mit File Storage API (`create_file_from_storedfile`) in die neue Komponente kopieren und anhand contenthash/Referenzen verifizieren. Bankfragen und deren Originalmedien bleiben im Bankkontext. Absolute alte pluginfile-Links erkennen; nur eindeutig eigene Links korrigieren. Originalquelle erst nach erfolgreicher Prüfung bereinigen.
6. Bereits gezählte Review-Slots (`leitnerapplied > 0`): Kartenstand unverändert lassen und im generischen Ergebnisjournal als **historisch bereits angewendet** markieren; keine normale `commit_slot`-Ausführung. Historische Importfunktion ausschließlich CLI, exakter Quellenbeleg und Journal, keine Webroute. Eine vorhandene abgeschlossene/aktive Review mit final geprüftem und angewendetem Slot ist kein neuer Versuch. Bei Widerspruch `timechecked > 0`, Review, aber `leitnerapplied = 0` nicht raten oder nachträglich werten: blockieren und Bericht zur Einzelfallklärung.
7. Guided-Slots niemals in Ergebnisbelege umdeuten und keine Karten erzeugen. Bei aktiven Review-Sitzungen die noch offenen Slots neu registrieren, schon angewendete Slots importieren; identische Zielkarte und fixierte Frageversion verwenden. Nach Fortsetzung dürfen ausschließlich zuvor offene finale Slots zählen. Native Karten-/Notenzustände vollständig vor/nach Migration vergleichen; Migration allein verändert keinen Wert.
8. Nach erfolgreicher Verifikation je Sitzung: alte QUBA-Verknüpfung lösen, Alt-Slot-/Sitzungszeilen kontrolliert entfernen oder bis zum finalen Cleanup in zugriffsgesperrter Migrationsphase halten; nie zwei löschberechtigte Besitzer derselben QUBA. Spätestens vor erneuter Freigabe muss genau ein Owner bestehen. Generischer Alt-Löschpfad darf eine bereits übernommene QUBA nicht mehr löschen. Journal pro Sitzung `prepared → transferred → verified → detached` macht Abbruch/Wiederanlauf nachvollziehbar; ein Fehler rollt die einzelne Einheit zurück. Nichttransaktionale Dateischritte erkennen/kompensieren, Quelldateien erst nach Hashprüfung entfernen.
9. Migrationsabschlussbericht: alle Quell-/Zielanzahlen, bijektive Zuordnung, keine verwaisten/doppelt besessenen QUBA, identische Antworten/Steps/Fractions und Kartenfelder, gleiche Fristen. Alle Alt-URLs/Kurslinks und F03-Bestandskonfiguration explizit prüfen. Erst nach bestandener Probe Cutover in der lokalen Pilotinstallation.
10. Rollback bis zur Schreibfreigabe: vollständiges verifiziertes DB-/Moodledata-/Codepaket wiederherstellen oder nur nachweislich vollständig reversible Journaloperationen verwenden. Nach neuen Fahrup-Antworten ist ein alter Snapshot verlustbehaftet: dann vorwärts reparieren bzw. neue Daten gezielt zurückmigrieren; kein einfacher Code-/Versionsdowngrade. Pluginversionen bleiben monoton größer als die tatsächlich installierte 2026102400.

**Erwartetes Ergebnis:** zweiter Lauf ohne Änderung; bestehende Review zählt nach Umzug/Replay nicht erneut; aktive Review zählt nur offene Slots. Test31/32. Unklare Altdaten, inkonsistente Buchungsbelege oder unbekannte Antwortdateireferenzen sind Ausführungsblocker, keine still verworfenen Daten.

**Done-Checkliste**

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

### task23 — Fahrup-Kopplung aus LeitnerFlow entfernen

**Status:** offen · **Prio:** hoch · **Feature:** feat15 · **Abnahme:** feat15.AC08/09/11.
**Abhängigkeit:** verifizierte Migration task22 und gesicherte Rückkehrmöglichkeit.
**Ziel:** LeitnerFlow enthält nur generische Karten-/Wiederholungsfunktionen und Connectorvertrag.

**Schritte**

- Player/Assets, szenenspezifische Klassen/Strings/Dateibereiche, Filemanager, Einstieg/Weiterleitungen, alte iframe-Einbettung aus task17, Szenentemplate/CLI und `scene_cleanup` nach erfolgreicher Übernahme entfernen. Keine `fahrup.scene.f07`-Sonderfälle, keine Szenen-ID in Runtime oder neuen Installationsdaten. Ggf. alte Upgrade-/Migrationskompatibilität klar von Laufzeit unterscheiden; historische Versionsnachweise erhalten.
- Allgemeine Lösch-, Reset-, Privacy- und Backup-Pfade wieder über generische Sitzungs-/QE-Dienste führen. Die nützlichen Korrekturen für Guided-only-/Session-only-Nutzer und Modulkontexte als generische Sitzungserfassung erhalten, keine Regression auf frühere Privacyfehler.
- Zwei Installationspfade behandeln: frische LeitnerFlow-Installation ohne Szenentabellen/-felder; bestehender lokaler Pilot mit Schema 2026102400. Neue XMLDB-Version höher als installierter Stand, keine bereits angewandte Savepoint-Nummer rückdatieren oder umdeuten. Alte Szenenfelder/-tabelle erst entfernen, wenn keine unmigrierten Szenen-/Slotdaten oder Dateien mehr vorhanden sind. Destruktives Cleanup getrennt vom normalen Upgrade nach verifizierter Migration; bei Restdaten verständlich stoppen. Install-/Upgrade-Endschema müssen anschließend übereinstimmen. Historische Upgradeblöcke dürfen nicht unbemerkt neue Fahrup-Abhängigkeiten verlangen.
- Vorhandene allgemeine Feedback-/Berechtigungs-/Testkorrekturen aus task16 und anderen Änderungen erhalten; ausschließlich per Hunk geprüfte Szenenkopplung entfernen. Kein pauschales Zurücksetzen der vorher schon schmutzigen Arbeitskopie.
- Verpackung beider Komponenten prüfen: LeitnerFlow ohne Three/GLB/Fahrup-Abhängigkeit, Fahrup mit zugehörigen Lizenzen. Produktdoku beschreibt wieder generisches LeitnerFlow; alte Pilotnachweise bleiben datiert und als abgelöst kenntlich.

**Erwartetes Ergebnis:** Quell- und Laufzeitaudit zeigt keine erforderliche Fahrup-Klasse/-Datei/-Konfiguration in LeitnerFlow. Test29/34, unabhängiges Installieren/Deinstallieren in Testkopie.

**Done-Checkliste**

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

### task24 — Abnahme, Versionsmatrix und Betriebsübergabe

**Status:** offen · **Prio:** hoch · **Feature:** feat15 · **Abnahme:** feat15.AC01–11.
**Abhängigkeit:** task20–23. **Ziel:** Trennung und Datenintegrität nachweisen, keine bloße UI-Abnahme.

**Schritte / Abnahmematrix**

| Paket | Kriterien | Geplante Nachweise |
|---|---|---|
| task19 | AC01–11 als Vertrags-/Übernahmeinventar | test31/32; vollständige Zuordnung aller bestehenden Änderungen/Daten |
| task20 | AC02/03/05/08/10 | test29/32; generischer Test-Dummy als Quelle, kein Fahrup installiert |
| task21 | AC01–07/09/10 | test30/33; echte QE-Antworten und native Szenenoberfläche |
| task22 | AC02–05/07/10/11 | test31/32; Restorekopie, zweimalige Migration, Fehlerunterbrechung |
| task23 | AC08/09/11 | test29/34; saubere Neuinstallation und migrierter Pilot |
| task24 | AC01–11 | test29–34; gemeinsame Abnahme / Dokumentationsabgleich |

- CI für LeitnerFlow **allein** und eine separate Integrationssuite mit Fahrup; unabhängiger Lauf darf Fahrup nicht als Fixture installieren. Bestehende Engine-/Berechtigungs-/Feedbacktests übernehmen. 4.5/5.0/5.2 gemäß vorhandener CI; keine Behauptung zur nicht getesteten 4.4-Untergrenze. Fahrup zunächst 5.2. Mindestversion/Abhängigkeit erst anhand implementiertem API-Savepoint festschreiben.
- PHPUnit für API, Modusisolation, echte Mehrfachantwort-/Teilrichtigkeitsbewertung, Rechte/Kontexte, persistente Deduplizierung, Datenschutz und Migration. Reale parallele HTTP-Requests plus gemischte native/Connector-Schreibvorgänge; keine Concurrencybehauptung nur anhand serieller Unit-Aufrufe.
- Behat/manuell: Erkunden → alle drei Fragen mit letzter Erklärung → Zusammenfassung; ungeprüfte Auswahl und Feedback bei Rückkehr/Reload; 320 px, 200 % Zoom, Tastatur/Escape/Fokusrückgabe, Kameraänderung und 2D-Fallback. Originalmedien-/Fehlermeldungen prüfen. Bestehende F03-Pilotmedien nach Übernahme nachweisen, ohne F03 fachlich neu zu spezifizieren.
- Sicherung und Wiederherstellung im selben und in anderem Kurs/System: Frage-/Nutzer-/Zielzuordnung und Belege; kein Doppelzählen restaurierter Abschlüsse. Deinstallation Fahrup in Testkopie lässt LeitnerFlow samt Kartenbetrieb intakt; Entfernen/Zurücksetzen einer Quellaktivität kann keine alten Ergebnisse wieder anwenden.
- Doku während Umsetzung: LeitnerFlow `02-user-doc`, `03-dev-doc`, `product`, Privacy/Security, API/`upgrade.txt`, Changelog/Release Notes; eigener Fahrup-DevFlow für neue Komponente. Dieser Plan beschreibt Sollzustand, nicht den aktuellen implementierten Zustand in `03-dev-doc`. Nutzeranleitung mit zwei Aktivitäten und eindeutiger Zielverknüpfung; Betriebsanleitung mit Sicherung, Dry-run, Stop-Kriterien, Cutover, Rollback und 90-Tage-Frist. Historischen Fahrup-Plan TD-01 als durch diesen Vertrag überholt kennzeichnen, nicht als Ausführungsauftrag verwenden.

**Erwartetes Ergebnis:** getrennte Prüfprotokolle für generisches LeitnerFlow, Fahrup und Migration. Tests bleiben bis zur tatsächlichen Ausführung pending. Keine automatische Release-/Deploymentfreigabe oder PO-Done-Markierung.

**Done-Checkliste**

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

**Historischer Abschluss des Planungsauftrags:** Damals nur Dokumentation geändert. Der danach erteilte ausdrückliche Implementierungsauftrag und dessen Nachweise stehen im folgenden aktuellen Stand; die damalige Planungssperre gilt dafür nicht mehr.

**Dokumentprüfung des Plans (24.10.2026):** `git diff --check` bestanden. LeitnerFlow-DevFlow-Check ohne doppelte IDs, mit den sieben bereits bekannten Pfadbefunden (sechs dokumentierte Altpfade und erforderliche Three-Lizenz); keine fremde Dokumentbereinigung. Fahrup-Projektcheck bestanden: keine losen Dokumente, keine doppelten IDs. Nur Master/Features/Tasks/Quality in LeitnerFlow und der koordinierende Fahrup-Task-/Frageeintrag geändert; Nutzerdoku, Ist-Architektur, Produkttexte, Release-/Privacy-/Security-Dokumente im reinen Planungsauftrag nicht verändert. Deren Umsetzungspflege ist task21/24 zugeordnet. Keine neuen Funktionstests ausgeführt, da kein Code geändert wurde. GitLab-Ziel dient laut Nutzerantwort erst der späteren Umsetzung; kein Push erfolgt.

## Aktueller Umsetzungsstand der Trennung — 24.10.2026

Nutzerauftrag: „Prüfe den Plan noch einmal auf Lücken und Risiken. Wenn er passt, setze die Trennung um und teste sie lokal. LeitnerFlow muss ohne Fahrup weiter funktionieren.“ task19–23: Implementierung lokal erfolgt, weiterhin kein PO-Done-Sign-off. task24: Prüfungen und Abnahme in Arbeit; offene Releaseprüfungen siehe test33/34 und Fahrup test05/06.

Planprüfung schloss vier technische Lücken: QUBA-Eigentümernachweis benötigt generischen Quellcallback; native/externe Kartenschreibvorgänge benötigen dieselbe Sperre; Migration benötigt Wartungsmodus auch im Service und atomaren Transfer je Quellaktivität; restaurierte/widerrufene Reviews benötigen einen expliziten neuen Wertungsversuch statt Wiederverwendung alter Belege. Hinzu kommen eigene Deinstallationsbereinigung und phasengerechte QE-Dateirechte. Keine neue Fragen-/Boxenengine und kein erweitertes Produktverhalten daraus abgeleitet.

Eigener Checkout `/Users/moskaliuk/Documents/Code/mod_fahrup` angelegt, lokal Git main ohne Commit initialisiert, origin auf das vom Nutzer genannte GitLab-Ziel gesetzt; kein Push. Abhängigkeit ausschließlich Fahrup→LeitnerFlow. Für eine spätere koordinierte Lieferung muss zuerst die generische API in LeitnerFlow verfügbar sein; ein veröffentlichtes Fahrup darf nicht auf ein unveröffentlichtes Gegenstück verweisen.

Lokaler Cutover: Legacy-cm7/cm9 behalten generische Karten/Sitzungen; neue Fahrup-cm12/cm13 besitzen Szenendateien und drei F07-Versuche. Kartenhash vor/nach Transfer identisch, vorhandene Wertung nur als Beleg importiert. Bestehende andere Arbeitsbaumänderungen und Rückmeldungs-/Privacy-Korrekturen erhalten. Schema 2026102402 entfernt nach Übernahme die Szenenspalten/-tabelle; Runtime-/Paketprüfung findet in LeitnerFlow keine Fahrup-Klasse oder Grafikassets. Neue Fh-Version 2026102401 lokal installiert, MATURITY_ALPHA. Keine zusätzlichen Szenen/Bankfragen produziert.

Nachweise: 45 LeitnerFlow-Tests/99 Assertions grün; 29 Fahrup-Tests/72 Assertions grün. Eigenständiges LeitnerFlow zusätzlich mit wirklich entferntem Fahrup-Verzeichnis frisch installiert und 44 Tests/94 Assertions geprüft sowie native Frage im Browser beantwortet/abgeschlossen. Migration auf separater DB-/Dateikopie, historischer Replay, partiell gewertete Review mit Response-Datei und echte Fh-HTTP-Doppelrequests nachgewiesen. Moodle-Deinstallation in Kopie lässt alle Kartenfelder unverändert und entfernt eigene QUBA. Details/Nachweisgrenzen in test29–34 und im neuen Fahrup-DevFlow; abschließender Behat-Gesamtlauf nach regulärem Neuaufbau der freien Testinstanz: 2 Szenarien / 49 Schritte pass, inklusive Core-Accessibility-Prüfung. Cross-site, 200%-Zoom, gemischte native/externe HTTP-Parallelität und weitere Moodle-Versionen bleiben ausdrücklich offene Releaseprüfungen.
