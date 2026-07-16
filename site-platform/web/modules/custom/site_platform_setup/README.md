# Site Platform Setup

## Purpose

Site Platform Setup provides Drush commands for the setup lifecycle.

This phase adds the setup runner foundation only.

## Commands

    ddev drush site-platform:setup-preview
    ddev drush site-platform:setup-run
    ddev drush site-platform:setup-status
    ddev drush site-platform:setup-reset-demo

## Current Scope

The runner can:

- show the setup plan
- summarize current platform entity counts
- record a setup run in Drupal state
- dry-run demo content reset
- delete demo content only when explicitly requested with `--execute`

## Safety

`site-platform:setup-reset-demo` is dry-run by default.

It only deletes demo records when called with:

    ddev drush site-platform:setup-reset-demo --execute

## Later Phases

Later setup phases can add:

- YAML setup import
- full idempotent site setup
- site reset by key
- setup completion tracking
- demo content replacement
