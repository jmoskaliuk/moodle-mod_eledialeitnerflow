# mod_eledialeitnerflow — PHPUnit Tests

## Übersicht

| Datei | Inhalt |
|---|---|
| `tests/engine/leitner_engine_test.php` | 22 Tests für die Kernlogik (Leitner-Engine) |
| `tests/generator/lib.php` | Test-Datengenerator für schnelles Anlegen von Testdaten |
| `phpunit.xml` | Test-Suite-Konfiguration |

---

## Einmalige Einrichtung

```bash
# 1. In den Moodle-Root wechseln
cd /var/www/html/moodle   # dein Moodle-Pfad

# 2. Plugins installieren (falls noch nicht geschehen)
#    → mod/eledialeitnerflow/ und blocks/eledialeitnerflow/ kopieren
#    → Im Browser als Admin: Site administration → Notifications

# 3. Test-Datenbank initialisieren (einmalig, danach nur bei DB-Schema-Änderungen)
php admin/tool/phpunit/cli/init.php
```

---

## Tests ausführen

```bash
# Alle Tests für das Plugin
vendor/bin/phpunit --testsuite mod_eledialeitnerflow_testsuite

# Alle Tests mit lesbarer Ausgabe (empfohlen)
vendor/bin/phpunit --testdox --testsuite mod_eledialeitnerflow_testsuite

# Nur die Engine-Tests
vendor/bin/phpunit mod/eledialeitnerflow/tests/engine/leitner_engine_test.php

# Einzelnen Test ausführen (--filter = Name der Testmethode)
vendor/bin/phpunit --filter test_correct_answer_advances_box

# Mit Xdebug-Coverage-Report
vendor/bin/phpunit --coverage-html mod/eledialeitnerflow/tests/coverage/ \
    --testsuite mod_eledialeitnerflow_testsuite
```

---

## Erwartete Ausgabe (--testdox)

```
mod_eledialeitnerflow\tests\engine\leitner_engine_test
 ✓ calculate box [0 correct, 3 boxes → box 1]
 ✓ calculate box [1 correct, 3 boxes → box 1]
 ✓ calculate box [2 correct, 3 boxes → box 2]
 ✓ calculate box [at threshold stays in last box]
 ✓ calculate box [negative correct → box 1]
 ...
 ✓ correct answer on new card increments correctcount
 ✓ correct answer advances card to next box
 ✓ reaching threshold marks card as learned
 ✓ learned card stays in highest box
 ✓ wrong answer with reset behavior
 ✓ wrong answer with back one behavior
 ✓ wrong answer with no change behavior
 ✓ wrong answer on fresh card with back1 stays at box 1
 ✓ correct after reset rebuilds progress
 ✓ save and reload card state
 ✓ save card state updates existing record
 ✓ get card state returns null for unknown
 ✓ get user stats counts correctly
 ✓ get user stats zero total gives zero percent
 ✓ get box distribution groups cards correctly
 ✓ select session respects session size limit
 ✓ learned cards excluded from session
 ✓ delete user data removes all records
 ✓ attempt count increments on every answer
 ✓ card states are isolated per user
 ✓ process answer on already learned card stays learned
 ✓ boxcount 5 distributes correctly
```

---

## Wenn Tests fehlschlagen

```bash
# Test-DB neu aufbauen (bei Schema-Änderungen nötig)
php admin/tool/phpunit/cli/init.php --run

# Nur Tabellen neu anlegen ohne Datenverlust
php admin/tool/phpunit/cli/util.php --buildcomponentconfigs
```

---

## Neue Tests hinzufügen

1. Neue Methode in `leitner_engine_test.php` anlegen, Name beginnt mit `test_`
2. Testdaten über `$this->getDataGenerator()` oder den Plugin-Generator anlegen
3. Assertions mit `$this->assertEquals()`, `$this->assertCount()` etc. schreiben
4. `vendor/bin/phpunit --filter test_mein_neuer_test` ausführen
