# Phase 12: YAML Setup Import Foundation

## Goal

Add the first YAML-driven setup import command.

## Command

    site-platform:setup-import /path/to/setup.yml

## What This Phase Imports

This foundation imports:

- Site Profile
- Site Pages
- Site Content Blocks

## Stable Identity

Records are updated by stable keys.

Site Profile:

    site.key

Site Page:

    site.key + page.key

Site Content Block:

    site.key + source + key

## Example

    ddev drush site-platform:setup-import /var/www/html/setup/examples/sample.site.yml

## Dry Run

The command supports dry-run mode:

    ddev drush site-platform:setup-import /var/www/html/setup/examples/sample.site.yml --dry-run

Dry-run parses the YAML and prints the same summary, but does not save records.

## Current Limits

This phase does not import:

- menus
- forms
- components
- media/images
- translations
- users/roles

Those can be added in later setup phases.
