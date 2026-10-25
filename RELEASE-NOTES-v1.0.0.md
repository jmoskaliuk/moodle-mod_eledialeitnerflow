# LeitnerFlow 1.0.0 — First public release

LeitnerFlow brings the **Leitner spaced-repetition method** into Moodle as a first-class activity module. Questions from the Question Bank become virtual flashcards that move through a configurable series of boxes — correct answers advance a card, wrong answers send it back. Cards in lower boxes appear more frequently, so students naturally spend more time on what they find hardest. Once a card has been answered correctly enough times, it graduates to *Learned*.

This is the first public release on the Moodle Plugins Directory / Marketplace. Earlier 1.x–2.x iterations existed as internal eLeDia pre-releases; the consolidated feature set below is what ships as 1.0.0.

---

## For learners

- **Visual Leitner board** with per-box card counts and a multi-segment progress bar.
- **Clickable boxes** to practise a single difficulty level in isolation.
- **Session history** with per-session correct rate, duration, and a recent-vs.-all-time trend indicator.
- **Five feedback styles** — *Off*, *Minimal*, *Animated*, *Detailed*, *Gamified* — with configurable animation delay. Gamified mode tracks current and best streaks per session.
- **Guided first-visit user tour** delivered via `tool_usertours`, auto-imported on install/upgrade.

## For teachers

- **Multi-category question sourcing** — pull cards from one or many Question Bank categories simultaneously.
- **Dynamic or fixed question pools**, configurable box count (1–5), session size, correct-answers threshold, wrong-answer behaviour (reset / back one / no change), and card-selection strategy (lower boxes first / mixed).
- **Per-student report** with progress bars, session counts, last-session timestamp, and a reset-progress action per participant.
- **Summary dashboard** with participant count, pool size, and average learned percentage.
- **Optional gradebook integration** (percentage of cards learned).

## Platform integration

- Activity-module **backup and restore** support.
- **Privacy API / GDPR compliant** — metadata, export, deletion, and userlist provider implemented.
- **Course-reset support** — per-activity progress and session history can be wiped through the standard course-reset form.
- **Event logging**: `session_started`, `session_completed`, `progress_reset`.
- Full capability model: `view`, `attempt`, `viewreport`, `manage`, `resetprogress`, `addinstance`.

---

## Installation

1. Download the release ZIP (attached to this release) or clone the repository into `mod/eledialeitnerflow` under your Moodle install.
2. Visit **Site Administration → Notifications** to trigger the database upgrade.
3. Create a LeitnerFlow activity in any course and select your Question Bank categories.

## Compatibility

| Component | Minimum | Tested against |
|-----------|---------|----------------|
| Moodle | 4.4 (`2024042200`) | 5.1 |
| PHP | 8.1 | 8.3 |
| Database | MySQL 8 / MariaDB 10.6 / PostgreSQL 13+ | PostgreSQL 18 |

No external services, no paid dependencies, no Composer required.

## Code-quality snapshot

- `phpcs --standard=moodle`: **0 errors, 0 warnings**
- PHPUnit: **37 tests, 81 assertions** — all green
- Moodle Plugin Directory prechecks: all green (phplint, phpcs, phpdoc, savepoint, js/eslint, css/stylelint, grunt AMD build, thirdparty)

## Upgrade notes

This is the first public release — no upgrade from earlier public versions. Internal eLeDia sites that had a pre-1.0 build installed will be automatically cleaned up by the 2026042101 upgrade step, which de-duplicates accumulated `tool_usertours` entries (a historical side-effect of non-idempotent tour installation).

## Links

- **Source code**: https://github.com/jmoskaliuk/moodle-mod_eledialeitnerflow
- **Issue tracker**: https://github.com/jmoskaliuk/moodle-mod_eledialeitnerflow/issues
- **Documentation**: See `README.md` (English) / `README-DE.md` (Deutsch) in the repo
- **Maintainer**: [eLeDia GmbH](https://eledia.de)

## License

GNU GPL v3 or later.
