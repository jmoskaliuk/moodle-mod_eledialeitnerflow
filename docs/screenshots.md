# screenshots — Bildinventar

Welche Screenshots es gibt, worauf sie sich beziehen und auf welchem Stand sie
sind. Ein Feature mit sichtbarer Oberfläche ist erst **done**, wenn es hier
einen aktuellen Eintrag hat (`00-master.md` §6).

**Stand 2026-08-27: `docs/screenshots/` enthält ausschließlich eine `.gitkeep`.
Es existiert kein einziger Screenshot.** Das Inventar unten ist damit vollständig
ein Soll-Bestand → `task01`, offene Rahmenfragen in `q04`.

---

## Aufnahmekonvention

| | |
|---|---|
| Ablage | `docs/screenshots/<sprache>/<id>-<kurzname>.png` |
| Sprachen | `de/`, `en/`; Rohmaterial ohne Zuschnitt in `roh/` |
| Auflösung | 1440 × variabel, 2× (Retina), PNG |
| Theme | Boost, Standardfarben, helles Farbschema |
| Browser | Chrome, Fenster ohne Erweiterungen, kein Zoom |
| Moodle | die im Aufnahmedatum aktuelle getestete Version, hier eintragen |
| Inhalt | Demo-Fragenbestand, keine echten Personendaten, Anzeigenamen wie „Anna Beispiel" |
| Zuschnitt | auf den relevanten Bereich, keine Browser-Chrome, keine Kurs-Navigation |
| Nachbearbeitung | nur Zuschnitt und ggf. Hervorhebungsrahmen in `#E8590C` |

Wird ein Bild ersetzt, bleibt die `shotXX`-ID erhalten und das Datum wird
aktualisiert. Neue Bildmotive bekommen eine neue ID.

---

## Inventar

| ID | Motiv | Feature | Datei | Sprache | Aufgenommen | Status |
|---|---|---|---|---|---|---|
| shot01 | Lern-Dashboard mit Fächerverteilung und Fortschrittsbalken | feat05 | `de/shot01-dashboard.png` | de | — | fehlt |
| shot02 | Dashboard mit Sitzungshistorie und Trendanzeige | feat06 | `de/shot02-historie.png` | de | — | fehlt |
| shot03 | Frage in einer laufenden Sitzung | feat02 | `de/shot03-sitzung.png` | de | — | fehlt |
| shot04 | Rückmeldung nach richtiger Antwort inkl. Fachwechsel | feat07 | `de/shot04-feedback.png` | de | — | fehlt |
| shot05 | Einstellungsformular, Abschnitt Leitner-System | feat03, feat04 | `de/shot05-einstellungen.png` | de | — | fehlt |
| shot06 | Teilnehmendenbericht mit Kopfzeile und Reset-Aktion | feat10 | `de/shot06-bericht.png` | de | — | fehlt |
| shot07 | Einführungstour, erster Schritt | feat09 | `de/shot07-tour.png` | de | — | fehlt |
| shot08 | Aktivitätsauswahl im Kurs mit LeitnerFlow-Symbol | feat01 | `de/shot08-auswahl.png` | de | — | fehlt |
| shot09 | Gamified-Rückmeldung mit Streak | feat08 | `de/shot09-streak.png` | de | — | fehlt |
| shot10 | Kurs-Reset-Formular mit LeitnerFlow-Abschnitt | feat12 | `de/shot10-kursreset.png` | de | — | fehlt |

`shot01`–`shot07` sind das Minimum für Nutzerdoku und Katalog. `shot08`–`shot10`
sind ergänzend.

---

## Verwendung

| Ziel | Bilder |
|---|---|
| `02-user-doc.md` | shot01, shot02, shot03, shot04, shot05, shot06, shot07 |
| `README.md` / `README-DE.md` | shot01, shot06 |
| Plugins Directory | shot01, shot03, shot05, shot06 |
| PluginFlow `02-user-doku.md` | shot01–shot07 |

Bisher bindet **keine** dieser Dateien ein Bild ein.

---

## Offene Punkte

- `task01` — Bilder aufnehmen und Inventar füllen.
- `q04` — Sprache, Instanz und Demo-Fragenbestand festlegen, damit die Aufnahmen
  reproduzierbar sind.
- Sobald `bug01` gefixt ist, muss `shot01` neu aufgenommen werden, falls sich die
  Fächerverteilung sichtbar ändert.


Szenenpilot task17, 24.10.2026: lokale Belegaufnahme im Fahrup-Arbeitsbereich content/nuggets-pilot/moodle-pilot/integrated-scene.png zeigt 3D-Szene und Fragen-üben-Knopf innerhalb von LeitnerFlow. Kein Release-Screenshot.

### shot08 — F07 Moodle-Wiederholung

`docs/screenshots/f07-moodle-review.png`, 24.10.2026, h5p52 / Moodle 5.2.4+, lokales Admin-Testkonto. feat15: eigener Wiederholungsversuch, 3/3-Auswertung, explizite Wiederholung und eingeklappte Nachlese. Prüfnachweis test27; keine fachliche/amtliche Freigabe.

### shot09–10 — F07 Moodle-Szene Desktop/Mobil

`docs/screenshots/f07-moodle-scene.png` und `f07-moodle-mobile.png`, 24.10.2026, h5p52 / Moodle 5.2.4+. Desktop-Ausschnitt und vollständiger 320-px-Lauf mit Kamerabewegung/Blickpunkterklärung; direkte Einbettung ohne iframe. test27, feat15. Der mobile Vollseitenbeleg enthält den lokalen Moodle-Debug-Fußbereich.
