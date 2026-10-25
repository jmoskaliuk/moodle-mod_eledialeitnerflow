---
slug: leitnerflow
component: mod_eledialeitnerflow
title: LeitnerFlow
tagline: "Karteikartenlernen nach Leitner — direkt aus der Moodle-Fragensammlung."
status: Verfügbar
group: student
groupLabel: Studierende
icon: layers
audience: [Lernende, Lehrende]
catalogFeed: false
suite: pruefungen
tags: [lernen, wiederholung]
pakete: [lms]
demo: leitnerflow
docsBase: de
moodle: "4.4+"
php: "8.1+"
licence: GPL-3.0-or-later
release: "1.0.0"

forWhom:
  Lernende: >-
    Kurze, fokussierte Lerneinheiten statt langer Wiederholungssitzungen. Das
    Dashboard zeigt jederzeit, wie viele Karten in welchem Fach liegen und wie
    weit der eigene Fortschritt ist.
  Lehrende: >-
    Eine Aktivität anlegen, Fragenkategorien auswählen — fertig. Die Fragen aus
    der Fragensammlung werden weiterverwendet, es entsteht kein zweiter
    Inhaltsbestand, der gepflegt werden muss.
  Entscheider: >-
    Verteiltes Lernen ohne externen Dienst: alle Lerndaten bleiben in der
    eigenen Moodle-Instanz, mit Privacy-API, Backup/Restore und Event-Logging.

useCases:
  - "Prüfungsvorbereitung mit verteilter Wiederholung über mehrere Wochen"
  - "Vokabel- und Faktenlernen im Sprach- oder Fachkurs"
  - "Auffrischung von Sicherheits- oder Compliance-Wissen in Pflichtschulungen"

features:
  - title: "Fächer statt Fragenliste"
    text: >-
      Jede Frage wird zu einer Karte in Fach 1. Richtig beantwortet wandert sie
      weiter, falsch beantwortet zurück. Karten in niedrigen Fächern erscheinen
      häufiger — Lernende arbeiten automatisch an dem, was ihnen schwerfällt.
  - title: "Drei Strategien bei falscher Antwort"
    text: >-
      Zurück auf Fach 1, ein Fach zurück oder unverändert lassen. Damit lässt
      sich dieselbe Aktivität für hartes Prüfungstraining und für lockere
      Auffrischung einsetzen.
  - title: "Fortschritt, der etwas aussagt"
    text: >-
      Fächerverteilung, mehrstufiger Fortschrittsbalken, Sitzungshistorie mit
      Richtig-Quote und Dauer sowie ein Trend gegen den eigenen Durchschnitt.
  - title: "Gezielt üben"
    text: >-
      Fächer sind anklickbar. Wer weiß, dass Fach 2 hakt, übt genau dort weiter,
      statt eine gemischte Sitzung zu starten.
  - title: "Teilnehmendenbericht für Lehrende"
    text: >-
      Fortschritt, Anzahl der Sitzungen und die Möglichkeit, den Lernstand
      einzelner Personen zurückzusetzen — ohne Eingriff in die Fragensammlung.
  - title: "Sauber in Moodle eingebettet"
    text: >-
      Question Engine mit Sofort-Feedback, optionale Bewertungsintegration,
      Backup und Restore, Kurs-Reset, Event-Logging und eine geführte
      Einführungstour beim ersten Besuch.

proven:
  model: "2026.2"
  reviewed: 2026-08-21
  scores:
    purpose_fit:
      points: 3
      public: "Eng umrissener Lernzweck, für Lernende und Lehrende sofort verständlich."
      evidence: "Ein Anwendungsfall, keine Feature-Sammlung; modulename_help beschreibt ihn vollständig."
    moodle_compatibility:
      points: 2
      public: "Läuft ab Moodle 4.4."
      evidence: "version.php deklariert requires 2024042200 und MATURITY_STABLE, aber kein $plugin->supported — die beworbene Spanne ist damit nicht maschinenlesbar belegt."
    privacy_and_gdpr:
      points: 3
      public: "Vollständiger Privacy-Provider, Export und Löschung werden unterstützt."
      evidence: "classes/privacy/provider.php implementiert request\\plugin\\provider."
    security_permissions:
      points: 3
      public: "Eigene Capabilities, durch Behat abgesichert."
      evidence: "db/access.php plus tests/behat/capabilities.feature."
    install_upgrade:
      points: 3
      public: "Saubere Installation und Upgrade-Pfad."
      evidence: "db/install.xml, db/upgrade.php und db/access.php vorhanden."
    code_quality_tests:
      points: 2
      public: "Behat deckt die Nutzerpfade ab, die Unit-Tests konzentrieren sich auf die Lernlogik."
      evidence: "5 Behat-Features, aber nur eine PHPUnit-Datei (engine/leitner_engine_test.php). Breite fehlt außerhalb der Engine."
    usability_docs_mobile:
      points: 2
      public: "Gepflegte Nutzerdokumentation, Einführungstour in DE und EN."
      evidence: "docs/02-user-doc.md vollständig; docs/screenshots/ ist leer und Moodle-App-Unterstützung ist nicht verifiziert."
    operations_support:
      points: 2
      evidence: "Event-Logging und Kurs-Reset vorhanden; keine Lastdaten für große Fragenpools erhoben."
---

## Hero

LeitnerFlow bringt das Karteikastenprinzip nach Sebastian Leitner in Moodle —
ohne dass jemand einen zweiten Inhaltsbestand pflegen muss. Die Fragen liegen
schon in der Fragensammlung; LeitnerFlow macht Karten daraus.

## Das Problem

Wiederholung ist der Teil des Lernens, der am zuverlässigsten wirkt und am
seltensten stattfindet. In Moodle steht dafür üblicherweise das Quiz zur
Verfügung — aber ein Quiz ist eine Prüfung: es hat einen Anfang, ein Ende und
eine Note. Wer damit über Wochen wiederholen will, legt entweder dieselbe
Fragenmenge mehrfach an oder verlässt Moodle und arbeitet mit einer externen
Karteikarten-App. Beim ersten Weg wächst der Pflegeaufwand, beim zweiten
verlassen die Lerndaten die eigene Instanz.

## Die Lösung

LeitnerFlow ist eine eigene Aktivität, die genau eine Sache tut: verteiltes
Wiederholen. Sie greift auf die vorhandenen Fragenkategorien zu, verwaltet pro
Person einen Kartenbestand über mehrere Fächer und zeigt in kurzen Sitzungen
bevorzugt das, was noch nicht sitzt. Die Fragen bleiben dort, wo sie gepflegt
werden — in der Fragensammlung. Der Lernstand bleibt dort, wo er hingehört —
in der Moodle-Instanz.
