# DevFlow — mod_eledialeitnerflow (LeitnerFlow)

Projekt-DevFlow nach [eLeDia.OS_DevFlow](https://github.com/jmoskaliuk/eLeDia.OS_DevFlow).
Der Rahmen selbst liegt in `~/Documents/Code/eLeDia.OS_DevFlow`.

Markdown ist die Quelle der Wahrheit. Generierte Dateien wie HTML-Exporte sind
Artefakte und werden nie zur Pflegestelle.

## Die fünf Kernperspektiven

| Datei | Frage |
|---|---|
| [`01-features.md`](01-features.md) | Was bauen wir und warum? |
| [`02-user-doc.md`](02-user-doc.md) | Wie bedient man es? |
| [`03-dev-doc.md`](03-dev-doc.md) | Wie ist es implementiert? |
| [`04-tasks.md`](04-tasks.md) | Was tun wir gerade, was ist offen? |
| [`05-quality.md`](05-quality.md) | Funktioniert es, und was ist kaputt? |

## Verbindliche Querschnittsdateien

| Datei | Frage |
|---|---|
| [`00-master.md`](00-master.md) | Eintrittspunkt: Rollen, ADRs, Prompt-Shortcuts, Checklisten |
| [`product.md`](product.md) | Welches Produkt, für wen, mit welchem Versprechen? |
| [`screenshots.md`](screenshots.md) | Welche Bilder gibt es, auf welchem Stand? |
| [`privacy.md`](privacy.md) | Welche personenbezogenen Daten fließen wohin? |
| [`security.md`](security.md) | Schutzwerte, Bedrohungen, Maßnahmen |
| [`changelog.md`](changelog.md) | Was hat sich je Plugin-Version technisch geändert? |
| [`releasenotes.md`](releasenotes.md) | Was müssen Betrieb und Nutzende je Release wissen? |

## Weitere Dateien in diesem Ordner

| Datei | Zweck |
|---|---|
| `product.de.md` | Katalog-/PluginFlow-Ableitung von `product.md`, mit YAML-Frontmatter |
| `user-doc.css` | Styling des HTML-Exports der Nutzerdoku |
| `user-doc.html` | generiertes Artefakt — nicht bearbeiten, in `.gitignore` |
| `screenshots/` | Bildmaterial (aktuell leer → `task01`) |

## Warum der DevFlow hier liegt und nicht im Projekt-Root

Bei anderen eLeDia-Plugins liegt der DevFlow im Projekt-Root neben dem
Plugin-Verzeichnis, damit er nicht im Release-ZIP landet. Hier **ist** der
Repo-Root das Plugin-Verzeichnis — ein Root-Ordner wäre Teil des Plugins.
`docs/` ist in `.gitattributes` bereits als `export-ignore` markiert, `git
archive` in `bin/release.sh` lässt den Ordner also weg. Begründung als `adr10`
in `00-master.md`.

Die ausgelieferte Dokumentation bleibt davon unberührt: `README.md`,
`README-DE.md`, `CHANGELOG.md` und `upgrade.txt` im Repo-Root richten sich an
die Person, die das Plugin installiert; dieser Ordner an die, die es
weiterentwickelt.

## HTML-Export der Nutzerdoku bauen

```bash
bash bin/build-user-doc.sh
```

Baut `docs/user-doc.html` aus `docs/02-user-doc.md` mit Pandoc. Immer die
Markdown-Datei bearbeiten, nie das HTML.

## Einstieg

1. [`00-master.md`](00-master.md) lesen
2. [`product.md`](product.md) — welches Produkt, für wen, welcher Umfang
3. [`04-tasks.md`](04-tasks.md) — offene Tasks und `qXX`
4. `#status` für den aktuellen Stand, `#next` für die nächsten Schritte
