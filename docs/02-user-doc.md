# LeitnerFlow Benutzer-Dokumentation

## Meta

Dieses Dokument beschreibt, wie Lernende und Lehrkraefte LeitnerFlow in Moodle
bedienen.

Es ist die Quelle der Wahrheit fuer sichtbares Verhalten aus Benutzersicht.
Technische Details gehoeren in die Entwickler-Dokumentation, offene Aufgaben in
das Task-Tracking und Testergebnisse in die Qualitaetsdokumentation.

## Zielgruppen

LeitnerFlow richtet sich an zwei Hauptgruppen:

- Lernende, die Fragen aus der Moodle-Fragensammlung in kurzen Wiederholungssitzungen ueben.
- Lehrkraefte, die eine LeitnerFlow-Aktivitaet einrichten, Fragenkategorien auswaehlen und den Lernfortschritt ihrer Teilnehmenden beobachten.

## Kernidee

LeitnerFlow verwandelt Fragen aus der Moodle-Fragensammlung in digitale
Karteikarten. Jede Karte startet in Box 1. Wird sie richtig beantwortet, wandert
sie weiter. Wird sie falsch beantwortet, faellt sie je nach Einstellung zurueck.
Nach genuegend richtigen Antworten gilt die Karte als gelernt.

Karten in niedrigen Boxen stehen fuer Inhalte, die noch mehr Uebung brauchen.
Das Dashboard zeigt, wie viele Karten sich in den einzelnen Boxen befinden und
wie weit der persoenliche Lernfortschritt ist.

## Fuer Lernende

### LeitnerFlow oeffnen

1. Kurs in Moodle oeffnen.
2. Die LeitnerFlow-Aktivitaet im Kurs anklicken.
3. Das Dashboard mit Lernfortschritt, Boxen und Startmoeglichkeit ansehen.

Erwartetes Ergebnis: Die Aktivitaet zeigt den eigenen Fortschritt, die
Verteilung der Karten auf die Boxen und eine Schaltflaeche zum Starten einer
Lernsitzung.

### Lernsitzung starten

1. Auf **Start study session** klicken.
2. Die angezeigte Frage beantworten.
3. Die Antwort pruefen.
4. Mit der naechsten Frage fortfahren, bis die Sitzung abgeschlossen ist.

Erwartetes Ergebnis: LeitnerFlow merkt sich jede Antwort und aktualisiert die
Box der jeweiligen Karte automatisch.

### Bestimmte Box gezielt ueben

1. Im Dashboard eine Box mit Karten anklicken.
2. Die gestartete Sitzung bearbeiten.

Erwartetes Ergebnis: Die Sitzung enthaelt Karten aus der gewaehlen Box. So
koennen Lernende gezielt schwierige oder bestimmte Wiederholungsstufen ueben.

### Sitzung fortsetzen oder abbrechen

Wenn eine Sitzung bereits begonnen wurde, zeigt LeitnerFlow im Dashboard eine
Moeglichkeit zum Fortsetzen an.

1. Auf **Continue session** klicken, um weiterzumachen.
2. Alternativ in der Sitzung **End session** verwenden, um die laufende Sitzung zu beenden.

Erwartetes Ergebnis: Beim Fortsetzen geht es an der gespeicherten Stelle weiter.
Beim Beenden wird die laufende Sitzung geschlossen und das Dashboard kann neu
verwendet werden.

### Fortschritt verstehen

Das Dashboard zeigt:

- die Anzahl der Karten pro Leitner-Box
- gelernte Karten
- den Fortschrittsbalken
- die persoenliche Sitzungshistorie, sofern bereits Sitzungen abgeschlossen wurden

Je mehr Karten als gelernt markiert sind, desto naeher ist der Fragenpool fuer
diese Aktivitaet abgeschlossen.

## Fuer Lehrkraefte

### Voraussetzungen

Vor dem Anlegen einer LeitnerFlow-Aktivitaet sollten passende Fragen in der
Moodle-Fragensammlung vorhanden sein. Die Fragen muessen einer oder mehreren
Fragenkategorien zugeordnet sein.

### Aktivitaet anlegen

1. Bearbeiten im Moodle-Kurs einschalten.
2. Eine neue Aktivitaet oder Material hinzufuegen.
3. **LeitnerFlow** auswaehlen.
4. Namen und Beschreibung eintragen.
5. Eine oder mehrere Fragenkategorien auswaehlen.
6. Sitzungsgroesse, Box-Anzahl und Wiederholungsregeln einstellen.
7. Aktivitaet speichern.

Erwartetes Ergebnis: Lernende koennen die Aktivitaet oeffnen und mit den
ausgewaehlten Fragen lernen.

### Wichtige Einstellungen

| Einstellung | Bedeutung |
|---|---|
| Fragenkategorien | Bestimmt, welche Fragen als Karten verwendet werden. |
| Fragen pro Sitzung | Legt fest, wie viele Karten eine Lernsitzung enthaelt. |
| Anzahl Boxen | Bestimmt, wie fein der Lernfortschritt aufgeteilt wird. |
| Richtige Antworten bis gelernt | Legt fest, wann eine Karte als gelernt gilt. |
| Verhalten bei falscher Antwort | Steuert, ob Karten zurueckgesetzt, eine Box zurueckgesetzt oder nicht verschoben werden. |
| Kartenauswahl | Steuert, ob niedrige Boxen priorisiert oder Karten gemischt werden. |
| Bewertung | Optional: Fortschritt kann als Bewertung in Moodle verwendet werden. |

