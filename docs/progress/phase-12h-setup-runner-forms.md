# Phase 12H: Setup Runner Default Forms

## Completed Scope

This phase adds idempotent setup for frontend-safe default webforms.

## Default Forms

The setup runner ensures these forms exist:

- contact_us
- job_application

## Safety

The runner is safe to re-run.

If a webform already exists, the runner reuses it and does not overwrite its existing fields.

If a webform is missing, the runner creates a minimal frontend-safe version.

## Manifest

Created or existing webform IDs are tracked in setup manifest state under:

- webforms

## Frontend Contract

These forms support the existing frontend endpoints:

- GET /api/v1/forms/contact-us
- POST /api/v1/forms/contact-us/submit
- GET /api/v1/forms/job-application
- POST /api/v1/forms/job-application/submit

## Environment-specific Note

Do not export config after manually testing setup runs.

Created webforms and runtime setup state are environment-specific unless intentionally exported as shared defaults.

## Next Phase

Phase 12I should add setup completion, lock, and verification status.
