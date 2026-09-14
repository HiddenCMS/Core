# English-first HiddenCMS

## Translation Rules

English is the default source language. Installers let administrators choose English or French as the primary language, with English selected by default. Both remain enabled. The CLI accepts `--language=en` or `--language=fr`. Existing sites keep their language order, preferences and administrator-written content.

- Write interface source strings in English and use the existing `lang()` or translating control API.
- Catalogue keys are the CRC32 of the English source. Preserve placeholders and plural forms.
- Keep translation objects lazy inside addon `__info()`; eager string conversion can recursively initialize metadata.
- Translate once and JSON-encode translated values in generated JavaScript.
- Do not translate stored identifiers, technical enums, routes, product names or administrator-written content.

French translations live in component/theme `langs/fr.php` files and the shared `hiddencms/langs/fr.php`. Component overrides take precedence over the shared catalogue. An addon can explicitly declare its source language; otherwise it is English.

There is no intermediate source-language manifest or conversion dictionary. The one-off migration scripts and mapping files have been removed.

## Useful Checks

```powershell
php tools/test-i18n.php C:/wamp64/www/hNeoFrag_onwork en
php tools/test-i18n.php C:/wamp64/www/hNeoFrag_onwork fr
node tools/test-i18n-assets.cjs C:/wamp64/www/hNeoFrag_onwork
php tools/test-i18n-install.php C:/wamp64/www/hNeoFrag_onwork
php tools/i18n-audit.php
php tools/i18n-scan-text.php
```

The audit checks direct literal calls against French catalogues. The text scanner also reports source text outside catalogues and PHP comments. Neither alone certifies semantic coverage; review reported identifiers and runtime output.

The installation test imports into a temporary database and removes it. It requires development-server CREATE/DROP privileges and never imports over the existing site.

EN/FR interface tests, generated JavaScript, fresh installation defaults, isolated user tests, privacy checks and Slider tests were verified during migration. The PHP-constraint assertion requires `composer/semver`; Calendar's full recurrence suite requires its Composer dependencies.

## Runtime Details

- The language widget changes the language prefix while preserving the current route and query.
- Cookie consent fingerprints exclude translated titles/descriptions.
- Country names are translated before sorting; ICU was only used to generate source names, not added as a runtime dependency.
- Calendar follows the current site language for buttons and date formatting.
- Zone display labels are translated separately from layout identifiers.

Before release, run the available tests in each affected repository and visually review the administration/front end in English and French.
