# Phase 24: Config and Release Readiness

## Goal

Add a safe pre-release configuration sanity check for the backend deadline.

This phase confirms the expected repository layout and important release files exist:

- Drupal project root under `site-platform/`
- DDEV/settings base files
- config sync/split directories
- setup YAML example
- custom Site Platform modules
- Webform dependency presence in the Drupal Composer project

## Local Script

    ./scripts/setup/verify-config-release-readiness.sh

## Safety

This phase does not export config, import config, delete files, or alter the database.
