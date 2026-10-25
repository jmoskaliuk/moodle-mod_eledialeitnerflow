# privacy — Datenverarbeitung

Welche personenbezogenen Daten LeitnerFlow warum und wie verarbeitet, wie sie
gelöscht werden und wie Betroffenenrechte umgesetzt sind.

**Status:** beschreibt den Ist-Zustand aus der Code-Analyse vom 2026-08-27.
Fachliche Freigabe steht aus → `task12`.

---

## 1. Rolle und Verantwortlichkeit

LeitnerFlow ist ein Plugin innerhalb einer Moodle-Instanz. Verantwortlich im
Sinne der DSGVO ist die Betreiberin der Instanz, nicht die eLeDia GmbH. Das
Plugin verarbeitet Daten ausschließlich lokal in der Moodle-Datenbank.

**Keine Übermittlung an Dritte.** Es gibt keinen externen Dienst, keinen
API-Aufruf nach außen, keine Telemetrie, keine Analytics-Einbindung, keine
KI-Anbindung.

---

## 2. Verarbeitete Daten

### `eledialeitnerflow_card_state` — Lernstand je Karte

| Feld | Inhalt | Personenbezug |
|---|---|---|
| `userid` | Moodle-Nutzer-ID | direkt |
| `questionid` | Frage aus der Fragensammlung | mittelbar (welche Frage geübt wurde) |
| `currentbox` | aktuelles Fach | mittelbar (Leistungsstand) |
| `correctcount` | Anzahl richtiger Antworten | mittelbar (Leistungsstand) |
| `attemptcount` | Anzahl Versuche | mittelbar (Leistungsstand) |
| `status` | offen / gelernt / fehlerhaft | mittelbar (Leistungsstand) |
| `timecreated`, `timemodified` | Zeitpunkte | mittelbar (Nutzungsverhalten) |

### `eledialeitnerflow_sessions` — Lernsitzungen

| Feld | Inhalt | Personenbezug |
|---|---|---|
| `userid` | Moodle-Nutzer-ID | direkt |
| `questionids` | Fragen dieser Sitzung (JSON) | mittelbar |
| `qubaid` | Verweis auf `question_usages` | mittelbar |
| `currentindex`, `questionsasked`, `questionscorrect` | Fortschritt und Ergebnis | mittelbar (Leistungsstand) |
| `currentstreak`, `beststreak` | Serien richtiger Antworten | mittelbar |
| `status`, `timecreated`, `timecompleted` | Zustand und Zeitpunkte | mittelbar (Nutzungsverhalten) |

### `eledialeitnerflow` — Aktivitätsinstanz

Enthält ausschließlich Konfiguration, keine personenbezogenen Daten.

### Daten in Moodle-Subsystemen

| Subsystem | Was entsteht | Verantwortung |
|---|---|---|
| Question Engine | `question_usages`, `question_attempts` mit den gegebenen Antworten | Core; LeitnerFlow legt sie an und löscht sie wieder |
| Bewertungsbereich | Note „Prozent gelernt", nur bei `grademethod = 1` | Core |
| Logstore | `session_started`, `session_completed`, `progress_reset` mit `userid` und Kontext | Core |
| `tool_usertours` | Merker, welche Tour eine Person gesehen hat | Core |

Die Question-Usages einer Sitzung werden beim Sitzungsabschluss gelöscht und
`qubaid` auf `NULL` gesetzt — die abgegebenen Antworten bleiben also nicht
dauerhaft liegen; erhalten bleibt nur die Zusammenfassung in der Sitzungszeile.

**Nicht verarbeitet:** Namen, E-Mail-Adressen, IP-Adressen, Freitexteingaben
außerhalb der Question Engine, Geräteinformationen. Der Bericht zeigt Namen und
Profilbild, liest sie aber aus dem Core (`get_enrolled_users()`, `fullname()`)
und speichert nichts davon.

---

## 3. Zweck und Rechtsgrundlage

**Zweck:** Verteiltes Wiederholen. Der Lernstand muss personenbezogen gespeichert
werden, weil er genau das ist, was das Produkt leistet — ohne ihn gäbe es keinen
Karteikasten.

**Rechtsgrundlage:** ergibt sich aus dem Verhältnis zwischen Betreiberin und
Lernenden (in der Regel Art. 6 Abs. 1 lit. b oder e DSGVO, bei Beschäftigten
ggf. § 26 BDSG). Die Einordnung trifft die Betreiberin der Instanz.

**Sichtbarkeit gegenüber Lehrenden:** Der Teilnehmendenbericht macht den
Lernstand einzelner Personen für Rollen mit
`mod/eledialeitnerflow:viewreport` sichtbar — gelernte, offene und fehlerhafte
Karten, Anzahl Sitzungen, Zeitpunkt der letzten Sitzung. Das ist eine bewusste
Produkteigenschaft (`feat10`) und muss in der Datenschutzinformation der Instanz
stehen. Einzelne Antworten auf einzelne Fragen zeigt der Bericht **nicht**.

---

## 4. Speicherdauer und Löschung

Es gibt keine automatische Löschfrist. Daten bestehen, bis einer dieser Pfade
greift:

