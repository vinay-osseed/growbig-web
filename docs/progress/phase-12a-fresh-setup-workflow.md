# Phase 12A: Fresh Setup Workflow

## Completed Planning Scope

This phase defines the first-run setup workflow after Drupal is installed.

The workflow supports:

- production deployment
- local/development setup
- non-technical admin setup through Drupal UI
- developer setup through scripts/config
- single-site setup
- multi-site setup
- decoupled frontend rendering requirements
- setup retry
- setup reset
- setup locking
- setup-created data manifest
- backup-aware production setup

## Key Decision

The setup workflow should require only minimal user input, but it must always generate frontend-safe defaults.

Required from user:

- site identity
- URLs
- primary contact email
- country
- setup choices

Required for frontend:

- site API values
- menu values
- page structure
- form metadata
- analytics config
- empty-safe arrays and fallback values

## Main Documentation

See:

- `docs/fresh-setup-workflow.md`

## Next Phase

Phase 12B should add setup config/status foundations.

Likely next files:

- setup config schema
- setup status config
- setup manifest structure
- setup permissions
- setup route placeholder
