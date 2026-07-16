# Phase 11: Setup Runner Foundation

## Goal

Add the first setup runner foundation for Site Platform.

## Commands

    site-platform:setup-preview
    site-platform:setup-run
    site-platform:setup-status
    site-platform:setup-reset-demo

## What This Phase Does

This phase introduces a dedicated setup module and Drush command surface.

The runner can:

- print the current setup plan
- count existing platform entities
- record a setup run in Drupal state
- preview demo content reset
- execute demo content reset only with an explicit option

## Demo Reset Safety

The reset command is dry-run by default.

This command only previews:

    ddev drush site-platform:setup-reset-demo

This command deletes demo records:

    ddev drush site-platform:setup-reset-demo --execute

## Current Limits

This phase does not import YAML setup files yet.

It does not yet create a full site from one setup file.

Those come in the next setup phases.