| Auslöser | Wirkung | Umsetzung |
|---|---|---|
| Aktivität löschen | alle Kartenstände, Sitzungen und Question-Usages der Instanz | `eledialeitnerflow_delete_instance()` |
| Kurs zurücksetzen | dasselbe für alle Instanzen des Kurses | `eledialeitnerflow_reset_userdata()` |
| Lernstand einer Person zurücksetzen (Bericht) | Kartenstände, Sitzungen und Usages dieser Person in dieser Aktivität | `leitner_engine::delete_user_data()`, Event `progress_reset` |
| Sitzung abschließen | Question-Usage dieser Sitzung | `_eledialeitnerflow_finish_session()` |
| Privacy-Löschanfrage | je nach Umfang: eine Person, alle Personen eines Kontexts, eine Personenliste | `provider::delete_data_for_user()`, `…for_all_users_in_context()`, `…for_users()` |

Alle Löschpfade laufen letztlich über `delete_user_data()` und räumen die
Question-Usages mit auf — es bleiben keine verwaisten Frageversuche zurück.

**Nicht mitgelöscht:** Logstore-Einträge (Core-Aufbewahrung) und der
Bewertungseintrag beim Kurs-Reset (→ `bug02`).

---

## 5. Betroffenenrechte

`classes/privacy/provider.php` implementiert
`core_privacy\local\metadata\provider`,
`core_privacy\local\request\plugin\provider` und
`core_privacy\local\request\core_userlist_provider`.

| Recht | Umsetzung | Bewertung |
|---|---|---|
| Auskunft / Datenübertragbarkeit | `export_user_data()` schreibt Kartenstände und Sitzungen in den Aktivitätskontext | funktioniert, aber unvollständig → `bug05` |
| Löschung | drei Löschmethoden, alle über `delete_user_data()` | funktioniert für Personen mit Kartenständen |
| Übersicht der Betroffenen je Kontext | `get_users_in_context()` | unvollständig → `bug05` |
| Transparenz | Metadaten beschreiben beide Tabellen feldweise, Sprachstrings `privacy:metadata:*` | vollständig |

### Bekannte Lücken

**`bug04` (S3)** — `get_contexts_for_userid()` schränkt nicht auf das Modul
`eledialeitnerflow` ein. Trifft die Instanz-ID einer anderen Aktivitätsart auf
dieselbe Zahl, erscheint deren Kontext im Export. Es werden dabei keine fremden
Daten ausgegeben, aber die Kontextzuordnung ist falsch.

**`bug05` (S3)** — Kontext- und Personenermittlung stützen sich allein auf
`eledialeitnerflow_card_state`. Wer eine Sitzung startet, ohne eine Frage zu
beantworten, hinterlässt nur eine Sitzungszeile und wird von der Privacy-API
nicht gefunden. Diese Sitzungsdaten (Zeitpunkte, Zählerstände) werden dann weder
exportiert noch auf Anfrage gelöscht.

Beide Lücken werden mit `task15` behoben, der Nachweis mit `test23`.
Bis dahin: Wer eine Löschanfrage vollständig bedienen muss, sollte zusätzlich
prüfen, ob für die Person Zeilen in `eledialeitnerflow_sessions` ohne
zugehörige Kartenstände existieren.

---

## 6. Datenschutzrisiken

| Risiko | Bewertung | Maßnahme |
|---|---|---|
| Leistungsprofil einzelner Lernender für Lehrende sichtbar | mittel — beabsichtigt, aber erklärungsbedürftig | Capability `viewreport`; Aufnahme in die Datenschutzinformation der Instanz |
| Unvollständige Auskunft bei reinen Sitzungsdaten | mittel | `bug05` / `task15` |
| Falsche Kontextzuordnung im Export | niedrig bis mittel | `bug04` / `task15` |
| Note bleibt nach Kurs-Reset stehen | niedrig | `bug02` / `task14` |
| Rückschluss auf Lernverhalten über Zeitstempel | niedrig | keine eigene Maßnahme; entspricht der üblichen Moodle-Praxis |
| Abfluss nach außen | keiner | keine externe Kommunikation im Plugin |

---

## 7. Was bei einer Prüfung zu zeigen ist

1. `classes/privacy/provider.php` — Metadaten, Export, drei Löschmethoden.
2. `lang/en/eledialeitnerflow.php`, Abschnitt `privacy:metadata:*`.
3. `leitner_engine::delete_user_data()` als gemeinsamer Löschpfad.
4. `db/install.xml` — die drei Tabellen und ihre Felder.
5. Diese Datei zusammen mit `security.md`.
6. Die offenen Punkte `bug04`, `bug05`, `bug02` — offen ausgewiesen, mit
   zugeordnetem Task.


## Generische externe Wiederholung nach Trennung (24.10.2026)

Szenen, Sichtungen, Guided und 90-Tage-Antwortnachlese gehören zum eigenständigen `mod_fahrup`. LeitnerFlow besitzt nur generische externe Registrierungen und Wertungsbelege zusätzlich zu Karten und nativen Sitzungen. Keine Fahrup-Pluginabhängigkeit. Die Privacy-Kontext-/Personensuche umfasst Karten, native Sitzungen und externe Registrierungen und filtert das tatsächliche Modul. Export und Löschung erfassen die neuen Tabellen; das Löschen eines Quellversuchs setzt nicht still Kartenwissen zurück.

Quelle und Ziel sind standardmäßig nicht für externe Wertung freigegeben. Interne PHP-Aufrufe prüfen explizite Zulassung, Modul-/Kursbindung, Zielrecht, aktuelle Person, Quellcallback und QE-Komponente/Kontext. Nur finale automatisch bewertete QE-Slots, keine Client-Korrektheitsangaben. Transaktion und Unique-Beleg verhindern Doppelwertung; Reset widerruft alte Registrierungen, Restore aktiviert keine fremden Quellen. Keine externen Dienste oder neuen Authentifizierungstoken.
