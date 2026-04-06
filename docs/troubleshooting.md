---
layout: page
title: Troubleshooting
description: Typische Probleme mit LeitnerFlow strukturiert analysieren.
---

<section class="page-hero">
  <span class="eyebrow">Troubleshooting</span>
  <h1>Typische Probleme schnell und systematisch eingrenzen</h1>
  <p class="lead">LeitnerFlow laesst sich in Moodle in der Regel stabil betreiben. Wenn dennoch Probleme auftreten, liegen die Ursachen haeufig in Sichtbarkeit, Rechten, Fragenkategorien oder unklaren Erwartungen an die Aktivitaet. Diese Seite bietet eine pragmatische Pruefreihenfolge.</p>
</section>

## Die haeufigsten Fehlerbilder

| Beobachtung | Typische Ursache | Erste sinnvolle Pruefung |
|---|---|---|
| Aktivitaet ist nicht auswaehlbar | Plugin nicht sauber installiert oder nicht aktualisiert | Installation, Upgrade und Plugin-Sichtbarkeit pruefen |
| Keine Fragen erscheinen | leere oder unpassende Kategorien, Konfigurationsproblem | Fragenkategorien und Pool-Inhalt kontrollieren |
| Lernende sehen die Aktivitaet nicht | Sichtbarkeit oder Rechte fehlen | Kursabschnitt, Verfuegbarkeit und Rollen pruefen |
| Fortschritt wirkt unplausibel | Fehlerlogik oder Boxenprinzip missverstanden | Konfiguration und didaktische Einfuehrung abgleichen |
| Berichtsdaten fehlen oder wirken leer | keine Sessions abgeschlossen oder Rechte fehlen | Testsession durchspielen und Rollen kontrollieren |

## Schrittfolge fuer die Diagnose

### 1. Installation und Plugin-Stand pruefen

Wenn die Aktivitaet bereits nicht in der Modulliste erscheint, beginnen Sie mit dem Grundsystem. Pruefen Sie Installationspfad, Moodle-Mitteilungen und den aktuellen Plugin-Stand. Gerade nach Updates lohnt ein Blick darauf, ob das Upgrade vollstaendig durchgelaufen ist.

### 2. Aktivitaetskonfiguration kontrollieren

Wenn die Aktivitaet angelegt werden kann, aber im Kurs nicht sinnvoll arbeitet, ist die Konfiguration der naechste Blickpunkt. Pruefen Sie insbesondere, ob geeignete Fragenkategorien ausgewaehlt wurden und ob die Session-Konfiguration zum vorhandenen Fragenpool passt.

### 3. Fragenpool gegenpruefen

LeitnerFlow ist nur so stark wie der zugrunde liegende Fragenpool. Wenn Kategorien leer, unvollstaendig oder sehr heterogen sind, entstehen schnell scheinbar technische Fehler, die in Wahrheit inhaltliche Ursachen haben. Testen Sie im Zweifel mit einer kleinen, bewusst kuratierten Kategorie.

### 4. Rollen und Sichtbarkeit pruefen

Wenn Lehrende alles sehen, Lernende aber nicht, liegt die Ursache oft in Kursrechten, Abschnittssichtbarkeit oder Aktivitaetsverfuegbarkeit. Pruefen Sie die Aktivitaet einmal gezielt aus der Rolle einer lernenden Person.

### 5. End-to-End-Test durchspielen

Viele Probleme lassen sich am schnellsten erkennen, wenn eine komplette Testsession mit realen Fragen durchlaufen wird. So wird sichtbar, ob Ansicht, Frageausspielung, Session-Abschluss und Berichtsdaten konsistent zusammenspielen.

## Spezifische Hinweise zu einzelnen Symptomen

<div class="card-grid">
  <div class="card">
    <h3>Keine Fragen trotz vorhandener Aktivitaet</h3>
    <p>Oft ist keine geeignete Kategorie zugewiesen oder der ausgewaehlte Pool enthaelt nicht die erwarteten Fragen. Beginnen Sie mit einer kleinen Testkategorie.</p>
  </div>
  <div class="card">
    <h3>Fortschritt sinkt nach Fehlern</h3>
    <p>Das kann je nach Konfiguration korrekt sein. Pruefen Sie, ob bei falscher Antwort ein Reset oder eine Rueckstufung vorgesehen ist.</p>
  </div>
  <div class="card">
    <h3>Lernende sind irritiert vom Verhalten</h3>
    <p>Dann fehlt oft keine Technik, sondern eine klare Einfuehrung. Erklaeren Sie das Leitner-Prinzip und die Rolle der Boxen noch einmal knapp im Kurs.</p>
  </div>
</div>

## Support-Checkliste fuer den Betrieb

<ul class="check-list">
  <li>Plugin-Version und Moodle-Version abgleichen.</li>
  <li>Installationspfad und erfolgreiches Upgrade bestaetigen.</li>
  <li>Aktivitaet mit realer Fragenkategorie testen.</li>
  <li>Rollen und Sichtbarkeit aus Lernendenperspektive pruefen.</li>
  <li>Mindestens eine Session bis zum Ende durchspielen.</li>
  <li>Berichtsansicht und Fortschrittsdarstellung danach kontrollieren.</li>
</ul>

## Wann eine Eskalation sinnvoll ist

Wenn Installation, Rechte, Sichtbarkeit und Fragenpool nachweislich stimmen und das Verhalten trotzdem reproduzierbar fehlerhaft bleibt, ist eine tiefergehende Analyse des Plugins sinnvoll. Fuer eine saubere Eskalation sollten dann Moodle-Version, Plugin-Stand, konkrete Aktivitaetskonfiguration und das beobachtete Verhalten moeglichst klar dokumentiert sein.
