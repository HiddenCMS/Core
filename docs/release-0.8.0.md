# HiddenCMS 0.8.0

## Highlights

- English is now the default source language, with French translation catalogues across the administration, frontend and official addons.
- A language widget switches language while preserving the current route and query.
- Sign-in pages support a logo, a home link and safe return navigation.
- New modular HiddenCMS logo and matching SVG favicon.
- Redesigned responsive web installer with progress indicators, remembered light/dark mode, visible errors, retry support and server logging.
- Web and interactive CLI installers let administrators choose English or French as the primary language.
- French accents and publication controls are harmonized.

## Upgrade

- Requires PHP 8.1 or newer and Altitude 0.4.
- Updated official addon releases require Core 0.8; update the core and addons together.
- Existing language order and administrator-written content are preserved.
- Migration 0.8.0 registers the language widget without duplicating existing entries.
- Temporary translation conversion files are no longer used.

## Validation

EN/FR runtime checks, generated JavaScript, isolated database installation, publication labels, PHP syntax and responsive installer previews were checked. Review tools remain available in tools/ and docs/english-first.md.
