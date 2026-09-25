# HiddenCMS Core 0.9.11

- Add the `files-sync` CLI command to index media folders copied directly into `upload/files`.
- Normalize imported physical filenames while preserving their original display names.
- Generate unique, URL-safe public media links and retain compatibility with legacy links.
- Add collapsible media-library folders and automatically expand the current branch.
- Remove the legacy Azuro theme and migrate existing Azuro sites and outlines to Altitude.

## Compatibility

PHP >=8.1; Altitude ^0.5. Database schema 0.9.11 with an automatic Azuro-to-Altitude migration.

## Validation

PHP syntax, JavaScript syntax, Composer metadata, media synchronization, public media URLs and git diff checks pass.
