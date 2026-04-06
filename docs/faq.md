---
layout: page
title: FAQ
description: Haeufige Fragen zu LeitnerFlow in Moodle.
---

<section class="page-hero">
  <span class="eyebrow">FAQ</span>
  <h1>Haeufige Fragen zu LeitnerFlow</h1>
  <p class="lead">Diese Antworten sind bewusst kurz gehalten und greifen typische Rueckfragen aus Einfuehrung, Kursbetrieb und Support auf. Fuer detaillierte Handlungsanweisungen verweisen die Antworten auf die passenden Guides.</p>
</section>

## Was ist der Unterschied zwischen LeitnerFlow und einem normalen Quiz?

Ein klassisches Quiz prueft vor allem einen einzelnen Bearbeitungsmoment. LeitnerFlow organisiert dagegen Wiederholung ueber mehrere Sessions hinweg. Fragen werden zu virtuellen Lernkarten, deren erneutes Auftauchen vom bisherigen Lernerfolg abhaengt. Dadurch entsteht ein kontinuierlicher Lernprozess statt einer einmaligen Messung.

## Fuer welche Inhalte eignet sich LeitnerFlow am besten?

Besonders gut funktioniert das Plugin fuer Inhalte, die regelmaessig abgerufen und gefestigt werden sollen, zum Beispiel Begriffe, Definitionen, Vokabeln, Faktenwissen, Regeln oder standardisierte Entscheidungssituationen. Weniger geeignet sind sehr offene oder stark diskursive Fragestellungen.

## Muessen fuer LeitnerFlow neue Inhalte erstellt werden?

Nein. LeitnerFlow nutzt die Moodle-Fragensammlung. In vielen Faellen kann mit bereits vorhandenen Kategorien gearbeitet werden. Oft reicht es, den vorhandenen Pool etwas zu kuratieren, damit Anspruchsniveau und Fragequalitaet besser zum wiederholungsorientierten Einsatz passen.

## Warum erscheinen manche Fragen oefter als andere?

Das ist der Kern des Leitner-Prinzips. Karten in unteren Boxen gelten als unsicher und werden deshalb frueher wiederholt. Karten in hoeheren Boxen erscheinen seltener, weil ihr Inhalt bereits stabiler sitzt. Genau diese Gewichtung macht LeitnerFlow didaktisch wirksam.

## Ist ein Rueckschritt nach einer falschen Antwort ein Problem?

Nein. Ein Rueckschritt ist kein technischer Fehler und auch keine Bestrafung, sondern ein Signal im Wiederholungsprozess. Lernende sehen dadurch, welche Inhalte noch nicht stabil genug sind. Wichtig ist, diese Logik bei der Einfuehrung klar zu kommunizieren.

## Kann LeitnerFlow benotet werden?

Ja, das Plugin unterstuetzt eine optionale Bewertungsintegration. Ob das sinnvoll ist, haengt vom Einsatzszenario ab. In vielen Faellen ist LeitnerFlow als formative Lernaktivitaet besonders stark. Wenn Sie eine Bewertung aktivieren, sollte die Logik fuer Lernende transparent erklaert werden.

## Welche Moodle-Version wird unterstuetzt?

Im Repository ist Moodle `4.4+` als technische Voraussetzung hinterlegt. Fuer oeffentliche Kommunikation empfiehlt es sich, diese Angabe regelmaessig mit der jeweils aktuellen Plugin-Version im Code abzugleichen.

## Was tun, wenn keine Fragen angezeigt werden?

Pruefen Sie zuerst, ob der Aktivitaet gueltige Fragenkategorien zugewiesen wurden und ob der zugrunde liegende Pool ueberhaupt passende Fragen enthaelt. Danach lohnt ein Blick auf Rechte, Sichtbarkeit und die konkrete Aktivitaetskonfiguration. Eine strukturierte Pruefreihenfolge finden Sie im [Troubleshooting](troubleshooting.html).

## Wie gross sollte eine Session sein?

Fuer die meisten Kurse sind `10 bis 20` Fragen pro Session ein guter Start. Die Aktivitaet bleibt damit kurz genug fuer den Alltag und lang genug, um einen merkbaren Wiederholungseffekt zu erzeugen. Bei sehr komplexen Fragen kann auch eine kleinere Session-Groesse sinnvoll sein.

## Sind mehr Boxen automatisch besser?

Nicht unbedingt. Mehr Boxen erlauben feinere Abstufungen, machen das System aber auch etwas erklaerungsbeduerftiger. Fuer viele Einfuehrungen sind `3` Boxen ein sehr guter Ausgangspunkt. Erst wenn das Prinzip etabliert ist, lohnt sich gegebenenfalls eine staerkere Differenzierung.

## Sollte bei falschen Antworten immer auf Box 1 zurueckgesetzt werden?

Der komplette Reset macht das Prinzip besonders klar und betont den Wiederholungscharakter. In sensibleren Szenarien kann eine mildere Rueckstufung sinnvoll sein, etwa "eine Box zurueck". Die passende Wahl haengt davon ab, wie motivierend oder streng das Lernformat erlebt werden soll.

## Wie fuehre ich LeitnerFlow gut im Kurs ein?

Am besten mit einer kurzen didaktischen Einordnung: Warum gibt es das Format, wie oft sollen Sessions stattfinden und warum tauchen unsichere Karten haeufiger auf? Wenn Lernende diese drei Punkte verstehen, steigt die Akzeptanz erfahrungsgemaess deutlich. Der [Guide fuer Lehrende](teacher-guide.html) hilft bei der Kommunikation im Kurs.
