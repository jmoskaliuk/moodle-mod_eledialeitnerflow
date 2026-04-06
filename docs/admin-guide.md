---
layout: page
title: Guide fuer Administrator:innen
description: LeitnerFlow installieren, betreiben und sauber in Moodle ausrollen.
---

<section class="page-hero">
  <span class="eyebrow">Administration</span>
  <h1>LeitnerFlow installieren und betrieblich sauber einfuehren</h1>
  <p class="lead">Dieser Guide richtet sich an Administrator:innen und technische Verantwortliche, die LeitnerFlow in einer Moodle-Umgebung bereitstellen, aktualisieren und nachvollziehbar betreiben wollen. Der Fokus liegt auf einem robusten Rollout ohne zusaetzliche Infrastrukturkomplexitaet.</p>
</section>

## Technischer Rahmen

LeitnerFlow ist ein Moodle-Modul und orientiert sich eng an den ueblichen Moodle-Mechanismen fuer Installation, Rechte, Frageverarbeitung und Reporting. Das Plugin benoetigt laut Repository eine Umgebung ab Moodle `4.4` und wird in den Modulpfad `mod/eledialeitnerflow` eingebunden.

| Bereich | Empfehlung |
|---|---|
| Moodle-Version | ab 4.4, vor Einfuehrung in Testumgebung pruefen |
| Installationspfad | `mod/eledialeitnerflow` |
| Einfuehrung | zuerst Staging oder Testsystem, danach produktiv |
| Verantwortlichkeit | technische Bereitstellung mit enger Abstimmung zur Didaktik |

## Installation

Die Installation folgt dem ueblichen Moodle-Vorgehen fuer Aktivitaetsmodule. Nach dem Einspielen des Quellcodes erkennt Moodle das Plugin beim Aufruf der Mitteilungen und fuehrt die erforderlichen Installationsschritte aus.

1. Laden Sie das Repository in den Pfad `mod/eledialeitnerflow` Ihrer Moodle-Installation.
2. Rufen Sie in Moodle `Website-Administration -> Mitteilungen` auf.
3. Lassen Sie das Upgrade durchlaufen und pruefen Sie, ob die Aktivitaet anschliessend in Kursen verfuegbar ist.
4. Legen Sie eine Testaktivitaet an und kontrollieren Sie den End-to-End-Ablauf aus Admin- oder Trainer-Sicht.

<div class="highlight-box">
  <p><strong>Empfehlung fuer den Rollout:</strong> Testen Sie die Aktivitaet nicht nur auf Installationsfehler, sondern auch mit einer echten Fragenkategorie und mindestens einer abgeschlossenen Session. Gerade bei Lernaktivitaeten zeigt sich Betriebsqualitaet erst im kompletten Ablauf.</p>
</div>

## Updates und Versionspflege

Fuer einen professionellen Betrieb sollte die oeffentliche Dokumentation sich immer an der technischen Wahrheit im Repository orientieren. Im vorliegenden Code ist die Release-Angabe in `version.php` massgeblich. Wenn README und Code voneinander abweichen, sollte die Kommunikation nach dem technisch verbindlichen Stand ausgerichtet werden.

Bei Updates empfiehlt sich daher ein kurzer Standardprozess:

<ul class="check-list">
  <li>Release-Stand in `version.php` pruefen.</li>
  <li>Upgrade-Hinweise und Changelog gegenlesen.</li>
  <li>Update zuerst in einer nicht-produktiven Umgebung einspielen.</li>
  <li>Eine bestehende Aktivitaet mit Lernfortschritt testweise oeffnen.</li>
  <li>Erst danach in produktive Kurse ausrollen.</li>
</ul>

## Rechte und Rollen

LeitnerFlow laesst sich gut in bestehende Moodle-Rollenmodelle einordnen. Relevant sind vor allem Berechtigungen fuer das Anlegen der Aktivitaet, das Bearbeiten von Einstellungen, das Bearbeiten von Sessions und das Einsehen von Berichten. Fuer den produktiven Betrieb sollte klar sein, welche Rolle nur Inhalte nutzt und welche Rolle in Fortschritte oder Reset-Funktionen eingreifen darf.

<div class="info-grid">
  <div class="card">
    <h3>Kursverantwortliche</h3>
    <p>Benoetigen typischerweise Rechte zum Anlegen und Verwalten der Aktivitaet sowie zum Einsehen von Berichten.</p>
  </div>
  <div class="card">
    <h3>Lernende</h3>
    <p>Brauchen Zugriff auf Ansicht und Bearbeitung, aber in der Regel keine administrativen Eingriffe in Konfiguration oder Fortschrittsdaten.</p>
  </div>
  <div class="card">
    <h3>Support und Betrieb</h3>
    <p>Sollten wissen, welche Rolle fuer Fehleranalyse, Sichtbarkeit und gegebenenfalls Fortschritts-Resets vorgesehen ist.</p>
  </div>
</div>

## Was vor dem Rollout geprueft werden sollte

Ein sauberer Rollout ist nicht nur eine technische, sondern auch eine kommunikative Aufgabe. Pruefen Sie daher neben Installation und Rechten auch, ob Lehrende geeignete Fragenkategorien zur Verfuegung haben und ob klar ist, wie LeitnerFlow im Kurs erklaert werden soll.

| Pruefpunkt | Warum er wichtig ist |
|---|---|
| Aktivitaet im Kurs waehlbar | Basis fuer jeden weiteren Test |
| Fragenkategorien vorhanden | Ohne passenden Pool kann keine sinnvolle Session entstehen |
| Rechte fuer Lehrende korrekt | Sonst scheitert die Einrichtung oft erst im Kurs |
| Lernendenansicht getestet | Sichtbarkeit und Session-Ablauf frueh pruefen |
| Bericht und Reset-Funktionen getestet | Relevant fuer Support und Betreuung |

## Datenschutz, Backup und Moodle-Integration

Das Plugin ist nah an den Moodle-Standards entwickelt und bringt laut Repository Unterstuetzung fuer Privacy API, Backup und Restore sowie Event-Logging mit. Das ist fuer den Betrieb wichtig, weil LeitnerFlow damit nicht als isolierte Sonderloesung, sondern als integrierter Teil der Plattform behandelt werden kann.

Im Alltag bedeutet das: Backups sollten LeitnerFlow-Aktivitaeten mitdenken, Testwiederherstellungen sollten Bestandteil groesserer Moodle-Upgradezyklen sein und Support-Teams sollten wissen, dass Session- und Fortschrittsdaten Teil des Betriebsbilds sind.

## Wenn Probleme auftreten

Die haeufigsten Stoerungen sind in der Regel keine Installationsdefekte, sondern Konfigurations-, Rechte- oder Fragepool-Themen. Beginnen Sie die Analyse deshalb mit Sichtbarkeit, Kategorien und Rollen, bevor Sie von einem tieferen Softwarefehler ausgehen. Die passende Schrittfolge finden Sie im [Troubleshooting](troubleshooting.html).

## Empfohlener naechster Schritt

Wenn LeitnerFlow technisch bereitsteht, sollten Lehrende mit dem [Guide fuer Lehrende](teacher-guide.html) weiterarbeiten. So wird aus einer erfolgreichen Installation auch eine didaktisch stimmige Einfuehrung.
