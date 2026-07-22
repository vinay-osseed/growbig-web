# Phase 38: Final Completion Verifier

## Goal

Finish the current Site Platform v2 backend/admin checkpoint with one strict, visible final verifier.

This phase does not change Drupal active configuration and does not import or export config. It adds a final verification script that directly checks the runtime state instead of hiding nested verifier output.

## What This Adds

- `scripts/setup/verify-site-platform-final-completion.sh`
- this implementation note

## What It Checks

The verifier checks:

- Site Platform admin controller PHP syntax
- safe clickable CRUD helper methods
- no `Drupal\\Core\\Url` import in table rows
- no `Link::fromTextAndUrl()` usage
- required admin routes exist
- admin pages render through Drupal
- rendered admin output contains clickable links
- managed node bundle counts are readable
- Webform is enabled and has at least one Webform
- core public API endpoints return HTTP 200 locally
- repository whitespace check passes

## What This Avoids

This phase intentionally avoids:

- `drush cim`
- `drush cex`
- `site-platform/config/sync` changes
- `config/install` changes
- active Drupal configuration mutation

## Verification

Run:

```bash
./scripts/setup/verify-site-platform-final-completion.sh
```

Expected final line:

```text
Site Platform final completion verification passed.
```

When this passes, the current backend/admin track is closed for this checkpoint.