### Teilnehmendenbericht ansehen

1. LeitnerFlow-Aktivitaet oeffnen.
2. Auf **All participants overview** klicken.
3. Fortschritt, Sitzungen und letzte Aktivitaet der Teilnehmenden pruefen.

Erwartetes Ergebnis: Die Lehrkraft sieht eine Uebersicht ueber Lernfortschritt
und Nutzung der Aktivitaet.

### Fortschritt zuruecksetzen

1. Teilnehmendenbericht oeffnen.
2. Bei der betreffenden Person die Reset-Aktion verwenden.
3. Den Bestaetigungsdialog bestaetigen.

Erwartetes Ergebnis: Der LeitnerFlow-Fortschritt dieser Person in der Aktivitaet
wird geloescht. Die Fragen koennen danach wieder von vorne geuebt werden.

## Typischer Ablauf

1. Lehrkraft erstellt Fragen in der Fragensammlung.
2. Lehrkraft legt eine LeitnerFlow-Aktivitaet an und waehlt Fragenkategorien.
3. Lernende starten kurze Lernsitzungen.
4. LeitnerFlow verschiebt Karten anhand der Antworten.
5. Lernende sehen ihren Fortschritt im Dashboard.
6. Lehrkraefte pruefen bei Bedarf den Teilnehmendenbericht.

## Hinweise und Grenzen

- LeitnerFlow verwendet Fragen aus der Moodle-Fragensammlung. Ohne passende Fragen kann keine sinnvolle Lernsitzung gestartet werden.
- Der Lernfortschritt ist personenbezogen. Mehrere Lernende koennen dieselben Fragen verwenden, behalten aber jeweils ihren eigenen Leitner-Status.
- Eine als gelernt markierte Karte wird in normalen Sitzungen nicht weiter priorisiert.
- Die verfuegbaren Begriffe und Schaltflaechen koennen je nach installierter Sprachversion von Moodle leicht abweichen.


### Letzte Antwort einer Sitzung

Bei aktivierter Rückmeldung bleibt auch die letzte Antwort sichtbar. Mit Weiter gelangen Sie zur Zusammenfassung. Der ausführliche Rückmeldungsmodus zeigt zusätzlich die hinterlegte allgemeine Erklärung der Frage.


### Lernszene und Fragen in einer Aktivität

Optional können Sie in den Aktivitätseinstellungen eine Lernszene mit index.html und Begleitdateien hinterlegen. Sie wird vor dem Training geöffnet angezeigt. Fragen üben startet beziehungsweise setzt die Sitzung fort. Während einer Frage und ihrer Rückmeldung können Lernende Situation erkunden aufklappen und danach wieder schließen; die aktuelle Antwortauswahl bleibt erhalten. Erkundung ist freiwillig. Betrachtete Blickpunkte werden nicht als Moodle-Abschluss gespeichert und werden beim Seitenwechsel zurückgesetzt.


## F07 angeleitet üben und später wiederholen

Bei einer konfigurierten F07-Lernszene starten Sie zuerst die angeleitete Übung. Öffnen Sie die Zahlen zum Erkunden und bearbeiten Sie anschließend drei Fragen. Ihre Antworten verändern in dieser Übung keine Leitnerboxen. Nach jeder Prüfung lesen Sie die Rückmeldung und wählen ausdrücklich die nächste Frage; nach der dritten folgt die Zusammenfassung.

Über „Zurück zur Szene“ können Sie während einer Frage deren Blickpunkt öffnen. Ihre Auswahl wird ohne Bewertung gespeichert. Grün bedeutet nur, dass ein Blickpunkt angesehen wurde. Das Zurücksetzen der Blickpunkte löscht keine Antworten und kein Kartenwissen.

Erst „Spätere Wiederholung starten“ beginnt einen eigenen Versuch mit regulärer Boxenwertung. Eine erneut gestartete angeleitete Übung bleibt ohne Boxenwertung. Abgeschlossene Antworten sind 90 Tage nachlesbar, danach bleibt der Leitner-Kartenstand erhalten. Zu F07 ist kein Prüfungsoriginalmedium zugeordnet; die Lernszene ist eine schematische Darstellung.

## Verbundene Aktivitäten (lokaler Entwicklungsstand)

Fahrup-Szenen und angeleitete Übungen befinden sich in einer eigenen Fahrup-Aktivität. LeitnerFlow bleibt ohne diese Aktivität nutzbar. Für eine Verbindung müssen die Website-Administration die Quellkomponente unter „Trusted review source components“ und Lehrende das Ziel unter „Allow external review results“ freischalten. Externe gewertete Antworten erscheinen getrennt von nativen Sitzungen; beide verändern denselben Kartenstand. Guided zählt nicht. Quelle und Ziel müssen im selben Kurs liegen. Nach Restore sind Quellen deaktiviert und müssen ausdrücklich neu verbunden werden.
