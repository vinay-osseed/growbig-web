# Phase 37: Final Backend Readiness Closeout

## Goal

Close the current Site Platform backend/admin rework track with one final acceptance verifier.

This phase does not change active Drupal configuration and does not import or export config. It validates the runtime state already built through the previous phases:

- Site Platform admin workspace routes
- clickable CRUD/admin links
- native Webform-first form workflow
- legacy compatibility wrapper isolation
- major public API endpoints
- managed record availability

## What This Adds

- `scripts/setup/verify-final-backend-readiness.sh`
- `docs/admin/site-platform-final-operator-checklist.md`
- this implementation note

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
./scripts/setup/verify-final-backend-readiness.sh
```

Expected:

```text
Final backend readiness verification passed.
```

## Acceptance Meaning

When this verifier passes, the backend/admin track is considered deadline-ready for the current Site Platform v2 checkpoint.

Future deeper improvements such as fully replacing legacy menu wrappers with Drupal core menu entities or moving media metadata into Drupal Media Library can be handled as enhancements without blocking this checkpoint.
