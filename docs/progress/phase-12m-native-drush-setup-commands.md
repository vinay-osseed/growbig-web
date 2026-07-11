# Phase 12M: Native Drush Setup Commands

## Completed Scope

This phase adds native Drush commands for the site setup workflow.

## Added Commands

- `site-platform:setup-status`
- `site-platform:setup-preview`
- `site-platform:setup-run`
- `site-platform:setup-complete`
- `site-platform:setup-unlock`
- `site-platform:setup-reset-status`

## Added Aliases

- `sp-setup-status`
- `sp-setup-preview`
- `sp-setup-run`
- `sp-setup-complete`
- `sp-setup-unlock`
- `sp-setup-reset-status`

## Updated

The helper script now calls native Drush commands:

- `scripts/site-setup.sh`

## Safety

The native Drush commands follow the existing setup service behavior.

The reset command only resets setup status.

It does not delete pages, forms, roles, content, files, or submissions.

## Next Phase

Phase 12N should add setup import/export planning for YAML-based deployment defaults or begin multi-site setup row support.
